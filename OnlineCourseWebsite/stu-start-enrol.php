<?php
session_start();
if (!isset($_SESSION["user_email"])) {
    header("Location: index.php");
    exit();
}

date_default_timezone_set('Asia/Kuala_Lumpur');
$currentDateTime = date("Y-m-d H:i:s");

$studentId = $_SESSION["student_id"];
$courseId = $_GET["courseId"];

include "conn.php";

//create course enrolment
$stmt = $conn->prepare("INSERT INTO course_enrolment (last_access_date, course_id, student_id) VALUES (?, ?, ?)");
$stmt->bind_param("sii", $currentDateTime, $courseId, $studentId);
$stmt->execute();
$stmt->close();

//get the newly inserted enrol_id
$stmt = $conn->prepare("SELECT enrol_id FROM course_enrolment WHERE course_id = ? AND student_id = ? ORDER BY enrol_id DESC LIMIT 1");
$stmt->bind_param("ii", $courseId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$enrolId = $row["enrol_id"];
$stmt->close();

//fetch all sections for this course
$stmt = $conn->prepare("
    SELECT s.section_id
    FROM Section s
    JOIN Chapter c ON s.chapter_id = c.chapter_id
    WHERE c.course_id = ?
");
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();
$sectionRows = [];
while ($row = $result->fetch_assoc()) {
    $sectionRows[] = $row;
}
$stmt->close();

//insert into progression table
$progress = 0;
$stmt = $conn->prepare("INSERT INTO progression (enrol_id, section_id, progress) VALUES (?, ?, ?)");
foreach ($sectionRows as $section) {
    $stmt->bind_param("iii", $enrolId, $section["section_id"], $progress);
    $stmt->execute();
}
$stmt->close();

$_SESSION["course_id"] = $courseId;
$_SESSION["enrol_id"] = $enrolId;
//redirect
header("Location: stu_section.php");
exit();
?>
