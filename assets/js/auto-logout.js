(function () {
    if (typeof auto_logout_minutes === "undefined" || Number(auto_logout_minutes) <= 0) {
        return;
    }

    const maxInactivity = Number(auto_logout_minutes) * 60 * 1000;
    const lastActivityKey = 'lastActivityTime';
    const logoutFlagKey = 'logoutInitiated';
    const channel = new BroadcastChannel('auto_logout_channel');
    let isLogoutInProgress = false;
    let countdownInterval = null;

    function resetLogoutTimer() {
        const currentTime = Date.now();
        localStorage.setItem(lastActivityKey, currentTime);
    }

    function checkInactivity() {
        const lastActivityTime = parseInt(localStorage.getItem(lastActivityKey), 10) || Date.now();
        const currentTime = Date.now();

        if (currentTime - lastActivityTime >= maxInactivity) {
            showLogoutWarning();
        }
    }

    // 🔹 Browser/system notification
    function showSystemNotification() {
        if (Notification.permission === "granted") {
            const notification = new Notification("Session Expiring", {
                body: "You will be logged out in 1 minute. Click to continue your session.",
                icon: "/assets/images/logout-icon.png" // update path if needed
            });

            // If user clicks notification → focus + continue session
            notification.onclick = () => {
                window.focus();
                resetLogoutTimer();
                channel.postMessage("continue_session");
            };
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    showSystemNotification();
                }
            });
        }
    }

    function showLogoutWarning() {
        if (document.getElementById("logoutWarningModal")) return; // already shown

        let countdown = 60; // 1 minute

        const modal = document.createElement("div");
        modal.id = "logoutWarningModal";
        modal.style.position = "fixed";
        modal.style.top = "0";
        modal.style.left = "0";
        modal.style.width = "100%";
        modal.style.height = "100%";
        modal.style.background = "rgba(0,0,0,0.5)";
        modal.style.display = "flex";
        modal.style.justifyContent = "center";
        modal.style.alignItems = "center";
        modal.style.zIndex = "9999";

        modal.innerHTML = `
            <div style="background:#fff;padding:20px;border-radius:8px;text-align:center;max-width:400px;width:90%;">
                <h3>Session Expiring</h3>
                <p>You will be logged out in <span id="logoutCountdown">${countdown}</span> seconds.</p>
                <button id="continueSessionBtn" style="margin-top:10px;padding:8px 15px;background:#28a745;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                    Continue Session
                </button>
            </div>
        `;
        document.body.appendChild(modal);

        // Sync across tabs
        channel.postMessage("show_warning");

        // Also show system notification
        showSystemNotification();

        countdownInterval = setInterval(() => {
            countdown--;
            const el = document.getElementById("logoutCountdown");
            if (el) el.innerText = countdown;

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                triggerLogout();
            }
        }, 1000);

        document.getElementById("continueSessionBtn").addEventListener("click", () => {
            clearInterval(countdownInterval);
            document.body.removeChild(modal);
            resetLogoutTimer();
            channel.postMessage("continue_session"); // notify other tabs
        });
    }

    function triggerLogout() {
        if (!localStorage.getItem(logoutFlagKey) && !isLogoutInProgress) {
            localStorage.setItem(logoutFlagKey, 'true');
            channel.postMessage('initiate_logout');
            session_destroy();
        } else {
            location.reload();
        }
    }

    function handleMessage(event) {
        if (event.data === 'initiate_logout') {
            location.reload();
        } else if (event.data === 'show_warning') {
            if (!document.getElementById("logoutWarningModal")) {
                showLogoutWarning();
            }
        } else if (event.data === 'continue_session') {
            const modal = document.getElementById("logoutWarningModal");
            if (modal) {
                clearInterval(countdownInterval);
                document.body.removeChild(modal);
            }
            resetLogoutTimer();
        }
    }

    resetLogoutTimer();

    const events = [
        'load', 'mousemove', 'keydown', 'scroll', 'click', 'input', 'change', 'touchstart',
        'touchmove', 'wheel', 'resize', 'focus'
    ];

    events.forEach(event => {
        window.addEventListener(event, resetLogoutTimer);
    });

    setInterval(checkInactivity, 5 * 1000);

    channel.addEventListener('message', handleMessage);

    window.addEventListener('unload', () => {
        channel.close();
    });

    function session_destroy() {
        if (isLogoutInProgress) return;

        isLogoutInProgress = true;

        $.ajax({
            url: admin_url + 'staff/auto_logout',
            method: "POST",
            dataType: 'json',
            async: true
        }).done(function () {
            channel.postMessage('initiate_logout');
            location.reload();
        }).always(function () {
            isLogoutInProgress = false;
        });
    }

    window.addEventListener('load', () => {
        localStorage.removeItem(logoutFlagKey);

        // Ask permission for system notifications
        if ("Notification" in window && Notification.permission !== "granted") {
            Notification.requestPermission();
        }
    });

})();
