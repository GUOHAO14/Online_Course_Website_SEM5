<?php
// use SESSION variable to identify if the user is viewing his/her own profile
// toggle display of the edit buttons
session_start();
if (isset($_REQUEST["user_email"])) {
    $userEmail = $_GET["user_email"];
}

include "conn.php";

$idSQL = "SELECT student_id FROM student WHERE user_email = '$userEmail';";
$idExe = mysqli_query($conn, $idSQL);
$studentId = mysqli_fetch_assoc($idExe)["student_id"];

//obtain user's personal info from user table
$userSQL = 
"SELECT * from user WHERE user_email = '$userEmail'";
$userExe = mysqli_query($conn, $userSQL);
$infoRow = mysqli_fetch_assoc($userExe);

$studentSQL = 
"SELECT edu_level, learning_style from student WHERE user_email = '$userEmail'";
$studentExe = mysqli_query($conn, $studentSQL);
$stuRow = mysqli_fetch_assoc($studentExe);

//retrieve enrolled courses
$enrolledSQL = 
"SELECT cp.*, c.course_id, ce.enrol_id
FROM course AS c
JOIN course_proposal AS cp
ON c.proposal_id = cp.proposal_id
RIGHT JOIN course_enrolment AS ce
ON c.course_id = ce.course_id
WHERE ce.student_id = $studentId;";
$enrolledExe = mysqli_query($conn, $enrolledSQL);

//student's field of preference
$fieldSQL = 
"SELECT name 
FROM field_preference as fp
LEFT JOIN field as f
ON fp.field_id = f.field_id
WHERE student_id = $studentId;";
$fieldExe = mysqli_query($conn, $fieldSQL);

//student's career of preference
$careerSQL = 
"SELECT name 
FROM career_preference as cp
LEFT JOIN career as c
ON cp.career_id = c.career_id
WHERE student_id = $studentId;";
$careerExe = mysqli_query($conn, $careerSQL);

$ratingSQL = 
"SELECT c.course_id, AVG(rating) AS avgRating 
FROM course_enrolment AS ce
RIGHT JOIN course AS c
ON ce.course_id = c.course_id
GROUP BY c.course_id;"; //retreive average rating for each courseId
$ratingExe = mysqli_query($conn, $ratingSQL);
$avgRatings = []; //key value pair, `courseId => average rating`

while ($row = mysqli_fetch_assoc($ratingExe)) {
    if ($row["avgRating"] !== null) {
        $avgRatings[$row["course_id"]] = round($row["avgRating"], 2);
    } else { //courseId with no reviews yet
        $avgRatings[$row["course_id"]] = "-";
    }
}

