<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/SystemAdmin.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== USER_ROLE_SYSTEM_ADMIN) {
    redirect('/index.php?page=login');
}

$admin  = new SystemAdmin($pdo);
$action = $_GET['action'] ?? 'list';

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $act = $_POST['form_action'] ?? '';

        if ($act === 'create' || $act === 'update') {
            $imgUrl = null;
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imgUrl = $admin->handleImageUpload($_FILES['image']);
                if (!$imgUrl) {
                    flash_message('Invalid image file. Max 5MB, JPEG/PNG/GIF/WEBP only.', 'error');
                    redirect('/index.php?page=system-admin-products&action=' . ($act === 'update' ? 'edit&id='.$_POST['product_id'] : 'add'));
                }
            }

            $data = [
                'product_code'        => trim($_POST['product_code'] ?? ''),
                'product_name'        => trim($_POST['product_name'] ?? ''),
                'category'            => trim($_POST['category'] ?? ''),
                'unit_price'          => (float) ($_POST['unit_price'] ?? 0),
                'minimum_stock_level' => (int) ($_POST['minimum_stock_level'] ?? 100),
                'is_active'           => isset($_POST['is_active']) ? 1 : 0,
            ];
            if ($imgUrl) $data['image_url'] = $imgUrl;

            if ($act === 'create') {
                $id = $admin->createProduct($data);
                $admin->logAction($_SESSION['user_id'], 'CREATE', 'product', $id, "Created product: " . $data['product_name']);
                flash_message('Product created!', 'success');
            } else {
                $pid = (int) $_POST['product_id'];
                $admin->updateProduct($pid, $data);
                $admin->logAction($_SESSION['user_id'], 'UPDATE', 'product', $pid, "Updated product: " . $data['product_name']);
                flash_message('Product updated!', 'success');
            }
            redirect('/index.php?page=system-admin-products');

        } elseif ($act === 'toggle' && !empty($_POST['product_id'])) {
            $pid = (int) $_POST['product_id'];
            $admin->toggleProductActive($pid);
            $admin->logAction($_SESSION['user_id'], 'TOGGLE', 'product', $pid, "Toggled product active status");
            flash_message('Product status updated.', 'success');
            redirect('/index.php?page=system-admin-products');

        } elseif ($act === 'delete' && !empty($_POST['product_id'])) {
            $pid = (int) $_POST['product_id'];
            $p = $admin->getProductById($pid);
            $admin->deleteProduct($pid);
            $admin->logAction($_SESSION['user_id'], 'DELETE', 'product', $pid, "Deleted product: " . ($p['product_name'] ?? '#'.$pid));
            flash_message('Product deleted.', 'success');
            redirect('/index.php?page=system-admin-products');
        }
    } catch (Exception $e) {
        flash_message('Error: ' . $e->getMessage(), 'error');
        redirect('/index.php?page=system-admin-products');
    }
}

// ── FORM VIEW ────────────────────────────────────────────────
if ($action === 'add' || $action === 'edit') {
    $editProduct = null;
    if ($action === 'edit' && !empty($_GET['id'])) {
        $editProduct = $admin->getProductById((int) $_GET['id']);
        if (!$editProduct) {
            flash_message('Product not found.', 'error');
            redirect('/index.php?page=system-admin-products');
        }
    }
    $categories = $admin->getProductCategories();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <?php display_flash(); ?>

        <div class="flex items-center gap-3 mb-8">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']"><?php echo $editProduct ? 'Edit Product' : 'Add New Product'; ?></h1>
                <p class="text-sm text-gray-500"><?php echo $editProduct ? 'Update '.$editProduct['product_code'] : 'Add a new catalogue item'; ?></p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" action="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products" class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
            <input type="hidden" name="form_action" value="<?php echo $editProduct ? 'update' : 'create'; ?>">
            <?php if ($editProduct): ?><input type="hidden" name="product_id" value="<?php echo $editProduct['product_id']; ?>"><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Product Code <span class="text-red-500">*</span></label>
                    <input type="text" name="product_code" required value="<?php echo htmlspecialchars($editProduct['product_code'] ?? ''); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm" placeholder="e.g. P009">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="product_name" required value="<?php echo htmlspecialchars($editProduct['product_name'] ?? ''); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Category <span class="text-red-500">*</span></label>
                    <input type="text" name="category" required list="categoryList" value="<?php echo htmlspecialchars($editProduct['category'] ?? ''); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm" placeholder="e.g. Construction">
                    <datalist id="categoryList">
                        <?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat); ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Unit Price (Rs) <span class="text-red-500">*</span></label>
                    <input type="number" name="unit_price" required step="0.01" min="0" value="<?php echo htmlspecialchars($editProduct['unit_price'] ?? ''); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Min Stock Level</label>
                    <input type="number" name="minimum_stock_level" min="0" value="<?php echo htmlspecialchars($editProduct['minimum_stock_level'] ?? 100); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Product Image</label>
                <div class="flex items-center gap-4">
                    <?php if (!empty($editProduct['image_url'])): ?>
                        <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($editProduct['image_url']); ?>" alt="Current" class="w-16 h-16 rounded-xl object-cover border border-white/60 shadow-sm">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"
                           class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition">
                </div>
                <p class="text-xs text-gray-400 mt-1">Max 5MB. JPEG, PNG, GIF, or WebP.</p>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?php echo (!$editProduct || ($editProduct['is_active'] ?? 1)) ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-500"></div>
                </label>
                <span class="text-sm font-medium text-gray-700">Product Active</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200/50">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products" class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-white/50 transition text-sm font-medium">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-blue-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-base"><?php echo $editProduct ? 'save' : 'add_box'; ?></span>
                    <?php echo $editProduct ? 'Save Changes' : 'Create Product'; ?>
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
$search   = trim($_GET['search'] ?? '');
$catFilter = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['pg'] ?? 1));
$perPage  = 10;

