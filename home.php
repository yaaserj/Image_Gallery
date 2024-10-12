<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';  // Your database connection

// Initialize variables for messages
$signupMessage = "";
$loginMessage = "";

// Handle the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if it's the Signup form
    if (isset($_POST['signup'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Hash the password
        $role = 'user';  // Default role is user

        // Check if email already exists
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $signupMessage = "Email already exists! Please login or use another email.";
        } else {
            // Insert user into the database
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($stmt->execute()) {
                $signupMessage = "Signup successful! Please login to continue.";
                // Redirect to login form (you can adjust timing with JavaScript for better user experience)
                echo "<script>setTimeout(function(){ window.location.href = '#login'; }, 2000);</script>";
            } else {
                $signupMessage = "Error occurred: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();

        // Check if it's the Login form
    } elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check user in the database
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password, $role);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $hashed_password)) {
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;

                // Redirect based on role
                if ($role == 'admin') {
                    header("Location: adminpanel.php");
                } elseif ($role == 'moderator') {
                    header("Location: moderator.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $loginMessage = "Invalid password!";
            }
        } else {
            $loginMessage = "No user found with that email!";
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Davis homepage</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
        media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Login/Signup Modal -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">Login/Signup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Signup Message -->
                    <?php if ($signupMessage): ?>
                        <div class="alert alert-info"><?php echo $signupMessage; ?></div>
                    <?php endif; ?>

                    <!-- Login Message -->
                    <?php if ($loginMessage): ?>
                        <div class="alert alert-danger"><?php echo $loginMessage; ?></div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form id="loginForm" method="post">
                        <h5 class="text-center">Login</h5>
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" id="loginEmail"
                                placeholder="Enter email" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" id="loginPassword"
                                placeholder="Password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                        <hr>
                        <p class="text-center">Don't have an account? <a href="#" id="showSignup">Sign Up</a></p>
                    </form>

                    <!-- Signup Form (Initially hidden) -->
                    <form id="signupForm" style="display: none;" method="post">
                        <h5 class="text-center">Sign Up</h5>
                        <div class="mb-3">
                            <label for="signupName" class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" id="signupName"
                                placeholder="Enter full name" required>
                        </div>
                        <div class="mb-3">
                            <label for="signupEmail" class="form-label">Email address</label>
                            <input type="email" name="email" class="form-control" id="signupEmail"
                                placeholder="Enter email" required>
                        </div>
                        <div class="mb-3">
                            <label for="signupPassword" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" id="signupPassword"
                                placeholder="Create password" required>
                        </div>
                        <button type="submit" name="signup" class="btn btn-primary w-100">Sign Up</button>
                        <hr>
                        <p class="text-center">Already have an account? <a href="#" id="showLogin">Login</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="images/logo.png" alt="Davis Logo" height="50">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#authModal">Login/Signup</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Welcome to Our Awesome Website</h1>
            <p>Join us and explore thousands of cool resources.</p>
            <button class="btn btn-lg btn-light" data-bs-toggle="modal" data-bs-target="#authModal">Get Started</button>
        </div>
    </section>

    <!-- Content Section -->
    <section class="content-section">
        <div class="container">
            <h2 class="text-center mb-5">Featured Content</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card">
                        <img src="images/image1.jpg" class="card-img-top" alt="Content Image 1" loading="lazy">
                        <div class="card-body">
                            <h5 class="card-title">Amazing Content</h5>
                            <p class="card-text">Some quick example text to build on the card title and make up the bulk
                                of the card's content.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="images/image2.jpg" class="card-img-top" alt="Content Image 2" loading="lazy">
                        <div class="card-body">
                            <h5 class="card-title">Incredible Designs</h5>
                            <p class="card-text">Explore beautiful and modern designs from our creative community.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="images/image3.jpg" class="card-img-top" alt="Content Image 3" loading="lazy">
                        <div class="card-body">
                            <h5 class="card-title">Discover Creativity</h5>
                            <p class="card-text">Join us and unleash your creativity with our exclusive tools.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p>Follow us on social media</p>
            <div class="social-icons mb-3">
                <a href="#" class="icon facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" class="icon twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" class="icon instagram"><i class="fab fa-instagram"></i></a>
            </div>
            <p class="mt-3">&copy; 2024 Davis. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" defer></script>
    <script>
        // Toggle between login and signup forms
        document.getElementById('showSignup').addEventListener('click', function () {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('signupForm').style.display = 'block';
        });

        document.getElementById('showLogin').addEventListener('click', function () {
            document.getElementById('signupForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        });
    </script>
</body>

</html>