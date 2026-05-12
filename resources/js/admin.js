import '../css/admin.css';
import Alpine from 'alpinejs';
import 'htmx.org';

// Boot Alpine
window.Alpine = Alpine;
Alpine.start();

// Heartbeat: keep session last_activity fresh and detect force-logout
document.addEventListener('DOMContentLoaded', function () {
    // Send a flag on every heartbeat tick so the server updates last_activity
    jQuery(document).on('heartbeat-send', function (e, data) {
        data.sp_session_tick = true;
    });

    // If the server marks this session as killed, redirect to login
    jQuery(document).on('heartbeat-tick', function (e, data) {
        if (data.sp_force_logout) {
            window.location.href = data.sp_logout_url || '/wp-login.php?loggedout=true';
        }
    });
});
