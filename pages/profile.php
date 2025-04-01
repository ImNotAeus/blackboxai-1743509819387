<?php
require_once '../includes/functions.inc.php';
require_once '../db/config.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $bloodType = sanitizeInput($_POST['blood_type']);
    $medicalInfo = sanitizeInput($_POST['medical_info']);
    $language = sanitizeInput($_POST['language']);

    $stmt = $conn->prepare("UPDATE users SET email = ?, blood_type = ?, medical_info = ?, language_pref = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $email, $bloodType, $medicalInfo, $language, $userId);
    
    if ($stmt->execute()) {
        $success = "Profile updated successfully";
        $user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
    } else {
        $error = "Failed to update profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency System - Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 text-white p-4">
            <h1 class="text-xl font-bold mb-6">Emergency System</h1>
            <nav>
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="block px-4 py-2 rounded bg-gray-700">
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
            <h2 class="text-2xl font-bold mb-6">Your Profile</h2>
            
            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="bg-white p-6 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2" for="username">Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled
                            class="w-full px-3 py-2 border rounded-lg bg-gray-100">
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2" for="email">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2" for="blood_type">Blood Type</label>
                        <select name="blood_type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="A+" <?php echo $user['blood_type'] === 'A+' ? 'selected' : ''; ?>>A+</option>
                            <option value="A-" <?php echo $user['blood_type'] === 'A-' ? 'selected' : ''; ?>>A-</option>
                            <option value="B+" <?php echo $user['blood_type'] === 'B+' ? 'selected' : ''; ?>>B+</option>
                            <option value="B-" <?php echo $user['blood_type'] === 'B-' ? 'selected' : ''; ?>>B-</option>
                            <option value="AB+" <?php echo $user['blood_type'] === 'AB+' ? 'selected' : ''; ?>>AB+</option>
                            <option value="AB-" <?php echo $user['blood_type'] === 'AB-' ? 'selected' : ''; ?>>AB-</option>
                            <option value="O+" <?php echo $user['blood_type'] === 'O+' ? 'selected' : ''; ?>>O+</option>
                            <option value="O-" <?php echo $user['blood_type'] === 'O-' ? 'selected' : ''; ?>>O-</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2" for="language">Language Preference</label>
                        <select name="language" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="en" <?php echo $user['language_pref'] === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="es" <?php echo $user['language_pref'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                            <option value="fr" <?php echo $user['language_pref'] === 'fr' ? 'selected' : ''; ?>>French</option>
                            <option value="de" <?php echo $user['language_pref'] === 'de' ? 'selected' : ''; ?>>German</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 mb-2" for="medical_info">Medical Information</label>
                        <textarea name="medical_info" rows="4"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($user['medical_info']); ?></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition duration-200">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>