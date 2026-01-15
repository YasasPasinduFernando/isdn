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

// Register service worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('./service-worker.js')
            .then(reg => console.log('ServiceWorker registered:', reg.scope))
            .catch(err => console.warn('ServiceWorker failed:', err));
    });
}
