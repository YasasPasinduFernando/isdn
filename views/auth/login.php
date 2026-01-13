<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/navigation.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600 py-12">
    <div class="bg-white p-8 rounded-lg shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">🏢 ISDN Login</h1>
            <p class="text-gray-600 mt-2">IslandLink Sales Distribution Network</p>
        </div>

        <?php display_flash(); ?>

        <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
            <input type="hidden" name="action" value="login">
            
            <div>
                <label class="block text-gray-700 font-semibold mb-2">📧 Email</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter your email">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">🔒 Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter your password">
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 transform hover:scale-105">
                🚀 Login
            </button>
        </form>

        <p class="text-center mt-6 text-gray-600">
            Don't have an account? <a href="<?php echo BASE_PATH; ?>/index.php?page=register" class="text-blue-600 font-semibold hover:underline">Register here</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
