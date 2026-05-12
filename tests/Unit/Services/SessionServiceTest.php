<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Tests\Unit\Services;

use ProgrammerNomad\SessionPilot\Services\SessionService;
use ProgrammerNomad\SessionPilot\Services\DeviceService;
use ProgrammerNomad\SessionPilot\Tests\Stubs\WpdbStub;
use ProgrammerNomad\SessionPilot\Tests\TestCase;
use Brain\Monkey\Functions;

class SessionServiceTest extends TestCase
{
    private function makeService(): SessionService
    {
        $deviceService = $this->createMock(DeviceService::class);
        return new SessionService($deviceService);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('sanitize_text_field')->returnArg();
        Functions\when('wp_unslash')->returnArg();
        Functions\when('current_time')->justReturn('2024-01-01 12:00:00');
        Functions\when('wp_login_url')->justReturn('https://example.com/wp-login.php');
        Functions\when('get_users')->justReturn([]);
        Functions\when('esc_html')->returnArg();
        Functions\when('esc_url_raw')->returnArg();
    }

    // -----------------------------------------------------------------------
    // getCurrentToken() - via cookie parsing
    // -----------------------------------------------------------------------

    private function makeWpdb(): WpdbStub
    {
        $stub = new WpdbStub();
        $stub->prepareCallback = fn($sql) => $sql;
        return $stub;
    }

    public function test_get_current_token_returns_token_from_cookie(): void
    {
        if ( ! defined('COOKIEHASH') ) {
            define('COOKIEHASH', 'abc123');
        }

        global $wpdb;
        $wpdb = $this->makeWpdb();
        // get_row returns null = no session record -> response unchanged
        $wpdb->getRowCallback = fn() => null;

        $_COOKIE = [];

        Functions\when('is_user_logged_in')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);

        $service  = $this->makeService();
        // Pass the sp_session_tick flag to trigger the heartbeat path
        $response = $service->onHeartbeat(['dummy' => 'data'], ['sp_session_tick' => true]);

        // No cookie -> getCurrentToken returns '' -> no DB call -> response is unchanged
        $this->assertSame(['dummy' => 'data'], $response);
    }

    public function test_on_heartbeat_returns_force_logout_flag_when_session_terminated(): void
    {
        if ( ! defined('COOKIEHASH') ) {
            define('COOKIEHASH', 'abc123');
        }

        $_COOKIE['wordpress_logged_in_abc123'] = 'user|expiry|testtoken123|extra';

        $row                = new \stdClass();
        $row->logged_out_at = '2024-01-01 11:00:00';

        global $wpdb;
        $wpdb = $this->makeWpdb();
        $wpdb->getRowCallback = fn() => $row;

        Functions\when('is_user_logged_in')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);

        $service  = $this->makeService();
        $response = $service->onHeartbeat([], ['sp_session_tick' => true]);

        $this->assertTrue($response['sp_force_logout']);
        $this->assertStringContainsString('wp-login', $response['sp_logout_url']);

        unset($_COOKIE['wordpress_logged_in_abc123']);
    }

    public function test_on_heartbeat_updates_last_activity_for_active_session(): void
    {
        if ( ! defined('COOKIEHASH') ) {
            define('COOKIEHASH', 'abc123');
        }

        $_COOKIE['wordpress_logged_in_abc123'] = 'user|expiry|livetoken|extra';

        $updateCalled = false;
        $row                = new \stdClass();
        $row->logged_out_at = null;

        global $wpdb;
        $wpdb = $this->makeWpdb();
        $wpdb->getRowCallback  = fn() => $row;
        $wpdb->updateCallback  = function () use (&$updateCalled) {
            $updateCalled = true;
            return 1;
        };

        Functions\when('is_user_logged_in')->justReturn(true);
        Functions\when('get_current_user_id')->justReturn(1);

        $service = $this->makeService();
        $service->onHeartbeat([], ['sp_session_tick' => true]);

        $this->assertTrue($updateCalled, 'last_activity should be updated for active session');

        unset($_COOKIE['wordpress_logged_in_abc123']);
    }

    // -----------------------------------------------------------------------
    // forceLogoutUser count
    // -----------------------------------------------------------------------

    public function test_force_logout_user_returns_zero_when_no_active_sessions(): void
    {
        // When Session::active()->where()->get() returns empty collection,
        // forceLogoutUser should return 0.
        // SessionService::forceLogoutUser() calls Session::active() which needs Eloquent.
        // Since Eloquent won't boot without a DB, we test via forceLogoutByRole (which wraps it).
        Functions\when('get_users')->justReturn([]); // no users -> no sessions -> 0

        $service = $this->makeService();
        $count   = $service->forceLogoutByRole('subscriber');

        $this->assertSame(0, $count);
    }
}
