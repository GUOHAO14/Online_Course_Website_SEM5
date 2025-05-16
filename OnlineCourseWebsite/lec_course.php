<?php
session_start();
if (!isset($_SESSION['user_email']) && !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include "conn.php";

$studentId = 1;
$userEmail = "guohao.k@gmail.com";
$courseId = $_GET['course_id'];
$_SESSION['course_id'] = $courseId;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["publish_status"])) {
    $publish_status = $_POST['publish_status'];
    $sql = "UPDATE course SET `status` = '$publish_status' WHERE course_id = '$courseId'";
    mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["remove_reason"])) {
    $remove_reason = $_POST['remove_reason'];
    $sql = "UPDATE course SET `status` = 'Removal Pending', removal_reason = '$remove_reason' WHERE course_id = '$courseId'";
    mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_removal"])) {
    $cancel_removal = $_POST['cancel_removal'];
    $sql = "UPDATE course SET `status` = '$cancel_removal', removal_reason = null WHERE course_id = '$courseId'";
    mysqli_query($conn, $sql);
}

$detailSQL = 
"SELECT count(sc.course_id) as totalSaveCount, update_date, publish_date, ul.pfp as lecturer_icon, ul.name as lecturer_name, user_email as lecturer_user_email, status, cp.*
FROM course as c 
JOIN course_proposal as cp 
on c.proposal_id = cp.proposal_id 
JOIN (SELECT pfp, lecturer_id, name, l.user_email FROM user as u RIGHT JOIN lecturer as l on u.user_email = l.user_email) as ul
on c.lecturer_id = ul.lecturer_id
LEFT JOIN saved_course as sc
on c.course_id = sc.course_id
WHERE c.course_id = $courseId;"; //all course details
$detailExe = mysqli_query($conn, $detailSQL);
$detailRow = mysqli_fetch_assoc($detailExe);
$proposalId = $detailRow["proposal_id"];

//student reviews
$reviewSQL = 
"SELECT * FROM course_enrolment AS ce 
LEFT JOIN 
(SELECT pfp as icon, s.student_id, name, s.user_email FROM student AS s LEFT JOIN user AS u on s.user_email = u.user_email) AS su
ON ce.student_id = su.student_id
WHERE course_id = $courseId
ORDER BY review_date DESC;"; 
$reviewExe = mysqli_query($conn, $reviewSQL);

$ratingOnlySQL = 
"SELECT r.rating, COUNT(ce.rating) as ratingNum
FROM (
    SELECT 1 as rating UNION ALL
    SELECT 2 UNION ALL
    SELECT 3 UNION ALL
    SELECT 4 UNION ALL
    SELECT 5
) AS r
LEFT JOIN course_enrolment ce on ce.rating = r.rating and ce.course_id = $courseId
GROUP BY r.rating
ORDER BY r.rating desc;
"; //count student ratings
$ratingOnlyExe = mysqli_query($conn, $ratingOnlySQL);
//above block can be improved, redundant database call

$enrolCountSQL = 
"SELECT count(enrol_id) as enrolTotal FROM course_enrolment WHERE course_id = $courseId;"; //total enrolled student num
$enrolCountExe = mysqli_query($conn, $enrolCountSQL);
$enrolCountRow = mysqli_fetch_assoc($enrolCountExe);

$avgRatingSQL = 
"SELECT AVG(rating) as avgRating, count(rating) as ratingNum FROM course_enrolment WHERE course_id = $courseId;"; 
//average rating and total number of ratings
$avgRatingExe = mysqli_query($conn, $avgRatingSQL);
$avgRatingRow = mysqli_fetch_assoc($avgRatingExe);

