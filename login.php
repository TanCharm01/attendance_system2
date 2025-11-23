<?php
//start session
session_start();
//connect the database using connection file
include 'db_connection.php';

if (isset($_POST['login_btn'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    //sql query to find the user by email
    $sql = "SELECT id, full_name, password, role FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s",$email);
    $stmt->execute();

    $result = $stmt->get_result();

    //check if user exists

    if ($result->num_rows ===1){
        $user = $result->fetch_assoc();

        //compare password enetered with hash

        if(password_verify($password, $user['password'])){

            //log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

           // redirect to dashboard
            //header("Location:dashboard.php");
            //exit();
            echo "<h1>Login Success!</h1>";
            echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
            echo "<a href='dashboard.php'>Click here to go to Dashboard</a>";
            exit();
            
        } else{
            echo "Invalid password";
        }
    }else{
        echo "User not found";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Login</h2>
    <form method = "POST" action ="login.php">
        <input type = "email" name ="email" placeholder="Enter Email" required><br><br>
        <input type = "password" name ="password" placeholder="Enter password" required><br><br>
        <button type ="submit" name ="login_btn">Login</button>

    </form>
    <p> Don't have an account?<a href="register.php">Register here</a></p>
</body>
</html>