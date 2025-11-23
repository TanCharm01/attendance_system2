<?php
session_start();
include 'db_connection.php';

//check if user logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
$message ="";
if (isset($_POST['create_course_btn']) && $_SESSION['role']=='faculty'){
    $course_name = $_POST['course_name'];
    $faculty_id = $_SESSION['user_id'];

    if(!empty($course_name)){
        $sql= "INSERT INTO courses(course_name,faculty_id)VALUES(?,?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si",$course_name,$faculty_id);

        if($stmt->execute()){
            $message="div style='color:green;margin-bottom:10px;'>Course '$course_name' created!</div>";
        }
        else{
            $message="div style='color:red;margin-bottom:10px;'>Error ".$stmt->error ."</div>";
        }

    }else{
        $message="Please enter a course name";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo $_SESSION['user_name'];?>!</h1>
        <p>Role: <?php echo $_SESSION['role'];?></p>
        <a href="logout.php" class="logout-button">Logout</a>

    </div>
    <hr>
    <?php echo $message; ?>
    <?php if ($_SESSION['role']=='faculty'):?>
        <div class ="dashboard-box faculty-panel">
            <h3>Faculty Dashboard</h3>

            <div class="form-container">
                <h4>Create a New Course</h4>
                <form method="post" action="dashboard.php">
                    <input type ="text" name="course_name" placeholder="Enter course name" required>
                    <button type ="submit" name="create_course_btn">Create Course</button>
                </form>
            </div>
        </div>

    <?php elseif ($_SESSION['role']=='student'):?>
        <div class="dashboard-box student-panel">
            <h3>Student Dashboard</h3>
            <button>Browse Courses</button>
        </div>
    <?php endif; ?>  
</body>
</html>