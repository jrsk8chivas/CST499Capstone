<!DOCTYPE html>
<html>
<head>
    <title>Unregister from Course</title>
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
            text-align: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        p {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Unregister from Course</h1>

        <?php
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php"); // Redirect to login if not logged in
            exit();
        }

        $user_id = $_SESSION['user_id'];

        // Database connection details
        $conn = new mysqli("localhost", "root", "", "course_registration");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $course_id = $_POST['course_id'];

            // Delete the enrollment record
            $stmt = $conn->prepare("DELETE FROM enrollments WHERE user_id = (SELECT id FROM users WHERE user_id = ?) AND course_id = ?");
            $stmt->bind_param("si", $user_id, $course_id);

            if ($stmt->execute()) {
                echo "<p>Course deleted from your schedule.</p>";
                echo "<p>Please wait for page to refresh.</p>";
                // Redirect to course_list.php after a short delay
                header("refresh:3;url=course_list.php"); 
                exit();
            } else {
                echo "<p>Error: " . $stmt->error . "</p>";
            }

            $stmt->close();
        }

        $conn->close();
        ?>
    </div>
</body>
</html>