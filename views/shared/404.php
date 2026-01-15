<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="text-center">
        <h1 class="text-6xl sm:text-9xl font-bold text-gray-300">404</h1>
        <p class="text-lg sm:text-2xl font-semibold text-gray-700 mb-4">Page Not Found</p>
        <a href="<?php echo BASE_PATH; ?>/index.php" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 inline-flex justify-center w-full sm:w-auto">
            Go Home
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

