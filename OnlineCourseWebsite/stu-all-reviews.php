<?php

include "conn.php";

if (isset($_REQUEST["courseId"])) {
    $courseId = (int) $_REQUEST["courseId"];
    $courseName = $_REQUEST["courseName"];
} else {
    $courseId = 1;
    $courseName = "Develop Your First Website with No Coding Experience";
}

$reviewSQL = 
"SELECT * FROM course_enrolment AS ce 
LEFT JOIN 
(SELECT pfp as icon, s.student_id, name, s.user_email FROM student AS s LEFT JOIN user AS u on s.user_email = u.user_email) AS su
ON ce.student_id = su.student_id
WHERE course_id = $courseId
ORDER BY review_date DESC;"; //course reviews by students
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

$avgRatingSQL = 
"SELECT AVG(rating) as avgRating, count(rating) as ratingNum FROM course_enrolment WHERE course_id = $courseId;"; 
//average rating and total number of ratings
$avgRatingExe = mysqli_query($conn, $avgRatingSQL);
$avgRatingRow = mysqli_fetch_assoc($avgRatingExe);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Reviews - <?php echo $courseName;?></title>
    <link rel="stylesheet" href="stu-course-detail.css">
    <style>
        #reviewHeader {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-auto-rows: max-content;
            column-gap: 3rem;
            margin: 1rem 0;
            height: fit-content;
        }

        @media screen and (min-width: 750px) {
            .rating {
                font-size: 23px;
            }
        }

        @media screen and (min-width: 600px) and (max-width: 750px) {
            .rating {
                font-size: 19px;
            }

            #reviewHeader {
                column-gap: 1.5rem;
            }
        }

        @media screen and (max-width: 600px) {
            #reviewHeader {
                column-gap: 0.75rem;
            }
        }
        
    </style>
</head>
<body>
    <div class="whiteSectionBg">
        <div class="contentSection">
            <div style="display: flex; align-items: center;">
                <button class="button" onclick="window.location.href = 'stu-home.php';">Home</button>
                <img style="margin: 0 0.5rem;" class="right" src="system_img/right.png" alt=""> 
                <button class="button" onclick="window.location.href = 'stu-course-detail.php?courseId=<?php echo $courseId?>';">Course Information</button>
            </div>
            
            <div id="reviewHeader">
                <h1 id="courseName"><?php echo $courseName;?></h1>
                <div id="ratingOverview">
                    <div style="font-weight: bold;" class="rating">Average Rating: <?php echo round($avgRatingRow["avgRating"], 2)?>&#x02B50;</div>
                    <div><i>(<?php echo round($avgRatingRow["ratingNum"], 2)?> total ratings)</i></div>
                    <?php 
                    $totalReviews = 0;
                    while ($row = mysqli_fetch_assoc($ratingOnlyExe)) {
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
                    ?>
                </div>
            </div>
            <h2>All Reviews</h2>
            <div style="margin-top: 3rem;" id="reviewCardList">
                <?php
                    while ($row = mysqli_fetch_assoc($reviewExe)) {
                        if ($row["icon"] === null) {
                            $icon = "profile/defaultProfile.jpg";
                        } else {
                            $icon = $row["icon"];
                        }

                        $dateFormatted = date('F j, Y', strtotime($row['review_date']));

                        $ratingTemplate = 
                        '<div class="reviewCard">
                            <a class="studentLink" href="stu-profile.php?user_email='.$row["user_email"].'&origin=stu-course-detail.php&courseId='.$courseId.'">
                                <div class="studentIconContainer">
                                    <img class="lecturerIcon" src="'.$icon.'" alt="">
                                </div>
                                <span class="studentName">'.$row["name"].'</span>
                            </a>
                            <div class="rating">
                                <div style="font-weight: bold; display: flex; align-items: center;">Rating:&nbsp;&nbsp;</div>
                                <div style="font-size=18px; display: flex; align-items: center;">'.$row["rating"].'&#x02B50;</div>
                            </div>
                            <div class="more"><span class="moreButton">&vellip;</span></div>
                            <div class="reviewTitle"><h3>'.$row["review_title"].'<h3></div>
                            <div class="reviewDate"><u>'.$dateFormatted.'</u></div>
                            <div class="comment">'.$row["review_comment"].'</div>
                        </div>';

                        echo $ratingTemplate;
                    }
                ?>
            </div>
        </div>
    </div>
    <script>
        const image = document.querySelector('img .right');
    </script>
</body>
</html>