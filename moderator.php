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
// Set session timeout duration (in seconds)
$timeout_duration = 1800; // 30 minutes

// Check if the timeout variable is set
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    // If the session is inactive for the timeout duration, destroy the session
    session_unset();
    session_destroy();
    header("Location: home.php");
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Database connection
include 'db.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// User query
$sql = 'SELECT * FROM users';
$result = mysqli_query($conn, $sql);

// Count users
$total_users_sql = "SELECT COUNT(*) as total_user FROM users";
$users_result = mysqli_query($conn, $total_users_sql);
$total_users = $users_result ? $users_result->fetch_assoc()['total_user'] : 0;

// Total image count query
$total_images_sql = "SELECT COUNT(*) as count FROM images";
$total_result = $conn->query($total_images_sql);
$total_images = $total_result ? $total_result->fetch_assoc()['count'] : 0;

// Fetch image uploads per month for the last 6 months
$image_uploads_sql = "
    SELECT DATE_FORMAT(upload_date, '%M') as month, COUNT(*) as count 
    FROM images 
    WHERE upload_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month 
    ORDER BY upload_date ASC";
$uploads_result = mysqli_query($conn, $image_uploads_sql);

$months = [];
$upload_counts = [];

if ($uploads_result) {
    while ($row = mysqli_fetch_assoc($uploads_result)) {
        $months[] = $row['month'];
        $upload_counts[] = $row['count'];
    }
}

// Prepare data for JavaScript in JSON format
$months_json = json_encode($months);
$upload_counts_json = json_encode($upload_counts);
?>


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
            overflow: hidden;
        }

        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f8f9fa;
        }

        .search-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 20px;
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
            width: 300px;
        }

        .form-input:focus {
            box-shadow: none;
            border: none;
        }

        .sidebar {
            position: fixed;
            top: 70px;
            bottom: 0;
            width: 250px;
            background-color: #f8f9fa;
            overflow-y: auto;
            padding: 1rem;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .main-content {
            margin-top: 70px;
            margin-left: 250px;
            padding: 20px;
            overflow-y: auto;
            height: calc(100vh - 70px);
        }

        .card-custom {
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            margin-bottom: 20px;
        }

        .card.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .card-custom.card-blue {
            background: linear-gradient(135deg, #5ee7df 0%, #b490ca 100%);
        }

        .card-custom.card-green {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .card-custom .card-title {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .card .card-text {
            font-size: 18px;
        }

        .chart-container {
            width: 100%;
            height: 400px;
        }

        @media (max-width: 992px) {
            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

</head>

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
                    <a href="moderator.php" class="nav-link" id="manageImagesLink">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="manageUploadLink">Upload Images</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="manageImagesLink">Manage Images</a>
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
                    <a href="moderator.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="manageUploadLink">Upload Images</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="manageImagesLink">Manage Images</a>
                </li>
            </ul>
        </div>
    </aside>

    <!-- Main content area -->
    <div class="main-content container-fluid">
        <div class="row">
            <!-- Content Section -->
            <div class="content pt-5" id="mainContent">
                <!-- Row 1: Statistics cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                        <div class="card-custom card-blue">
                            <h4 class="text-center">Total Users</h4>
                            <p class="text-center"><?php echo htmlspecialchars($total_users); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                        <div class="card-custom card-green">
                            <h4 class="text-center">Total Images Uploaded</h4>
                            <p class="text-center"><?php echo htmlspecialchars($total_images); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Image Uploads Chart -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="text-center mb-3">Image Uploads in Last 6 Months</h5>
                        <div class="chart-container"
                            style="position: relative; height: 50vh; width: 60vw; margin: 0 auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 15px; background-color: #fff; border-radius: 10px;">
                            <canvas id="uploadsChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- AJAX for dynamic content loading -->
<script>
    $(document).ready(function () {
        // Function to load content dynamically via AJAX
        function loadContent(url) {
            // Fade out current content
            $('#mainContent').fadeOut(200, function () {
                // Show a loading indicator
                $('#mainContent').html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>').fadeIn(100);

                // Make AJAX request
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function (response) {
                        // Load the response into the main content area and fade it in
                        $('#mainContent').html(response).fadeIn(200);
                    },
                    error: function () {
                        // Display an error message in case of failure
                        $('#mainContent').html('<p>Error loading content. Please try again later.</p>').fadeIn(200);
                    }
                });
            });
        }

        // Click handler for Manage Users link
        $(document).on('click', '#manageUploadLink', function (e) {
            e.preventDefault();
            loadContent('manageUpload.php');  // URL for managing users
        });

        // Click handler for Manage Images link
        $(document).on('click', '#manageImagesLink', function (e) {
            e.preventDefault();
            loadContent('manageimages.php');  // URL for managing images
        });
    });
</script>

<!-- Chart.js Script -->
<script>
    var ctx = document.getElementById('uploadsChart').getContext('2d');
    var uploadsChart = new Chart(ctx, {
        type: 'line', // You can also try 'bar' for a bar chart
        data: {
            labels: <?php echo $months_json; ?>, // e.g. ["January", "February", "March"]
            datasets: [{
                label: 'Image Uploads',
                data: <?php echo $upload_counts_json; ?>, // e.g. [10, 25, 50]
                borderColor: 'rgba(75, 192, 192, 1)',  // Line color
                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Fill color under the line
                borderWidth: 3, // Thicker line for better visibility
                pointRadius: 5, // Size of the points
                pointBackgroundColor: 'rgba(255, 99, 132, 1)', // Point color
                pointBorderColor: '#fff', // Border around points
                pointHoverRadius: 7, // Size of point on hover
                pointHoverBackgroundColor: 'rgba(255, 99, 132, 1)', // Hover effect
                pointHoverBorderColor: '#fff',
                tension: 0.4 // Smooth line
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allow responsive resizing
            plugins: {
                legend: {
                    display: true, // Show the legend
                    labels: {
                        color: '#333', // Text color for labels
                        font: {
                            size: 14, // Font size for legend
                            family: 'Arial' // Font family
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(0, 0, 0, 0.7)', // Darker tooltip
                    titleFont: {
                        size: 16,
                        family: 'Arial'
                    },
                    bodyFont: {
                        size: 14
                    },
                    padding: 10,
                    cornerRadius: 5
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true, // Show gridlines on X axis
                        color: 'rgba(200, 200, 200, 0.2)', // Custom gridline color
                    },
                    title: {
                        display: true, // X-axis title
                        text: 'Months',
                        color: '#666',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#666', // Tick labels color
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(200, 200, 200, 0.2)'
                    },
                    title: {
                        display: true, // Y-axis title
                        text: 'Number of Uploads',
                        color: '#666',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#666', // Tick labels color
                        font: {
                            size: 12
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest', // Ensures the tooltip interacts with the nearest point
                axis: 'x'
            },
            hover: {
                mode: 'nearest', // Show details on the nearest point
                intersect: true
            }
        }
    });
</script>

</script>

</body>

</html>