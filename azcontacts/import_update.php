<?php

// Database connection parameters
$host = 'localhost';
$dbname = 'azcontacts';
$username = 'root';
$password = '';

// CSV file path
$csvFilePath = 'contactsupdate.csv';

// Function to handle database connection
function connectToDatabase($host, $dbname, $username, $password) {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Connect to database
$conn = connectToDatabase($host, $dbname, $username, $password);

// Function to update new_price and new_image based on ID
function updateProductData($conn, $id, $newPrice, $newImage) {
    $query = "UPDATE products SET new_price = ?, new_image = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        die("Error in preparing query: " . $conn->error);
    }

    $stmt->bind_param("dss", $newPrice, $newImage, $id);
    if (!$stmt->execute()) {
        die("Error in executing query: " . $stmt->error);
    }
    $stmt->close();
}

// Function to import data from CSV
function importDataFromCSV($conn, $csvFilePath) {
    $handle = fopen($csvFilePath, "r");
    if ($handle === false) {
        die("Error opening CSV file");
    }

    // Skip header row
    fgetcsv($handle);

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        // Skip empty rows
        if (empty(array_filter($data))) {
            continue;
        }

        $id = $data[0]; // Assuming ID is in the first column
        $newPrice = $data[10]; // Assuming new_price is in the second column
        $newImage = $data[6]; // Assuming new_image is in the third column

        // Update product data based on ID
        updateProductData($conn, $id, $newPrice, $newImage);
    }

    fclose($handle);
}

// Import data from CSV
importDataFromCSV($conn, $csvFilePath);

// Close database connection
$conn->close();

echo "Data imported successfully from CSV and updated in the database.";

?>
