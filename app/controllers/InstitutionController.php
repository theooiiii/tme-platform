<?php

defined('BASE_PATH') || exit('Acesso direto nao permitido.');

class InstitutionController extends Controller
{
    public function search(): void
    {
        $term = trim($_GET['q'] ?? '');

        if (strlen($term) < 2) {
            $this->json(['data' => []]);
            return;
        }

        $this->json([
            'data' => (new Institution())->searchByName($term),
        ]);
    }
}
