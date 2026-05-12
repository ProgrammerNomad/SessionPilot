<div class="wrap sp-admin-wrap" x-data="spSessions()">
    <h1 class="sp-page-title">Sessions</h1>

    {{-- Toolbar --}}
    <div class="sp-toolbar">
        <input type="text" x-model="search" placeholder="Search user, IP, browser…" class="sp-input" style="max-width:220px" />
        <label class="sp-toggle">
            <input type="checkbox" x-model="activeOnly" @change="fetchSessions()" />
            Active only
        </label>
        <button class="sp-btn sp-btn--secondary sp-btn--destructive" @click="killAll()" :disabled="loading">
            Force logout all
        </button>
    </div>

    {{-- Table --}}
    <div class="sp-card">
        <table class="sp-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Browser</th>
                    <th>OS</th>
                    <th>Device</th>
                    <th>IP</th>
                    <th>Last Seen</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-if="loading">
                    <tr><td colspan="8" class="sp-empty"><span class="spinner is-active" style="float:none;margin:0 6px 0 0;vertical-align:middle;"></span>Loading…</td></tr>
                </template>
                <template x-if="!loading && sessions.length === 0">
                    <tr><td colspan="8" class="sp-empty">No sessions found.</td></tr>
                </template>
                <template x-for="s in filteredSessions" :key="s.id">
                    <tr>
                        <td>
                            <strong x-text="s.display_name || s.user_login"></strong><br>
                            <small x-text="s.user_login"></small>
                        </td>
                        <td x-text="s.browser + (s.browser_version ? ' ' + s.browser_version : '')"></td>
                        <td x-text="s.os"></td>
                        <td x-text="s.device_type"></td>
                        <td x-text="s.ip_address || '-'"></td>
                        <td x-text="s.last_activity ? timeAgo(s.last_activity) : '-'"></td>
                        <td>
                            <span class="sp-badge" :class="s.is_active ? 'sp-badge--active' : 'sp-badge--inactive'"
                                  x-text="s.is_active ? 'Active' : 'Ended'"></span>
                        </td>
                        <td>
                            <button class="sp-btn sp-btn--sm" @click="openSession(s)">Details</button>
                            <button x-show="s.is_active" class="sp-btn sp-btn--sm sp-btn--danger"
                                    @click="killSession(s.id)">Logout</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Session Details Modal --}}
    <div class="sp-modal-overlay" x-show="showModal" x-cloak @click.self="showModal = false" @keydown.escape.window="showModal = false">
        <div class="sp-modal" role="dialog" aria-modal="true" aria-labelledby="sp-modal-title">
            <div class="sp-modal-header">
                <h2 id="sp-modal-title">Session Details</h2>
                <button class="sp-modal-close" @click="showModal = false" aria-label="Close">&times;</button>
            </div>
            <div class="sp-modal-body" x-show="selectedSession">
                <dl class="sp-modal-dl">
                    <dt>User</dt>
                    <dd x-text="selectedSession ? (selectedSession.display_name || selectedSession.user_login) : ''"></dd>
                    <dt>Login</dt>
                    <dd x-text="selectedSession ? selectedSession.user_login : ''"></dd>
                    <dt>IP Address</dt>
                    <dd x-text="selectedSession ? (selectedSession.ip_address || '—') : ''"></dd>
                    <dt>Browser</dt>
                    <dd x-text="selectedSession ? (selectedSession.browser + (selectedSession.browser_version ? ' ' + selectedSession.browser_version : '')) : ''"></dd>
                    <dt>Operating System</dt>
                    <dd x-text="selectedSession ? (selectedSession.os || '—') : ''"></dd>
                    <dt>Device</dt>
                    <dd x-text="selectedSession ? (selectedSession.device_type || '—') : ''"></dd>
                    <dt>Login Time</dt>
                    <dd x-text="selectedSession ? (selectedSession.login_at || '—') : ''"></dd>
                    <dt>Last Activity</dt>
                    <dd x-text="selectedSession ? (selectedSession.last_activity ? timeAgo(selectedSession.last_activity) : '—') : ''"></dd>
                    <dt>Status</dt>
                    <dd x-text="selectedSession ? (selectedSession.is_active ? 'Active' : 'Ended') : ''"></dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="sp-pagination" x-show="totalPages > 1">
        <button @click="prevPage()" :disabled="page <= 1" class="sp-btn sp-btn--sm">← Prev</button>
        <span x-text="`Page ${page} of ${totalPages}`"></span>
        <button @click="nextPage()" :disabled="page >= totalPages" class="sp-btn sp-btn--sm">Next →</button>
    </div>
</div>

<script>
function spSessions() {
    return {
        sessions: [],
        loading: true,
        search: '',
        activeOnly: true,
        page: 1,
        total: 0,
        perPage: 25,
        showModal: false,
        selectedSession: null,

        get totalPages() { return Math.ceil(this.total / this.perPage); },
        get filteredSessions() {
            if (!this.search) return this.sessions;
            const q = this.search.toLowerCase();
            return this.sessions.filter(s =>
                (s.user_login || '').toLowerCase().includes(q) ||
                (s.display_name || '').toLowerCase().includes(q) ||
                (s.ip_address || '').includes(q) ||
                (s.browser || '').toLowerCase().includes(q)
            );
        },

        async init() { await this.fetchSessions(); },

        async fetchSessions() {
            this.loading = true;
            const params = new URLSearchParams({ page: this.page });
            if (this.activeOnly) params.set('active', 'true');
            const res = await fetch(spData.restUrl + 'sessions?' + params, {
                headers: { 'X-WP-Nonce': spData.restNonce }
            });
            const json = await res.json();
            this.sessions = json.data || [];
            this.total    = json.total || 0;
            this.loading  = false;
        },

        async killSession(id) {
            if (!confirm('Force logout this session?')) return;
            await fetch(spData.restUrl + 'sessions/' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': spData.restNonce }
            });
            await this.fetchSessions();
        },

        async killAll() {
            if (!confirm('Force logout ALL active sessions? This includes your own session.')) return;
            await fetch(spData.restUrl + 'sessions/kill', {
                method: 'POST',
                headers: { 'X-WP-Nonce': spData.restNonce }
            });
            await this.fetchSessions();
        },

        prevPage() { if (this.page > 1) { this.page--; this.fetchSessions(); } },
        nextPage() { if (this.page < this.totalPages) { this.page++; this.fetchSessions(); } },

        timeAgo(iso) {
            const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
            if (diff < 60) return diff + 's ago';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            return Math.floor(diff / 86400) + 'd ago';
        },

        openSession(s) {
            this.selectedSession = s;
            this.showModal = true;
        },
    };
}
</script>
