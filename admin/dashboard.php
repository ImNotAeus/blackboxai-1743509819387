<?php
require_once '../../includes/functions.inc.php';
require_once '../../db/config.php';

session_start();

if (!isAdmin()) {
    redirect('../pages/admin-login.php');
}

// Get active incidents
$incidents = $conn->query("
    SELECT i.id, u.username, i.latitude, i.longitude, i.timestamp 
    FROM incidents i
    JOIN users u ON i.user_id = u.id
    WHERE i.status = 'active'
    ORDER BY i.timestamp DESC
");

// Get recent community alerts
$alerts = $conn->query("
    SELECT * FROM community_alerts
    ORDER BY timestamp DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
    <script>
    function initMap(lat, lng) {
        const map = new google.maps.Map(document.getElementById("map"), {
            center: { lat: lat, lng: lng },
            zoom: 15
        });
        new google.maps.Marker({
            position: { lat: lat, lng: lng },
            map: map
        });
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
                        <a href="dashboard.php" class="block px-4 py-2 rounded bg-gray-700">
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
            <h2 class="text-2xl font-bold mb-6">Admin Dashboard</h2>
            
            <!-- Active Incidents -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Active Emergency Incidents</h3>
                    <a href="incidents.php" class="text-blue-500 hover:underline">View All</a>
                </div>
                
                <?php if ($incidents->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($incident = $incidents->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($incident['username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="#" onclick="initMap(<?php echo $incident['latitude']; ?>, <?php echo $incident['longitude']; ?>); return false;" 
                                           class="text-blue-500 hover:underline">
                                            View on Map
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M j, Y g:i A', strtotime($incident['timestamp'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" action="resolve_incident.php" class="inline">
                                            <input type="hidden" name="incident_id" value="<?php echo $incident['id']; ?>">
                                            <button type="submit" class="text-green-500 hover:text-green-700">
                                                <i class="fas fa-check"></i> Resolve
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No active incidents</p>
                <?php endif; ?>
            </div>

            <!-- Map Container -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h3 class="text-lg font-semibold mb-4">Incident Map</h3>
                <div id="map" style="height: 400px; width: 100%;" class="border rounded-lg"></div>
            </div>

            <!-- Recent Community Alerts -->
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Recent Community Alerts</h3>
                    <a href="alerts.php" class="text-blue-500 hover:underline">View All</a>
                </div>
                
                <?php if ($alerts->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while ($alert = $alerts->fetch_assoc()): ?>
                        <div class="border-b pb-4">
                            <h4 class="font-medium"><?php echo htmlspecialchars($alert['title']); ?></h4>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($alert['description']); ?></p>
                            <div class="flex justify-between mt-2">
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($alert['location']); ?></span>
                                <span class="text-xs text-gray-500"><?php echo date('M j, Y g:i A', strtotime($alert['timestamp'])); ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No community alerts</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>