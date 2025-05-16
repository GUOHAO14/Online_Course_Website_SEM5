<?php
include "conn.php";
if (isset($_REQUEST["user_email"]) && isset($_REQUEST["origin"])) {
    $userEmail = $_REQUEST["user_email"];
    $origin = $_REQUEST["origin"];
    
    $sql = 
    "SELECT role FROM user WHERE user_email = '$userEmail';";
    $checkRole = mysqli_query($conn, $sql);

    if (mysqli_num_rows($checkRole) === 1) { //should be one
        $row = mysqli_fetch_assoc($checkRole);
        $userType = $row['role'];

        if ($userType === "student") {
            if (isset($_REQUEST["courseId"])) {
                $courseId = $_REQUEST["courseId"];
                header('Location: stu-profile.php?user_email='.$userEmail.'&origin=stu-course-detail.php&courseId='.$courseId);
            }
        } 

        else if ($userType === "lecturer") {
            if (isset($_REQUEST["courseId"])) {
                $courseId = $_REQUEST["courseId"];
                header('Location: lec-profile.php?user_email='.$userEmail.'&origin=stu-course-detail.php&courseId='.$courseId);
            }
        }
    } else { //somehow cannot find user, go index to resolve as guest
        header('Location: index.php');
        exit();
    }
} else {
    if (!empty($_SERVER['HTTP_REFERER'])) {
        //referring page (previous page)
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        //if no referrer, go to a default page
        header('Location: index.php');
        exit();
    }
}
?>