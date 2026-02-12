<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/SystemAdmin.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== USER_ROLE_RDC_CLERK) {
    redirect('/index.php?page=login');
}

$admin  = new SystemAdmin($pdo);
$action = $_GET['action'] ?? 'list';

// ── Handle POST (Create only) ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $act = $_POST['form_action'] ?? '';

        if ($act === 'create') {
            $data = [
                'name'                => trim($_POST['name'] ?? ''),
                'description'         => trim($_POST['description'] ?? ''),
                'product_id'          => (int) ($_POST['product_id'] ?? 0),
                'product_count'       => (int) ($_POST['product_count'] ?? 1),
                'discount_percentage' => (float) ($_POST['discount_percentage'] ?? 0),
                'start_date'          => $_POST['start_date'] ?? '',
                'end_date'            => $_POST['end_date'] ?? '',
                'is_active'           => isset($_POST['is_active']) ? 1 : 0,
            ];

            // Validation
            if (empty($data['name']) || empty($data['product_id']) || empty($data['start_date']) || empty($data['end_date'])) {
                flash_message('Please fill in all required fields.', 'error');
                redirect('/index.php?page=rdc-clerk-promotions&action=add');
            }
            if ($data['discount_percentage'] <= 0 || $data['discount_percentage'] > 100) {
                flash_message('Discount must be between 0.01 and 100.', 'error');
                redirect('/index.php?page=rdc-clerk-promotions&action=add');
            }
            if ($data['end_date'] < $data['start_date']) {
                flash_message('End date cannot be before start date.', 'error');
                redirect('/index.php?page=rdc-clerk-promotions&action=add');
            }

            $id = $admin->createPromotion($data);
            $admin->logAction($_SESSION['user_id'], 'CREATE', 'promotion', $id, "Created promotion: " . $data['name']);
            flash_message('Promotion created successfully!', 'success');
            redirect('/index.php?page=rdc-clerk-promotions');
        }
    } catch (Exception $e) {
        flash_message('Error: ' . $e->getMessage(), 'error');
        redirect('/index.php?page=rdc-clerk-promotions');
    }
}

