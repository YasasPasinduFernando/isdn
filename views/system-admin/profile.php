<?php
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/SystemAdmin.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== USER_ROLE_SYSTEM_ADMIN) {
    redirect('/index.php?page=login');
}

$admin  = new SystemAdmin($pdo);
$userId = (int) $_SESSION['user_id'];

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['form_action'] ?? '';
    try {
        if ($act === 'update_profile') {
            $admin->updateProfile($userId, [
                'username'       => trim($_POST['username'] ?? ''),
                'email'          => trim($_POST['email'] ?? ''),
                'name'           => trim($_POST['name'] ?? ''),
                'contact_number' => trim($_POST['contact_number'] ?? ''),
                'address'        => trim($_POST['address'] ?? ''),
            ]);
            $_SESSION['username'] = trim($_POST['username'] ?? $_SESSION['username']);
            $admin->logAction($userId, 'UPDATE', 'profile', $userId, 'Updated own profile');
            flash_message('Profile updated successfully!', 'success');

        } elseif ($act === 'change_password') {
            $result = $admin->changePassword($userId, $_POST['current_password'] ?? '', $_POST['new_password'] ?? '');
            if ($result === true) {
                $admin->logAction($userId, 'UPDATE', 'profile', $userId, 'Changed password');
                flash_message('Password changed successfully!', 'success');
            } else {
                flash_message($result, 'error');
            }
        }
    } catch (Exception $e) {
        flash_message('Error: ' . $e->getMessage(), 'error');
    }
    redirect('/index.php?page=system-admin-profile');
}

$profile = $admin->getAdminProfile($userId);
$loginHistory = $admin->getLoginHistory($userId, 15);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <?php display_flash(); ?>

        <div class="flex items-center gap-3 mb-8">
            <a href="<?php echo BASE_PATH; ?>/index.php?page=system-admin-dashboard" class="w-10 h-10 rounded-xl bg-white/50 border border-white/60 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:bg-white/70 transition"><span class="material-symbols-rounded">arrow_back</span></a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 font-['Outfit']">My Profile</h1>
                <p class="text-sm text-gray-500">Manage your account settings</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Info -->
            <div class="glass-panel rounded-3xl p-6 sm:p-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="material-symbols-rounded text-teal-500 text-2xl">person</span>
                    <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Profile Information</h2>
                </div>
                <form method="POST" action="<?php echo BASE_PATH; ?>/index.php?page=system-admin-profile" class="space-y-5">
                    <input type="hidden" name="form_action" value="update_profile">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Username</label>
                        <input type="text" name="username" required value="<?php echo htmlspecialchars($profile['username'] ?? ''); ?>"
                               class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>"
                               class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>"
                               class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Contact Number</label>
                        <input type="text" name="contact_number" value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>"
                               class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Address</label>
                        <textarea name="address" rows="2"
                                  class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-teal-500 transition shadow-sm resize-none"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <p class="text-xs text-gray-400">Role: <span class="font-bold text-teal-600"><?php echo ucwords(str_replace('_', ' ', $profile['role'] ?? '')); ?></span></p>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-600 text-white font-bold text-sm shadow-lg shadow-teal-200/50 hover:scale-[1.02] transition flex items-center gap-2">
                            <span class="material-symbols-rounded text-base">save</span> Save Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="space-y-8">
                <div class="glass-panel rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center space-x-3 mb-6">
                        <span class="material-symbols-rounded text-red-500 text-2xl">lock</span>
                        <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Change Password</h2>
                    </div>
                    <form method="POST" action="<?php echo BASE_PATH; ?>/index.php?page=system-admin-profile" class="space-y-5">
                        <input type="hidden" name="form_action" value="change_password">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Current Password</label>
                            <input type="password" name="current_password" required
                                   class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-400 transition shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">New Password</label>
                            <input type="password" name="new_password" required minlength="6"
                                   class="w-full border border-white/40 bg-white/30 backdrop-blur-sm rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-400 transition shadow-sm" placeholder="Min 6 characters">
                        </div>
                        <button type="submit" class="w-full px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white font-bold text-sm shadow-lg shadow-red-200/50 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                            <span class="material-symbols-rounded text-base">vpn_key</span> Update Password
                        </button>
                    </form>
                </div>

                <!-- Account Info Card -->
                <div class="glass-card rounded-3xl p-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="material-symbols-rounded text-blue-500 text-xl">info</span>
                        <h3 class="text-sm font-bold text-gray-800 font-['Outfit']">Account Details</h3>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">User ID</span><span class="font-semibold text-gray-800">#<?php echo $profile['id'] ?? ''; ?></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Joined</span><span class="font-semibold text-gray-800"><?php echo $profile['created_at'] ? date('M j, Y', strtotime($profile['created_at'])) : '-'; ?></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Role</span><span class="font-bold text-teal-600"><?php echo ucwords(str_replace('_', ' ', $profile['role'] ?? '')); ?></span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login History -->
        <?php if (!empty($loginHistory)): ?>
        <div class="glass-panel rounded-3xl p-6 sm:p-8 mt-8">
            <div class="flex items-center space-x-3 mb-6">
                <span class="material-symbols-rounded text-indigo-500 text-2xl">history</span>
                <h2 class="text-lg font-bold text-gray-800 font-['Outfit']">Login History</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200/50">
                        <th class="pb-3 pr-4">Date &amp; Time</th><th class="pb-3 pr-4">IP Address</th><th class="pb-3">Details</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100/50">
                        <?php foreach ($loginHistory as $lh): ?>
                        <tr class="hover:bg-white/30 transition">
                            <td class="py-3 pr-4 text-gray-600"><?php echo date('M j, Y H:i:s', strtotime($lh['created_at'])); ?></td>
                            <td class="py-3 pr-4 font-mono text-xs text-gray-500"><?php echo htmlspecialchars($lh['ip_address'] ?? '-'); ?></td>
                            <td class="py-3 text-gray-500 text-xs"><?php echo htmlspecialchars($lh['details'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
