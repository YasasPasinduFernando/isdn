<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-10 sm:py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Forgot Password Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 transform hover:scale-[1.01] transition duration-500">
            <!-- Icon Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-tr from-amber-500 to-orange-500 p-4 rounded-full shadow-lg mb-4 shadow-amber-500/30">
                    <span class="material-symbols-rounded text-white text-5xl">lock_reset</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Forgot Password?</h1>
                <p class="text-gray-500 mt-2">Enter your email and we'll send you a reset link</p>
            </div>

            <!-- Flash Messages -->
            <?php display_flash(); ?>

            <!-- Forgot Password Form -->
            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
                <input type="hidden" name="action" value="forgot_password">
                
                <!-- Email Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-amber-500 transition-colors">mail</span>
                    </div>
                    <input type="email" name="email" required 
                        class="w-full pl-10 pr-4 py-3 bg-white/90 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Enter your registered email">
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-amber-500 to-orange-600 text-white py-3.5 rounded-xl font-bold font-['Outfit'] hover:from-amber-600 hover:to-orange-700 transition duration-300 transform hover:scale-[1.02] shadow-lg shadow-amber-500/30 flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">send</span> Send Reset Link
                </button>
            </form>

            <!-- Info -->
            <div class="mt-6 bg-blue-50/60 backdrop-blur-sm border border-blue-100 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-rounded text-blue-500 mt-0.5 text-xl">info</span>
                    <div class="text-sm text-blue-700">
                        <p>A password reset link will be sent to your email. The link expires in <strong>1 hour</strong>.</p>
                        <p class="mt-1 text-blue-500">Check your spam folder if you don't see the email.</p>
                    </div>
                </div>
            </div>

            <!-- Back to Login Link -->
            <p class="text-center mt-6 text-gray-600 text-sm flex items-center justify-center">
                Remember your password?
                <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="text-teal-600 font-bold hover:text-teal-700 bg-teal-50 px-3 py-1 rounded-full hover:bg-teal-100 transition ml-1 flex items-center">
                    Back to Login <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                </a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
