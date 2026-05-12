<?php

declare(strict_types=1);

namespace ProgrammerNomad\SessionPilot\Providers;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ( ! is_admin() ) {
            return;
        }

        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        // AJAX: heartbeat-based force-logout check
        add_action('wp_ajax_sp_check_session', [$this, 'ajaxCheckSession']);

        // AJAX: settings
        add_action('wp_ajax_sp_get_settings', [$this, 'ajaxGetSettings']);
        add_action('wp_ajax_sp_save_settings', [$this, 'ajaxSaveSettings']);
    }

    public function registerMenus(): void
    {
        add_menu_page(
            __('SessionPilot', 'sessionpilot'),
            __('SessionPilot', 'sessionpilot'),
            'manage_options',
            'sessionpilot',
            [$this, 'renderDashboard'],
            'dashicons-admin-users',
            30
        );

        add_submenu_page('sessionpilot', __('Dashboard', 'sessionpilot'), __('Dashboard', 'sessionpilot'), 'manage_options', 'sessionpilot', [$this, 'renderDashboard']);
        add_submenu_page('sessionpilot', __('Sessions', 'sessionpilot'), __('Sessions', 'sessionpilot'), 'manage_options', 'sessionpilot-sessions', [$this, 'renderSessions']);
        add_submenu_page('sessionpilot', __('Activity Logs', 'sessionpilot'), __('Activity Logs', 'sessionpilot'), 'manage_options', 'sessionpilot-logs', [$this, 'renderLogs']);
        add_submenu_page('sessionpilot', __('Rules', 'sessionpilot'), __('Rules', 'sessionpilot'), 'manage_options', 'sessionpilot-rules', [$this, 'renderRules']);
        add_submenu_page('sessionpilot', __('Devices', 'sessionpilot'), __('Devices', 'sessionpilot'), 'manage_options', 'sessionpilot-devices', [$this, 'renderDevices']);
        add_submenu_page('sessionpilot', __('Settings', 'sessionpilot'), __('Settings', 'sessionpilot'), 'manage_options', 'sessionpilot-settings', [$this, 'renderSettings']);
    }

    public function enqueueAssets(string $hook): void
    {
        // Only load on SessionPilot pages
        if ( strpos($hook, 'sessionpilot') === false ) {
            return;
        }

        wp_enqueue_style(
            'sessionpilot-admin',
            SESSIONPILOT_PLUGIN_URL . 'public/css/admin.css',
            [],
            SESSIONPILOT_VERSION
        );

        wp_enqueue_script(
            'sessionpilot-admin',
            SESSIONPILOT_PLUGIN_URL . 'public/js/admin.js',
            [],
            SESSIONPILOT_VERSION,
            true
        );

        wp_localize_script('sessionpilot-admin', 'spData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sessionpilot_nonce'),
            'restUrl' => rest_url('sp/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function renderDashboard(): void
    {
        $this->render('dashboard.index');
    }

    public function renderSessions(): void
    {
        $this->render('sessions.index');
    }

    public function renderLogs(): void
    {
        $this->render('logs.index');
    }

    public function renderRules(): void
    {
        $this->render('rules.index');
    }

    public function renderDevices(): void
    {
        $this->render('devices.index');
    }

    public function renderSettings(): void
    {
        $this->render('settings.index');
    }

    public function ajaxCheckSession(): void
    {
        check_ajax_referer('sessionpilot_nonce', 'nonce');

        if ( ! is_user_logged_in() ) {
            wp_send_json_error(['action' => 'logout'], 401);
        }

        wp_send_json_success(['action' => 'continue']);
    }

    public function ajaxGetSettings(): void
    {
        check_ajax_referer('sessionpilot_nonce', 'nonce');

        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error('Unauthorized', 403);
        }

        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT setting_key, setting_value FROM {$wpdb->prefix}sp_settings",
            OBJECT_K
        );

        $settings = [];
        foreach ($rows as $key => $row) {
            $val = $row->setting_value;
            if (in_array($key, ['anonymize_ip', 'alert_on_limit_exceeded', 'alert_on_login_failures'], true)) {
                $val = (bool) $val;
            } elseif (in_array($key, ['idle_timeout_seconds', 'heartbeat_grace_period', 'session_retention_days', 'log_retention_days', 'heartbeat_interval', 'login_failure_threshold'], true)) {
                $val = (int) $val;
            }
            $settings[$key] = $val;
        }

        wp_send_json($settings);
    }

    public function ajaxSaveSettings(): void
    {
        check_ajax_referer('sessionpilot_nonce', 'nonce');

        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error('Unauthorized', 403);
        }

        global $wpdb;

        $allowed = [
            'idle_timeout_seconds', 'heartbeat_grace_period', 'session_retention_days',
            'log_retention_days', 'anonymize_ip', 'alert_email',
            'alert_on_limit_exceeded', 'alert_on_login_failures', 'login_failure_threshold',
        ];

        foreach ($allowed as $key) {
            if ( ! isset($_POST[$key]) ) {
                continue;
            }
            $value = sanitize_text_field(wp_unslash($_POST[$key]));
            $wpdb->update(
                $wpdb->prefix . 'sp_settings',
                ['setting_value' => $value, 'updated_at' => current_time('mysql')],
                ['setting_key' => $key],
                ['%s', '%s'],
                ['%s']
            );
        }

        wp_send_json_success('Settings saved.');
    }

    private function render(string $view): void
    {
        if ( ! current_user_can('manage_options') ) {
            wp_die( esc_html__('You do not have permission to access this page.', 'sessionpilot') );
        }

        echo view('sessionpilot::' . $view)->render(); // phpcs:ignore WordPress.Security.EscapeOutput
    }
}

