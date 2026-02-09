<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center py-10 sm:py-12 px-4">
    <div class="max-w-md w-full">
        <!-- Register Card -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 transform hover:scale-[1.01] transition duration-500">
            <!-- Icon Header -->
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-tr from-teal-500 to-emerald-500 p-4 rounded-full shadow-lg mb-4 shadow-teal-500/30">
                    <span class="material-symbols-rounded text-white text-5xl">person_add</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Create Account</h1>
                <p class="text-gray-500 mt-2">Join ISDN distribution network</p>
            </div>

            <!-- Flash Messages -->
            <?php display_flash(); ?>

            <!-- Register Form -->
            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-5">
                <input type="hidden" name="action" value="register">
                
                <!-- Full Name Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">person</span>
                    </div>
                    <input type="text" name="username" required 
                        class="w-full pl-10 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Full Name">
                </div>

                <!-- Email Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">mail</span>
                    </div>
                    <input type="email" name="email" required 
                        class="w-full pl-10 pr-4 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Email Address">
                </div>

                <!-- Password Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">lock</span>
                    </div>
                    <input type="password" name="password" required id="password"
                        class="w-full pl-10 pr-12 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all placeholder-gray-400 text-gray-700 shadow-sm"
                        placeholder="Create Password">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-teal-600 transition select-none">
                        <span class="material-symbols-rounded" id="toggleIcon">visibility</span>
                    </button>
                </div>

                <!-- User Role Selection -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400 group-focus-within:text-teal-500 transition-colors">work</span>
                    </div>
                    <select name="role" required 
                        class="w-full pl-10 pr-10 py-3 bg-white/50 backdrop-blur-sm border border-white/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all appearance-none text-gray-700 shadow-sm cursor-pointer">
                        <option value="">Select User Type</option>
                        <option value="customer">Customer (Retailer)</option>
                        <option value="rdc_sales_ref">RDC Sales Representative</option>
                        <option value="rdc_clerk">RDC Clerk</option>
                        <option value="rdc_manager">RDC Manager</option>
                        <option value="logistics_officer">Logistics Officer</option>
                        <option value="rdc_driver">RDC Driver</option>
                        <option value="head_office_manager">Head Office Manager</option>
                        <option value="system_admin">System Admin</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="material-symbols-rounded text-gray-400">expand_more</span>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start">
                    <input type="checkbox" required class="mt-1 rounded text-teal-600 focus:ring-teal-500 border-gray-300">
                    <label class="ml-2 text-sm text-gray-600">
                        I agree to the <a href="#" class="text-teal-600 font-semibold hover:underline">Terms & Conditions</a> and <a href="#" class="text-teal-600 font-semibold hover:underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Register Button -->
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3.5 rounded-xl font-bold font-['Outfit'] hover:from-teal-600 hover:to-emerald-700 transition duration-300 transform hover:scale-[1.02] shadow-lg shadow-teal-500/30 flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">rocket_launch</span> Create Account
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200/60"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-wider">
                    <span class="px-4 bg-transparent text-gray-400 font-medium">Or register with</span>
                </div>
            </div>

            <!-- Social Register Buttons -->
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

            <!-- Login Link -->
            <p class="text-center mt-6 text-gray-600 text-sm flex items-center justify-center">
                Already have an account? 
                <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="text-teal-600 font-bold hover:text-teal-700 bg-teal-50 px-3 py-1 rounded-full hover:bg-teal-100 transition ml-1 flex items-center">
                    Login here <span class="material-symbols-rounded text-sm ml-1">arrow_forward</span>
                </a>
            </p>
        </div>

        <!-- Benefits -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 text-center">
            <div class="glass-panel rounded-xl p-4 transform hover:translate-y-[-2px] transition">
                <div class="text-teal-500 mb-2"><span class="material-symbols-rounded text-3xl">local_shipping</span></div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Fast Delivery</p>
            </div>
            <div class="glass-panel rounded-xl p-4 transform hover:translate-y-[-2px] transition">
                <div class="text-teal-500 mb-2"><span class="material-symbols-rounded text-3xl">sell</span></div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Best Prices</p>
            </div>
            <div class="glass-panel rounded-xl p-4 transform hover:translate-y-[-2px] transition">
                <div class="text-teal-500 mb-2"><span class="material-symbols-rounded text-3xl">verified</span></div>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Verified</p>
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

