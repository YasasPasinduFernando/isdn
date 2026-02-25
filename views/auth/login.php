<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="hidden lg:block">
    <?php
    $authMode = 'login';
    require __DIR__ . '/partials/auth_swap.php';
    ?>
</div>

<div class="lg:hidden min-h-screen flex items-center justify-center py-10 sm:py-12 px-4">
    <div class="max-w-md w-full">
        <div class="glass-card rounded-3xl p-6 sm:p-8 transform hover:scale-[1.01] transition duration-500">
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-tr from-teal-500 to-emerald-500 p-4 rounded-full shadow-lg mb-4 shadow-teal-500/30">
                    <span class="material-symbols-rounded text-white text-5xl">account_circle</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Welcome Back!</h1>
                <p class="text-gray-500 mt-2">Login to your ISDN account</p>
            </div>

            <?php display_flash(); ?>

            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
                <input type="hidden" name="action" value="login">

                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">mail</span>
                    </div>
                    <input type="email" name="email" required
                        class="w-full pl-10 pr-4 py-3 bg-white/90 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Enter your email">
                </div>

                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">lock</span>
                    </div>
                    <input type="password" name="password" required id="password"
                        class="w-full pl-10 pr-12 py-3 bg-white/90 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Enter your password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition select-none">
                        <span class="material-symbols-rounded" id="toggleIcon">visibility</span>
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="rounded text-teal-600 focus:ring-teal-500 border-gray-300">
                        <span class="ml-2 text-gray-600">Remember me</span>
                    </label>
                    <a href="<?php echo BASE_PATH; ?>/index.php?page=forgot-password" class="text-teal-600 hover:text-teal-700 font-medium hover:underline">Forgot Password?</a>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3.5 rounded-xl font-bold font-['Outfit'] hover:from-teal-600 hover:to-emerald-700 transition duration-300 transform hover:scale-[1.02] shadow-lg shadow-teal-500/30 flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">login</span> Login Now
                </button>
            </form>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200/60"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wider">
                    <span class="px-4 bg-transparent text-gray-400 font-medium">Or continue with</span>
                </div>
            </div>

            <a href="<?php echo BASE_PATH; ?>/controllers/AuthController.php?action=google_login"
               class="w-full flex items-center justify-center space-x-3 bg-white/70 backdrop-blur-sm border border-white/50 rounded-xl py-3 hover:bg-white/90 hover:shadow-md transition duration-300 group">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span class="text-gray-700 font-semibold text-sm">Continue with Google</span>
            </a>

            <p class="text-center mt-6 text-gray-600 text-sm flex items-center justify-center">
                Don't have an account?
                <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="text-teal-600 font-bold hover:text-teal-700 bg-teal-50 px-3 py-1 rounded-full hover:bg-teal-100 transition ml-1 flex items-center">
                    Register here <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                </a>
            </p>
        </div>

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
    if (!password || !icon) return;
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
