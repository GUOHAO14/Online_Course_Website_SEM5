<?php
//from stu-home.php, ajax posts
$course = (int) $_REQUEST["course"];
$student = (int) $_REQUEST["student"];

$obj = new stdClass(); //create object -- key value pair
$obj->student = $student;
$obj->course = $course;

include "conn.php";

try { 
    //save bookmark
    $bookmarkStmt = $conn->prepare("INSERT INTO saved_course VALUES (?, ?);");
    $bookmarkStmt->bind_param("ii", $student, $course);

    if ($bookmarkStmt->execute()) {
        $obj->status = "success"; //bookmark successful
    } else {
        $obj->status = "Failed to save bookmark. Try again later.";
    }
    $bookmarkStmt->close();
} catch (mysqli_sql_exception $e) { 
    //unable to execute stmt
    //if due to duplicate PK, means user intend to remove bookmark
    if (str_contains($e->getMessage(), 'Duplicate entry')) { //remove bookmark
        $bookmarkStmt = $conn->prepare("DELETE FROM saved_course WHERE student_id = ? AND course_id = ?;");
        $bookmarkStmt->bind_param("ii", $student, $course);

        if ($bookmarkStmt->execute()) {
            $obj->status = "removed";
        } else {
            $obj->status = "Failed to remove bookmark. Try again later.";
        }
        $bookmarkStmt->close();
    } else {
        $obj->status = "Error: ".$e->getMessage().". Try again later.";
    }
}

$conn->close();

echo json_encode($obj); //json literal object
?>