// ── ADD FORM VIEW ────────────────────────────────────────────
if ($action === 'add') {
    $products = $admin->getProductsForDropdown();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <?php display_flash(); ?>

        <div class="flex items-center gap-3 mb-8">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-promotions"
               class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition">
                <span class="material-symbols-rounded">arrow_back</span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Add New Promotion</h1>
                <p class="text-sm text-gray-500">Create a new promotional offer</p>
            </div>
        </div>

        <form method="POST" action="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-promotions"
              class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
            <input type="hidden" name="form_action" value="create">

            <!-- Name -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Promotion Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm"
                       placeholder="e.g. Cement Bulk Offer">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                    Description
                </label>
                <textarea name="description" rows="3"
                          class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm"
                          placeholder="Brief description of this promotion..."></textarea>
            </div>

            <!-- Product + Min Qty + Discount -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                        Product <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" required
                            class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm">
                        <option value="">-- Select Product --</option>
                        <?php foreach ($products as $prod): ?>
                            <option value="<?php echo $prod['product_id']; ?>">
                                <?php echo htmlspecialchars($prod['product_code'] . ' - ' . $prod['product_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                        Min Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="product_count" required min="1" value="1"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm"
                           placeholder="e.g. 10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                        Discount % <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="discount_percentage" required step="0.01" min="0.01" max="100"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm"
                           placeholder="e.g. 10.00">
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" required
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" required
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm">
                </div>
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                </label>
                <span class="text-sm font-medium text-gray-700">Promotion Active</span>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200/50">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-promotions"
                   class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-white/50 transition text-sm font-medium">Cancel</a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-purple-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-base">add_box</span>
                    Create Promotion
                </button>
            </div>
        </form>
    </div>
</div>

<?php
    require_once __DIR__ . '/../../includes/footer.php';
    return;
}

// ── LIST VIEW ────────────────────────────────────────────────
$search       = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page         = max(1, (int) ($_GET['pg'] ?? 1));
$perPage      = 10;

$promotions  = $admin->getPromotions($page, $perPage, $search, $statusFilter);
$totalPromos = $admin->countPromotions($search, $statusFilter);
$totalPages  = max(1, ceil($totalPromos / $perPage));
$today       = date('Y-m-d');
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <?php display_flash(); ?>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-dashboard"
                   class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition">
                    <span class="material-symbols-rounded">arrow_back</span>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Promotional Items</h1>
                    <p class="text-sm text-gray-500"><?php echo number_format($totalPromos); ?> promotions found</p>
                </div>
            </div>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-promotions&action=add"
               class="mt-4 md:mt-0 px-5 py-2.5 rounded-full bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-purple-200/50 hover:scale-[1.02] transition flex items-center gap-2 w-fit">
                <span class="material-symbols-rounded text-lg">add_box</span> Add Promotion
            </a>
        </div>

        <!-- Filters -->
        <form method="GET" class="glass-card rounded-2xl p-5 mb-6 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="rdc-clerk-promotions">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Promotion or product name..."
                       class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                <select name="status"
                        class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[160px] focus:ring-2 focus:ring-purple-500 transition shadow-sm">
                    <option value="">All</option>
                    <option value="active"   <?php echo $statusFilter === 'active'   ? 'selected' : ''; ?>>Active Now</option>
                    <option value="upcoming"  <?php echo $statusFilter === 'upcoming'  ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="expired"  <?php echo $statusFilter === 'expired'  ? 'selected' : ''; ?>>Expired</option>
                    <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Disabled</option>
                </select>
            </div>
            <button type="submit"
                    class="px-5 py-2 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 text-white font-bold text-sm shadow-lg hover:scale-[1.02] transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">search</span> Search
            </button>
            <?php if ($search || $statusFilter): ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=rdc-clerk-promotions"
                   class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1">
                    <span class="material-symbols-rounded text-base">refresh</span> Reset
                </a>
            <?php endif; ?>
        </form>

        <!-- Promotions Table -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-6">
            <?php if (empty($promotions)): ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-100/50 flex items-center justify-center text-gray-300 mb-4">
                        <span class="material-symbols-rounded text-3xl">loyalty</span>
                    </div>
                    <p class="text-gray-400 font-medium">No promotions found</p>
                    <p class="text-gray-400 text-sm mt-1">Click "Add Promotion" to create your first one.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-3">Promotion</th>
                                <th class="pb-3 pr-3">Product</th>
                                <th class="pb-3 pr-3 text-center">Min Qty</th>
                                <th class="pb-3 pr-3 text-center">Discount</th>
                                <th class="pb-3 pr-3">Period</th>
                                <th class="pb-3 pr-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($promotions as $promo):
                                $isActive  = (int) ($promo['is_active'] ?? 1);
                                $startDate = $promo['start_date'] ?? '';
                                $endDate   = $promo['end_date'] ?? '';

                                if (!$isActive) {
                                    $badgeClass = 'bg-gray-100/50 text-gray-500';
                                    $badgeText  = 'Disabled';
                                    $dotColor   = 'bg-gray-400';
                                } elseif ($endDate < $today) {
                                    $badgeClass = 'bg-red-100/50 text-red-600';
                                    $badgeText  = 'Expired';
                                    $dotColor   = 'bg-red-400';
                                } elseif ($startDate > $today) {
                                    $badgeClass = 'bg-blue-100/50 text-blue-600';
                                    $badgeText  = 'Upcoming';
                                    $dotColor   = 'bg-blue-400';
                                } else {
                                    $badgeClass = 'bg-green-100/50 text-green-600';
                                    $badgeText  = 'Active';
                                    $dotColor   = 'bg-green-500';
                                }
                            ?>
                            <tr class="hover:bg-white/30 transition">
                                <td class="py-3 pr-3">
                                    <div>
                                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($promo['name']); ?></span>
                                        <?php if (!empty($promo['description'])): ?>
                                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[200px]"><?php echo htmlspecialchars($promo['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 pr-3">
                                    <span class="text-gray-700"><?php echo htmlspecialchars($promo['product_name'] ?? 'N/A'); ?></span>
                                    <?php if (!empty($promo['product_code'])): ?>
                                        <span class="text-[10px] text-gray-400 ml-1">(<?php echo htmlspecialchars($promo['product_code']); ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 pr-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100/50 text-indigo-700">
                                        <?php echo number_format($promo['product_count']); ?>+ units
                                    </span>
                                </td>
                                <td class="py-3 pr-3 text-center">
                                    <span class="text-lg font-bold text-purple-600"><?php echo number_format($promo['discount_percentage'], 1); ?>%</span>
                                </td>
                                <td class="py-3 pr-3">
                                    <div class="text-xs text-gray-600">
                                        <div><?php echo date('M d, Y', strtotime($startDate)); ?></div>
                                        <div class="text-gray-400">to</div>
                                        <div><?php echo date('M d, Y', strtotime($endDate)); ?></div>
                                    </div>
                                </td>
                                <td class="py-3 pr-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold <?php echo $badgeClass; ?> px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full <?php echo $dotColor; ?>"></span>
                                        <?php echo $badgeText; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-2">
            <?php for ($i = 1; $i <= $totalPages; $i++):
                $isActivePg = $i === $page;
                $qs = http_build_query(array_merge($_GET, ['pg' => $i]));
            ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $qs; ?>"
                   class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition <?php echo $isActivePg ? 'bg-purple-500 text-white shadow-lg shadow-purple-200/50' : 'bg-white/50 text-gray-600 hover:bg-white/70 border border-white/60'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
