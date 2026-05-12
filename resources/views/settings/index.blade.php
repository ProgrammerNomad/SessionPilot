<div class="wrap sp-admin-wrap" x-data="spSettings()">
    <h1 class="sp-page-title">Settings</h1>

    <div class="sp-card sp-card--form sp-card--settings">
        <form @submit.prevent="saveSettings()">

            <h2>Session &amp; Cleanup</h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label>Idle timeout (seconds)</label></th>
                    <td>
                        <input type="number" x-model.number="settings.idle_timeout_seconds" min="0" class="sp-input sp-input--sm" />
                        <p class="description">Automatically logs users out after this many seconds of inactivity. Set to 0 to disable.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Heartbeat grace period (seconds)</label></th>
                    <td>
                        <input type="number" x-model.number="settings.heartbeat_grace_period" min="30" class="sp-input sp-input--sm" />
                        <p class="description">Extra time allowed before considering a session inactive. Minimum 30 seconds.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Session retention (days)</label></th>
                    <td>
                        <input type="number" x-model.number="settings.session_retention_days" min="1" class="sp-input sp-input--sm" />
                        <p class="description">How long ended sessions are kept in the database before automatic cleanup.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Log retention (days)</label></th>
                    <td>
                        <input type="number" x-model.number="settings.log_retention_days" min="1" class="sp-input sp-input--sm" />
                        <p class="description">How long activity log entries are kept before automatic cleanup.</p>
                    </td>
                </tr>
            </table>

            <h2>Privacy</h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label>Anonymize IP addresses</label></th>
                    <td>
                        <label>
                            <input type="checkbox" x-model="settings.anonymize_ip" />
                            Enable
                        </label>
                        <p class="description">Masks the last octet of IP addresses stored in logs (e.g. 192.168.1.x).</p>
                    </td>
                </tr>
            </table>

            <h2>Email Alerts</h2>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label>Alert email</label></th>
                    <td>
                        <input type="email" x-model="settings.alert_email" class="sp-input" placeholder="admin@example.com" />
                        <p class="description">Leave blank to use the site admin email address.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Session limit alerts</label></th>
                    <td>
                        <label>
                            <input type="checkbox" x-model="settings.alert_on_limit_exceeded" />
                            Alert when a session limit is exceeded
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Login failure alerts</label></th>
                    <td>
                        <label>
                            <input type="checkbox" x-model="settings.alert_on_login_failures" />
                            Alert on repeated login failures
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Failed login threshold</label></th>
                    <td>
                        <input type="number" x-model.number="settings.login_failure_threshold" min="1" class="sp-input sp-input--sm" />
                        <p class="description">Number of consecutive failures before an alert email is sent.</p>
                    </td>
                </tr>
            </table>

            <div class="sp-form-actions">
                <button type="submit" class="sp-btn sp-btn--primary" :disabled="saving">
                    <span x-text="saving ? 'Saving\u2026' : 'Save Settings'"></span>
                </button>
                <span class="sp-save-notice" x-show="saved" x-transition>Settings saved.</span>
            </div>
        </form>
    </div>
</div>

<script>
function spSettings() {
    return {
        settings: {},
        saving: false,
        saved: false,

        async init() {
            // Load current settings via AJAX (reads from wp_sp_settings)
            const res = await fetch(spData.ajaxUrl + '?action=sp_get_settings&nonce=' + spData.nonce);
            this.settings = await res.json();
        },

        async saveSettings() {
            this.saving = true;
            await fetch(spData.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'sp_save_settings',
                    nonce: spData.nonce,
                    ...Object.fromEntries(Object.entries(this.settings).map(([k, v]) => [k, v]))
                })
            });
            this.saving = false;
            this.saved  = true;
            setTimeout(() => this.saved = false, 3000);
        },
    };
}
</script>
