<?php
//from stu-home.php, ajax posts
$course = (int) $_REQUEST["course"];
$student = (int) $_REQUEST["student"];

include "conn.php";

$obj = new stdClass(); //create object -- key value pair

$recentStmt = $conn->prepare("SELECT recent_course_list FROM student WHERE student_id = ?"); 
//retrieve recently visited courses by student
$recentStmt->bind_param("i", $student);
$recentStmt->execute();
$result = $recentStmt->get_result(); 
if ($row = $result->fetch_assoc()) {
    $recentCourses = $row["recent_course_list"];
    $courseArray = explode(",", $recentCourses); //make a list of courses
    
    if (in_array($course, $courseArray)) { //if selected course is already part of recently visited
        $index = array_search((string) $course, $courseArray); //get index 
        array_splice($courseArray, $index, 1); //delete with index
        $courseArray[] = (string) $course; //append course
        $obj->status = "replacing";
    } else {
        $courseArray[] = (string) $course; //append course
        if (count($courseArray) > 8) { //maximum recent courses saved is 8
            array_splice($courseArray, 0, 1); //delete first course
            $obj->status = "adding";
        }
    }
    
    $string = implode(",", $courseArray); //make a string out of the list
    $newRecentStmt = $conn->prepare("UPDATE student SET recent_course_list = ? WHERE student_id = ?;");
    $newRecentStmt->bind_param("si", $string, $student);

    if ($newRecentStmt->execute()) { //check if executed
        $obj->status .= " success"; //save new recent courses successful
    } else {
        $obj->status .= " failed. Try again later.";
    }
} else {
    $obj->status = "all action failed";
}

$obj->student = $student;
$obj->course = $course;
$recentStmt->close();
$newRecentStmt->close();
$conn->close();

echo json_encode($obj); //created json response to inform user
?>