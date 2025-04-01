<?php
require_once '../../includes/functions.inc.php';
require_once '../../db/config.php';

session_start();

if (!isAdmin()) {
    redirect('../pages/admin-login.php');
}

// Handle user deactivation
if (isset($_GET['deactivate']) && is_numeric($_GET['deactivate'])) {
    $userId = sanitizeInput($_GET['deactivate']);
    $stmt = $conn->prepare("UPDATE users SET active = 0 WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $_SESSION['success'] = "User deactivated successfully";
}

// Handle user activation
if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $userId = sanitizeInput($_GET['activate']);
    $stmt = $conn->prepare("UPDATE users SET active = 1 WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $_SESSION['success'] = "User activated successfully";
}

// Get all users
$users = $conn->query("SELECT id, username, email, created_at, active FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white p-4">
            <h1 class="text-xl font-bold mb-6">Admin Panel</h1>
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="incidents.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Incidents
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="block px-4 py-2 rounded bg-gray-700">
                            <i class="fas fa-users mr-2"></i>Users
                        </a>
                    </li>
                    <li>
                        <a href="alerts.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-bullhorn mr-2"></i>Community Alerts
                        </a>
                    </li>
                    <li>
                        <a href="../../includes/logout.inc.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8 overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">User Management</h2>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $user['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $user['active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($user['active']): ?>
                                        <a href="?deactivate=<?php echo $user['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to deactivate this user?');"
                                           class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-user-slash"></i> Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a href="?activate=<?php echo $user['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to activate this user?');"
                                           class="text-green-500 hover:text-green-700">
                                            <i class="fas fa-user-check"></i> Activate
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>