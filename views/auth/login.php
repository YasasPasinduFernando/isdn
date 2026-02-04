<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-10 sm:py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Login Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 transform hover:scale-[1.01] transition duration-500">
            <!-- Icon Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-tr from-teal-500 to-emerald-500 p-4 rounded-full shadow-lg mb-4 shadow-teal-500/30">
                    <span class="material-symbols-rounded text-white text-5xl">account_circle</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Welcome Back!</h1>
                <p class="text-gray-500 mt-2">Login to your ISDN account</p>
            </div>

            <!-- Flash Messages -->
            <?php display_flash(); ?>

            <!-- Login Form -->
            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
                <input type="hidden" name="action" value="login">
                
                <!-- Email Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">mail</span>
                    </div>
                    <input type="email" name="email" required 
                        class="w-full pl-10 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Enter your email">
                </div>

                <!-- Password Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">lock</span>
                    </div>
                    <input type="password" name="password" required id="password"
                        class="w-full pl-10 pr-12 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Enter your password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition select-none">
                        <span class="material-symbols-rounded" id="toggleIcon">visibility</span>
                    </button>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="rounded text-teal-600 focus:ring-teal-500 border-gray-300">
                        <span class="ml-2 text-gray-600">Remember me</span>
                    </label>
                    <a href="#" class="text-teal-600 hover:text-teal-700 font-medium hover:underline">Forgot Password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3.5 rounded-xl font-bold font-['Outfit'] hover:from-teal-600 hover:to-emerald-700 transition duration-300 transform hover:scale-[1.02] shadow-lg shadow-teal-500/30 flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">login</span> Login Now
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200/60"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wider">
                    <span class="px-4 bg-transparent text-gray-400 font-medium">Or continue with</span>
                </div>
            </div>

            <!-- Social Login Buttons -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <button class="flex items-center justify-center space-x-2 bg-white/60 backdrop-blur-sm border border-white/50 rounded-xl py-2.5 hover:bg-white/80 transition duration-300 group">
                    <i class="fab fa-google text-red-500 group-hover:scale-110 transition"></i>
                    <span class="text-gray-700 font-medium text-sm">Google</span>
                </button>
                <button class="flex items-center justify-center space-x-2 bg-white/60 backdrop-blur-sm border border-white/50 rounded-xl py-2.5 hover:bg-white/80 transition duration-300 group">
                    <i class="fab fa-facebook text-blue-600 group-hover:scale-110 transition"></i>
                    <span class="text-gray-700 font-medium text-sm">Facebook</span>
                </button>
            </div>

            <!-- Register Link -->
            <p class="text-center mt-6 text-gray-600 text-sm flex items-center justify-center">
                Don't have an account? 
                <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="text-teal-600 font-bold hover:text-teal-700 bg-teal-50 px-3 py-1 rounded-full hover:bg-teal-100 transition ml-1 flex items-center">
                    Register here <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                </a>
            </p>
        </div>

        <!-- Features (Glass Badges) -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-center">
            <div class="glass-panel rounded-xl p-4 transform hover:translate-y-[-2px] transition">
                <div class="text-teal-500 mb-2"><span class="material-symbols-rounded text-3xl">security</span></div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Secure</p>
            </div>
            <div class="glass-panel rounded-xl p-4 transform hover:translate-y-[-2px] transition">
                <div class="text-blue-500 mb-2"><span class="material-symbols-rounded text-3xl">bolt</span></div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Fast</p>
            </div>
            <div class="glass-panel rounded-xl p-4 transform hover:translate-y-[-2px] transition">
                <div class="text-purple-500 mb-2"><span class="material-symbols-rounded text-3xl">headset_mic</span></div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Support</p>
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
        icon.textContent = 'visibility_off';
    } else {
        password.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
