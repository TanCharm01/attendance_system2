<?php
// Include database connection
include 'db_connection.php';

// Check if the register button was clicked
if (isset($_POST['submit'])) {

    // Get the form data
    $full_name = $_POST['full_name']; 
    $email     = $_POST['email'];
    $password  = $_POST['password'];
    $role      = $_POST['role'];

    // Hash the password so as not to store as plain text
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //the query
    $sql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // 'ssss' means 4 strings
        $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Registration successful!</p>";
            header("Location: login.php");
            exit();
        } else {
            echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "SQL Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<h2>Register</h2>

<form method="POST" action="register.php">

    <label for="full_name">Name:</label><br>
    <input type="text" id="full_name" name="full_name" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="password">Password:</label><br>
    <input type="password" id="password" name="password" required><br><br>

    <label for="role">Role:</label><br>
    <select id="role" name="role" required>
        <option value="student">Student</option>
        <option value="faculty">Faculty</option>
    </select><br><br>

    <button type="submit" name="submit">Register</button>

</form>

</body>
</html>
