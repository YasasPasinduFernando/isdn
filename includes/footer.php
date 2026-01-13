<!-- Sticky Bottom Footer -->
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About Section -->
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-truck-fast text-purple-400 text-2xl"></i>
                        <h3 class="text-xl font-bold">ISDN</h3>
                    </div>
                    <p class="text-gray-400 text-sm">
                        IslandLink Sales Distribution Network - Your trusted partner for wholesale and retail distribution across the island.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-purple-400">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=products" class="text-gray-400 hover:text-white transition"><i class="fas fa-chevron-right text-xs mr-2"></i>Products</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=about" class="text-gray-400 hover:text-white transition"><i class="fas fa-chevron-right text-xs mr-2"></i>About Us</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=contact" class="text-gray-400 hover:text-white transition"><i class="fas fa-chevron-right text-xs mr-2"></i>Contact</a></li>
                        <li><a href="<?php echo BASE_PATH; ?>/index.php?page=faq" class="text-gray-400 hover:text-white transition"><i class="fas fa-chevron-right text-xs mr-2"></i>FAQ</a></li>
                    </ul>
                </div>

                <!-- Regional Distribution Centers -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-purple-400">RDC Locations</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><i class="fas fa-map-marker-alt text-purple-400 mr-2"></i>Central Region - Colombo</li>
                        <li><i class="fas fa-map-marker-alt text-purple-400 mr-2"></i>Northern Region - Jaffna</li>
                        <li><i class="fas fa-map-marker-alt text-purple-400 mr-2"></i>Southern Region - Galle</li>
                        <li><i class="fas fa-map-marker-alt text-purple-400 mr-2"></i>Eastern Region - Batticaloa</li>
                        <li><i class="fas fa-map-marker-alt text-purple-400 mr-2"></i>Western Region - Negombo</li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-purple-400">Contact Us</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        <li class="flex items-center">
                            <i class="fas fa-phone text-purple-400 mr-3"></i>
                            <span>+94 11 234 5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope text-purple-400 mr-3"></i>
                            <span>info@isdn.lk</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-clock text-purple-400 mr-3"></i>
                            <span>Mon - Sat: 8:00 AM - 6:00 PM</span>
                        </li>
                    </ul>

                    <!-- Social Media -->
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center hover:bg-purple-700 transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 mt-8 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        &copy; 2025 <?php echo APP_NAME; ?>. All rights reserved.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0 text-sm">
                        <a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a>
                        <a href="#" class="text-gray-400 hover:text-white transition">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" id="backToTop" class="fixed bottom-8 right-8 bg-purple-600 text-white w-12 h-12 rounded-full shadow-lg hover:bg-purple-700 transition opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="<?php echo BASE_PATH; ?>/assets/js/main.js"></script>
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

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
</body>
</html>