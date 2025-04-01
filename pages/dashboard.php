<?php
require_once '../includes/functions.inc.php';
require_once '../db/config.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user information
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, blood_type, medical_info FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get emergency contacts
$contacts = $conn->query("SELECT contact_name, contact_number FROM emergency_contacts WHERE user_id = $userId");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency System - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
    function triggerEmergency() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    fetch('../api/handle_sos.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            latitude: lat,
                            longitude: lng
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert('Emergency alert sent! Help is on the way.');
                    });
                },
                error => {
                    alert('Error getting location: ' + error.message);
                }
            );
        } else {
            alert('Geolocation is not supported by your browser');
        }
    }
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white p-4">
            <h1 class="text-xl font-bold mb-6">Emergency System</h1>
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="block px-4 py-2 rounded bg-gray-700">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-user mr-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a href="contacts.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-address-book mr-2"></i>Emergency Contacts
                        </a>
                    </li>
                    <li>
                        <a href="../includes/logout.inc.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h2 class="text-2xl font-bold mb-6">Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
            
            <!-- Emergency SOS Button -->
            <div class="mb-8 text-center">
                <button onclick="triggerEmergency()" 
                    class="bg-red-500 hover:bg-red-600 text-white text-xl font-bold py-4 px-8 rounded-full shadow-lg transition duration-200">
                    <i class="fas fa-bell mr-2"></i> EMERGENCY SOS
                </button>
                <p class="mt-2 text-gray-600">Press in case of emergency</p>
            </div>

            <!-- User Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Your Information</h3>
                    <div class="space-y-2">
                        <p><span class="font-medium">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><span class="font-medium">Blood Type:</span> <?php echo htmlspecialchars($user['blood_type']); ?></p>
                        <p><span class="font-medium">Medical Info:</span> <?php echo htmlspecialchars($user['medical_info']); ?></p>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-semibold mb-4">Emergency Contacts</h3>
                    <?php if ($contacts->num_rows > 0): ?>
                        <ul class="space-y-2">
                            <?php while ($contact = $contacts->fetch_assoc()): ?>
                                <li>
                                    <i class="fas fa-user-circle mr-2"></i>
                                    <?php echo htmlspecialchars($contact['contact_name']); ?>: 
                                    <?php echo htmlspecialchars($contact['contact_number']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500">No emergency contacts added</p>
                    <?php endif; ?>
                    <a href="contacts.php" class="mt-4 inline-block text-blue-500 hover:underline">
                        <i class="fas fa-plus mr-1"></i> Add Contacts
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>