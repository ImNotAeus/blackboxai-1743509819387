<?php
require_once 'config.php';

$sql = file_get_contents('schema.sql');
$queries = array_filter(array_map('trim', explode(';', $sql)));

foreach ($queries as $query) {
    if (!empty($query)) {
        if (!$conn->query($query)) {
            die("Error executing query: " . $conn->error);
        }
    }
}

echo "Database initialized successfully!";
?>