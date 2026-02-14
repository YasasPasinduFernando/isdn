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

    <script src="<?php echo BASE_PATH; ?>/assets/js/main.js?v=<?= time() ?>"></script>
    <script>
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
</body>
</html>