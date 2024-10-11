<!DOCTYPE html>
<html>
<head>
    <title>Online Course Registration</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f0f0;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align content within the container */
        }

        h1 {
            margin-bottom: 20px;
        }

        a {
            display: inline-block; /* Make links behave like blocks for better spacing */
            margin: 0 10px; /* Add some space between links */
            padding: 10px 15px;
            background-color: #4CAF50; /* Green background */
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Online Course Registration</h1>

        <?php
        session_start();
        if (isset($_SESSION['user_id'])) {
            echo "<p>You are logged in as: " . $_SESSION['user_id'] . "</p>";
            echo "<a href='course_list.php'>View Courses</a> | "; 
            echo "<a href='logout.php'>Logout</a>"; // Logout button
        } else {
            echo "<a href='login.php'>Login</a> | <a href='register.php'>Register</a>";
        }
        ?>
    </div>
</body>
</html>