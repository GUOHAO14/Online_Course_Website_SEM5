<?php
include "conn.php";

//to wy: set session variable
session_start();
if (!isset($_SESSION['user_email']) || !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
$lecturerId = $_SESSION["lecturer_id"];

//student and guest (lecturer pending) can retrieve and view courses
$courseSQL = 
"SELECT cp.*, c.* 
FROM course AS c
JOIN course_proposal AS cp
ON c.proposal_id = cp.proposal_id
WHERE c.lecturer_id = $lecturerId;";

$courseExe1 = mysqli_query($conn, $courseSQL);
$publish_course = [];
$unpublish_course = [];
$pending_course = [];
while ($row = mysqli_fetch_assoc($courseExe1)) {
    if($row["status"] == "Published"){
        $publish_course[] = $row;
    }else if($row["status"] == "Unpublished"){
        $unpublish_course[] = $row;
    }else if($row["status"] == "Removal Pending"){
        $pending_course[] = $row;
    }
}

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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Home</title>
    <link rel="stylesheet" href="lec_home.css">
    <style>
        button.manage_course_btn, button.status_btn {
            width: fit-content;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.4rem;
            background-color: rgb(84, 0, 200);
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease; /* animate everything */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 1rem 1rem 1rem 0;
        }
        button.manage_course_btn:hover, button.status_btn:hover {
            background-color: rgb(65, 31, 111);
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        button.manage_course_btn:active, button.status_btn:active {
            background-color: rgb(84, 0, 200);
            transform: scale(1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 10000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* optional nice effect */
        }

        .whiteSectionBg {
            background-color: white;
            padding: 2.5rem 0;
            margin: 0 0 2.5rem 0;
        }

        body {
            position: relative; 
            padding-top: 7rem;
        }

        @media (max-width: 1160px) {
            body {
                padding-top: 5.6rem
            }
        }

        @media (max-width: 1010px) {
            body {
                padding-top: 5.2rem
            }
        }

        @media (max-width: 326px) {
            body {
                padding-top: 10.2rem
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include "lec_header.php";?>
    </header>
    <div class="whiteSectionBg">
        <div class="contentSection">
            <div class="manage_course_area">
                <button class="manage_course_btn" onclick="proposal()">Proposal</button>
                <button class="manage_course_btn" onclick="create_course()">Create</button>
            </div>

            <h1>Courses</h1>

            <div class="course_area">
                <div class="status_area">
                    <button class="status_btn" onclick="publish()">Published</button>
                    <button class="status_btn" onclick="unpublish()">Unpublished</button>
                    <button class="status_btn" onclick="pending()">Removal Pending</button>
                </div>
                <div class="courseList1 tabCourse"> 
                    <?php
                    $count1 = 0; 
                    foreach ($publish_course as $course) {
                        echo 
                        '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(\''.$course['course_id'].'\')">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                </div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$course['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$course['course_style'].' Learning</p>
                                    <p class="courseTime">'.$course['completion_time'].' hours</p>
                                    <p class="courseRating">'.$avgRatings[$course['course_id']].'&#x02B50;</p>
                                </div>
                            </div>
                        </div>';
                        $count1++;
                    }
                    if ($count1 === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>

                <div class="courseList2 tabCourse"> 
                    <?php
                    $count2 = 0; 
                    foreach ($unpublish_course as $course) {
                        echo 
                        '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(\''.$course['course_id'].'\')">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                </div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$course['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$course['course_style'].' Learning</p>
                                    <p class="courseTime">'.$course['completion_time'].' hours</p>
                                    <p class="courseRating">'.$avgRatings[$course['course_id']].'&#x02B50;</p>
                                </div>
                            </div>
                        </div>';
                        $count2++;
                    }
                    if ($count2 === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>

                <div class="courseList2 tabCourse"> 
                    <?php
                    $count3 = 0; 
                    foreach ($pending_course as $course) {
                        echo 
                        '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(\''.$course['course_id'].'\')">
                                <div class="courseImgContainer">
                                    <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                </div>
                                <div class="courseMetadata">
                                    <p class="courseTitle">
                                        '.$course['title'].' 
                                    </p>
                                    <p class="courseStyle">'.$course['course_style'].' Learning</p>
                                    <p class="courseTime">'.$course['completion_time'].' hours</p>
                                    <p class="courseRating">'.$avgRatings[$course['course_id']].'&#x02B50;</p>
                                </div>
                            </div>
                        </div>';
                        $count3++;
                    }
                    if ($count3 === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.courseMain').forEach(main => {
            main.addEventListener('mouseenter', () => {
                main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
            });
            main.addEventListener('mouseleave', () => {
                main.closest('.courseCard').style.boxShadow = 'none';
            });
        });
        
        //to wy: u change this function
        function clickCourse(courseId) {
            window.location.href = "lec_course.php?course_id=" + courseId;
        }

        function proposal(){
            window.location.href = "lec_activity.php"; 
        }

        function create_course(){
            window.location.href = "lec_create_proposal.php"; 
        }

        function publish(){
            let publish_course = document.getElementsByClassName("courseList1")[0];
            publish_course.style.display = "grid";

            let unpublish_course = document.getElementsByClassName("courseList2")[0];
            unpublish_course.style.display = "none";

            let pending_course = document.getElementsByClassName("courseList2")[1];
            pending_course.style.display = "none";
        }

        function unpublish(){
            let publish_course = document.getElementsByClassName("courseList1")[0];
            publish_course.style.display = "none";

            let unpublish_course = document.getElementsByClassName("courseList2")[0];
            unpublish_course.style.display = "grid";

            let pending_course = document.getElementsByClassName("courseList2")[1];
            pending_course.style.display = "none";
        }

        function pending(){
            let publish_course = document.getElementsByClassName("courseList1")[0];
            publish_course.style.display = "none";

            let unpublish_course = document.getElementsByClassName("courseList2")[0];
            unpublish_course.style.display = "none";

            let pending_course = document.getElementsByClassName("courseList2")[1];
            pending_course.style.display = "grid";
        }
    </script>
</body>
</html>