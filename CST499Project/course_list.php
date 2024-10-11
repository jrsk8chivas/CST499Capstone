<!DOCTYPE html>
<html>
<head>
    <title>Course List</title>
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

        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
            text-align: left; /* Align list items to the left */
        }

        li {
            margin-bottom: 10px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Style for the Home button */
        a {
            display: inline-block;
            margin-top: 20px; /* Add some space above the button */
            padding: 10px 15px;
            background-color: #555; /* Gray background for the button */
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Course List</h1>

        <?php
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $user_id = $_SESSION['user_id'];

        $conn = new mysqli("localhost", "root", "", "course_registration");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Handle course registration
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['selected_courses'])) {
                $selected_courses = $_POST['selected_courses'];

                foreach ($selected_courses as $course_id) {
                    // Check if the course is full
                    $stmt = $conn->prepare("SELECT max_enrollment, (SELECT COUNT(*) FROM enrollments WHERE course_id = ?) as current_enrollment FROM courses WHERE id = ?");
                    $stmt->bind_param("ii", $course_id, $course_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();

                    // Check if already registered
                    $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = (SELECT id FROM users WHERE user_id = ?) AND course_id = ?");
                    $stmt->bind_param("si", $user_id, $course_id);
                    $stmt->execute();
                    $already_registered = $stmt->get_result()->num_rows > 0;

                    if ($already_registered) {
                        echo "<p>Already registered for course $course_id.</p>";
                    } else if ($row['current_enrollment'] < $row['max_enrollment']) {
                        // Enroll the student
                        $status = 'enrolled';
                        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id, status) VALUES ((SELECT id FROM users WHERE user_id = ?), ?, ?)");
                        $stmt->bind_param("sis", $user_id, $course_id, $status);

                        if ($stmt->execute()) {
                            echo "<p>Registration for course $course_id successful!</p>";
                        } else {
                            echo "<p>Error registering for course $course_id: " . $stmt->error . "</p>";
                        }
                    } else {
                        // Add the student to the waitlist
                        $status = 'waitlisted';
                        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id, status) VALUES ((SELECT id FROM users WHERE user_id = ?), ?, ?)");
                        $stmt->bind_param("sis", $user_id, $course_id, $status);

                        if ($stmt->execute()) {
                            echo "<p>Added to waitlist for course $course_id.</p>";
                        } else {
                            echo "<p>Error adding to waitlist for course $course_id: " . $stmt->error . "</p>";
                        }
                    }
                }
            } else {
                echo "<p>No courses selected.</p>";
            }
        }

        // Fetch all courses
        $sql = "SELECT c.id, c.course_name, c.semester, c.max_enrollment, e.status
                FROM courses c
                LEFT JOIN enrollments e ON c.id = e.course_id AND e.user_id = (SELECT id FROM users WHERE user_id = ?)
                ORDER BY e.status DESC"; // Show registered courses first

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Add the "Home" button/link
        echo "<a href='index.php'>Home</a>"; 

        if ($result->num_rows > 0) {
            echo "<h2>Registered Courses</h2>";
            echo "<ul>";
            $hasRegisteredCourses = false;
            while ($row = $result->fetch_assoc()) {
                if ($row['status']) { // Check if the user is registered for this course
                    $hasRegisteredCourses = true;
                    $course_id = $row['id'];
                    $course_name = $row['course_name'];
                    $semester = $row['semester'];
                    $status = $row['status'];
                    echo "<li>";
                    echo "<b>$course_name</b> ($semester) - You are <b>$status</b> in this course";

                    // Add unregister button/link
                    echo "<form method='post' action='delete_course.php' style='display:inline-block; margin-left:10px;'>";
                    echo "<input type='hidden' name='course_id' value='$course_id'>";
                    echo "<input type='submit' value='Unregister'>";
                    echo "</form>";
                    echo "</li>";
                }
            }
            echo "</ul>";

            if (!$hasRegisteredCourses) {
                echo "<p>No registered courses.</p>";
            }

            echo "<h2>Available Courses</h2>";
            echo "<form method='post' action=''>";
            echo "<ul>";
            $result->data_seek(0); // Reset result pointer
            while ($row = $result->fetch_assoc()) {
                if (!$row['status']) { // Check if the user is NOT registered for this course
                    $course_id = $row['id'];
                    $course_name = $row['course_name'];
                    $semester = $row['semester'];
                    $max_enrollment = $row['max_enrollment'];

                    // Get current enrollment count
                    $stmt = $conn->prepare("SELECT COUNT(*) as current_enrollment FROM enrollments WHERE course_id = ?");
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $enrollment_result = $stmt->get_result();
                    $enrollment_row = $enrollment_result->fetch_assoc();
                    $current_enrollment = $enrollment_row['current_enrollment'];

                    echo "<li>";
                    echo "<input type='checkbox' name='selected_courses[]' value='$course_id'>";
                    echo "<b>$course_name</b> ($semester) - Capacity: $current_enrollment / $max_enrollment";
                    echo "</li>";
                }
            }
            echo "</ul>";
            echo "<input type='submit' value='Register for Courses'>";
            echo "</form>";
        } else {
            echo "<p>No courses available.</p>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>