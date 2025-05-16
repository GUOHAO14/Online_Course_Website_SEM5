<?php
session_start();
if (!isset($_SESSION["user_email"])) {
    header("Location: index.php");
    exit();
}
include "conn.php";
$studentId = $_SESSION["student_id"];
$courseId = $_GET["courseId"];

$stmt = $conn->prepare("SELECT enrol_id FROM course_enrolment WHERE course_id = ? AND student_id = ? ORDER BY enrol_id DESC LIMIT 1");
$stmt->bind_param("ii", $courseId, $studentId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$enrolId = $row["enrol_id"];
$stmt->close();

$_SESSION["course_id"] = $courseId;
$_SESSION["enrol_id"] = $enrolId;
header("Location: stu_section.php");
exit();
?>
