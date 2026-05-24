<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class Course extends Model
{
    public function all(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (! empty($filters['status'])) {
            $where[] = 'courses.status = :status';
            $params['status'] = $filters['status'];
        }

        if (! empty($filters['category'])) {
            $where[] = 'courses.category = :category';
            $params['category'] = $filters['category'];
        }

        if (! empty($filters['teacher_id'])) {
            $where[] = 'courses.responsible_teacher_id = :teacher_id';
            $params['teacher_id'] = (int) $filters['teacher_id'];
        }

        $sql = 'SELECT courses.*,
                       teacher.full_name AS teacher_name,
                       creator.full_name AS creator_name,
                       COUNT(DISTINCT course_modules.id) AS modules_count,
                       COUNT(DISTINCT lessons.id) AS lessons_count
                FROM courses
                LEFT JOIN users teacher ON teacher.id = courses.responsible_teacher_id
                LEFT JOIN users creator ON creator.id = courses.creator_id
                LEFT JOIN course_modules ON course_modules.course_id = courses.id
                LEFT JOIN lessons ON lessons.course_id = courses.id';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY courses.id, teacher.full_name, creator.full_name
                  ORDER BY courses.created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function published(): array
    {
        $statement = $this->db->query(
            'SELECT courses.*,
                    teacher.full_name AS teacher_name,
                    COUNT(DISTINCT course_modules.id) AS modules_count,
                    COUNT(DISTINCT lessons.id) AS lessons_count
             FROM courses
             LEFT JOIN users teacher ON teacher.id = courses.responsible_teacher_id
             LEFT JOIN course_modules ON course_modules.course_id = courses.id
             LEFT JOIN lessons ON lessons.course_id = courses.id
             WHERE courses.status = "publicado"
             GROUP BY courses.id, teacher.full_name
             ORDER BY courses.created_at DESC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT courses.*,
                    teacher.full_name AS teacher_name,
                    creator.full_name AS creator_name
             FROM courses
             LEFT JOIN users teacher ON teacher.id = courses.responsible_teacher_id
             LEFT JOIN users creator ON creator.id = courses.creator_id
             WHERE courses.id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $id]);
        $course = $statement->fetch();

        return $course ?: null;
    }

    public function findPublished(int $id): ?array
    {
        $course = $this->find($id);

        return $course && $course['status'] === 'publicado' ? $course : null;
    }

    public function create(array $data): int
    {
        $slug = $this->uniqueSlug($data['title']);

        $statement = $this->db->prepare(
            'INSERT INTO courses (
                creator_id, responsible_teacher_id, title, slug, description, category, level,
                workload_hours, visibility, access_level, status, price, image_path, created_at, updated_at
             ) VALUES (
                :creator_id, :responsible_teacher_id, :title, :slug, :description, :category, :level,
                :workload_hours, :visibility, :access_level, :status, :price, :image_path, NOW(), NOW()
             )'
        );

        $statement->execute([
            'creator_id' => $data['creator_id'],
            'responsible_teacher_id' => $data['responsible_teacher_id'] ?: null,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'],
            'category' => $data['category'],
            'level' => $data['level'],
            'workload_hours' => $data['workload_hours'],
            'visibility' => 'privado',
            'access_level' => $data['access_level'] ?? 'gratuito',
            'status' => $data['status'],
            'price' => $data['price'],
            'image_path' => $data['image_path'] ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $statement = $this->db->prepare(
            'UPDATE courses
             SET responsible_teacher_id = :responsible_teacher_id,
                 title = :title,
                 description = :description,
                 category = :category,
                 level = :level,
                 workload_hours = :workload_hours,
                 access_level = :access_level,
                 status = :status,
                 price = :price,
                 image_path = COALESCE(:image_path, image_path),
                 updated_at = NOW()
             WHERE id = :id'
        );

        $statement->execute([
            'responsible_teacher_id' => $data['responsible_teacher_id'] ?: null,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'level' => $data['level'],
            'workload_hours' => $data['workload_hours'],
            'access_level' => $data['access_level'] ?? 'gratuito',
            'status' => $data['status'],
            'price' => $data['price'],
            'image_path' => $data['image_path'] ?: null,
            'id' => $id,
        ]);

        return $statement->rowCount() >= 0;
    }

    public function deactivate(int $id): bool
    {
        $statement = $this->db->prepare('UPDATE courses SET status = "arquivado", updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    public function categories(): array
    {
        $statement = $this->db->query(
            'SELECT DISTINCT category
             FROM courses
             WHERE category IS NOT NULL AND category <> ""
             ORDER BY category'
        );

        return array_column($statement->fetchAll(), 'category');
    }

    public function teachers(): array
    {
        $statement = $this->db->query(
            'SELECT users.id, users.full_name
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE roles.slug = "professor" AND users.status = "aprovado"
             ORDER BY users.full_name'
        );

        return $statement->fetchAll();
    }

    public function structure(int $courseId, bool $publishedOnly = false): array
    {
        $modules = (new CourseModule())->forCourse($courseId);
        $lessons = (new Lesson())->forCourse($courseId, $publishedOnly ? 'publicada' : null);
        $materials = (new Material())->forCourse($courseId);

        foreach ($lessons as &$lesson) {
            $lesson['materials'] = array_values(array_filter(
                $materials,
                static fn (array $material): bool => (int) $material['lesson_id'] === (int) $lesson['id']
            ));
        }

        unset($lesson);

        foreach ($modules as &$module) {
            $module['lessons'] = array_values(array_filter(
                $lessons,
                static fn (array $lesson): bool => (int) $lesson['module_id'] === (int) $module['id']
            ));
        }

        unset($module);

        $unassignedLessons = array_values(array_filter(
            $lessons,
            static fn (array $lesson): bool => empty($lesson['module_id'])
        ));

        return [
            'modules' => $modules,
            'unassigned_lessons' => $unassignedLessons,
        ];
    }

    private function uniqueSlug(string $title): string
    {
        $base = slugify($title);
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($slug)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug): bool
    {
        $statement = $this->db->prepare('SELECT id FROM courses WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);

        return (bool) $statement->fetchColumn();
    }
}
