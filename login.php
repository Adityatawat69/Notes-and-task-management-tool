<?php
// Start PHP session
session_start();
include 'db_connect.php';

$error = ""; // Initialize an error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Prepare a SQL statement to check if the user exists
        $stmt = $conn->prepare("SELECT password FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Bind the result to a variable
            $stmt->bind_result($stored_password);
            $stmt->fetch();

            // Verify the password using password_verify
            if (password_verify($password, $stored_password)) {
                // Passwords match, set session variable and redirect to dashboard
                $_SESSION['username'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No user found with that username.";
        }

        // Close the statement
        $stmt->close();
    } else {
        $error = "Please fill in both fields.";
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php
        // Display error message if any
        if (!empty($error)) {
            echo "<p style='color: red;'>$error</p>";
        }
        ?>

        <form id="loginForm" method="POST" action="login.php">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        </form>
    </div>
</body>
</html>
