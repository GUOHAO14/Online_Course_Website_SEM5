<?php
session_start();
if (isset($_SESSION["role"]) && isset($_SESSION["student_id"]) && $_SESSION["role"] === "student") {
    $studentId = $_SESSION["student_id"];
    $guest = false;
} else if (isset($_SESSION["role"]) && $_SESSION["role"] === "pending lecturer") {
    $guest = true;
    $studentId = "pendinglecturer"; //placeholder value
} else {
    header("Location: login-page.php");
    session_write_close();
    exit();
}

include "conn.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (strlen($search) < 2) {
    echo "Please enter at least 2 characters.";
    exit;
}

$searchWildcard = "%$search%";

$sql = 
"SELECT 
    cp.*, 
    c.course_id, 
    ROUND(AVG(ce.rating), 2) AS avgRating
FROM course AS c
JOIN course_proposal AS cp ON c.proposal_id = cp.proposal_id
LEFT JOIN course_enrolment AS ce ON c.course_id = ce.course_id
WHERE c.status IN ('Published', 'Removal Pending')
AND cp.title LIKE ?
GROUP BY c.course_id
ORDER BY avgRating DESC;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $searchWildcard);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    if ($row["avgRating"] === null) {
        $row["avgRating"] = "-";
    }
    $courses[] = $row;
}

$bookmarkSQL = "SELECT course_id FROM saved_course WHERE student_id = $studentId;"; //check bookmark status
$bookmarkExe = mysqli_query($conn, $bookmarkSQL);
$bookmarkCourses = [];

while ($row = mysqli_fetch_assoc($bookmarkExe)) {
    $bookmarkCourses[] = $row["course_id"]; 
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <link rel="stylesheet" href="stu-shared.css">
    <link rel="stylesheet" href="stu-home.css">
</head>
<body>
    <header>
        <?php include "header.php";?>
    </header>
    <div class="whiteSectionBg">
        <div class="contentSection">
            <button style="margin-bottom: 3rem;" class="purpleBtn" id="backButton" onclick="window.location.href = 'stu-home.php';">Back</button>
            <div style="display: flex; align-items: end;"><h1>Search Result: </h1><h2 style="margin-bottom: 0.5rem;">&nbsp;&nbsp;"<?php echo $search;?>"</h2></div>
            <div class="courseList tabCourse"> 
            <?php
                $count = 0; 
                $allSuggestedCourses = [];
                if (empty($courses)) {

                } else {
                    foreach ($courses as $course) {
                        $suggestedCourse = 
                        '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(\''.$course['course_id'].'\')">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">';
                        
                        $text = in_array($course['course_id'], $bookmarkCourses) ? "&check;" : "+";
                        if ($text === "&check;") {
                            $height = "3rem";
                        } else {
                            $height = "2rem";
                        }
                        $suggestedCourse .=
                                    '<div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, \'tick'.$course['course_id'].'\')"><p class="tick tick'.$course['course_id'].'">'.$text.'</p></div>';
                        
                        $suggestedCourse .= 
                                '</div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$course['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$course['course_style'].' Learning</p>
                                    <p class="courseTime">'.$course['completion_time'].' hours</p>
                                    <p class="courseRating">'.$course["avgRating"].'&#x02B50;</p>
                                </div>
                            </div>
                        </div>';
                        $count++;
                        echo $suggestedCourse;
                    }
                }
                ?>
            <div>
        </div>
    </div>
    
</body>
</html>

<script>
    function clickCourse(courseId) {
        console.log(`Click ${courseId}`);
        if (!guest) {
            const request = new XMLHttpRequest();
            request.open("POST", "save-recent-course.php?course=" + courseId + "&student=" + <?php echo $studentId;?>);
            request.send();
        }
        
        window.location.href = "stu-course-detail.php?courseId=" + courseId; //go page
    }
</script>

