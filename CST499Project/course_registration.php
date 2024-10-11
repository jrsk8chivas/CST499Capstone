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

    // Check if the course is full
    $stmt = $conn->prepare("SELECT max_enrollment, (SELECT COUNT(*) FROM enrollments WHERE course_id = ?) as current_enrollment FROM courses WHERE id = ?");
    $stmt->bind_param("ii", $course_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['current_enrollment'] < $row['max_enrollment']) {
        // Enroll the student
        $status = 'enrolled';
    } else {
        // Add the student to the waitlist
        $status = 'waitlisted';
    }

    $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id, status) VALUES ((SELECT id FROM users WHERE user_id = ?), ?, ?)");
    $stmt->bind_param("sis", $user_id, $course_id, $status);

    if ($stmt->execute()) {
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?>