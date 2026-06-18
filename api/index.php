<?php

declare(strict_types=1);

$_ENV['TME_SERVERLESS'] = $_ENV['TME_SERVERLESS'] ?? 'true';
putenv('TME_SERVERLESS=true');

require dirname(__DIR__) . '/public/index.php';
