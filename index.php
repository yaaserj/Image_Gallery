<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not logged in, redirect to login page
    header("Location: home.php");
    exit;
}

// Include database connection
include 'db.php';

// Set how many images per page (21)
$limit = 15;

// Get the current page from the URL, default to page 1 if not set
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

// Calculate the offset
$offset = ($page - 1) * $limit;

// Fetch images from the database with limit and offset
$sql = "SELECT * FROM images LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Get the total number of images for pagination purposes
$total_images_sql = "SELECT COUNT(*) as count FROM images";
$total_result = $conn->query($total_images_sql);
$total_row = $total_result->fetch_assoc();
$total_images = $total_row['count'];

// Calculate the total number of pages
$total_pages = ceil($total_images / $limit);

// Debugging Information
// Check what the total pages and current page values are
// Uncomment these lines to see the output on your screen and debug
// echo "Total Images: " . $total_images . "<br>";
// echo "Total Pages: " . $total_pages . "<br>";
// echo "Current Page: " . $page . "<br>";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Davis Website</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* General Styles */
        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .wrapper {
            display: flex;
            height: 100vh;
        }

        /* Fixed Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 70px;
            bottom: 0;
            width: 250px;
            background-color: #f8f9fa;
            overflow-y: auto;
            padding: 1rem;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        /* Main content scrolls */
        .main-content {
            margin-top: 70px;
            margin-left: 250px;
            padding: 20px;
            overflow-y: auto;
            height: calc(100vh - 70px);
        }

        /* Search bar inside the Navbar */
        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form {
            position: relative;
        }

        .form .fa-search {
            position: absolute;
            top: 10px;
            left: 20px;
            color: #9ca3af;
        }

        .form span {
            position: absolute;
            right: 17px;
            top: 10px;
            padding: 2px;
            border-left: 1px solid #d1d5db;
        }

        .form-input {
            height: 40px;
            text-indent: 33px;
            border-radius: 10px;
        }

        .form-input:focus {
            box-shadow: none;
            border: none;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 992px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* Style the pagination buttons */
        .pagination-buttons {
            margin-top: 20px;
            text-align: center;
        }

        .pagination-buttons a {
            margin: 0 5px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        .pagination-buttons a.disabled {
            background-color: #6c757d;
            pointer-events: none;
        }

        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.8);
            /* Black w/ opacity */
        }

        /* modal for images */
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            /* Centered with some margin from top */
            padding: 10px;
            /* Reduced padding */
            border: 1px solid #888;
            width: 60%;
            /* Adjust the width to reduce size */
            max-width: 600px;
            /* Set a maximum width for larger screens */
            border-radius: 5px;
            /* Optional: add some rounding to corners */
        }

        .close {
            color: #aaa;
            float: right;
            /* Float to the right */
            font-size: 24px;
            /* Adjust font size if needed */
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .image-card {
            cursor: pointer;
        }
    </style>
</head>

<body>
    <!-- Wrapper container for navbar and sidebar -->
    <div class="wrapper">
        <!-- Navigation Bar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
            <div class="container-fluid">
                <!-- Logo -->
                <a class="navbar-brand" href="#">
                    <img src="images/logo.png" alt="Davis Logo" height="50">
                </a>

                <!-- Search Bar -->
                <div class="search-container">
                    <div class="form">
                        <label for="search-input" class="visually-hidden">Search anything</label>
                        <i class="fa fa-search"></i>
                        <input type="text" id="search-input" class="form-control form-input"
                            placeholder="Search anything...">
                        <span class="left-pan"><i class="fa fa-microphone"></i></span>
                    </div>
                </div>


                <!-- Toggler button and Logout button, both aligned on the right -->
                <div class="navbar-right">
                    <!-- Toggler button -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar"
                        aria-controls="sidebar" aria-expanded="false" aria-label="Toggle sidebar">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Logout button, aligned to the right of the toggler -->
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Sidebar (Offcanvas for small screens, Static for large screens) -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="sidebarLabel">Filters</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Tags</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Price</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Static Sidebar for large screens -->
        <aside class="sidebar">
            <div class="p-3">
                <h5>Filters</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Tags</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Price</a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="main-content container-fluid">
            <!-- Gallery Section -->
            <h2 class="mt-4 text-center">Image Gallery</h2>

            <!-- Message when no images are found -->
            <div id="no-images-message" class="alert alert-warning" style="display: none;">
                No images found matching your search criteria.
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4 card-margin">
                <?php
                if ($result->num_rows > 0) {
                    // Loop through the images and display them in the gallery
                    while ($row = $result->fetch_assoc()) {
                        $imageTitle = htmlspecialchars($row['title'], ENT_QUOTES);
                        $imageUrl = htmlspecialchars($row['image_url'], ENT_QUOTES);
                        $imageId = htmlspecialchars($row['id'], ENT_QUOTES); // Assuming there's an 'id' field in your database for the image
                
                        echo '
                        <div class="col image-card" data-title="' . $imageTitle . '">
                            <div class="card shadow-sm" onclick="openModal(\'' . $imageUrl . '\', \'' . $imageTitle . '\')"> <!-- Call JavaScript function -->
                                <img src="' . $imageUrl . '" class="card-img-top lazy" alt="' . $imageTitle . '">
                                <div class="card-body">
                                    <h5 class="card-title" style="font-size: 1rem; text-decoration: none; text-align: center;">' . $imageTitle . '</h5> 
                                </div>
                            </div>
                        </div>
                        ';
                    }
                } else {
                    echo '<p>No images found. Please upload some images.</p>';
                }

                // Close the database connection
                $conn->close();
                ?>

            </div>

            <!-- Modal -->
            <div id="imageModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <!-- Close button -->
                    <img id="modalImage" src="" alt="" style="width: 100%; border-radius: 5px;">
                    <!-- Optionally add border-radius to the image -->
                    <div class="modal-footer"
                        style="display: flex; justify-content: space-between; align-items: center;">
                        <h5 id="modalTitle" style="margin: 0;"></h5>
                        <a id="downloadButton" href="" download class="btn btn-primary">Download</a>
                    </div>
                </div>
            </div>

            <!-- Pagination Buttons -->
            <div class="pagination-buttons mt-4 text-center">
                <!-- Show "Previous" button only if the user is not on the first page -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary"><- Previous Page</a>
                        <?php else: ?>
                            <a class="btn btn-secondary disabled"><- Previous Page</a>
                                <?php endif; ?>

                                <!-- Show "Next" button only if there are more images to show -->
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-primary">Next Page -></a>
                                <?php else: ?>
                                    <a class="btn btn-primary disabled">Next Page -></a>
                                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Wait for the DOM to load before adding event listeners
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("search-input");
            const imageCards = document.querySelectorAll(".image-card");
            const noImagesMessage = document.getElementById("no-images-message");

            // Add an input event listener to the search input field
            searchInput.addEventListener("input", function () {
                const searchTerm = searchInput.value.toLowerCase();
                let foundImage = false; // Flag to track if any image matches

                imageCards.forEach(function (card) {
                    const imageTitle = card.getAttribute("data-title").toLowerCase();

                    // Check if the search term matches the image title
                    if (imageTitle.includes(searchTerm)) {
                        card.style.display = "block";  // Show matching image
                        foundImage = true; // An image matched
                    } else {
                        card.style.display = "none";   // Hide non-matching image
                    }
                });

                // Show or hide the message based on whether any images were found
                if (foundImage) {
                    noImagesMessage.style.display = "none"; // Hide message if images found
                } else {
                    noImagesMessage.style.display = "block"; // Show message if no images found
                }
            });
        });
        //modal for images
        function openModal(imageUrl, title) {
            document.getElementById('modalImage').src = imageUrl; // Set the image source
            document.getElementById('modalTitle').innerText = title; // Set the title
            document.getElementById('downloadButton').href = imageUrl; // Set the download link
            document.getElementById('imageModal').style.display = "block"; // Show the modal
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = "none"; // Hide the modal
        }
    </script>

</body>

</html>