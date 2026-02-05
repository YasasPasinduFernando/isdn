<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="bg-gradient-to-br from-purple-50 to-blue-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- text header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Payment Page 🚚</h1>
            <p class="text-gray-600">Complete your payments here</p>
        </div>

        
</div>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
