<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-500 to-teal-600 py-12">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">📝 Register</h1>

        <?php display_flash(); ?>

        <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-4">
            <input type="hidden" name="action" value="register">
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">👤 Full Name</label>
                <input type="text" name="username" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="Enter your full name">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">📧 Email</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="Enter your email">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">🔒 Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    placeholder="Create a password">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">🏪 User Type</label>
                <select name="role" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="customer">Customer (Retailer)</option>
                    <option value="rdc_staff">RDC Staff</option>
                    <option value="logistics">Logistics Team</option>
                </select>
            </div>

            <button type="submit" 
                class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-200 transform hover:scale-105">
                ✅ Register
            </button>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Already have an account? <a href="<?php echo BASE_PATH; ?>/index.php?page=login" class="text-green-600 font-semibold hover:underline">Login here</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
