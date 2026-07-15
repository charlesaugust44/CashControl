(async function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    const publicKeyMeta = document.querySelector('meta[name="vapid-public-key"]');
    if (!publicKeyMeta || !publicKeyMeta.content) {
        return;
    }

    const vapidPublicKey = publicKeyMeta.content;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const apiUrl = document.querySelector('meta[name="api-url"]')?.content || '/api';

    async function subscribe() {
        const registration = await navigator.serviceWorker.ready;
        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
            });
        }

        await sendToServer(subscription);
    }

    async function sendToServer(subscription) {
        const key = subscription.getKey('p256dh');
        const auth = subscription.getKey('auth');

        if (!key || !auth) return;

        await fetch(`${apiUrl}/push/subscribe`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                keys: {
                    p256dh: arrayBufferToBase64(key),
                    auth: arrayBufferToBase64(auth),
                },
            }),
        });
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            await subscribe();
        }
    } catch (e) {
        console.error('Push notification setup failed:', e);
    }
})();
