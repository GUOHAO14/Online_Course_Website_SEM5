<?php
include "conn.php";
$email = $_REQUEST["email"];
$password = $_REQUEST["password"];

//check if credentials are found in the user table
$stmt = $conn->prepare("SELECT * FROM user WHERE user_email = ?;");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$response = new stdClass();

if ($result->num_rows > 0) {
    //email exist

    //verify the password (hashed)
    $user = $result->fetch_assoc();
    if (password_verify($password, $user["password"])) {
        $password_error = false;
    } else {
        $password_error = true;
    }

    //verify the password
    if (!$password_error) {
        session_start();
        $_SESSION["user_email"] = $user["user_email"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["role"] = $user["role"];
        $_SESSION["pfp"] = $user["pfp"];

        $response->status = "success";
        $response->user_email = $user["user_email"];
        $response->role = $user["role"];

        if ($user["role"] === "student") {
            $roleStmt = $conn->prepare("SELECT student_id FROM student WHERE user_email = ?;");
            $roleStmt->bind_param("s", $email);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $student = $roleResult->fetch_assoc();

            $_SESSION["student_id"] = (int) $student["student_id"];
            
            $roleStmt->close();
        } else if ($user["role"] === "lecturer") {
            $roleStmt = $conn->prepare("SELECT lecturer_id FROM lecturer WHERE user_email = ?;");
            $roleStmt->bind_param("s", $email);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $lecturer = $roleResult->fetch_assoc();

            $_SESSION["lecturer_id"] = (int) $lecturer["lecturer_id"];

            $roleStmt->close();
        } else if ($user["role"] === "admin") {
            $roleStmt = $conn->prepare("SELECT admin_id FROM admin WHERE user_email = ?;");
            $roleStmt->bind_param("s", $email);
            $roleStmt->execute();
            $roleResult = $roleStmt->get_result();
            $admin = $roleResult->fetch_assoc();

            $_SESSION["admin_id"] = (int) $admin["admin_id"];

            $roleStmt->close();
        } else if ($user["role"] === "pending lecturer") {
            //only needs session role as pending lecturer for limited stu-home visits
        } else {
            $response->role = null;
        }
        $response->status = "success";
    } else {
        $response->status = "password_error";
    }

} else { 
    //check user in removed_user table
    $removedStmt = $conn->prepare("SELECT * FROM removed_user WHERE user_email = ?;");
    $removedStmt->bind_param("s", $email);
    $removedStmt->execute();
    $removedResult = $removedStmt->get_result();

    if ($removedResult->num_rows > 0) {
        $response->status = "removed";

        $removedUser = $removedResult->fetch_assoc();
        $response->user_email = $removedUser["user_email"];
        $response->name = $removedUser["name"];
        $response->removed_reason = $removedUser["removed_reason"];
        $response->removed_status = $removedUser["removed_status"];

    } else { 
        //not in removed user table
        //that means email not in database (never signed up)
        $response->status = "email_error";
    }
    $removedStmt->close();
}
$stmt->close();
$conn->close();

echo json_encode($response);
?>