include "admin_sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo $infoRow['name']?></title>
    <link rel="icon" type="image/x-icon" href="system_img/Capstone real logo.png">
    <link rel="stylesheet" href="stu-home.css">
    <link rel="stylesheet" href="stu-shared.css">
    <link rel="stylesheet" href="stu-profile.css">
    <script src="https://cdn.jsdelivr.net/npm/validator@13.6.0/validator.min.js"></script>
    <style>
        body {
            padding-top: 0;
        }

        .contentSection {
            padding-top: 0;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="contentSection">
            <button style="margin-bottom: 1rem;" class="purpleBtn" id="backButton" onclick="window.history.back();">back</button>
            <div id="mainContainer">
                <div class="personalInfo">
                    <div id="profileHeader">
                        <h1>Personal Details</h1>
                    </div>
                    <div class="profileContainer">
                        <img class="profile" src=<?php echo $infoRow['pfp'];?> alt="Profile Picture">
                    </div>
                    <div onclick="chat();" id="chat">
                        <p>Chat</p>
                        <img id="chatImg" src="system_img/chat.png" alt="" title="Message">
                    </div>
                    <?php
                    $dob = new DateTime($infoRow['DOB']);
                    $dobFormatted = $dob->format('F j, Y');
                    $personalInfoTemplate = 
                    '<div id="name">'.$infoRow['name'].'</div>
                    <div id="email">'.$infoRow['user_email'].'</div>
                    <div id="dob">DOB: '.$dobFormatted.'</div>
                    <div id="learn">'.$stuRow['learning_style'].' Learning</div>
                    <div id="role">'.$stuRow['edu_level'].'</div>';

                    echo $personalInfoTemplate;
                    ?>
                </div>
                <div class="preferences">
                    <div id="fieldPreferences">
                        <div class="editContainer">
                            <img class="editBtn" width="30px" height="30px" onclick="changePreference();" src="system_img/edit.png" alt="Edit Personal Details">
                        </div>
                        <h2>Field Preference:</h2>
                        <div class="preferenceList">
                            <?php
                            if (mysqli_num_rows($fieldExe) == 0) {
                                echo "<div style='margin-top: 2rem; text-align: center; width: 100%;'>No Field Preferences.</div>";
                            } else {
                                while ($row = mysqli_fetch_assoc($fieldExe)) {
                                    echo $fieldTemplate = 
                                    '<div class="preference">'.$row['name'].'</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div id="careerPreferences">
                        <h2>Career Preference:</h2>
                        <div class="preferenceList">
                            <?php
                            if (mysqli_num_rows($careerExe) == 0) {
                                echo "<div style='margin-top: 2rem; text-align: center; width: 100%;'>No Career Preferences.</div>";
                            } else {
                                while ($row = mysqli_fetch_assoc($careerExe)) {
                                    echo $fieldTemplate = 
                                    '<div class="preference">'.$row['name'].'</div>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
                $inProgressCount = 0;
                $completedCourseArray = [];
                $progressCourseArray = [];
                while ($row = mysqli_fetch_assoc($enrolledExe)) {
                    $courseId = $row['course_id'];
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
                        $totalProgress = 1; //avoid dividing by 0
                    } else {
                        $totalProgress = $progressRow['total'];
                    }

                    $enrolId = $row["enrol_id"];
                    if ($totalProgress === $progressRow["completed"]) {
                        $stmt = $conn->prepare("SELECT percentage FROM quiz_performance WHERE enrol_id = ?");
                        $stmt->bind_param("s", $enrolId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $scoreNum = $result->num_rows;
                        $totalScore = 0;
                        $countScore = 0;
                        if ($scoreNum !== 0) {
                            while ($score = $result->fetch_assoc()) {
                                $countScore++;
                                $totalScore += $score["percentage"];
                            }
                            $avgScore = $totalScore / $countScore;
                        } else {
                            $avgScore = "-";
                        }
                        $stmt->close();

                        $completedCourse = 
                        '<div class="courseCard '.strtolower($row['difficulty']).'Border '.strtolower($row['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($row['difficulty']).'Bg">'.ucfirst($row['difficulty']).'</div>
                            </div>
                            <div class="courseMain courseMain'.$courseId.'" onclick="clickCourse(`'.$courseId.'`)">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=`img/defaultCourse.jpg`;" class="courseImg" src="'.$row['cover_img_url'].'" alt="">
                                </div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$row['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$row['course_style'].' Learning</p>
                                    <p class="courseTime">'.$row['completion_time'].' hours</p>
                                    <p class="courseRating">'.$avgRatings[$courseId].'&#x02B50;</p>
                                    <p class="courseScore">&#x1F4DD;'.$avgScore.'%</p>
                                </div>
                            </div>
                        </div>';
                        $completedCourseArray[] = $completedCourse;
                    }
                }
                ?>

                <div style="scroll-margin-top: 5rem;" id="completedCourseContainer" class="courseContainer">
                    <h1>Courses Completed</h1>
                    <div class="completedCourse courseList">
                        <?php
                            foreach ($completedCourseArray as $course) {
                                echo $course;
                            }
                        ?>
                    </div>
                    <?php
                    if (empty($completedCourseArray)) {
                        echo "<div style='margin-bottom: 1rem; text-align: center;'>No courses completed yet.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        let quizScores = document.querySelectorAll(".courseScore");

        quizScores.forEach(quizScore => {
            console.log(quizScore);
            let score = quizScore.textContent;
            let splicedScore = score.replace("📝", "");
            splicedScore = splicedScore.replace("%", "");
            let intScore = parseInt(splicedScore);
            console.log(intScore);
            if (intScore >= 0 && intScore < 40) {
                quizScore.style.color = "red";
            } else if (intScore >= 40 && intScore < 60) {
                quizScore.style.color = "#ff6000";
            } else if (intScore >= 60 && intScore < 80) {
                quizScore.style.color = "#ffd600";
            } else if (intScore >= 80 && intScore < 90) {
                quizScore.style.color = "#ffff00";
            } else if (intScore >= 90 && intScore <= 100) {
                quizScore.style.color = "#60ff00";
            }
        });

        function clickCourse(courseId) {
            window.location.href = "admin_view_course.php?courseId=" + courseId; //go page
        }

        function chat() {
            //go ivan's chat page
        }

        function changePreference() {
            //go colwyn's choose preference page
        }

        //dynamically add event handlers
        function addEventHandlers() {
            document.querySelectorAll('.courseMain').forEach(main => {
                main.addEventListener('mouseenter', () => {
                    main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
                });
                main.addEventListener('mouseleave', () => {
                    main.closest('.courseCard').style.boxShadow = 'none';
                });
            });
        }
        addEventHandlers();
    </script>
    <script></script>
</body>
</html>