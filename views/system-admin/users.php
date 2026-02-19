<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/SystemAdmin.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== USER_ROLE_SYSTEM_ADMIN) {
    redirect('/index.php?page=login');
}

$admin = new SystemAdmin($pdo);
$action = $_GET['action'] ?? 'list';

// ── Handle POST actions ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $act = $_POST['form_action'] ?? '';

        if ($act === 'create') {
            $id = $admin->createUser([
                'username'  => trim($_POST['username'] ?? ''),
                'email'     => trim($_POST['email'] ?? ''),
                'password'  => $_POST['password'] ?? '',
                'role'      => $_POST['role'] ?? 'customer',
                'rdc_id'    => $_POST['rdc_id'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);
            $admin->logAction($_SESSION['user_id'], 'CREATE', 'user', $id, "Created user: " . trim($_POST['username'] ?? ''));
            flash_message('User created successfully!', 'success');
            redirect('/index.php?page=system-admin-users');

        } elseif ($act === 'update' && !empty($_POST['user_id'])) {
            $uid = (int) $_POST['user_id'];
            $admin->updateUser($uid, [
                'username'  => trim($_POST['username'] ?? ''),
                'email'     => trim($_POST['email'] ?? ''),
                'password'  => $_POST['password'] ?? '',
                'role'      => $_POST['role'] ?? 'customer',
                'rdc_id'    => $_POST['rdc_id'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ]);
            $admin->logAction($_SESSION['user_id'], 'UPDATE', 'user', $uid, "Updated user #$uid: " . trim($_POST['username'] ?? ''));
            flash_message('User updated successfully!', 'success');
            redirect('/index.php?page=system-admin-users');

        } elseif ($act === 'toggle' && !empty($_POST['user_id'])) {
            $uid = (int) $_POST['user_id'];
            $admin->toggleUserActive($uid);
            $admin->logAction($_SESSION['user_id'], 'TOGGLE', 'user', $uid, "Toggled active status for user #$uid");
            flash_message('User status updated.', 'success');
            redirect('/index.php?page=system-admin-users');

        } elseif ($act === 'delete' && !empty($_POST['user_id'])) {
            $uid = (int) $_POST['user_id'];
            $user = $admin->getUserById($uid);
            $admin->deleteUser($uid);
            $admin->logAction($_SESSION['user_id'], 'DELETE', 'user', $uid, "Deleted user: " . ($user['username'] ?? '#'.$uid));
            flash_message('User deleted.', 'success');
            redirect('/index.php?page=system-admin-users');
        }
    } catch (Exception $e) {
        flash_message('Error: ' . $e->getMessage(), 'error');
        redirect('/index.php?page=system-admin-users');
    }
}

// ── FORM VIEW (add / edit) ───────────────────────────────────
require_once __DIR__ . '/../../includes/header.php';

if ($action === 'add' || $action === 'edit') {
    $editUser = null;
    if ($action === 'edit' && !empty($_GET['id'])) {
        $editUser = $admin->getUserById((int) $_GET['id']);
        if (!$editUser) {
            flash_message('User not found.', 'error');
            redirect('/index.php?page=system-admin-users');
        }
    }
    $allRdcs  = $admin->getAllRdcs();
    $allRoles = $admin->getAllRoles();
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <?php display_flash(); ?>

        <div class="flex items-center gap-3 mb-8">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']"><?php echo $editUser ? 'Edit User' : 'Add New User'; ?></h1>
                <p class="text-sm text-gray-500"><?php echo $editUser ? 'Update user #'.$editUser['id'] : 'Create a new system user'; ?></p>
            </div>
        </div>

        <form method="POST" action="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users" class="glass-panel rounded-3xl p-6 sm:p-8 space-y-6">
            <input type="hidden" name="form_action" value="<?php echo $editUser ? 'update' : 'create'; ?>">
            <?php if ($editUser): ?><input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>"><?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" required value="<?php echo htmlspecialchars($editUser['username'] ?? ''); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($editUser['email'] ?? ''); ?>"
                           class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Password <?php echo $editUser ? '(leave blank to keep current)' : '<span class="text-red-500">*</span>'; ?></label>
                <input type="password" name="password" <?php echo $editUser ? '' : 'required'; ?> minlength="6"
                       class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm"
                       placeholder="<?php echo $editUser ? 'Leave blank to keep current password' : 'Min 6 characters'; ?>">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="roleSelect" required
                            class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                        <?php foreach ($allRoles as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php echo (($editUser['role'] ?? '') === $key) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="rdcField">
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Assigned RDC</label>
                    <select name="rdc_id" id="rdcSelect"
                            class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
                        <option value="">None</option>
                        <?php foreach ($allRdcs as $r): ?>
                            <option value="<?php echo $r['rdc_id']; ?>" <?php echo (($editUser['rdc_id'] ?? '') == $r['rdc_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($r['rdc_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?php echo (!$editUser || ($editUser['is_active'] ?? 1)) ? 'checked' : ''; ?>>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-teal-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-500"></div>
                </label>
                <span class="text-sm font-medium text-gray-700">Account Active</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200/50">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users" class="px-5 py-2.5 rounded-xl text-gray-600 hover:bg-white/50 transition text-sm font-medium">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-teal-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                    <span class="material-symbols-rounded text-base"><?php echo $editUser ? 'save' : 'person_add'; ?></span>
                    <?php echo $editUser ? 'Save Changes' : 'Create User'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide RDC field based on role
const rdcRoles = ['rdc_manager','rdc_clerk','rdc_sales_ref','logistics_officer','rdc_driver'];
const roleSelect = document.getElementById('roleSelect');
const rdcField = document.getElementById('rdcField');
function toggleRdc() {
    rdcField.style.display = rdcRoles.includes(roleSelect.value) ? '' : 'none';
}
roleSelect.addEventListener('change', toggleRdc);
toggleRdc();
</script>

<?php
    require_once __DIR__ . '/../../includes/footer.php';
    return; // stop here for form view
}

// ── LIST VIEW ────────────────────────────────────────────────
$search     = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$page       = max(1, (int) ($_GET['pg'] ?? 1));
$perPage    = 10;

$users      = $admin->getUsers($page, $perPage, $search, $roleFilter);
$totalUsers = $admin->countUsers($search, $roleFilter);
$totalPages = max(1, ceil($totalUsers / $perPage));
$allRoles   = $admin->getAllRoles();
$totalAll   = $admin->countUsers('', '');

$roleBadges = [
    'customer'            => 'bg-gray-100 text-gray-700',
    'rdc_manager'         => 'bg-blue-100 text-blue-700',
    'rdc_clerk'           => 'bg-purple-100 text-purple-700',
    'rdc_sales_ref'       => 'bg-indigo-100 text-indigo-700',
    'logistics_officer'   => 'bg-yellow-100 text-yellow-700',
    'rdc_driver'          => 'bg-orange-100 text-orange-700',
    'head_office_manager' => 'bg-teal-100 text-teal-700',
    'system_admin'        => 'bg-red-100 text-red-700',
];
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <?php display_flash(); ?>

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-dashboard" class="w-10 h-10 rounded-xl bg-white/70 border border-white/80 flex items-center justify-center text-gray-500 hover:text-teal-600 hover:bg-white shadow-sm transition"><span class="material-symbols-rounded">arrow_back</span></a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">User Management</h1>
                    <p class="text-sm text-gray-500">Manage accounts and roles</p>
                </div>
            </div>
            <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users&action=add" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-teal-200/50 hover:shadow-teal-200/70 hover:scale-[1.02] transition flex items-center gap-2 w-fit">
                <span class="material-symbols-rounded text-lg">person_add</span> Add User
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="stat-card flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-teal-100 flex items-center justify-center text-teal-600">
                    <span class="material-symbols-rounded text-2xl">group</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalAll); ?></p>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Users</p>
                </div>
            </div>
            <div class="stat-card flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <span class="material-symbols-rounded text-2xl">filter_list</span>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalUsers); ?></p>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo ($search || $roleFilter) ? 'Showing (filtered)' : 'In list'; ?></p>
                </div>
            </div>
            <div class="stat-card flex items-center gap-4 sm:col-span-2 lg:col-span-1">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                    <span class="material-symbols-rounded text-2xl">badge</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-700 truncate"><?php echo count($allRoles); ?> roles</p>
                    <p class="text-xs text-gray-500">Customer, RDC, Admin...</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="glass-card rounded-2xl p-5 mb-6 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="system-admin-users">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Username or email..."
                       class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 focus:border-transparent transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Role</label>
                <select name="role" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2.5 text-sm min-w-[160px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All Roles</option>
                    <?php foreach ($allRoles as $k => $v): ?>
                        <option value="<?php echo $k; ?>" <?php echo $roleFilter === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-sm shadow-lg hover:scale-[1.02] transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">search</span> Search
            </button>
            <?php if ($search || $roleFilter): ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users" class="px-4 py-2.5 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1"><span class="material-symbols-rounded text-base">refresh</span> Reset</a>
            <?php endif; ?>
        </form>

        <!-- Users Table -->
        <div class="glass-panel rounded-3xl overflow-hidden mb-6 shadow-sm">
            <?php if (empty($users)): ?>
                <div class="text-center py-20">
                    <div class="w-20 h-20 mx-auto rounded-2xl bg-gray-100/80 flex items-center justify-center text-gray-300 mb-4"><span class="material-symbols-rounded text-4xl">person_search</span></div>
                    <p class="text-gray-500 font-medium">No users found</p>
                    <p class="text-sm text-gray-400 mt-1">Try adjusting search or filters</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm admin-table">
                        <thead>
                            <tr class="text-left">
                                <th>#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>RDC</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($users as $u): ?>
                            <tr class="table-row-hover" data-user="<?php echo htmlspecialchars(json_encode(['id' => $u['id'], 'username' => $u['username'], 'email' => $u['email'], 'role' => $u['role'], 'rdc_name' => $u['rdc_name'] ?? '-', 'is_active' => (int)($u['is_active'] ?? 1), 'created_at' => $u['created_at']])); ?>">
                                <td class="text-gray-400 font-mono text-xs"><?php echo $u['id']; ?></td>
                                <td>
                                    <button type="button" onclick="openViewUser(this.closest('tr'))" class="font-semibold text-gray-800 hover:text-teal-600 transition text-left flex items-center gap-2">
                                        <span class="w-8 h-8 rounded-lg bg-teal-100/80 flex items-center justify-center text-teal-600 flex-shrink-0"><span class="material-symbols-rounded text-base">person</span></span>
                                        <?php echo htmlspecialchars($u['username']); ?>
                                    </button>
                                </td>
                                <td class="text-gray-600"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><span class="px-2.5 py-1 rounded-lg text-xs font-bold <?php echo $roleBadges[$u['role']] ?? 'bg-gray-100 text-gray-600'; ?>"><?php echo ucwords(str_replace('_', ' ', $u['role'])); ?></span></td>
                                <td class="text-gray-500 text-xs"><?php echo htmlspecialchars($u['rdc_name'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($u['is_active'] ?? 1): ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-green-600"><span class="w-2 h-2 rounded-full bg-green-500"></span>Active</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-red-500"><span class="w-2 h-2 rounded-full bg-red-400"></span>Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-gray-400 text-xs"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users&action=edit&id=<?php echo $u['id']; ?>" class="admin-action-btn admin-action-btn-edit" title="Edit"><span class="material-symbols-rounded text-lg">edit</span></a>
                                        <button type="button" onclick="openConfirmUser(<?php echo $u['id']; ?>, 'toggle', '<?php echo ($u['is_active'] ?? 1) ? 'Deactivate' : 'Activate'; ?> this user?')" class="admin-action-btn admin-action-btn-toggle <?php echo ($u['is_active'] ?? 1) ? '' : 'on'; ?>" title="<?php echo ($u['is_active'] ?? 1) ? 'Deactivate' : 'Activate'; ?>"><span class="material-symbols-rounded text-lg"><?php echo ($u['is_active'] ?? 1) ? 'person_off' : 'person'; ?></span></button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" onclick="openConfirmUser(<?php echo $u['id']; ?>, 'delete', 'Permanently delete this user? This cannot be undone.')" class="admin-action-btn admin-action-btn-delete" title="Delete"><span class="material-symbols-rounded text-lg">delete</span></button>
                                        <?php endif; ?>
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
        <div class="flex items-center justify-center gap-2 flex-wrap">
            <?php for ($i = 1; $i <= $totalPages; $i++):
                $isActive = $i === $page;
                $qs = http_build_query(array_merge($_GET, ['pg' => $i]));
            ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?<?php echo $qs; ?>"
                   class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold transition <?php echo $isActive ? 'bg-teal-500 text-white shadow-lg shadow-teal-200/50' : 'bg-white/70 text-gray-600 hover:bg-white border border-slate-200/80'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- View User Modal -->
<div id="modal-view-user" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-view-user-title">
    <div class="modal-box modal-box-lg" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="modal-view-user-title" class="text-lg font-bold text-gray-800">User Details</h2>
            <button type="button" class="btn-modal-close" onclick="closeViewUser()" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
        </div>
        <div class="modal-body">
            <div class="space-y-4">
                <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-[var(--admin-accent)]" style="background: var(--admin-accent-light);">
                        <span class="material-symbols-rounded text-3xl">person</span>
                    </div>
                    <div>
                        <p id="view-user-name" class="text-lg font-bold text-gray-800"></p>
                        <p id="view-user-email" class="text-sm text-gray-500"></p>
                    </div>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500 font-medium">Role</dt><dd id="view-user-role" class="font-semibold text-gray-800 mt-0.5"></dd></div>
                    <div><dt class="text-gray-500 font-medium">RDC</dt><dd id="view-user-rdc" class="font-semibold text-gray-800 mt-0.5"></dd></div>
                    <div><dt class="text-gray-500 font-medium">Status</dt><dd id="view-user-status" class="mt-0.5"></dd></div>
                    <div><dt class="text-gray-500 font-medium">Joined</dt><dd id="view-user-joined" class="font-semibold text-gray-800 mt-0.5"></dd></div>
                </dl>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeViewUser()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition">Close</button>
            <a id="view-user-edit-link" href="#" class="btn-primary-modal inline-flex items-center gap-1.5">Edit user</a>
        </div>
    </div>
</div>

<!-- Confirm Action Modal (Toggle / Delete) -->
<div id="modal-confirm-user" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-confirm-user-title">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h2 id="modal-confirm-user-title" class="text-lg font-bold text-gray-800">Confirm</h2>
            <button type="button" class="btn-modal-close" onclick="closeConfirmUser()" aria-label="Close"><span class="material-symbols-rounded">close</span></button>
        </div>
        <form method="POST" id="form-confirm-user" action="<?php echo BASE_PATH; ?>/index.php?page=system-admin-users">
            <input type="hidden" name="form_action" id="confirm-user-action" value="">
            <input type="hidden" name="user_id" id="confirm-user-id" value="">
            <div class="modal-body">
                <p id="modal-confirm-user-message" class="text-gray-600"></p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeConfirmUser()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold text-sm hover:bg-slate-200 transition">Cancel</button>
                <button type="submit" id="modal-confirm-user-submit" class="px-4 py-2.5 rounded-xl font-semibold text-sm text-white transition">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function openViewUser(tr) {
        var data = JSON.parse(tr.getAttribute('data-user'));
        document.getElementById('view-user-name').textContent = data.username;
        document.getElementById('view-user-email').textContent = data.email;
        document.getElementById('view-user-role').textContent = (data.role || '').replace(/_/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
        document.getElementById('view-user-rdc').textContent = data.rdc_name || '-';
        document.getElementById('view-user-status').innerHTML = data.is_active ? '<span class="text-green-600 font-semibold">Active</span>' : '<span class="text-red-600 font-semibold">Inactive</span>';
        document.getElementById('view-user-joined').textContent = data.created_at ? new Date(data.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
        document.getElementById('view-user-edit-link').href = '<?php echo BASE_PATH; ?>/index.php?page=system-admin-users&action=edit&id=' + data.id;
        document.getElementById('modal-view-user').classList.add('modal-open');
    }
    function closeViewUser() {
        document.getElementById('modal-view-user').classList.remove('modal-open');
    }
    window.openViewUser = openViewUser;
    window.closeViewUser = closeViewUser;
    document.getElementById('modal-view-user').addEventListener('click', closeViewUser);
})();

function openConfirmUser(userId, action, message) {
    document.getElementById('confirm-user-id').value = userId;
    document.getElementById('confirm-user-action').value = action;
    document.getElementById('modal-confirm-user-message').textContent = message;
    var btn = document.getElementById('modal-confirm-user-submit');
    btn.textContent = action === 'delete' ? 'Delete' : 'Confirm';
    btn.className = 'px-4 py-2.5 rounded-xl font-semibold text-sm text-white transition ' + (action === 'delete' ? 'bg-red-500 hover:bg-red-600' : 'bg-amber-500 hover:bg-amber-600');
    document.getElementById('modal-confirm-user').classList.add('modal-open');
}
function closeConfirmUser() {
    document.getElementById('modal-confirm-user').classList.remove('modal-open');
}
document.getElementById('modal-confirm-user').addEventListener('click', function(e) { if (e.target === this) closeConfirmUser(); });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
