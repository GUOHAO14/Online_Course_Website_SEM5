<?php
// retrieve progress
$progressSQL = 
"SELECT count(*) AS total, sum(CASE WHEN progress = 1 THEN 1 ELSE 0 END) AS completed
FROM section AS s
JOIN progression AS p ON s.section_id = p.section_id
WHERE enrol_id = 
    (SELECT enrol_id 
    FROM course_enrolment 
    WHERE student_id = $studentId AND course_id = $courseId);";

$progressExe = mysqli_query($conn, $progressSQL);
$progressRow = mysqli_fetch_assoc($progressExe);

if ($progressRow['total'] == 0) {
    $totalProgress = 1;
} else {
    $totalProgress = $progressRow['total'];
}
?>