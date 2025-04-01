<?php
require_once '../../includes/functions.inc.php';
require_once '../../db/config.php';

session_start();

if (!isAdmin()) {
    redirect('../pages/admin-login.php');
}

// Handle alert creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $location = sanitizeInput($_POST['location']);
    $alertType = sanitizeInput($_POST['alert_type']);

    $stmt = $conn->prepare("INSERT INTO community_alerts (title, description, location, alert_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $description, $location, $alertType);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Alert created successfully";
    } else {
        $_SESSION['error'] = "Failed to create alert: " . $conn->error;
    }
}

// Handle alert deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $alertId = sanitizeInput($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM community_alerts WHERE id = ?");
    $stmt->bind_param("i", $alertId);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Alert deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete alert";
    }
}

// Get all alerts
$alerts = $conn->query("SELECT * FROM community_alerts ORDER BY timestamp DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Community Alerts</title>
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
                        <a href="users.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-users mr-2"></i>Users
                        </a>
                    </li>
                    <li>
                        <a href="alerts.php" class="block px-4 py-2 rounded bg-gray-700">
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
                <h2 class="text-2xl font-bold">Community Alerts</h2>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Create Alert Form -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h3 class="text-lg font-semibold mb-4">Create New Alert</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2" for="title">Title</label>
                            <input type="text" name="title" required
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2" for="alert_type">Alert Type</label>
                            <select name="alert_type" required
                                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="weather">Weather</option>
                                <option value="safety">Safety</option>
                                <option value="health">Health</option>
                                <option value="crime">Crime</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="location">Location</label>
                        <input type="text" name="location" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2" for="description">Description</label>
                        <textarea name="description" rows="4" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                        Create Alert
                    </button>
                </form>
            </div>

            <!-- Alerts List -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Active Alerts</h3>
                <?php if ($alerts->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($alert = $alerts->fetch_assoc()): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-lg"><?php echo htmlspecialchars($alert['title']); ?></h4>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full 
                                        <?php 
                                        switch($alert['alert_type']) {
                                            case 'weather': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'safety': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'health': echo 'bg-red-100 text-red-800'; break;
                                            case 'crime': echo 'bg-purple-100 text-purple-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($alert['alert_type']); ?>
                                    </span>
                                </div>
                                <a href="?delete=<?php echo $alert['id']; ?>" 
                                   onclick="return confirm('Are you sure you want to delete this alert?');"
                                   class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                            <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($alert['description']); ?></p>
                            <div class="flex justify-between mt-4 text-sm text-gray-500">
                                <span><?php echo htmlspecialchars($alert['location']); ?></span>
                                <span><?php echo date('M j, Y g:i A', strtotime($alert['timestamp'])); ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No community alerts found</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>