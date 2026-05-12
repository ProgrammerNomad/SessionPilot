<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Brain\Monkey;

// Define WordPress constants used in source files
define('ABSPATH', __DIR__ . '/../');
define('SESSIONPILOT_VERSION', '1.0.0');
define('SESSIONPILOT_PLUGIN_DIR', __DIR__ . '/../');

// Brain Monkey setup/teardown is handled per-test via TestCase

// Minimal WP_User stub so type-hinted code can be called in tests
if ( ! class_exists('WP_User') ) {
    class WP_User {
        public int $ID = 0;
        public string $user_login = '';
        public array $roles = [];
    }
}
