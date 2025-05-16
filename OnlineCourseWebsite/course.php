<?php
include "conn.php";
if (isset($_GET['fieldId'])) {
    $fieldId = (int) $_GET['fieldId'];

    //retrieve based on field
    $courseSQL = 
    "SELECT cp.*, c.course_id
    FROM course AS c
    JOIN course_proposal AS cp
    ON c.proposal_id = cp.proposal_id
    JOIN course_field as cf
    ON c.proposal_id = cf.proposal_id
    WHERE c.status IN ('Published', 'Removal Pending') AND cf.field_id = $fieldId;"; 
    //courses that are published
    $courseExe = mysqli_query($conn, $courseSQL);

    $courseData = []; // Array to hold all course rows

    while ($row = mysqli_fetch_assoc($courseExe)) {
        $courseData[] = $row;
    }
    echo json_encode($courseData);

} else if (isset($_GET['courseId'])) {  
    $courseId = (int) $_GET['courseId'];

    $courseSQL = 
    "SELECT cp.*, c.course_id
    FROM course AS c
    JOIN course_proposal AS cp
    ON c.proposal_id = cp.proposal_id
    WHERE c.status IN ('Published', 'Removal Pending') AND c.course_id = $courseId;"; 
    //courses that are published
    $courseExe = mysqli_query($conn, $courseSQL);

    $courseData = []; // Array to hold all course rows

    while ($row = mysqli_fetch_assoc($courseExe)) {
        $courseData[] = $row;
    }
    echo json_encode($courseData);

} else {
    header('Content-Type: application/json');
    echo json_encode(["error" => "fieldId not provided"]);
}
$conn->close();
?>