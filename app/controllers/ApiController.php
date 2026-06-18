<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class ApiController extends Controller
{
    public function health(): void
    {
        $database = 'ok';

        try {
            Database::connection()->query('SELECT 1');
        } catch (Throwable) {
            $database = 'unavailable';
        }

        $this->json([
            'data' => [
                'app' => config('app.name'),
                'status' => 'ok',
                'database' => $database,
                'serverless' => (bool) (env('VERCEL') || env('TME_SERVERLESS')),
                'time' => date(DATE_ATOM),
            ],
        ]);
    }

    public function token(): void
    {
        $input = $this->input();
        $email = strtolower(trim($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $limiter = new RateLimiter();
        $limitKey = 'api-token:' . Security::clientIp() . ':' . sha1($email);
        $decay = (int) config('app.rate_limits.login.decay_seconds', 900);

        if ($limiter->tooManyAttempts($limitKey, (int) config('app.rate_limits.login.max_attempts', 5), $decay)) {
            $this->json(['error' => ['code' => 'rate_limited', 'message' => 'Muitas tentativas.']], 429);
            return;
        }

        $user = (new User())->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password_hash']) || $user['status'] !== 'aprovado') {
            $limiter->hit($limitKey, $decay);
            $this->json(['error' => ['code' => 'invalid_credentials', 'message' => 'Credenciais invalidas.']], 401);
            return;
        }

        try {
            $token = (new ApiToken())->issue((int) $user['id'], trim($input['name'] ?? 'API Token'));
        } catch (PDOException) {
            $this->json(['error' => ['code' => 'api_tokens_unavailable', 'message' => 'Aplique a migration de API antes de emitir tokens.']], 503);
            return;
        }

        $limiter->clear($limitKey);
        (new ActionLog())->record((int) $user['id'], 'api.token.issued', ['token_id' => $token['id']], 'security');

        $this->json([
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $token['token'],
            ],
        ], 201);
    }

    public function me(): void
    {
        $user = current_user();

        $this->json([
            'data' => [
                'id' => (int) $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role_slug'],
                'status' => $user['status'],
            ],
        ]);
    }

    public function courses(): void
    {
        $courses = Cache::remember('api.courses.published', 60, fn () => (new Course())->published());

        $this->json([
            'data' => array_map(fn (array $course): array => $this->courseResource($course), $courses),
        ]);
    }

    public function course(string $id): void
    {
        $course = (new Course())->findPublished((int) $id);

        if (! $course) {
            $this->json(['error' => ['code' => 'not_found', 'message' => 'Curso nao encontrado.']], 404);
            return;
        }

        $this->json([
            'data' => $this->courseResource($course) + [
                'structure' => (new Course())->structure((int) $id, true),
            ],
        ]);
    }

    public function plans(): void
    {
        $plans = (new Plan())->active();

        $this->json(['data' => $plans]);
    }

    public function ranking(): void
    {
        $courseId = (int) ($_GET['course_id'] ?? 0);

        $this->json([
            'data' => (new Gamification())->ranking($courseId ?: null),
        ]);
    }

    public function validateCertificate(string $code): void
    {
        $certificate = (new Certificate())->findByCode($code);

        if (! $certificate) {
            $this->json(['data' => ['valid' => false, 'message' => 'Certificado nao encontrado.']], 404);
            return;
        }

        $this->json([
            'data' => [
                'valid' => $certificate['validation_status'] === 'valido',
                'code' => $certificate['code'],
                'title' => $certificate['title'],
                'student' => $certificate['student_name'],
                'workload_hours' => (int) $certificate['workload_hours'],
                'issued_at' => $certificate['issued_at'],
                'status' => $certificate['validation_status'],
            ],
        ]);
    }

    private function courseResource(array $course): array
    {
        return [
            'id' => (int) $course['id'],
            'title' => $course['title'],
            'description' => $course['description'],
            'category' => $course['category'],
            'level' => $course['level'],
            'workload_hours' => (int) $course['workload_hours'],
            'price' => (float) $course['price'],
            'access_level' => $course['access_level'] ?? 'gratuito',
            'teacher' => $course['teacher_name'] ?? null,
            'modules_count' => (int) ($course['modules_count'] ?? 0),
            'lessons_count' => (int) ($course['lessons_count'] ?? 0),
        ];
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input') ?: '';
        $json = json_decode($raw, true);

        return is_array($json) ? $json + $_POST : $_POST;
    }
}
