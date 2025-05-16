<?php
session_start(); //session var
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

$userEmail = $_SESSION["user_email"];

if (isset($_REQUEST["courseId"])) {
    $courseId = (int) $_REQUEST["courseId"];
} else {
    header("Location: index.php");
    session_write_close();
    exit();
}

include "conn.php";

echo "<script>console.log($courseId);</script>";
$deleteStatus = false;
$updateStatus = false;
$failStatus = false;
if ($_SERVER['REQUEST_METHOD'] == "POST") { //handle review submission
    $type = $_POST["form_type"];
     
    if ($type === "create") {
        $comment = $_POST["reviewComment"];
        $rating = (int) $_POST["rating"];
        $title = $_POST["reviewTitle"];
        $dateTime = date("Y-m-d H:i:s"); //current date time

        //if empty string, becomes null
        $comment = $comment ?: null; 
        $title = $title ?: null; 
        $stmt = $conn->prepare("UPDATE course_enrolment SET rating = ? , review_title = ?, review_comment = ?, review_date = ? WHERE student_id = $studentId");

        //bind parameter
        $stmt->bind_param("isss", $rating, $title, $comment, $dateTime);

        //execute the statement
        if ($stmt->execute()) {
            $updateStatus = true;
        } else {
            $failStatus = true;
        }
    } else {
        $rating = null;
        $title = null;
        $comment = null;
        $dateTime = null;
        //need be in variable to bind param

        $stmt = $conn->prepare("UPDATE course_enrolment SET rating = ? , review_title = ?, review_comment = ?, review_date = ? WHERE student_id = $studentId");

        //bind parameter
        $stmt->bind_param("ssss", $rating, $title, $comment, $dateTime);

        //execute the statement
        if ($stmt->execute()) {
            $deleteStatus = true;
        } else {
            $failStatus = true;
        }
    }

    //close statement
    $stmt->close();
}
//check if user is enrolled in this course
//enrolStatus === 1 means enrolled
if (!$guest) {
    $enrolSQL = 
    "SELECT * FROM course_enrolment WHERE student_id = $studentId AND course_id = $courseId;";
    $checkEnrol = mysqli_query($conn, $enrolSQL);
    $enrolStatus = mysqli_num_rows($checkEnrol);
    if ($enrolStatus === 1) {
        include "checkProgress.php";
    }
} else {
    $enrolStatus = 2;
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
    <link rel="stylesheet" href="stu-course-detail.css">
    <link rel="stylesheet" href="stu-shared.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="stu-course-detail.js"></script>
</head>
<body>
    <header>
        <?php include "header.php";?>
    </header>
    <div class="blurOverlay"></div>
    <div class="whiteSectionBg">
        <div class="contentSection">
            <button class="purpleBtn" id="backButton" onclick="window.location.href = 'stu-home.php';">Back</button>
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

                if ($enrolStatus === 1) {
                    $score = 100 * $progressRow["completed"] / $totalProgress;
                    $headerTemplate .= 
                    '<div class="courseProgress">
                        <h3 style="color: rgb(97, 97, 97);">'.$score.'% complete</h3>
                        <div class="scoreBar">
                            <div style="width: '.$score.'%" class="greenBar"></div>
                        </div>
                    </div>';
                    if ($score === 100) {
                        $enrolStatus = 3;
                    }
                }

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
                    <a class="profileLink" href="profile-police.php?user_email='.$detailRow["lecturer_user_email"].'&origin=stu-course-detail.php&courseId='.$courseId.'">
                        <div class="lecturerIconContainer">
                            <img class="lecturerIcon" src="'.$icon.'" alt="">
                        </div>
                        <span id="lecturerName">'.$detailRow["lecturer_name"].'</span>
                    </a>
                    
                </div> 
                <div id="courseEnrol">
                    <button class="purpleBtn" id="enrolButton" onclick="accessCourse('.$courseId.')">Get Started</button>
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
                    '<div style="scroll-margin-top: 5rem;" id="objective" class="tabContent">
                        <h2>Objective of This Course</h2><br>
                        '.$detailRow["objective"].'
                    </div>
                    <div style="scroll-margin-top: 5rem;" id="scope" class="tabContent">
                        <h2>What Will You Learn?</h2><br>
                        '.$detailRow["scope"].'
                    </div>
                    <div style="scroll-margin-top: 5rem;" id="description" class="tabContent">
                        <h2>Description</h2><br>'.$detailRow["description"].'
                    </div>';
                    echo $overviewTemplate;
                    ?>
                    <div style="scroll-margin-top: 5rem;" id="review" class="tabContent">
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
                        
                            <form id="reviewForm" action="" method="POST" onsubmit="preventSubmission(event)">
                                <input type="hidden" name="form_type" id="formType" value="create">
                                <div class="reviewCard" id="createReview">
                                    <div id="createReviewHeader"><h3>Create You Review</h3></div>
                                    <h4>Rating</h4>
                                    <div class="ratingContainer">
                                        <input type="radio" name="rating" id="star5" value="5">
                                        <label for="star5">&#9733;</label>
                                        <input type="radio" name="rating" id="star4" value="4">
                                        <label for="star4">&#9733;</label>
                                        <input type="radio" name="rating" id="star3" value="3">
                                        <label for="star3">&#9733;</label>
                                        <input type="radio" name="rating" id="star2" value="2">
                                        <label for="star2">&#9733;</label>
                                        <input type="radio" name="rating" id="star1" value="1">
                                        <label for="star1">&#9733;</label>
                                    </div>
                                    <label for="reviewTitleInput"><h4>Title *</h4></label>
                                    <input oninput="checkReview('title'); updateSaveButton();" type="text" name="reviewTitle" id="reviewTitleInput">
                                    <div style="color: red;" id="reviewTitleError"></div>
                                    <label for="reviewCommentInput"><h4>Comment *</h4></label>
                                    <div>
                                        <textarea oninput="checkReview('comment'); updateSaveButton();" name="reviewComment" id="reviewCommentInput"></textarea>
                                    </div>
                                    <div style="color: red;" id="reviewCommentError"></div>
                                    <div id="createReviewFooter">
                                        <p>*optional fields</p>
                                        <button class="purpleBtn" onclick="document.getElementById('reviewForm').submit();" class="button" id="saveReviewBtn">Save</button>
                                    </div>
                                </div>
                            </form>
                            <?php
                            $reviewCount = 0;
                            $myReviewStatus = "false";
                            $myReview = "";
                            $myReviewArray = [];
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
                                        
                                        if ($row["user_email"] === $userEmail) {
                                            $myReview = $ratingTemplate;
                                            $myReviewArray = $row;
                                            $myReviewStatus = "true";
                                        } else {
                                            if ($reviewCount < 3) echo $ratingTemplate;
                                            $reviewCount++;
                                        }
                                    }
                                    
                                }
                            }
                            ?>
                        </div>
                        <?php
                        if ($reviewCount > 2) {
                            echo 
                            '<div style="margin-top: 2rem; text-align: center;">
                                <button class="purpleBtn" id="moreReviewsButton" onclick="window.location.href = `stu-all-reviews.php?courseId='.$courseId.'&courseName='.$detailRow['title'].'`">See More</button> 
                            </div>'; //only shown 3 reviews, more in next page
                        }
                        ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <dialog class="exitDialog" id="updateMsg">
        <div class="dialogHeader">
            Review updated successfully.
            <br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('updateMsg')">Okay</button>
        </div>
    </dialog>

    <dialog class="exitDialog" id="deleteMsg">
        <div class="dialogHeader">
            Review deleted successfully.
            <br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('deleteMsg')">Okay</button>
        </div>
    </dialog>

    <dialog class="exitDialog" id="failMsg">
        <div class="dialogHeader">
            Changes failed to be saved.<br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('failMsg')">Okay</button>
        </div>
    </dialog>

    <div class="modal" id="reportPostModal" style="display:none;">
        <div class="modal-content">
            <span class="modal-close" id="reportModalCloseBtn">×</span>
            <h2>Report Post</h2>
            <form id="reportPostForm">
                <div class="form-group">
                    <label>Select Report Categories:</label>
                    <div class="category-checkboxes">
                        <label><input type="checkbox" name="reportCategory" value="spam" /> Spam</label>
                        <label><input type="checkbox" name="reportCategory" value="harassment" /> Harassment</label>
                        <label><input type="checkbox" name="reportCategory" value="hate_speech" /> Hate Speech</label>
                        <label><input type="checkbox" name="reportCategory" value="off_topic" /> Off-topic</label>
                        <label><input type="checkbox" name="reportCategory" value="other" /> Other</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="reportReason">Reason (optional):</label>
                    <textarea id="reportReason" name="reportReason" placeholder="Explain why you are reporting this post..."></textarea>
                </div>

                <div class="modal-buttons">
                    <button type="submit" class="btn btn-create">Submit</button>
                    <button type="button" class="btn btn-cancel" id="reportCancelBtn">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let updateStatus = <?php echo $updateStatus ? 'true' : 'false'; ?>;
        let deleteStatus = <?php echo $deleteStatus ? 'true' : 'false'; ?>;
        let failStatus = <?php echo $failStatus ? 'true' : 'false'; ?>;
        
        if (failStatus) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("failMsg").showModal();
        }
        if (updateStatus) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("updateMsg").showModal();
        }
        if (deleteStatus) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("deleteMsg").showModal();
        }

        function exitDialog(dialogId) {
            const dialog = document.getElementById(dialogId);
            dialog.close();
            document.querySelector(".blurOverlay").style.visibility = "hidden";
        }

        let enrolStatus = <?php echo $enrolStatus;?>;
        console.log(enrolStatus);
        //check if enrolled
        if (enrolStatus === 2) {
            const createReview = document.getElementById('createReview');
            createReview.innerHTML = 'Enrol in the course and review the course!';
        } else if (enrolStatus === 0 && enrolStatus === 1) {
            document.getElementById("enrolButton").innerHTML = "Continue";
            document.getElementsByClassName("courseImgContainer")[0].style.gridRow = "2 / 7";
            //don't need progress bar, change css
        } else if (enrolStatus === 3) {
            document.getElementById("enrolButton").innerHTML = "Completed";
            document.getElementsByClassName("courseImgContainer")[0].style.gridRow = "2 / 7";
            document.getElementById("courseEnrol").style.gridRow = "4";
            //don't need progress bar, change css
        } 

        const commentInput = document.getElementById('reviewCommentInput');
        //make sure input is available (user able to create review)
        if (commentInput) {
            commentInput.addEventListener('input', function () {
                this.style.height = 'auto'; // always reset first
                this.style.height = this.scrollHeight + 'px'; // then adjust to scrollHeight
            });
            //initial checking when page loads
            updateSaveButton();

            //listen for changes on the whole form (event delegation)
            const container = document.getElementById("createReview");
            
            container.addEventListener("change", function (e) {
                updateSaveButton();
            });
        }

        let myReviewStatus = "<?php echo $myReviewStatus;?>";
        console.log("myReviewStatus: "+myReviewStatus);
        if (myReviewStatus === "true") {
            let reviewCardList = document.getElementById("reviewCardList");
            let myReview = <?php echo json_encode($myReview);?>;
            reviewCardList.insertAdjacentHTML('afterbegin', myReview);

            reviewCardList.children[0].style.backgroundColor = "rgb(222 199 255)";
            reviewCardList.children[0].classList.add("myReviewCard");
            document.getElementById("createReview").style.display = "none";
        }

        const mediaQuery = window.matchMedia("(max-width: 720px)");

        function handleChange(e) {
            if (enrolStatus === 1) {
                if (e.matches) {
                    document.getElementById("courseHeader").style.gridTemplateColumns = "5fr 2.5fr";
                    document.getElementById("courseEnrol").style.gridRow = "5 / 8";
                    document.getElementsByClassName("courseImgContainer")[0].style.gridRow = "3";
                } else {
                    document.getElementById("courseHeader").style.gridTemplateColumns = "2.5fr 0.8fr 3.2fr";
                    document.getElementById("courseEnrol").style.gridRow = "4 / 7";
                    document.getElementById("enrolButton").innerHTML = "Continue";
                    document.getElementsByClassName("courseImgContainer")[0].style.gridRow = "2 / 7";
                }
            }
        }

        mediaQuery.addEventListener("change", handleChange);
        handleChange(mediaQuery);

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

        //check length validity of review title and comment (optional for user but if entered, must be in valid length)
        //works to enable or disable the save button
        function checkReview(type) { 
            let newType = ucfirst(type), max = 0;
            console.log("value: "+newType);
            const item = document.getElementById("review"+newType+"Input").value.trim();
            switch (type) {
                case "title": max = 100; break;
                case "comment": max = 800; break;
            }
            console.log("value: "+item);
            if (item.length > max) {
                document.getElementById("review"+newType+"Error").innerHTML = newType+" is too long.";
                return true;
            } else if (item.length < 10 && item.length > 0) { //allow empty
                document.getElementById("review"+newType+"Error").innerHTML = newType+" is too short.";
                return true;
            } else { //no error
                document.getElementById("review"+newType+"Error").innerHTML = "";
                return false;
            }
        }

        //checks if rating stars are selected (must be available to submit form)
        //works to enable or disable the save button
        function checkRating() {
            const saveBtn = document.getElementById("saveReviewBtn");

            const selectedRating = document.querySelector('input[name="rating"]:checked');
            return !selectedRating;
        }
        
        function updateSaveButton() {
            const saveBtn = document.getElementById("saveReviewBtn");
            let comStatus = checkReview("comment");
            let tiStatus = checkReview("title");
            let raStatus = checkRating();
            console.log(comStatus);
            console.log(tiStatus);
            //both must be false to enable the button, allowing user to save review
            saveBtn.disabled = raStatus || (comStatus || tiStatus);
        }

        function preventSubmission(event) {
            event.preventDefault();
        }

        //description in header cannot be longer than 250

        function accessCourse(courseId) {
            if (enrolStatus === 0) {
                window.location.href = "stu-start-enrol.php?courseId="+courseId;
            } else if (enrolStatus === 1) {
                window.location.href = "stu-enrol.php?courseId="+courseId; //wenyew
            }
        }

        //more button for my review card
        document.querySelectorAll('.myReviewCard .moreButton').forEach(button => {
            button.addEventListener('click', function (e) {
                e.stopPropagation(); // Prevent click from bubbling up
                document.querySelectorAll('.myReviewCard .moreMenu')[0].innerHTML = 
                `<div style="color: orange; font-weight: bold;" class="menuItem bi bi-pencil" onclick="manageMyReview('edit')">&nbsp;&nbsp;Edit</div>
                <div style="color: red; font-weight: bold;" class="menuItem bi-trash3" onclick="manageMyReview('delete')">&nbsp;&nbsp;Delete</div>`;
                const menu = this.nextElementSibling;
                //close all other menus
                document.querySelectorAll('.moreMenu').forEach(m => {
                    if (m !== menu) m.style.display = 'none';
                });
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            });
        });
        
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

        function manageMyReview(type) {
            const myReviewCard = document.querySelector('.myReviewCard');
            const createReview = document.getElementById("createReview");
            if (type === "edit") {
                myReviewCard.style.transition = "opacity 0.5s ease"; // fade over 0.5s
                myReviewCard.style.opacity = "0";
                setTimeout(() => {
                    myReviewCard.style.display = "none";
                    myReviewCard.style.opacity = "unset";
                    createReview.style.opacity = "0";
                    createReview.style.display = "flex";
                    createReview.style.transition = "opacity 1s ease-in";
                    void createReview.offsetWidth;
                    createReview.style.opacity = "1";

                    let header = document.getElementById("createReviewHeader");
                    header.querySelector("h3").innerHTML = "Edit Your Review";
                    header.innerHTML += `<button id="revertEditBtn" onclick="manageMyReview('revertEdit')">cancel</button>`;

                    let array = <?php echo json_encode($myReviewArray);?>;
                    let rating = array.rating.toString();
                    createReview.querySelector('input[name="rating"][value="'+array.rating+'"]').checked = true;
                    let title = array.review_title !== null ? array.review_title : "";
                    createReview.querySelector('#reviewTitleInput').value = title;
                    let comment = array.review_comment !== null ? array.review_comment : "";
                    let commentInput = createReview.querySelector('#reviewCommentInput');
                    commentInput.value = comment;
                    //trigger the input event handler
                    commentInput.dispatchEvent(new Event('input', { bubbles: true }));
                }, 500);
            } else if (type === "revertEdit") {
                createReview.style.transition = "opacity 0.5s ease";
                createReview.style.opacity = "0";
                setTimeout(() => {
                    createReview.style.display = "none";
                    createReview.style.opacity = "unset";
                    document.getElementById("revertEditBtn").remove();

                    myReviewCard.style.opacity = "0";
                    myReviewCard.style.display = "grid";
                    myReviewCard.style.transition = "opacity 1s ease-in";
                    void myReviewCard.offsetWidth;
                    myReviewCard.style.opacity = "1";
                }, 500);
                
            } else if (type === "delete") {
                document.getElementById("formType").value = "delete";
                document.getElementById("reviewForm").submit();
            }
        }

        function reportReview(enrolId) {

        }

        // Close menu if clicking elsewhere
        document.addEventListener('click', () => {
            document.querySelectorAll('.moreMenu').forEach(m => m.style.display = 'none');
        });

        console.log(enrolStatus);
        if (enrolStatus === 2 || enrolStatus === 3) {
            const enrolBtn = document.getElementById("enrolButton");
            enrolBtn.style.backgroundColor = "rgb(152 152 152)";
            enrolBtn.disabled = true;
            enrolBtn.style.pointerEvents = "none"; // disables hover/click interaction
            enrolBtn.style.boxShadow = "none";
            enrolBtn.style.transform = "none";
        } 
    </script>
</body>
</html>