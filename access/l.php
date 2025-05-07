<?php
session_start();
$authenticated = false;
if(isset($_SESSION["email"])) {
$authenticated = true;
}
?>

<!doctype html> 
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FINAL PROJECT</title>
    <link rel="icon" href="/image/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
      /* Hero Section Styling */
      .hero {
        background-image: url('logo.png'); /* Update the correct path to your image */
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: white;
        height: 100vh; 
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        position: relative;
      }

      /* Add a semi-transparent overlay */
      .hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Black overlay with 50% opacity */
        z-index: 1;
      }

      .hero .content {
        max-width: 600px;
        z-index: 2; /* Bring content above the overlay */
        text-align: center;
      }

      .hero h1 {
        font-size: 2.5rem;
        font-weight: bold;
      }

      .hero p {
        font-size: 1.2rem;
      }

      /* Navbar Styling */
      .navbar {
        background: gray; /* Dark background with opacity */
      }

      .navbar-brand {
        font-weight: bold;
        color: white;
      }

      .navbar-nav .nav-link {
        color: #ffffff;
        font-weight: 500;
      }

      /* Responsive Adjustments */
      @media (max-width: 768px) {
        .hero {
          padding: 0 20px;
          text-align: center;
        }
        .hero h1 {
          font-size: 2rem;
        }
        .hero p {
          font-size: 1rem;
        }
      }
    </style>
  </head>
  <body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-pphKo1+p3aVjAwcE8PFXRb7RzQh/B1SxXT3aK5wB2P7jsD5zY4f7l5lYZQ3jc6pw" crossorigin="anonymous"></script>
  </body>
</html>
