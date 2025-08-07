<?php

require "db-connection.php";
require "db-connection-vig.php";

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Prepare secure login query using prepared statements
    $stmt = $conn_vig->prepare("SELECT role_id, password FROM admin_user WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role_id'];
        $db_password = $row['password'];

        // 🔐 Use password_verify if you store hashed passwords
        if ($pass === $db_password /* OR password_verify($pass, $db_password) */) {
            $_SESSION['username'] = $user;
            $_SESSION['role'] = $role;

            // Get employee details
            $stmt_emp = $conn_vig->prepare("SELECT Employee_Name, Designation, organization_Location FROM vig.common_all WHERE New_Employee_No = ?");
            $stmt_emp->bind_param("s", $user);
            $stmt_emp->execute();
            $result_emp = $stmt_emp->get_result();

            if ($result_emp && $result_emp->num_rows > 0) {
                $row_emp = $result_emp->fetch_assoc();

                $_SESSION['organization_location'] = $row_emp['organization_Location'];
                $_SESSION['designation'] = $row_emp['Designation'];
                $_SESSION['empname'] = $row_emp['Employee_Name'];
                $_SESSION['emp_no'] = $user;

                // Set role based on user ID
                if ($user == "19208") {
                    $_SESSION['role'] = "admin";
                    header("Location: admin-home.php");
                } else {
                    $_SESSION['role'] = "emp";
                    header("Location: emp_home.php");
                }
                exit();
            }
        } else {
            echo '<script>alert("Wrong Username or Password")</script>';
        }
    } else {
        echo '<script>alert("Wrong Username or Password")</script>';
    }
}

$conn->close();
$conn_vig->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login Page</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="assets/css/login.css" rel="stylesheet">

</head>

<body>

    <div class="login-container">

        <img class="mb-4" src="assets/img/mgvcl_logo.png" alt="Logo" width="110" height="90">
        <h1 class="h3 mb-3 font-weight-normal">Login</h1>

        <?php if (isset($error_message)): ?>
            <div class="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" id="username" class="form-control" placeholder="Enter Username" name="username" required autofocus>
            <input type="password" id="password" class="form-control" placeholder="Enter Password" name="password" required>
            <button class="btn btn-primary" type="submit">Sign in</button><br><br>
            <p>Use Vigilance Checking System Credentials To Login</p>
        </form>
    </div>

</body>

</html>