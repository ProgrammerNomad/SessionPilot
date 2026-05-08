<div class="wrap sp-admin-wrap" x-data="spSettings()">
    <h1 class="sp-page-title">Settings</h1>

    <div class="sp-card sp-card--form" style="max-width: 640px;">
        <form @submit.prevent="saveSettings()">

            <h2>Session & Cleanup</h2>

            <div class="sp-form-row">
                <label>Idle timeout (seconds) <small>0 = disabled</small></label>
                <input type="number" x-model.number="settings.idle_timeout_seconds" min="0" class="sp-input sp-input--sm" />
            </div>
            <div class="sp-form-row">
                <label>Heartbeat grace period (seconds)</label>
                <input type="number" x-model.number="settings.heartbeat_grace_period" min="30" class="sp-input sp-input--sm" />
            </div>
            <div class="sp-form-row">
                <label>Session retention (days)</label>
                <input type="number" x-model.number="settings.session_retention_days" min="1" class="sp-input sp-input--sm" />
            </div>
            <div class="sp-form-row">
                <label>Log retention (days)</label>
                <input type="number" x-model.number="settings.log_retention_days" min="1" class="sp-input sp-input--sm" />
            </div>

            <h2>Privacy</h2>

            <div class="sp-form-row">
                <label>
                    <input type="checkbox" x-model="settings.anonymize_ip" />
                    Anonymize IP addresses (mask last octet)
                </label>
            </div>

            <h2>Email Alerts</h2>

            <div class="sp-form-row">
                <label>Alert email <small>leave blank to use admin email</small></label>
                <input type="email" x-model="settings.alert_email" class="sp-input" placeholder="admin@example.com" />
            </div>
            <div class="sp-form-row">
                <label>
                    <input type="checkbox" x-model="settings.alert_on_limit_exceeded" />
                    Alert when session limit is exceeded
                </label>
            </div>
            <div class="sp-form-row">
                <label>
                    <input type="checkbox" x-model="settings.alert_on_login_failures" />
                    Alert on repeated login failures
                </label>
            </div>
            <div class="sp-form-row">
                <label>Failed login alert threshold</label>
                <input type="number" x-model.number="settings.login_failure_threshold" min="1" class="sp-input sp-input--sm" />
            </div>

            <div class="sp-form-actions">
                <button type="submit" class="sp-btn sp-btn--primary" :disabled="saving">
                    <span x-text="saving ? 'Saving…' : 'Save Settings'"></span>
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
