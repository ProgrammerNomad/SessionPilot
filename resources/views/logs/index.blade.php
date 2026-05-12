<div class="wrap sp-admin-wrap" x-data="spLogs()">
    <h1 class="sp-page-title">Activity Logs</h1>

    <div class="sp-toolbar">
        <select x-model="filters.action" @change="fetchLogs()" class="sp-select">
            <option value="">All actions</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="login_failed">Login failed</option>
            <option value="role_change">Role change</option>
            <option value="plugin_activated">Plugin activated</option>
            <option value="plugin_deactivated">Plugin deactivated</option>
            <option value="theme_switch">Theme switch</option>
            <option value="setting_change">Setting change</option>
            <option value="password_reset">Password reset</option>
        </select>
        <select x-model="filters.severity" @change="fetchLogs()" class="sp-select">
            <option value="">All severity</option>
            <option value="info">Info</option>
            <option value="warning">Warning</option>
            <option value="critical">Critical</option>
        </select>
        <button class="sp-btn sp-btn--secondary" @click="exportCsv()">Export CSV</button>
    </div>

    <div class="sp-card">
        <table class="sp-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP</th>
                    <th>Severity</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="6" class="sp-empty"><span class="spinner is-active" style="float:none;margin:0 6px 0 0;vertical-align:middle;"></span>Loading…</td></tr>
                </template>
                <template x-for="log in logs" :key="log.id">
                    <tr>
                        <td x-text="log.timestamp"></td>
                        <td x-text="log.user_id || '-'"></td>
                        <td><span class="sp-badge sp-badge--action" x-text="formatAction(log.action_type)"></span></td>
                        <td x-text="log.description"></td>
                        <td x-text="log.ip || '-'"></td>
                        <td><span class="sp-badge" :class="`sp-badge--${log.severity}`" x-text="log.severity.charAt(0).toUpperCase() + log.severity.slice(1)"></span></td>
                    </tr>
                </template>
                <template x-if="!loading && logs.length === 0">
                    <tr><td colspan="6" class="sp-empty">No logs found.</td></tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="sp-pagination" x-show="totalPages > 1">
        <button @click="prevPage()" :disabled="page <= 1" class="sp-btn sp-btn--sm">← Prev</button>
        <span x-text="`Page ${page} of ${totalPages}`"></span>
        <button @click="nextPage()" :disabled="page >= totalPages" class="sp-btn sp-btn--sm">Next →</button>
    </div>
</div>

<script>
function spLogs() {
    return {
        logs: [], loading: true, page: 1, total: 0, perPage: 50,
        filters: { action: '', severity: '' },

        get totalPages() { return Math.ceil(this.total / this.perPage); },
        async init() { await this.fetchLogs(); },

        async fetchLogs() {
            this.loading = true;
            const p = new URLSearchParams({ page: this.page });
            if (this.filters.action)   p.set('action', this.filters.action);
            if (this.filters.severity) p.set('severity', this.filters.severity);
            const res = await fetch(spData.restUrl + 'logs?' + p, { headers: { 'X-WP-Nonce': spData.restNonce } });
            const json = await res.json();
            this.logs  = json.data || [];
            this.total = json.total || 0;
            this.loading = false;
        },

        exportCsv() {
            const p = new URLSearchParams({ format: 'csv' });
            if (this.filters.action)   p.set('action', this.filters.action);
            if (this.filters.severity) p.set('severity', this.filters.severity);
            window.location.href = spData.restUrl + 'logs?' + p + '&_wpnonce=' + spData.restNonce;
        },

        prevPage() { if (this.page > 1) { this.page--; this.fetchLogs(); } },
        nextPage() { if (this.page < this.totalPages) { this.page++; this.fetchLogs(); } },

        formatAction(str) {
            if (!str) return '';
            return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        },
    };
}
</script>