$fieldSQL = "SELECT name as field_name FROM field as f RIGHT JOIN course_field as cf on f.field_id = cf.field_id WHERE cf.proposal_id = $proposalId;"; //obtain course's fields
$fieldExe = mysqli_query($conn, $fieldSQL);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $detailRow["title"];?></title>
    <link rel="stylesheet" href="lec_course.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
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
            <button class="purpleBtn" id="backButton" onclick="window.location.href = 'lec_home.php';">Back</button>
            <div class="manage_course_area">
                <?php
                if($detailRow["status"] == "Published"){
                    echo '
                    <p class="course_status">Published</p>
                    <button class="manage_course_btn" title="Unpublish Course" onclick="unpublish()">Unpublish</button>
                    <button class="manage_course_btn" title="Track Progression" onclick="track_progression('.$courseId.')">Track Progression</button>
                    <button class="manage_course_btn" title="Edit" onclick="edit('.$courseId.')">Edit</button>
                    <button class="manage_course_btn" title="Remove" onclick="remove()">Remove</button>
                    ';
                }else if($detailRow["status"] == "Unpublished"){
                    echo '
                    <p class="course_status">Unpublished</p>
                    <button class="manage_course_btn" title="Publish Course" onclick="publish()">Publish</button>
                    <button class="manage_course_btn" title="Track Progression" onclick="track_progression('.$courseId.')">Track Progression</button>
                    <button class="manage_course_btn" title="Edit" onclick="edit('.$courseId.')">Edit</button>
                    <button class="manage_course_btn" title="Remove" onclick="remove()">Remove</button>
                    ';
                }else if($detailRow["status"] == "Removal Pending"){
                    echo '
                    <p class="course_status">Removal Pending</p>
                    <button class="manage_course_btn" title="Cancel Removal" onclick="cancel_removal()">Cancel Removal</button>
                    ';
                }
                ?>

            </div>
            <div id="courseHeader">
                <?php
                if ($detailRow["lecturer_icon"] === null) {
                    $icon = "profile/defaultProfile.jpg";
                } else {
                    $icon = $detailRow["lecturer_icon"];
                }

                if ($detailRow["update_date"] === null) {
                    $dateText = "Published";
                    $date = $detailRow["publish_date"];
                } else {
                    $dateText = "Updated";
                    $date = $detailRow["update_date"];
                }

                $dateFormatted = date('F j, Y', strtotime($date));

                $headerTemplate = '';

                $headerTemplate .= 
                '<div id="courseTitle">

                    <h1>'.$detailRow["title"].'</h1>
                </div>
                <div class="courseImgContainer" id="coursePicture">
                    <img class="courseImg" src="'.$detailRow["cover_img_url"].'" alt="">
                </div>
                <div id="courseDescriptionContainer">
                    <span id="courseDescription">'.$detailRow["description"].'</span>
                    <a href="#description" id="seeMoreButton">... See More</a>
                </div>
                
                <div id="courseLecturer">
                    <span>Created by</span>
                    <div class="profileLink">
                        <div class="lecturerIconContainer">
                            <img class="lecturerIcon" src="'.$icon.'" alt="">
                        </div>
                        <span id="lecturerName">'.$detailRow["lecturer_name"].'</span>
                    </div>
                    
                </div> 
                <div id="courseDate">
                    <span>'.$dateText.' on </span>'.$dateFormatted.'
                </div>
                <div id="enrolCount">
                    <b>'.$enrolCountRow["enrolTotal"].'</b> already enrolled, <b>'.$detailRow["totalSaveCount"].'</b> has bookmarked this course
                </div>';

                echo $headerTemplate; //print template
                ?> 
            </div>
            <div id="courseDetails">
                <?php
                $detailsTemplate = 
                '<div>
                    <div class="detailHeader">
                        <b>Ratings</b>
                    </div>
                    <div>'.round($avgRatingRow["avgRating"], 2).'&#x02B50; ('.$avgRatingRow["ratingNum"].')</div>
                </div>
                <div>
                    <div class="detailHeader">
                        <b>Difficulty</b>
                    </div>
                    <div>'.ucfirst($detailRow["difficulty"]).'</div>
                </div>
                <div>
                    <div class="detailHeader">
                        <b>Course Style</b>
                    </div>
                    <div>'.$detailRow["course_style"].' Learning</div>
                </div>
                <div>
                    <div class="detailHeader">
                        <b>Completion Time</b>
                    </div>
                    <div>'.$detailRow["completion_time"].'</div>
                </div>';

                echo $detailsTemplate; //print template
                ?>
            </div>
            <div id="courseFields">
                <h3>Topics Covered:</h3>
                <div id="topicList">
                    <?php
                    while ($row = mysqli_fetch_assoc($fieldExe)) {
                        $topicTemplate = 
                        '<div class="topic">'.$row["field_name"].'</div>';
                        echo $topicTemplate;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="whiteSectionBg">
        <div class="contentSection">
            <div id="courseInfo">
                <div id="infoSidebar">
                    <a class="tablinks" href="#courseHeader">Overview</a>
                    <a class="tablinks" href="#objective">Objective</a>
                    <a class="tablinks" href="#scope">Scope</a>
                    <a class="tablinks" href="#description">Description</a>
                    <a class="tablinks" href="#review">Ratings & Reviews</a>
                </div>
                <div id="infoDisplay">
                    <?php
                    $overviewTemplate = 
                    '<div id="objective" class="tabContent">
                        <h2>Objective of This Course</h2><br>
                        '.$detailRow["objective"].'
                    </div>
                    <div id="scope" class="tabContent">
                        <h2>What Will You Learn?</h2><br>
                        '.$detailRow["scope"].'
                    </div>
                    <div id="description" class="tabContent">
                        <h2>Description</h2><br>'.$detailRow["description"].'
                    </div>';
                    echo $overviewTemplate;
                    ?>

                    <div id="review" class="tabContent">
                        <h2 style="margin-bottom: 2rem;">Ratings and Reviews</h2>
                        <div style="margin-bottom: 1.5rem;" id="ratingOverview">
                            <div style="font-weight: bold;" class="rating">Average Rating: <?php echo round($avgRatingRow["avgRating"], 2)?>&#x02B50;</div>
                            <div><i>(<?php echo round($avgRatingRow["ratingNum"], 2)?> total ratings)</i></div>
                            <?php 
                            $totalReviews = 0;
                            while ($row = mysqli_fetch_assoc($ratingOnlyExe)) {
                                if ($avgRatingRow["ratingNum"] > 0) {
                                    $widthPercent = 100 * $row["ratingNum"] / $avgRatingRow["ratingNum"];
                                    $ratingOverviewTemplate = 
                                    '<div style="display: flex; align-items: center;">
                                        <div style="flex-shrink: 0;">'.$row["rating"].' &#x02B50;:</div>
                                        <div style="width: '.$widthPercent.'%;" class="reviewBar"></div>
                                        <div>('.$row["ratingNum"].')</div>
                                    </div>';
                                    echo $ratingOverviewTemplate;
                                    $totalReviews += $row["ratingNum"];
                                }
                            }
                            ?>
                        </div>
                        
                        <div id="reviewCardList">
                            <?php
                            // $reviewCount = 0;
                            if ($totalReviews > 0) { //if ratings exist
                                while ($row = mysqli_fetch_assoc($reviewExe)) {
                                    if ($row["rating"] !== null) {
                                        if ($row["icon"] === null) {
                                            $icon = "profile/defaultProfile.jpg";
                                        } else {
                                            $icon = $row["icon"];
                                        }
        
                                        $dateFormatted = date('F j, Y', strtotime($row['review_date']));
        
                                        $ratingTemplate = 
                                        '<div class="reviewCard">
                                            <a class="studentLink" href="profile-police.php?user_email='.$row["user_email"].'&origin=stu-course-detail.php&courseId='.$courseId.'">
                                                <div class="studentIconContainer">
                                                    <img onerror="this.onerror=null; this.src=`img/defaultCourse.jpg`;" class="lecturerIcon" src="'.$icon.'" alt="">
                                                </div>
                                                <span class="studentName">'.$row["name"].'</span>
                                            </a>
                                            <div class="rating">
                                                <div style="font-weight: bold; display: flex; align-items: center;">Rating:&nbsp;&nbsp;</div>
                                                <div style="font-size=18px; display: flex; align-items: center;">'.$row["rating"].'&#x02B50;</div>
                                            </div>
                                            <div class="more">
                                                <span class="moreButton">&vellip;</span>
                                                <div class="moreMenu">
                                                    <div style="color: red; font-weight: bold;" class="menuItem bi bi-flag-fill" onclick="reportReview('.$row["enrol_id"].')">&nbsp;&nbsp;Report</div>
                                                </div>
                                            </div>
                                            <div class="reviewTitle"><h3>'.$row["review_title"].'<h3></div>
                                            <div class="reviewDate"><u>'.$dateFormatted.'</u></div>
                                            <div class="comment">'.$row["review_comment"].'</div>
                                        </div>';

                                        echo $ratingTemplate;

                                        // if ($reviewCount < 3) echo $ratingTemplate;
                                        // $reviewCount++;
                                    }
                                    
                                }
                            }
                            ?>
                        </div>

                        <div class="box_overlay">
                            <form class="box" method="post">
                                <h2 class="remove_title">Removal Form</h2>
                                <div class="remove_reason_area">
                                    <p class="remove_text">Reason to remove course:</p>
                                    <input type="text" name="remove_reason" class="remove_reason" placeholder="Please Enter Your Reason" required>
                                </div>
                                <div class="box_btn_area">
                                    <button type="submit" class="box_btn">Submit</button>
                                    <button type="button" class="box_btn" onclick="close_box()">Close</button>
                                </div>
                            </form>
                        </div>
                        <?php
                        // if ($reviewCount > 3) {
                        //     echo 
                        //     '<div style="margin-top: 2rem; text-align: center;">
                        //         <button class="purpleBtn" id="moreReviewsButton" onclick="window.location.href = `stu-all-reviews.php?courseId='.$courseId.'&courseName='.$detailRow['title'].'`">See More</button> 
                        //     </div>';
                        // }
                        ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let reviewCount = <?php echo $totalReviews;?>;
        if (reviewCount === 0) {
            console.log(document.getElementById("ratingOverview").innerHTML);
            document.getElementById("ratingOverview").innerHTML = 
            '<?php echo "No reviews yet"; ?>';
            document.getElementById("ratingOverview").style.textShadow = "1px 1px 10px rgb(219, 219, 219), -1px -1px 10px rgb(219, 219, 219)";
            document.getElementById("ratingOverview").style.textAlign = "center";
            document.getElementById("ratingOverview").style.fontSize = "larger";
        }

        function showDescription() {
            let description = document.getElementById("courseDescription");
            let descriptionContent = description.textContent;
            if ((descriptionContent.length > 270)) {
                shortText = descriptionContent.substr(0, 260);
                description.textContent = shortText;
                document.getElementById("seeMoreButton").style.visibility = "visible";
                document.getElementById("seeMoreButton").textContent = "... See More";
            }
            return descriptionContent;
        }

        showDescription();

        function ucfirst(str) {
            if (!str) return str; // return the string if it's empty
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        //more button for all other review cards
        document.querySelectorAll('.moreButton:not(.myReviewCard .moreButton)').forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation(); //prevent click from bubbling up
                const menu = this.nextElementSibling;
                //close all other menus
                document.querySelectorAll('.moreMenu').forEach(m => {
                    if (m !== menu) m.style.display = 'none';
                });
                //toggle current menu
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });

        function reportReview(enrolId) {

        }

        // Close menu if clicking elsewhere
        document.addEventListener('click', () => {
            document.querySelectorAll('.moreMenu').forEach(m => m.style.display = 'none');
        });

        function openTab(evt, subjectName) {
            let tabcontent = document.getElementsByClassName("tabContent");
            for (let i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            let tablinks = document.getElementsByClassName("tablinks");
            for (let i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            // Show the current tab, and add an "active" class to the button that opened the tab
            document.getElementById(subjectName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        function track_progression(course_id) {
            window.location.href = "lec_track_progression.php";
        }

        function edit(course_id) {
            window.location.href = "lec_edit_sidebar.php";
        }

        function unpublish() {
            let contentSection = document.getElementsByClassName("contentSection")[0];
            contentSection.insertAdjacentHTML("beforeend", `
                <form class="hidden_form" method="post">
                    <input type="hidden" name="publish_status" value="Unpublished">
                </form>
            `);
            setTimeout(() => {
                let hidden_form = document.getElementsByClassName("hidden_form")[0];
                hidden_form.submit();
            }, 0);
        }

        function publish() {
            let contentSection = document.getElementsByClassName("contentSection")[0];
            contentSection.insertAdjacentHTML("beforeend", `
                <form class="hidden_form" method="post">
                    <input type="hidden" name="publish_status" value="Published">
                </form>
            `);
            setTimeout(() => {
                let hidden_form = document.getElementsByClassName("hidden_form")[0];
                hidden_form.submit();
            }, 0);
        }

        function remove() {
            document.getElementsByClassName("box_overlay")[0].style.display = "flex";
        }

        function close_box() {
            document.getElementsByClassName("box_overlay")[0].style.display = "none";
        }

        function cancel_removal() {
            let contentSection = document.getElementsByClassName("contentSection")[0];
            contentSection.insertAdjacentHTML("beforeend", `
                <form class="hidden_form" method="post">
                    <input type="hidden" name="cancel_removal" value="Unpublished">
                </form>
            `);
            setTimeout(() => {
                let hidden_form = document.getElementsByClassName("hidden_form")[0];
                hidden_form.submit();
            }, 0);
        }
    </script>
</body>
</html>