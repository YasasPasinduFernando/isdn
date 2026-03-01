<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    redirect('/index.php?page=login');
}

$role = $_SESSION['role'] ?? 'customer';
$canSkip = in_array($role, ['system_admin', 'head_office_manager']);
$allRdcs = isset($pdo) ? $pdo->query("SELECT rdc_id, rdc_name, rdc_code FROM rdcs ORDER BY rdc_name")->fetchAll(PDO::FETCH_ASSOC) : [];
?>

<div class="min-h-screen flex items-center justify-center py-10 sm:py-12 px-4">
    <div class="max-w-md w-full">
        <div class="glass-card rounded-3xl p-6 sm:p-8 transform hover:scale-[1.01] transition duration-500">
            <div class="text-center mb-8">
                <div class="inline-block bg-gradient-to-tr from-teal-500 to-emerald-500 p-4 rounded-full shadow-lg mb-4 shadow-teal-500/30">
                    <span class="material-symbols-rounded text-white text-5xl">location_on</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 font-['Outfit']">Select Your RDC</h1>
                <p class="text-gray-500 mt-2">Choose your preferred Regional Distribution Center to continue</p>
            </div>

            <?php display_flash(); ?>

            <form method="POST" action="<?php echo BASE_PATH; ?>/controllers/AuthController.php" class="space-y-6">
                <input type="hidden" name="action" value="select_rdc">

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Preferred RDC <?php echo $canSkip ? '' : '<span class="text-red-500">*</span>'; ?></label>
                    <select name="rdc_id" <?php echo $canSkip ? '' : 'required'; ?>
                            class="w-full border border-gray-200 bg-white/90 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-teal-500/50 focus:border-teal-500 transition-all text-gray-700">
                        <option value=""><?php echo $canSkip ? 'None / Not applicable' : 'Select your RDC'; ?></option>
                        <?php foreach ($allRdcs as $r): ?>
                            <option value="<?php echo $r['rdc_id']; ?>"><?php echo htmlspecialchars($r['rdc_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-teal-500 to-emerald-600 text-white py-3.5 rounded-xl font-bold font-['Outfit'] hover:from-teal-600 hover:to-emerald-700 transition duration-300 transform hover:scale-[1.02] shadow-lg shadow-teal-500/30 flex items-center justify-center">
                    <span class="material-symbols-rounded mr-2">arrow_forward</span> Continue to Dashboard
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
