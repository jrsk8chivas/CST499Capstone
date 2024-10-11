<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
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
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        p {
            color: red; /* For error messages */
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registration</h1>

        <?php
        // Check if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get the user input
            $user_id = $_POST['user_id'];
            $password = $_POST['password'];
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];

            // Connect to your database 
            $conn = new mysqli("localhost", "root", "", "course_registration");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Hash the password (essential for security)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare and execute the SQL query to insert the user data
            $stmt = $conn->prepare("INSERT INTO users (user_id, password, name, phone, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $user_id, $hashed_password, $name, $phone, $email);

            if ($stmt->execute()) {
                echo "<p>Registration successful!</p>";
                // Redirect to the login page after a short delay
                header("refresh:3;url=login.php"); 
                exit();            
            } else {
                // Check for duplicate user ID error
                if ($stmt->errno == 1062) { // Duplicate entry error code for MySQL
                    echo "<p>User ID already exists. Please choose a different one.</p>";
                } else {
                    echo "<p>Error: " . $stmt->error . "</p>";
                }
            }

            $stmt->close();
            $conn->close();
        }
        ?>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="user_id">User ID:</label>
            <input type="text" id="user_id" name="user_id" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone"><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>