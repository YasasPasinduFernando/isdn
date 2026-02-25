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

// PWA registration/install flow is intentionally handled in footer.php to avoid duplicate listeners.
