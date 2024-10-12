<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the image ID from the POST data
    $imageId = $_POST['imageId'];

    // Prepare a statement to delete the image record
    $sql = "SELECT image_url FROM images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $imagePath = $row['image_url'];

            // Delete the image record from the database
            $stmt->close();
            $sql = "DELETE FROM images WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $imageId);
            if ($stmt->execute()) {
                // Delete the image file from the server
                if (file_exists($imagePath)) {
                    unlink($imagePath); // Delete the image file
                }
                // Redirect back to the index.php with a success message
                header("Location: moderator.php?delete=success");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Image not found.";
        }
    } else {
        echo "Error executing query.";
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>