<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
    <div class="max-w-md w-full">
        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 transform hover:scale-105 transition duration-300">
            <!-- Icon Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-r from-green-500 to-teal-600 p-4 rounded-full shadow-lg mb-4">
                    <i class="fas fa-user-plus text-white text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Create Account 🎉</h1>
                <p class="text-gray-600 mt-2">Join ISDN distribution network</p>
            </div>

            <!-- Flash Messages -->
            <?php display_flash(); ?>

            <!-- Register Form -->
            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-5">
                <input type="hidden" name="action" value="register">
                
                <!-- Full Name Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="username" required 
                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="Full Name">
                </div>

                <!-- Email Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" required 
                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="Email Address">
                </div>

                <!-- Password Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" required id="password"
                        class="w-full pl-10 pr-12 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="Create Password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="toggleIcon"></i>
                    </button>
                </div>

                <!-- User Role Selection -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-briefcase text-gray-400"></i>
                    </div>
                    <select name="role" required 
                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition appearance-none bg-white">
                        <option value="">Select User Type</option>
                        <option value="customer">🛒 Customer (Retailer)</option>
                        <option value="rdc_staff">🏢 RDC Staff</option>
                        <option value="logistics">🚚 Logistics Team</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start">
                    <input type="checkbox" required class="mt-1 rounded text-green-600 focus:ring-green-500">
                    <label class="ml-2 text-sm text-gray-600">
                        I agree to the <a href="#" class="text-green-600 font-semibold hover:underline">Terms & Conditions</a> and <a href="#" class="text-green-600 font-semibold hover:underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Register Button -->
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-green-500 to-teal-600 text-white py-3 rounded-lg font-semibold hover:from-green-600 hover:to-teal-700 transition duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-rocket mr-2"></i>Create Account
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Or register with</span>
                </div>
            </div>

            <!-- Social Register Buttons -->
            <div class="grid grid-cols-2 gap-4">
                <button class="flex items-center justify-center space-x-2 border-2 border-gray-300 rounded-lg py-2 hover:bg-gray-50 transition">
                    <i class="fab fa-google text-red-500"></i>
                    <span class="text-gray-700 font-semibold">Google</span>
                </button>
                <button class="flex items-center justify-center space-x-2 border-2 border-gray-300 rounded-lg py-2 hover:bg-gray-50 transition">
                    <i class="fab fa-facebook text-blue-600"></i>
                    <span class="text-gray-700 font-semibold">Facebook</span>
                </button>
            </div>

            <!-- Login Link -->
            <p class="text-center mt-6 text-gray-600">
                Already have an account? 
                <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="text-green-600 font-semibold hover:text-green-700">
                    Login here <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </p>
        </div>

        <!-- Benefits -->
        <div class="mt-8 grid grid-cols-3 gap-4 text-center text-white">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-shipping-fast text-2xl mb-2"></i>
                <p class="text-sm font-semibold">Fast Delivery</p>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-tags text-2xl mb-2"></i>
                <p class="text-sm font-semibold">Best Prices</p>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-shield-check text-2xl mb-2"></i>
                <p class="text-sm font-semibold">Verified</p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');
    
    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>