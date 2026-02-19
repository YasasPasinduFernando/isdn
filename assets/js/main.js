// Main JavaScript
console.log('ISDN System Initialized');

// Add smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Register service worker for PWA support.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('./service-worker.js')
            .then(function (reg) {
                // Ask browser to check for updates immediately.
                reg.update();
                console.log('ServiceWorker registered:', reg.scope);
            })
            .catch(function (err) {
                console.warn('ServiceWorker registration failed:', err);
            });
    });
}

// PWA install prompt flow (shows a button when install is available).
(function () {
    let deferredPrompt = null;
    let installBtn = null;

    function hideInstallButton() {
        if (!installBtn) return;
        installBtn.style.display = 'none';
    }

    function showInstallButton() {
        if (!installBtn) {
            installBtn = document.createElement('button');
            installBtn.type = 'button';
            installBtn.id = 'pwa-install-btn';
            installBtn.textContent = 'Install App';
            installBtn.style.position = 'fixed';
            installBtn.style.right = '16px';
            installBtn.style.bottom = '16px';
            installBtn.style.zIndex = '9999';
            installBtn.style.padding = '10px 14px';
            installBtn.style.border = '0';
            installBtn.style.borderRadius = '9999px';
            installBtn.style.background = 'linear-gradient(90deg,#14b8a6,#059669)';
            installBtn.style.color = '#fff';
            installBtn.style.fontWeight = '700';
            installBtn.style.boxShadow = '0 10px 25px rgba(20,184,166,.35)';
            installBtn.style.cursor = 'pointer';
            installBtn.style.display = 'none';

            installBtn.addEventListener('click', async function () {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                try {
                    await deferredPrompt.userChoice;
                } catch (e) {
                    // no-op
                }
                deferredPrompt = null;
                hideInstallButton();
            });

            document.body.appendChild(installBtn);
        }

        installBtn.style.display = 'inline-flex';
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        showInstallButton();
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        hideInstallButton();
    });
})();
