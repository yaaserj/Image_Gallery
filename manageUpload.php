<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Title from the form
    $title = $conn->real_escape_string($_POST['title']);

    // Check if an image file is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = $_FILES['image'];

        // Get file details
        $fileName = $image['name'];
        $fileTmpName = $image['tmp_name'];
        $fileSize = $image['size'];
        $fileError = $image['error'];
        $fileType = $image['type'];

        // Allow only certain file extensions (e.g., jpg, jpeg, png)
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExt, $allowedExt)) {
            // Define the directory to save the image
            $uploadDir = 'uploads/';
            // Generate a unique name for the image to avoid name conflicts
            $imageNewName = uniqid('', true) . "." . $fileExt;
            $imagePath = $uploadDir . $imageNewName;

            // Move the uploaded image to the specified directory
            if (move_uploaded_file($fileTmpName, $imagePath)) {
                // Prepare the SQL statement
                $sql = "INSERT INTO images (image_url, title) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    // Bind parameters and execute the statement
                    $stmt->bind_param("ss", $imagePath, $title);
                    if ($stmt->execute()) {
                        // Redirect to index.php with a success message
                        header("Location: moderator.php?upload=success");
                        exit(); // Make sure the script stops after redirect
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo "Failed to prepare the statement.";
                }
            } else {
                echo "Failed to move the uploaded image.";
            }
        } else {
            echo "Only JPG, JPEG, and PNG files are allowed.";
        }
    } else {
        echo "No image uploaded or there was an error.";
    }
}
?>
<style>
    .upload-container {
        max-width: 500px;
        /* Reduce the width to a normal size */
        margin: 30px auto;
        /* Center the container */
        padding: 20px;
        background-color: white;
        /* White background for the form */
        border-radius: 10px;
        /* Rounded corners */
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        /* Add some shadow */
    }

    .upload-container2 {
        max-width: 500px;
        /* Reduce the width to a normal size */
        margin: 30px auto;
        /* Center the container */
        padding: 20px;
        background-color: white;
        /* White background for the form */
        border-radius: 10px;
        text-align: center;
        /* Rounded corners */
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        /* Add some shadow */
    }

    .upload-container2 a {
        text-decoration: none;
    }

    .upload-box {
        border: 2px dashed #007bff;
        border-radius: 10px;
        text-align: center;
        padding: 30px;
        cursor: pointer;
        transition: border 0.3s ease;
        background-color: #f9f9f9;
        /* Light background for the upload area */
    }

    .upload-box:hover {
        border-color: #0056b3;
    }

    .upload-box input[type="file"] {
        display: none;
    }

    .upload-box label {
        display: block;
        color: #007bff;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-upload {
        margin-top: 20px;
        width: 100%;
    }

    .dragover {
        border-color: #007bff;
    }
</style>

<div class="upload-container">
    <h3 class="mb-3">Upload Image</h3>
    <form id="image-upload-form" action="manageUpload.php" method="POST" enctype="multipart/form-data">
        <!-- Image Title -->
        <div class="mb-3">
            <label for="title" class="form-label">Image Title</label>
            <input type="text" class="form-control" id="title" name="title" placeholder="Enter image title" required>
        </div>

        <!-- Image Upload Box -->
        <div class="upload-box mb-3" id="drop-area">
            <p>Choose file or drop here</p>
            <label for="image-upload">Choose file</label>
            <input type="file" id="image-upload" name="image" accept="image/*" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary btn-upload">Upload Image</button>
    </form>
</div>