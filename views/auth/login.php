<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-10 sm:py-12 px-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="max-w-md w-full">
        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 transform hover:scale-105 transition duration-300">
            <!-- Icon Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-r from-purple-600 to-blue-600 p-4 rounded-full shadow-lg mb-4">
                    <i class="fas fa-user-shield text-white text-4xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">Welcome Back! 👋</h1>
                <p class="text-gray-600 mt-2">Login to your ISDN account</p>
            </div>

            <!-- Flash Messages -->
            <?php display_flash(); ?>

            <!-- Login Form -->
            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
                <input type="hidden" name="action" value="login">
                
                <!-- Email Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" required 
                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="Enter your email">
                </div>

                <!-- Password Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" required id="password"
                        class="w-full pl-10 pr-12 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        placeholder="Enter your password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="toggleIcon"></i>
                    </button>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-gray-600">Remember me</span>
                    </label>
                    <a href="#" class="text-purple-600 hover:text-purple-700 font-semibold">Forgot Password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-purple-600 to-blue-600 text-white py-3 rounded-lg font-semibold hover:from-purple-700 hover:to-blue-700 transition duration-300 transform hover:scale-105 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login Now
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>

            <!-- Social Login Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button class="flex items-center justify-center space-x-2 border-2 border-gray-300 rounded-lg py-2 hover:bg-gray-50 transition">
                    <i class="fab fa-google text-red-500"></i>
                    <span class="text-gray-700 font-semibold">Google</span>
                </button>
                <button class="flex items-center justify-center space-x-2 border-2 border-gray-300 rounded-lg py-2 hover:bg-gray-50 transition">
                    <i class="fab fa-facebook text-blue-600"></i>
                    <span class="text-gray-700 font-semibold">Facebook</span>
                </button>
            </div>

            <!-- Register Link -->
            <p class="text-center mt-6 text-gray-600">
                Don't have an account? 
                <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="text-purple-600 font-semibold hover:text-purple-700">
                    Register here <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </p>
        </div>

        <!-- Features -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-center text-white">
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-shield-alt text-2xl mb-2"></i>
                <p class="text-sm font-semibold">Secure</p>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-bolt text-2xl mb-2"></i>
                <p class="text-sm font-semibold">Fast</p>
            </div>
            <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4">
                <i class="fas fa-headset text-2xl mb-2"></i>
                <p class="text-sm font-semibold">24/7 Support</p>
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
