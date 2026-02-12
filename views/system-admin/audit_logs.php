<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/SystemAdmin.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== USER_ROLE_SYSTEM_ADMIN) {
    redirect('/index.php?page=login');
}

$admin        = new SystemAdmin($pdo);
$search       = trim($_GET['search'] ?? '');
$actionFilter = trim($_GET['action_type'] ?? '');
$page         = max(1, (int) ($_GET['pg'] ?? 1));
$perPage      = 20;

$logs      = $admin->getAuditLogs($page, $perPage, $search, $actionFilter);
$totalLogs = $admin->countAuditLogs($search, $actionFilter);
$totalPages = max(1, ceil($totalLogs / $perPage));

$actionIcons = [
    'CREATE' => ['icon' => 'add_circle',  'bg' => 'bg-green-100/50 text-green-600',  'badge' => 'bg-green-100 text-green-700'],
    'UPDATE' => ['icon' => 'edit',         'bg' => 'bg-blue-100/50 text-blue-600',    'badge' => 'bg-blue-100 text-blue-700'],
    'DELETE' => ['icon' => 'delete',       'bg' => 'bg-red-100/50 text-red-600',      'badge' => 'bg-red-100 text-red-700'],
    'TOGGLE' => ['icon' => 'toggle_on',    'bg' => 'bg-yellow-100/50 text-yellow-600','badge' => 'bg-yellow-100 text-yellow-700'],
    'LOGIN'  => ['icon' => 'login',        'bg' => 'bg-teal-100/50 text-teal-600',    'badge' => 'bg-teal-100 text-teal-700'],
    'LOGOUT' => ['icon' => 'logout',       'bg' => 'bg-gray-100/50 text-gray-600',    'badge' => 'bg-gray-100 text-gray-700'],
];
$actionTypes = ['CREATE', 'UPDATE', 'DELETE', 'TOGGLE', 'LOGIN', 'LOGOUT'];
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <?php display_flash(); ?>

        <div class="flex items-center gap-3 mb-8">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-dashboard" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">Audit Logs</h1>
                <p class="text-sm text-gray-500"><?php echo number_format($totalLogs); ?> records &mdash; Track all admin actions</p>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="glass-card rounded-2xl p-5 mb-6 flex flex-wrap gap-4 items-end">
            <input type="hidden" name="page" value="system-admin-audit">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Username, entity, details..."
                       class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Action</label>
                <select name="action_type" class="border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-3 py-2 text-sm min-w-[140px] focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    <option value="">All Actions</option>
                    <?php foreach ($actionTypes as $at): ?>
                        <option value="<?php echo $at; ?>" <?php echo $actionFilter === $at ? 'selected' : ''; ?>><?php echo $at; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 text-white font-bold text-sm shadow-lg hover:scale-[1.02] transition flex items-center gap-1.5">
                <span class="material-symbols-rounded text-base">search</span> Filter
            </button>
            <?php if ($search || $actionFilter): ?>
                <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-audit" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-700 hover:bg-white/40 transition text-sm flex items-center gap-1"><span class="material-symbols-rounded text-base">refresh</span> Reset</a>
            <?php endif; ?>
        </form>

        <!-- Logs -->
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mb-6">
            <?php if (empty($logs)): ?>
                <div class="text-center py-16">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-100/50 flex items-center justify-center text-gray-300 mb-4"><span class="material-symbols-rounded text-3xl">history</span></div>
                    <p class="text-gray-400">No audit logs found</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($logs as $log):
                        $ai = $actionIcons[$log['action']] ?? ['icon' => 'info', 'bg' => 'bg-gray-100/50 text-gray-500', 'badge' => 'bg-gray-100 text-gray-600'];
                    ?>
                    <div class="flex items-start gap-4 bg-white/40 border border-white/60 rounded-2xl p-4 hover:bg-white/60 transition">
                        <div class="w-10 h-10 rounded-xl <?php echo $ai['bg']; ?> flex items-center justify-center flex-shrink-0 mt-0.5"><span class="material-symbols-rounded"><?php echo $ai['icon']; ?></span></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($log['username']); ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $ai['badge']; ?>"><?php echo $log['action']; ?></span>
                                <span class="text-gray-500 text-xs"><?php echo htmlspecialchars($log['entity_type']); ?><?php if ($log['entity_id']): ?> #<?php echo $log['entity_id']; ?><?php endif; ?></span>
                            </div>
                            <?php if ($log['details']): ?>
                                <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($log['details']); ?></p>
                            <?php endif; ?>
                            <div class="flex items-center gap-3 mt-1.5 text-[10px] text-gray-400">
                                <span class="flex items-center gap-0.5"><span class="material-symbols-rounded" style="font-size:12px">schedule</span> <?php echo date('M j, Y H:i:s', strtotime($log['created_at'])); ?></span>
                                <?php if ($log['ip_address']): ?>
                                    <span class="flex items-center gap-0.5"><span class="material-symbols-rounded" style="font-size:12px">language</span> <?php echo htmlspecialchars($log['ip_address']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