$products      = $admin->getProducts($page, $perPage, $search, $catFilter);
$totalProducts = $admin->countProducts($search, $catFilter);
$totalPages    = max(1, ceil($totalProducts / $perPage));
$categories    = $admin->getProductCategories();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <?php display_flash(); ?>

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-dashboard" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Product Management</h1>
                    <p class="text-sm text-gray-500"><?php echo number_format($totalProducts); ?> products found</p>
                </div>
            </div>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products&action=add" class="mt-4 md:mt-0 px-5 py-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-bold text-sm shadow-lg shadow-blue-200/50 hover:scale-[1.02] transition flex items-center gap-2 w-fit">
                <span class="material-symbols-rounded text-lg">add_box</span> Add Product
            </a>
        </div>

        <!-- Filters -->
        <form method="GET" class="glass-card rounded-2xl p-5 mb-6 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="system-admin-products">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name or code..."
                       class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Category</label>
                <select name="category" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[160px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $catFilter === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-sm shadow-lg hover:scale-[1.02] transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">search</span> Search
            </button>
            <?php if ($search || $catFilter): ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1"><span class="material-symbols-rounded text-base">refresh</span> Reset</a>
            <?php endif; ?>
        </form>

        <!-- Products Table -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-6">
            <?php if (empty($products)): ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-100/50 flex items-center justify-center text-gray-300 mb-4"><span class="material-symbols-rounded text-3xl">inventory_2</span></div>
                    <p class="text-gray-400">No products found</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                                <th class="pb-3 pr-3">Code</th>
                                <th class="pb-3 pr-3">Product</th>
                                <th class="pb-3 pr-3">Category</th>
                                <th class="pb-3 pr-3">Price</th>
                                <th class="pb-3 pr-3">Total Stock</th>
                                <th class="pb-3 pr-3">Status</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/50">
                            <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-white/30 transition group">
                                <td class="py-3 pr-3 font-mono text-xs text-gray-500"><?php echo htmlspecialchars($p['product_code']); ?></td>
                                <td class="py-3 pr-3">
                                    <div class="flex items-center gap-3">
                                        <?php if ($p['image_url']): ?>
                                            <img src="<?php echo BASE_PATH . '/' . htmlspecialchars($p['image_url']); ?>" class="w-9 h-9 rounded-lg object-cover border border-white/60" alt="">
                                        <?php else: ?>
                                            <div class="w-9 h-9 rounded-lg bg-gray-100/50 flex items-center justify-center text-gray-300"><span class="material-symbols-rounded text-base">image</span></div>
                                        <?php endif; ?>
                                        <span class="font-semibold text-gray-800"><?php echo htmlspecialchars($p['product_name']); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 pr-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100/50 text-indigo-700"><?php echo htmlspecialchars($p['category'] ?? '-'); ?></span></td>
                                <td class="py-3 pr-3 font-semibold text-gray-800">Rs.<?php echo number_format($p['unit_price'], 2); ?></td>
                                <td class="py-3 pr-3">
                                    <?php $stockClass = $p['total_stock'] < ($p['minimum_stock_level'] ?? 100) ? 'text-red-600 font-bold' : 'text-gray-600'; ?>
                                    <span class="<?php echo $stockClass; ?>"><?php echo number_format($p['total_stock']); ?></span>
                                    <span class="text-gray-400 text-[10px]">/ min <?php echo number_format($p['minimum_stock_level'] ?? 0); ?></span>
                                </td>
                                <td class="py-3 pr-3">
                                    <?php if ($p['is_active']): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-green-600"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-red-500"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition">
                                        <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-products&action=edit&id=<?php echo $p['product_id']; ?>" class="w-8 h-8 rounded-lg bg-blue-100/50 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition" title="Edit"><span class="material-symbols-rounded text-base">edit</span></a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Toggle product status?')">
                                            <input type="hidden" name="form_action" value="toggle">
                                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                            <button class="w-8 h-8 rounded-lg <?php echo $p['is_active'] ? 'bg-yellow-100/50 text-yellow-600 hover:bg-yellow-200' : 'bg-green-100/50 text-green-600 hover:bg-green-200'; ?> flex items-center justify-center transition" title="Toggle"><span class="material-symbols-rounded text-base"><?php echo $p['is_active'] ? 'visibility_off' : 'visibility'; ?></span></button>
                                        </form>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this product permanently?')">
                                            <input type="hidden" name="form_action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                            <button class="w-8 h-8 rounded-lg bg-red-100/50 text-red-500 flex items-center justify-center hover:bg-red-200 transition" title="Delete"><span class="material-symbols-rounded text-base">delete</span></button>
                                        </form>
                                    </div>
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
                $isActive = $i === $page;
                $qs = http_build_query(array_merge($_GET, ['pg' => $i]));
            ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $qs; ?>"
                   class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-bold transition <?php echo $isActive ? 'bg-teal-500 text-white shadow-lg shadow-teal-200/50' : 'bg-white/50 text-gray-600 hover:bg-white/70 border border-white/60'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
