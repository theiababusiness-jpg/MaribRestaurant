(function () {
    const root = document.querySelector('[data-admin-order-notifications]');

    if (!root) {
        return;
    }

    const config = {
        checkUrl: root.dataset.checkUrl || "",
        ordersUrl: root.dataset.ordersUrl || "",
        initialLastId: Number(root.dataset.lastId || 0),
        pollMs: Number(root.dataset.pollMs || 10000),
    };

    const storageKeys = {
        lastId: "admin-order-notifications-last-id",
        soundReady: "admin-order-notifications-sound-ready",
    };

    const el = {
        audio: document.getElementById("adminOrderNotificationAudio"),
        toast: document.getElementById("adminOrderNotificationToast"),
        toastTitle: document.getElementById("adminOrderNotificationTitle"),
        toastText: document.getElementById("adminOrderNotificationText"),
        dismiss: document.getElementById("adminOrderNotificationDismiss"),
        enableSound: document.getElementById("adminOrderNotificationEnable"),
        pageAlert: document.getElementById("newOrdersAlert"),
        pageAlertText: document.getElementById("newOrdersAlertText"),
    };

    let lastOrderId = Math.max(
        Number(localStorage.getItem(storageKeys.lastId) || 0),
        config.initialLastId
    );
    let polling = false;

    localStorage.setItem(storageKeys.lastId, String(lastOrderId));

    function hasSoundAccess() {
        return localStorage.getItem(storageKeys.soundReady) === "1";
    }

    function setSoundAccess(enabled) {
        localStorage.setItem(storageKeys.soundReady, enabled ? "1" : "0");

        if (el.enableSound) {
            el.enableSound.hidden = enabled;
        }
    }

    async function primeAudio() {
        if (!el.audio) {
            return false;
        }

        try {
            el.audio.muted = true;
            const result = el.audio.play();

            if (result && typeof result.then === "function") {
                await result;
            }

            el.audio.pause();
            el.audio.currentTime = 0;
            el.audio.muted = false;
            setSoundAccess(true);
            detachPrimers();

            return true;
        } catch (error) {
            el.audio.muted = false;
            setSoundAccess(false);
            return false;
        }
    }

    function detachPrimers() {
        ["pointerdown", "keydown", "touchstart"].forEach(function (eventName) {
            document.removeEventListener(eventName, handlePrimer, true);
        });
    }

    function handlePrimer() {
        void primeAudio();
    }

    function bindPrimers() {
        setSoundAccess(hasSoundAccess());

        ["pointerdown", "keydown", "touchstart"].forEach(function (eventName) {
            document.addEventListener(eventName, handlePrimer, { capture: true, passive: true });
        });

        el.enableSound?.addEventListener("click", function () {
            void primeAudio();
        });
    }

    function showVisualNotification(count) {
        const message = count > 1
            ? `يوجد ${count} طلبات جديدة تحتاج المراجعة.`
            : "يوجد طلب جديد يحتاج المراجعة.";
        const hasInlineAlert = Boolean(el.pageAlert && el.pageAlertText);

        if (hasInlineAlert) {
            el.pageAlertText.textContent = message;
            el.pageAlert.style.display = "block";
        }

        if (!hasInlineAlert && el.toast && el.toastText) {
            el.toastText.textContent = message;
            el.toast.hidden = false;
        }
    }

    async function playNotificationSound() {
        if (!el.audio) {
            return;
        }

        if (!hasSoundAccess()) {
            setSoundAccess(false);
            return;
        }

        try {
            el.audio.pause();
            el.audio.currentTime = 0;

            const result = el.audio.play();

            if (result && typeof result.then === "function") {
                await result;
            }
        } catch (error) {
            setSoundAccess(false);
        }
    }

    async function pollNewOrders() {
        if (polling || !config.checkUrl) {
            return;
        }

        polling = true;

        try {
            const response = await fetch(`${config.checkUrl}?last_id=${encodeURIComponent(lastOrderId)}`, {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "same-origin",
                cache: "no-store",
            });

            if (!response.ok) {
                throw new Error("POLL_FAILED");
            }

            const payload = await response.json();
            const incomingLastId = Number(payload.last_id || lastOrderId);
            const hasNewOrders = Boolean(payload.has_new) && incomingLastId > lastOrderId;

            lastOrderId = Math.max(lastOrderId, incomingLastId);
            localStorage.setItem(storageKeys.lastId, String(lastOrderId));

            if (!hasNewOrders) {
                return;
            }

            showVisualNotification(Number(payload.count || 1));
            await playNotificationSound();
        } catch (error) {
            console.error("Admin order notifications failed:", error);
        } finally {
            polling = false;
        }
    }

    el.dismiss?.addEventListener("click", function () {
        if (el.toast) {
            el.toast.hidden = true;
        }
    });

    root.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener("click", function () {
            if (el.toast) {
                el.toast.hidden = true;
            }
        });
    });

    bindPrimers();
    window.setInterval(pollNewOrders, Math.max(config.pollMs, 5000));
})();
