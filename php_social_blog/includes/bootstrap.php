<?php
declare(strict_types=1);

// Secure-ish session settings
ini_set('session.cookie_httponly', '1');
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
