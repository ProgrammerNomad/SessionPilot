<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Tests\Unit\Services;

use ProgrammerNomad\SessionPilot\Services\ActivityLogService;
use ProgrammerNomad\SessionPilot\Tests\Stubs\WpdbStub;
use ProgrammerNomad\SessionPilot\Tests\TestCase;
use Brain\Monkey\Functions;

class ActivityLogServiceTest extends TestCase
{
    private function makeService(): ActivityLogService
    {
        return new ActivityLogService();
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Stub WP functions
        Functions\when('esc_html')->returnArg();
        Functions\when('sanitize_text_field')->returnArg();
        Functions\when('wp_unslash')->returnArg();
        Functions\when('current_time')->justReturn('2024-01-01 12:00:00');
        Functions\when('get_bloginfo')->justReturn('https://example.com');
        Functions\when('get_option')->justReturn('admin@example.com');
        Functions\when('get_current_user_id')->justReturn(1);
        Functions\when('get_userdata')->justReturn(false);
        Functions\when('wp_mail')->justReturn(true);
    }

    // -----------------------------------------------------------------------
    // logSettingChange - ignored prefix filtering (pure PHP logic)
    // -----------------------------------------------------------------------

    /**
     * logSettingChange should silently skip transient options.
     * We verify that by ensuring no wpdb->insert call path is reached.
     * Since wpdb is a global we can intercept, we test via a wpdb mock stub.
     */
    private function makeWpdb(string $tableExistsReturn, ?callable $insertHook = null): WpdbStub
    {
        $stub = new WpdbStub();
        $stub->getVarCallback = fn($sql) => $tableExistsReturn;
        if ($insertHook !== null) {
            $stub->insertCallback = $insertHook;
        }
        return $stub;
    }

    public function test_log_setting_change_skips_transient_options(): void
    {
        global $wpdb;
        $insertCalled = false;
        $wpdb = $this->makeWpdb('wp_sp_activity_logs', function () use (&$insertCalled) {
            $insertCalled = true;
        });

        $service = $this->makeService();
        $service->logSettingChange('_transient_something', 'old', 'new');

        $this->assertFalse($insertCalled, 'logSettingChange should not insert for transient options');
    }

    public function test_log_setting_change_skips_site_transient_options(): void
    {
        global $wpdb;
        $insertCalled = false;
        $wpdb = $this->makeWpdb('wp_sp_activity_logs', function () use (&$insertCalled) {
            $insertCalled = true;
        });

        $service = $this->makeService();
        $service->logSettingChange('_site_transient_timeout_foo', 'old', 'new');

        $this->assertFalse($insertCalled, 'logSettingChange should not insert for _site_transient_ options');
    }

    public function test_log_setting_change_skips_cron_option(): void
    {
        global $wpdb;
        $insertCalled = false;
        $wpdb = $this->makeWpdb('wp_sp_activity_logs', function () use (&$insertCalled) {
            $insertCalled = true;
        });

        $service = $this->makeService();
        $service->logSettingChange('cron', [], []);

        $this->assertFalse($insertCalled, 'logSettingChange should not insert for cron option');
    }

    public function test_log_setting_change_skips_session_tokens(): void
    {
        global $wpdb;
        $insertCalled = false;
        $wpdb = $this->makeWpdb('wp_sp_activity_logs', function () use (&$insertCalled) {
            $insertCalled = true;
        });

        $service = $this->makeService();
        $service->logSettingChange('session_tokens', [], []);

        $this->assertFalse($insertCalled, 'logSettingChange should not insert for session_tokens option');
    }

    // -----------------------------------------------------------------------
    // Insert guard: table does not exist
    // -----------------------------------------------------------------------

    public function test_login_log_is_skipped_when_table_missing(): void
    {
        global $wpdb;
        $insertCalled = false;
        // get_var returns null = table does not exist
        $wpdb = $this->makeWpdb('', function () use (&$insertCalled) {
            $insertCalled = true;
        });
        // Override: return null (not found)
        $wpdb->getVarCallback = fn() => null;

        $user = new \WP_User();
        $user->ID         = 42;
        $user->user_login = 'johndoe';

        $service = $this->makeService();
        $service->logLogin('johndoe', $user);

        $this->assertFalse($insertCalled, 'logLogin should not insert when activity_logs table is missing');
    }
}
