<?php
require_once 'config.php';

try {
    // Read SQL file
    $sql = file_get_contents('create_tables.sql');
    
    // Execute SQL commands
    $conn->exec($sql);
    
    echo "Tables created successfully!";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?> 