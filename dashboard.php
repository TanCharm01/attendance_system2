<?php
session_start();
include 'db_connection.php';

// Check if user logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$message = "";


if(isset($_POST['join_course_btn']) && $_SESSION['role']=='student'){

    $course_id_to_join = $_POST['course_id'];
    $student_id = $_SESSION['user_id'];

    $check_sql = "SELECT id FROM enrollments WHERE student_id=? AND course_id=?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $student_id, $course_id_to_join);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0){
        $message = "<div style='color:orange; margin-bottom:10px;'>You have already requested this course</div>";
    } else {
        $insert_sql = "INSERT INTO enrollments(student_id, course_id, status) VALUES(?, ?, 'pending')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $student_id, $course_id_to_join);

        if($insert_stmt->execute()){
            $message = "<div style='color:green; margin-bottom:10px;'>Request sent to faculty</div>";
        } else {
            $message = "<div style='color:red; margin-bottom:10px;'>Error sending request</div>";
        }
    }
}

if (isset($_POST['update_request_btn']) && $_SESSION['role']=='faculty'){

    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action == 'approved' || $action == 'rejected'){
        $update_sql = "UPDATE enrollments SET status=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $action, $request_id); 

        if ($stmt->execute()){
            $message = "<div style='color:green; padding:10px; border:1px solid green;'>Request " . htmlspecialchars($action) . "!</div>";
        } else {
            $message = "<div style='color:red;'> Error updating record.</div>";
        }
    }
}

if (isset($_POST['create_course_btn']) && $_SESSION['role']=='faculty'){
    $course_name = $_POST['course_name'];
    $faculty_id = $_SESSION['user_id'];

    if(!empty($course_name)){
        $sql = "INSERT INTO courses(course_name, faculty_id) VALUES(?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $course_name, $faculty_id);

        if($stmt->execute()){
           
            $message = "<div style='color:green; margin-bottom:10px;'>Course '$course_name' created!</div>";
        } else {
           
            $message = "<div style='color:red; margin-bottom:10px;'>Error " . $stmt->error . "</div>";
        }

    } else {
        $message = "<div style='color:red;'>Please enter a course name</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo $_SESSION['user_name']; ?>!</h1>
        <p>Role: <?php echo $_SESSION['role']; ?></p>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
    <hr>
    
    <?php echo $message; ?>

    <?php if ($_SESSION['role']=='faculty'):?>
        <div class="dashboard-box faculty-panel">
            <h3>Faculty Dashboard</h3>

            <div class="form-container">
                <h4>Create a New Course</h4>
                <form method="post" action="dashboard.php">
                    <input type="text" name="course_name" placeholder="Enter course name" required>
                    <button type="submit" name="create_course_btn">Create Course</button>
                </form>
            </div>

            <br><hr><br>

            <h3>Manage Student Requests</h3>
            
            <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <th>Student Name</th>
                    <th>Course Requested</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php
                
                $my_id = $_SESSION['user_id'];
                
                $sql = "SELECT enrollments.id AS request_id, users.full_name, courses.course_name, enrollments.status
                        FROM enrollments
                        JOIN courses ON enrollments.course_id = courses.id
                        JOIN users ON enrollments.student_id = users.id
                        WHERE courses.faculty_id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $my_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                        
                        $color = 'black';
                        if ($row['status'] == 'pending') $color = 'orange';
                        if ($row['status'] == 'approved') $color = 'green';
                        if ($row['status'] == 'rejected') $color = 'red';
                        
                        echo "<td style='color:$color; font-weight:bold;'>" . ucfirst($row['status']) . "</td>";
                        
                        echo "<td>";
                        if ($row['status'] == 'pending') {
                            echo "<form method='POST' action='dashboard.php' style='display:inline;'>";
                            echo "<input type='hidden' name='request_id' value='" . $row['request_id'] . "'>";
                            
                            echo "<button type='submit' name='update_request_btn' onclick=\"this.form.action.value='approved';\">✅ Approve</button> ";

                            echo "<button type='submit' name='update_request_btn' onclick=\"this.form.action.value='rejected';\" style='color:red;'>❌ Reject</button>";
                            
                            echo "<input type='hidden' name='action' value=''>"; 
                            echo "</form>";
                        } else {
                            echo "Done";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No requests found.</td></tr>";
                }
                ?>
            </table>
            </div>

    <?php elseif ($_SESSION['role'] == 'student'): ?>
        
        <div class="dashboard-box student-panel">
            <h3>Student Dashboard</h3>
            <h3> My Active Classes</h3>
            <ul>
                <?php
                $my_id = $_SESSION['user_id'];
                
                $sql_my_courses = "SELECT courses.course_name, users.full_name 
                                   FROM enrollments 
                                   JOIN courses ON enrollments.course_id = courses.id 
                                   JOIN users ON courses.faculty_id = users.id 
                                   WHERE enrollments.student_id = ? AND enrollments.status = 'approved'";
                
                $stmt_my = $conn->prepare($sql_my_courses);
                $stmt_my->bind_param("i", $my_id);
                $stmt_my->execute();
                $result_my = $stmt_my->get_result();

                if ($result_my->num_rows > 0) {
                    while($row = $result_my->fetch_assoc()) {
                        echo "<li><strong>" . htmlspecialchars($row['course_name']) . "</strong> (Prof. " . htmlspecialchars($row['full_name']) . ")</li>";
                    }
                } else {
                    echo "<p>You have not been accepted into any courses yet.</p>";
                }
                ?>
            </ul>
            <hr>
            <h4>Available Courses</h4>

            <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <th>Course Name</th>
                    <th>Taught By</th>
                    <th>Action</th>
                </tr>

                <?php
                $sql = "SELECT courses.id, courses.course_name, users.full_name 
                        FROM courses 
                        JOIN users ON courses.faculty_id = users.id";
                
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['course_name'] . "</td>";
                        echo "<td>" . $row['full_name'] . "</td>"; 
                        echo "<td>";
                        
                        echo "<form method='POST' action='dashboard.php'>";
                        echo "<input type='hidden' name='course_id' value='" . $row['id'] . "'>";
                        echo "<button type='submit' name='join_course_btn'>Request to Join</button>";
                        echo "</form>";
                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No courses available yet.</td></tr>";
                }
                ?>
            </table>
        </div>

    <?php endif; ?>
</body>
</html>