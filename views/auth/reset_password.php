<?php
require_once __DIR__ . '/../../includes/header.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    flash_message('Invalid reset link.', 'error');
    redirect('/index.php?page=login');
}
?>

<div class="min-h-screen flex items-center justify-center py-10 sm:py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Reset Password Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 transform hover:scale-[1.01] transition duration-500">
            <!-- Icon Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-tr from-teal-500 to-emerald-500 p-4 rounded-full shadow-lg mb-4 shadow-teal-500/30">
                    <span class="material-symbols-rounded text-white text-5xl">key</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Reset Password</h1>
                <p class="text-gray-500 mt-2">Enter your new password below</p>
            </div>

            <!-- Flash Messages -->
            <?php display_flash(); ?>

            <!-- Reset Password Form -->
            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <!-- New Password Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">lock</span>
                    </div>
                    <input type="password" name="password" required id="password" minlength="6"
                        class="w-full pl-10 pr-12 py-3 bg-white/90 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="New Password (min 6 chars)">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition select-none">
                        <span class="material-symbols-rounded" id="toggleIcon">visibility</span>
                    </button>
                </div>

                <!-- Confirm Password Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">lock</span>
                    </div>
                    <input type="password" name="confirm_password" required id="confirm_password" minlength="6"
                        class="w-full pl-10 pr-12 py-3 bg-white/90 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Confirm New Password">
                    <button type="button" onclick="toggleConfirmPassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition select-none">
                        <span class="material-symbols-rounded" id="toggleConfirmIcon">visibility</span>
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3.5 rounded-xl font-bold font-['Outfit'] hover:from-teal-600 hover:to-emerald-700 transition duration-300 transform hover:scale-[1.02] shadow-lg shadow-teal-500/30 flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">check_circle</span> Reset Password
                </button>
            </form>

            <!-- Back to Login Link -->
            <p class="text-center mt-6 text-gray-600 text-sm flex items-center justify-center">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="text-teal-600 font-bold hover:text-teal-700 bg-teal-50 px-3 py-1 rounded-full hover:bg-teal-100 transition flex items-center">
                    <span class="material-symbols-rounded text-sm mr-1">arrow_back</span> Back to Login
                </a>
            </p>
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

function toggleConfirmPassword() {
    const password = document.getElementById('confirm_password');
    const icon = document.getElementById('toggleConfirmIcon');
    
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
