<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!-- Display Images from Database -->
<div class="image-gallery">
    <h3 class="text-center">Manage Uploaded Images</h3>
    <div class="row">
        <?php
        // Fetch images from the database
        $sql = "SELECT id, image_url, title FROM images";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Output each image
            while ($row = $result->fetch_assoc()) {
                echo '<div class="col-md-4 mb-3">';
                echo '<div class="card">';
                echo '<img src="' . htmlspecialchars($row['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($row['title']) . '" onclick="openModal(\'' . htmlspecialchars($row['image_url']) . '\', \'' . htmlspecialchars($row['title']) . '\')">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($row['title']) . '</h5>';
                echo '<button class="btn btn-danger" onclick="openDeleteModal(\'' . htmlspecialchars($row['image_url']) . '\', \'' . htmlspecialchars($row['title']) . '\', \'' . $row['id'] . '\')">Delete</button>'; // Assuming you have an ID for each image
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No images uploaded yet.</p>';
        }

        // Close the database connection
        $conn->close();
        ?>
    </div>

    <!-- Modal -->
    <div id="deleteModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h5 id="modalTitle" style="margin: 0;"></h5>
            <p>Are you sure you want to delete this image?</p>
            <img id="modalImage" src="" alt="" style="width: 100%; border-radius: 5px;">
            <div class="modal-footer"
                style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                <form id="deleteForm" method="POST" action="delete_image.php">
                    <input type="hidden" id="imageId" name="imageId" value="">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
                <button class="btn btn-secondary btn-sm" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal and Image Handling Script -->
<script>
    // Function to open the delete modal
    function openDeleteModal(imageUrl, title, imageId) {
        document.getElementById('modalImage').src = imageUrl; // Set the image source
        document.getElementById('modalTitle').innerText = title; // Set the title
        document.getElementById('imageId').value = imageId; // Set the image ID in the hidden input
        document.getElementById('deleteModal').style.display = "block"; // Show the modal
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById('deleteModal').style.display = "none"; // Hide the modal
    }
</script>