<?php
require_once '../../includes/functions.inc.php';
require_once '../../db/config.php';

session_start();

if (!isAdmin()) {
    redirect('../pages/admin-login.php');
}

// Handle incident resolution
if (isset($_GET['resolve']) && is_numeric($_GET['resolve'])) {
    $incidentId = sanitizeInput($_GET['resolve']);
    $stmt = $conn->prepare("UPDATE incidents SET status = 'resolved' WHERE id = ?");
    $stmt->bind_param("i", $incidentId);
    $stmt->execute();
    $_SESSION['success'] = "Incident marked as resolved";
}

// Get all incidents
$incidents = $conn->query("
    SELECT i.id, u.username, i.latitude, i.longitude, i.timestamp, i.status
    FROM incidents i
    JOIN users u ON i.user_id = u.id
    ORDER BY i.timestamp DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Incident Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script>
    function viewOnMap(lat, lng) {
        const map = new google.maps.Map(document.getElementById("map-modal"), {
            center: { lat: lat, lng: lng },
            zoom: 15
        });
        new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map
        });
        document.getElementById('map-modal').classList.remove('hidden');
    }
    </script>
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
                        <a href="incidents.php" class="block px-4 py-2 rounded bg-gray-700">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Incidents
                        </a>
                    </li>
                    <li>
                        <a href="users.php" class="block px-4 py-2 rounded hover:bg-gray-700">
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
                <h2 class="text-2xl font-bold">Incident Management</h2>
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($incident = $incidents->fetch_assoc()): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($incident['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="#" onclick="viewOnMap(<?php echo $incident['latitude']; ?>, <?php echo $incident['longitude']; ?>); return false;" 
                                       class="text-blue-500 hover:underline">
                                        View on Map
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y g:i A', strtotime($incident['timestamp'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $incident['status'] === 'active' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo ucfirst($incident['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($incident['status'] === 'active'): ?>
                                        <a href="?resolve=<?php echo $incident['id']; ?>" class="text-green-500 hover:text-green-700 mr-3">
                                            <i class="fas fa-check"></i> Resolve
                                        </a>
                                    <?php endif; ?>
                                    <a href="#" onclick="viewOnMap(<?php echo $incident['latitude']; ?>, <?php echo $incident['longitude']; ?>); return false;" 
                                       class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-map-marker-alt"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Modal -->
    <div id="map-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-4 rounded-lg w-4/5 h-4/5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Incident Location</h3>
                <button onclick="document.getElementById('map-modal').classList.add('hidden')" 
                    class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="map" style="height: calc(100% - 50px); width: 100%;" class="border rounded-lg"></div>
        </div>
    </div>
</body>
</html>