<!-- Sticky Bottom Footer -->
    <footer class="bg-gray-900 text-white mt-auto border-t border-gray-800">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About Section -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-1.5 rounded-lg text-white flex items-center justify-center">
                            <span class="material-symbols-rounded text-lg">local_shipping</span>
                        </div>
                        <h3 class="text-xl font-bold font-['Outfit']">ISDN</h3>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        IslandLink Sales Distribution Network - Your trusted partner for modern wholesale and retail distribution.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-white">Quick Links</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="text-gray-400 hover:text-teal-400 transition flex items-center"><span class="material-symbols-rounded text-[16px] mr-2 opacity-50">navigate_next</span>Products</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=about" class="text-gray-400 hover:text-teal-400 transition flex items-center"><span class="material-symbols-rounded text-[16px] mr-2 opacity-50">navigate_next</span>About Us</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=contact" class="text-gray-400 hover:text-teal-400 transition flex items-center"><span class="material-symbols-rounded text-[16px] mr-2 opacity-50">navigate_next</span>Contact</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=faq" class="text-gray-400 hover:text-teal-400 transition flex items-center"><span class="material-symbols-rounded text-[16px] mr-2 opacity-50">navigate_next</span>FAQ</a></li>
                    </ul>
                </div>

                <!-- RDC Locations -->
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-white">RDC Locations</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-start"><span class="material-symbols-rounded text-teal-500 mt-0.5 mr-2 text-[18px]">location_on</span><span>Central Region - Kandy</span></li>
                        <li class="flex items-start"><span class="material-symbols-rounded text-teal-500 mt-0.5 mr-2 text-[18px]">location_on</span><span>Northern Region - Jaffna</span></li>
                        <li class="flex items-start"><span class="material-symbols-rounded text-teal-500 mt-0.5 mr-2 text-[18px]">location_on</span><span>Southern Region - Galle</span></li>
                        <li class="flex items-start"><span class="material-symbols-rounded text-teal-500 mt-0.5 mr-2 text-[18px]">location_on</span><span>Eastern Region - Batticaloa</span></li>
                        <li class="flex items-start"><span class="material-symbols-rounded text-teal-500 mt-0.5 mr-2 text-[18px]">location_on</span><span>Western Region - Colombo</span></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-white">Contact Us</h4>
                    <ul class="space-y-4 text-sm text-gray-400">
                        <li class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center mr-3 text-teal-500">
                                <span class="material-symbols-rounded text-[18px]">call</span>
                            </div>
                            <span>+94 11 234 5678</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center mr-3 text-teal-500">
                                <span class="material-symbols-rounded text-[18px]">mail</span>
                            </div>
                            <span>info@isdn.lk</span>
                        </li>
                    </ul>

                    <!-- Social Media -->
                    <div class="flex space-x-3 mt-6">
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 text-gray-400">
                            <i class="fab fa-facebook-f text-sm"></i>
                        </a>
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 text-gray-400">
                            <i class="fab fa-twitter text-sm"></i>
                        </a>
                        <a href="#" class="w-8 h-8 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-teal-600 hover:text-white transition duration-300 text-gray-400">
                            <i class="fab fa-instagram text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 mt-12 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-gray-500 text-sm">
                        &copy; 2026 <?php echo APP_NAME; ?>. All rights reserved.
                    </p>
                    <div class="flex space-x-6 text-sm">
                        <a href="#" class="text-gray-500 hover:text-teal-400 transition">Privacy Policy</a>
                        <a href="#" class="text-gray-500 hover:text-teal-400 transition">Terms</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" id="backToTop" class="fixed bottom-8 right-8 bg-teal-600 text-white w-10 h-10 rounded-full shadow-lg hover:bg-teal-700 transition opacity-0 invisible flex items-center justify-center transform hover:scale-110 z-50">
        <span class="material-symbols-rounded text-xl">arrow_upward</span>
    </button>

    <!-- PWA Install Modal (cross-platform; safe for existing PHP app) -->
    <style>
        .isdn-pwa-overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            background: rgba(15, 23, 42, 0.55);
            z-index: 100000;
        }
        .isdn-pwa-overlay.is-open {
            display: flex;
            animation: isdnPwaFadeIn .22s ease;
        }
        @keyframes isdnPwaFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .isdn-pwa-modal {
            width: min(92vw, 420px);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(2, 6, 23, .28);
            padding: 20px;
        }
        .isdn-pwa-title {
            margin: 0 0 8px;
            font-size: 20px;
            line-height: 1.2;
            font-weight: 700;
            color: #0f172a;
        }
        .isdn-pwa-text {
            margin: 0 0 14px;
            color: #475569;
            font-size: 14px;
            line-height: 1.5;
        }
        .isdn-pwa-steps {
            margin: 0 0 14px 18px;
            color: #334155;
            font-size: 14px;
            line-height: 1.5;
        }
        .isdn-pwa-steps li { margin: 4px 0; }
        .isdn-pwa-options {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 13px;
        }
        .isdn-pwa-actions {
            margin-top: 14px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .isdn-pwa-btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .isdn-pwa-btn-muted {
            background: #e2e8f0;
            color: #0f172a;
        }
        .isdn-pwa-btn-primary {
            background: linear-gradient(90deg, #14b8a6, #059669);
            color: #fff;
        }
    </style>

    <div id="isdn-pwa-overlay" class="isdn-pwa-overlay" aria-hidden="true">
        <div class="isdn-pwa-modal" role="dialog" aria-modal="true" aria-labelledby="isdn-pwa-title">
            <h3 id="isdn-pwa-title" class="isdn-pwa-title">Install ISDN App</h3>
            <p id="isdn-pwa-text" class="isdn-pwa-text"></p>
            <ol id="isdn-pwa-ios-steps" class="isdn-pwa-steps" style="display:none;">
                <li>Tap the <strong>Share</strong> button in Safari.</li>
                <li>Tap <strong>Add to Home Screen</strong>.</li>
            </ol>
            <label class="isdn-pwa-options">
                <input type="checkbox" id="isdn-pwa-hide">
                <span>Don't show again</span>
            </label>
            <div class="isdn-pwa-actions">
                <button type="button" id="isdn-pwa-later" class="isdn-pwa-btn isdn-pwa-btn-muted">Not now</button>
                <button type="button" id="isdn-pwa-install" class="isdn-pwa-btn isdn-pwa-btn-primary">Install now</button>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_PATH; ?>/assets/js/main.js?v=<?= time() ?>"></script>
    <script>
        // Cross-platform PWA install controller.
        // Wrapped in IIFE + single-init guard to avoid duplicate listeners.
        (function () {
            if (window.__ISDN_PWA_INSTALL_INIT) return;
            window.__ISDN_PWA_INSTALL_INIT = true;

            var HIDE_KEY = 'isdn_pwa_hide_install_v1';
            var basePath = '<?php echo rtrim(BASE_PATH, '/'); ?>';
            var overlay = document.getElementById('isdn-pwa-overlay');
            var textEl = document.getElementById('isdn-pwa-text');
            var iosStepsEl = document.getElementById('isdn-pwa-ios-steps');
            var installBtn = document.getElementById('isdn-pwa-install');
            var laterBtn = document.getElementById('isdn-pwa-later');
            var hideCheck = document.getElementById('isdn-pwa-hide');
            var deferredPrompt = null;

            if (!overlay || !textEl || !iosStepsEl || !installBtn || !laterBtn || !hideCheck) return;

            function getCookie(name) {
                var value = '; ' + document.cookie;
                var parts = value.split('; ' + name + '=');
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            }

            function setCookie(name, value, maxAgeSeconds) {
                var secure = window.location.protocol === 'https:' ? '; Secure' : '';
                document.cookie = name + '=' + value + '; Max-Age=' + maxAgeSeconds + '; Path=/; SameSite=Lax' + secure;
            }

            var isSecureContextOk = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            if (!isSecureContextOk || isStandalone || getCookie(HIDE_KEY) === '1') return;

            var ua = navigator.userAgent.toLowerCase();
            var isIOS = /iphone|ipad|ipod/.test(ua);
            var isSafari = /safari/.test(ua) && !/crios|fxios|edgios|opr|opera|chrome/.test(ua);
            var isAndroid = /android/.test(ua);
            var isDesktop = !isIOS && !isAndroid;
            var hasPromptEvent = false;

            function openModal(mode) {
                if (mode === 'ios-guide') {
                    textEl.textContent = 'Install on iPhone/iPad using Safari:';
                    iosStepsEl.style.display = '';
                    installBtn.style.display = 'none';
                } else if (mode === 'ios-open-safari') {
                    textEl.textContent = 'To install on iPhone/iPad, open this site in Safari first, then use Share > Add to Home Screen.';
                    iosStepsEl.style.display = 'none';
                    installBtn.style.display = 'none';
                } else if (mode === 'manual-guide') {
                    textEl.textContent = 'Install manually from browser menu: tap menu (three dots) and choose Install app or Add to Home screen.';
                    iosStepsEl.style.display = 'none';
                    installBtn.style.display = 'none';
                } else {
                    textEl.textContent = isAndroid
                        ? 'Install this app on Android for a faster app-like experience.'
                        : (isDesktop ? 'Install this app on your desktop for quick access.' : 'Install this app for a better experience.');
                    iosStepsEl.style.display = 'none';
                    installBtn.style.display = '';
                }
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                if (hideCheck.checked) setCookie(HIDE_KEY, '1', 86400); // 1 day
                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');
            }

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeModal();
            });
            laterBtn.addEventListener('click', closeModal);

            installBtn.addEventListener('click', function () {
                if (!deferredPrompt) return closeModal();
                deferredPrompt.prompt();
                deferredPrompt.userChoice.finally(function () {
                    deferredPrompt = null;
                    closeModal();
                });
            });

            // Register service worker with BASE_PATH to support /isdn subfolder hosting.
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register(basePath + '/service-worker.js', { scope: basePath + '/' })
                        .catch(function (err) {
                            console.warn('ServiceWorker registration failed:', err);
                        });
                });
            }

            // Android + Windows (Chrome/Edge)
            window.addEventListener('beforeinstallprompt', function (event) {
                hasPromptEvent = true;
                event.preventDefault(); // prevent browser mini-infobar
                deferredPrompt = event;
                openModal('install');
            });

            window.addEventListener('appinstalled', function () {
                deferredPrompt = null;
                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');
            });

            // iOS Safari fallback guide (no forced install prompt support).
            if (isIOS && isSafari) {
                setTimeout(function () { openModal('ios-guide'); }, 900);
            } else if (isIOS) {
                // iOS non-Safari browsers cannot install PWAs directly.
                setTimeout(function () { openModal('ios-open-safari'); }, 900);
            } else if (isAndroid || isDesktop) {
                // If install prompt event does not appear, show manual install steps.
                setTimeout(function () {
                    if (!hasPromptEvent && !deferredPrompt) {
                        openModal('manual-guide');
                    }
                }, 2500);
            }
        })();

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Profile dropdown toggle (click to open/close; desktop also supports hover)
        function toggleProfileDropdown() {
            const menu = document.getElementById('profile-dropdown-menu');
            if (!menu) return;
            menu.classList.toggle('opacity-0');
            menu.classList.toggle('invisible');
        }
        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('profile-dropdown-wrap');
            const menu = document.getElementById('profile-dropdown-menu');
            if (!wrap || !menu) return;
            if (!wrap.contains(e.target)) {
                menu.classList.add('opacity-0', 'invisible');
            }
        });

        // Back to top button
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.classList.remove('opacity-0', 'invisible');
            } else {
                backToTop.classList.add('opacity-0', 'invisible');
            }
        });

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
        <script src="js/product.js?v=<?= time() ?>"></script>
        <script src="js/shopping_cart.js?v=<?= time() ?>"></script>
        <script src="js/sales_order.js?v=<?= time() ?>"></script>
        <script src="js/filter_drawer.js?v=<?= time() ?>"></script>
        <script src="js/customer_orders_filter_drawer.js?v=<?= time() ?>"></script>
        <script src="js/checkout.js?v=<?= time() ?>"></script>
</body>
</html>
