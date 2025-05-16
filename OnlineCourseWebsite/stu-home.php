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

//student and guest (lecturer pending) can retrieve and view courses
$courseSQL = 
"SELECT cp.*, c.course_id
FROM course AS c
JOIN course_proposal AS cp
ON c.proposal_id = cp.proposal_id
WHERE c.status IN ('Published', 'Removal Pending');"; //courses that are published
$courseExe1 = mysqli_query($conn, $courseSQL);
$courseExe = [];
while ($row = mysqli_fetch_assoc($courseExe1)) {
    $courseExe[] = $row; 
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

if (!$guest) { //info only retrievable if student id visits
    
    $bookmarkSQL = "SELECT course_id FROM saved_course WHERE student_id = $studentId;"; //check bookmark status
    $bookmarkExe = mysqli_query($conn, $bookmarkSQL);
    $bookmarkCourses = [];

    while ($row = mysqli_fetch_assoc($bookmarkExe)) {
        $bookmarkCourses[] = $row["course_id"]; 
    }

    $recentCourseSQL = 
    "SELECT cp.*, c.course_id
    FROM course AS c
    JOIN course_proposal AS cp
    ON c.proposal_id = cp.proposal_id
    WHERE c.status IN ('Published', 'Removal Pending') AND 
    FIND_IN_SET(c.course_id, 
        (SELECT recent_course_list FROM student WHERE student_id = $studentId))
        ORDER BY FIND_IN_SET(c.course_id, (
        SELECT recent_course_list FROM student WHERE student_id = $studentId
        )) DESC;"; 
    //courses that are published for recent courses
    $recentCourseExe = mysqli_query($conn, $recentCourseSQL);

    //retrieve in-progress (enrolled) course
    $enrolledSQL = 
    "SELECT cp.*, c.course_id
    FROM course AS c
    JOIN course_proposal AS cp
    ON c.proposal_id = cp.proposal_id
    RIGHT JOIN course_enrolment AS ce
    ON c.course_id = ce.course_id
    WHERE ce.student_id = $studentId;";
    $enrolledExe = mysqli_query($conn, $enrolledSQL);

    //student's field preference
    $fieldSQL = 
    "SELECT fp.field_id, experience, name 
    FROM field_preference as fp
    LEFT JOIN field as f
    ON fp.field_id = f.field_id
    WHERE student_id = $studentId;";
    $fieldExe = mysqli_query($conn, $fieldSQL);

    $fields = []; //reusable array
    while ($row = mysqli_fetch_assoc($fieldExe)) {
        $fields[] = $row;
    }

    //student's career of preference
    $careerSQL = 
    "SELECT cp.career_id, name, experience 
    FROM career_preference as cp
    LEFT JOIN career as c
    ON cp.career_id = c.career_id
    WHERE student_id = $studentId;";
    $careerExe = mysqli_query($conn, $careerSQL);

    $careers = []; //reusable array
    while ($row = mysqli_fetch_assoc($careerExe)) {
        $careers[] = $row;
    }

    $careerIds = array_column($careers, 'career_id'); //extract career_id only
    $careerIdList = implode(',', $careerIds);

    if (count($careerIds) !== 0) {
        //retrieve fields of career used for display
        $careerFieldSQL =
        "SELECT career_id, field_id 
        FROM career_field_relation
        WHERE career_id IN ($careerIdList);";
        $careerFieldExe = mysqli_query($conn, $careerFieldSQL);

        $careerFields = []; //reusable array
        while ($row = mysqli_fetch_assoc($careerFieldExe)) {
            $careerFields[] = $row;
        }
    } else {
        $careerFields = [];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Learning</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <link rel="stylesheet" href="stu-home.css">
</head>
<body>
    <header>
        <?php include "header.php";?>
    </header>
    <div id="lecturerPendingMsg">
        <div id="lecPendContainer">
            &#9888;     
            <div id="lecPendMsg">
            Your lecturer application is still pending for approval. At the meantime, you can explore courses that are available in the website.
            </div>
        </div>
    </div>

    <div class="whiteSectionBg">
        <div class="contentSection">
            <form style="display: flex;" id="" action="stu-search-results.php" method="GET" onsubmit="return validateSearch()">
                <input style="width: 20rem; margin-right: 1rem;" type="text" name="search" id="courseSearch" placeholder="Search for a course">
                <button type="submit" class="search"><img src="system_img/search.png"></button>
            </form>
        </div>
    </div>

    <div class="whiteSectionBg">
        <div class="contentSection">
            <h1 id="hello">In Progress</h1>
            <div class="courseNavContainer">
                <button class="navBtn progressLeftBtn" onclick="scrollProgressCourses(-1)"><img src="system_img/leftArrow.png" alt=""></button>
                <div class="courseViewWrapper">
                    <div id="progressCourseSlider">
                        <?php
                        $enrolCount = 0;
                        $progressCourseArray = []; 
                        if (!$guest) {
                            while ($row = mysqli_fetch_assoc($enrolledExe)) {
                                $courseId = $row['course_id'];
                                include "checkProgress.php";
                                
                                $text = in_array($courseId, $bookmarkCourses) ? "&check;" : "+";
                                if ($text === "&check;") {
                                    $height = "3rem";
                                } else {
                                    $height = "2rem";
                                }
    
                                if ($totalProgress !== $progressRow["completed"]) {
                                    $progressCourseTemplate = 
                                    '<div class="courseCard '.strtolower($row['difficulty']).'Border '.strtolower($row['difficulty']).'Bg">
                                        <div class="coursePins">
                                            <div class="difficulty '.strtolower($row['difficulty']).'Bg">'.ucfirst($row['difficulty']).'</div>
                                        </div>
                                        <div class="courseMain inProgress" id="courseMain'.$row['course_id'].'" onclick="clickCourse(`'.$courseId.'`)">
                                            <div style="border-radius: 0.5rem 0 0 0.5rem;" class="courseImgContainer">
                                                <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$row['cover_img_url'].'" alt="">
                                                <div style="height:'.$height.'" title="Bookmark" class="inProgressBookmark" onclick="bookmarkCourse(event, `tick'.$courseId.'`)"><p class="tick tick'.$courseId.'">'.$text.'</p></div>
                                            </div>
                                            <div style="border-radius: 0 0.5rem 0.5rem 0;" class="courseMetadata">
                                                <div class="courseProgress">
                                                    <h4 style="color: rgb(97, 97, 97);">'.(100 * $progressRow["completed"] / $totalProgress).'% complete</h4>
                                                    <div class="scoreBar">
                                                        <div style="width: '.(100 * $progressRow["completed"] / $totalProgress).'%" class="greenBar"></div>
                                                    </div>
                                                </div>
                                                <div class="courseTitle">'.$row['title'].'</div>
                                                <div class="courseStyle">'.$row['course_style'].' Learning</div>
                                                <div class="courseTime">'.$row['completion_time'].' hours</div>
                                                <div class="courseRating">'.$avgRatings[$courseId].'&#x02B50;</div>
                                            </div>
                                        </div>
                                    </div>';
                                    $enrolCount++;
                                    $progressCourseArray[] = $progressCourseTemplate;
                                }
                            }
                            $conn->close();
    
                            $chunks = array_chunk($progressCourseArray, 4); // Split into chunks of 4
    
                            foreach ($chunks as $group) {
                                echo '<div class="inProgressCourse">'.implode($group).'</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <button class="navBtn progressRightBtn" onclick="scrollProgressCourses(1)"><img src="system_img/rightArrow.png" alt=""></button>
            </div>
            <?php
            if ($enrolCount === 0) {
                echo "<div style='margin-top: 3rem; margin-bottom: 2rem;'>You haven't enrolled in any course yet.</div>";
            }
            ?>
        </div>
    </div>

    <div id="recentSection" class="whiteSectionBg">
        <div class="contentSection">
            <h1 id="hello">Recently Viewed</h1>
            <div class="courseNavContainer">
                <button class="navBtn recentLeftBtn" onclick="scrollRecentCourses(-1)"><img src="system_img/leftArrow.png" alt=""></button>
                <div class="courseViewWrapper">
                    <div class="recentCourse" id="recentCourseSlider">
                        <?php 
                        $recentCount = 0;
                        if (!$guest) {
                            while ($row = mysqli_fetch_assoc($recentCourseExe)) {
                                $courseId = $row["course_id"];
                                $text = in_array($courseId, $bookmarkCourses) ? "&check;" : "+";
                                if ($text === "&check;") {
                                    $height = "3rem";
                                } else {
                                    $height = "2rem";
                                }
                                $recentCourse = 
                                '<div class="courseCard '.strtolower($row['difficulty']).'Border '.strtolower($row['difficulty']).'Bg">
                                    <div class="coursePins">
                                        <div class="difficulty '.strtolower($row['difficulty']).'Bg">'.ucfirst($row['difficulty']).'</div>
                                    </div>
                                    <div class="courseMain" id="courseMain'.$row['course_id'].'" onclick="clickCourse(`'.$row['course_id'].'`)">
                                        <div class="courseImgContainer">
                                            <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$row['cover_img_url'].'" alt="">
                                            <div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, `tick'.$row['course_id'].'`)"><p class="tick tick'.$row['course_id'].'">'.$text.'</p></div>
                                        </div>
                                        <div class="courseMetadata">
                                            <p class="courseTitle">
                                                '.$row['title'].' 
                                            </p>
                                            <p class="courseStyle">'.$row['course_style'].' Learning</p>
                                            <p class="courseTime">'.$row['completion_time'].' hours</p>
                                            <p class="courseRating">'.$avgRatings[$row['course_id']].'&#x02B50;</p>
                                        </div>
                                    </div>
                                </div>';
                                $recentCount++;
                                echo $recentCourse;
                            }
                        }
                        ?>
                    </div>
                </div>
                <button class="navBtn recentRightBtn" onclick="scrollRecentCourses(1)"><img src="system_img/rightArrow.png" alt=""></button>
            </div>
        </div>
    </div>

    <div class="whiteSectionBg">
        <div class="contentSection">
            <h1>Courses For You</h1>
            <button onclick="setActive(this, 'field')" class="preferTypeToggle">My Fields</button>
            <button onclick="setActive(this, 'career')" class="preferTypeToggle">My Careers</button>
            
            <!--courses based on field preferences-->
            <div class="courseForYouContainer" id="fieldContainer">
                <div class="tab"> <!--list of tabs-->
                    <button class="tablinks" id="fieldAllTab" onclick="openTab(event, 'fieldAll')">All</button>
                    <?php
                    //field preferences of the user, made into tabs
                    //show courses based on the field
                    if (!$guest) {
                        foreach ($fields as $field) {
                            $dashedName = str_replace(' ', '-', $field['name']);
                            echo $tabTemplate = 
                            '<button class="tablinks" id="'.$dashedName.'Tab" onclick="openTab(event, `'.$dashedName.'`)">'.$field['name'].'</button>';
                        }
                    }
                    ?>
                </div>

                <div id="fieldAll" class="tabContent"> <!--first tab-->
                    <div class="courseList tabCourse"> 
                        <?php
                        $count = 0; 
                        $allSuggestedCourses = [];
                        if (empty($fields) || $guest) {
                            foreach ($courseExe as $course) {
                                $suggestedCourse = 
                                '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                                    <div class="coursePins">
                                        <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                                    </div>
                                    <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(\''.$course['course_id'].'\')">
                                        <div class="courseImgContainer">
                                            <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">';
                                
                                if (!$guest) {
                                    $text = in_array($course['course_id'], $bookmarkCourses) ? "&check;" : "+";
                                    if ($text === "&check;") {
                                        $height = "3rem";
                                    } else {
                                        $height = "2rem";
                                    }
                                    $suggestedCourse .=
                                            '<div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, \'tick'.$course['course_id'].'\')"><p class="tick tick'.$course['course_id'].'">'.$text.'</p></div>';
                                }
                                
                                $suggestedCourse .= 
                                        '</div>
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
                                $count++;
                                $allSuggestedCourses [] = $suggestedCourse;
                            }

                            foreach ($allSuggestedCourses as $course) {
                                echo $course;
                            }
                        } else {
                            $highPriorityArray = [];
                            $midPriorityArray = [];
                            $lowPriorityArray = [];

                            $orderMap = [
                                "beginner" => ["beginner", "intermediate", "advanced"],
                                "intermediate" => ["intermediate", "advanced", "beginner"],
                                "advanced" => ["advanced", "intermediate", "beginner"]
                            ];

                            foreach ($fields as $field) {
                                $jsonData = file_get_contents("http://localhost/CapstoneAssignment/course.php?fieldId=".$field["field_id"]);
                                if ($jsonData !== false) {
                                    $data = json_decode($jsonData, true);
                                } else {
                                    $data = [];
                                }

                                $priorityOrder = $orderMap[$field["experience"]] ?? ["beginner", "intermediate", "advanced"]; 
                                //fallback in case of invalid experience (null), replacing isset() checking

                                foreach ($data as $course) {
                                    switch (strtolower($course["difficulty"])) {
                                        case $priorityOrder[0]:
                                            $highPriorityArray[] = $course;
                                            break;
                                        case $priorityOrder[1]:
                                            $midPriorityArray[] = $course;
                                            break;
                                        case $priorityOrder[2]:
                                            $lowPriorityArray[] = $course;
                                            break;
                                    }
                                }
                            }
                            
                            //to remove duplicate courses within arrays
                            function uniqueByCourseId($array) {
                                $seen = [];
                                return array_filter($array, function ($course) use (&$seen) {
                                    if (in_array($course['course_id'], $seen)) {
                                        return false;
                                    }
                                    $seen[] = $course['course_id'];
                                    return true;
                                });
                            }
                            
                            $highPriorityArray = uniqueByCourseId($highPriorityArray);
                            $midPriorityArray = uniqueByCourseId($midPriorityArray);
                            $lowPriorityArray = uniqueByCourseId($lowPriorityArray);

                            

                            //array to store course_ids that already exists
                            //to check for duplicates
                            $seenCourseIds = [];

                            //filter high priority (keep all and mark as seen by adding into array)
                            $highPriorityArray = array_filter($highPriorityArray, function ($course) use (&$seenCourseIds) {
                                if (in_array($course['course_id'], $seenCourseIds)) {
                                    return false; // Already exists
                                }
                                $seenCourseIds[] = $course['course_id'];
                                return true;
                            });

                            //check mid priority array for duplicate course
                            $midPriorityArray = array_filter($midPriorityArray, function ($course) use (&$seenCourseIds) {
                                if (in_array($course['course_id'], $seenCourseIds)) {
                                    return false;
                                }
                                $seenCourseIds[] = $course['course_id'];
                                return true;
                            });

                            //check low priority array for duplicate course
                            $lowPriorityArray = array_filter($lowPriorityArray, function ($course) use (&$seenCourseIds) {
                                if (in_array($course['course_id'], $seenCourseIds)) {
                                    return false;
                                }
                                $seenCourseIds[] = $course['course_id'];
                                return true;
                            });

                            //randomize display
                            shuffle($highPriorityArray);
                            shuffle($midPriorityArray);
                            shuffle($lowPriorityArray);

                            foreach ([$highPriorityArray, $midPriorityArray, $lowPriorityArray] as $courseArray) {
                                foreach ($courseArray as $course) {
                                    $text = in_array($courseId, $bookmarkCourses) ? "&check;" : "+";
                                    if ($text === "&check;") {
                                        $height = "3rem";
                                    } else {
                                        $height = "2rem";
                                    }
                                    $suggestedCourse = 
                                    '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                                        <div class="coursePins">
                                            <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                                        </div>
                                        <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(`'.$course['course_id'].'`)">
                                            <div class="courseImgContainer">
                                                <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                                <div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, `tick'.$course['course_id'].'`)"><p class="tick tick'.$course['course_id'].'">'.$text.'</p></div>
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
                                    $count++;
                                    echo $suggestedCourse;
                                }
                            }
                        }
                        ?>
                    </div>
                    <?php
                    if ($count === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>

                <?php
                
                if (!$guest) {
                    //main page displaying courses of each field
                    foreach ($fields as $field) {
                        $jsonData = file_get_contents("http://localhost/CapstoneAssignment/course.php?fieldId=".$field['field_id']);
                        if ($jsonData !== false) {
                            $data = json_decode($jsonData, true);
                        } else {
                            $data = [];
                        }

                        $difficultyOrder = [];

                        //different priorities based on user's experience level in that particular field
                        if ($field["experience"] === "intermediate") {
                            $difficultyOrder = ['intermediate' => 0, 'advanced' => 1, 'beginner' => 2];
                        } else if ($field["experience"] === "advanced") {
                            $difficultyOrder = ['intermediate' => 1, 'advanced' => 0, 'beginner' => 2];
                        } else if ($field["experience"] === "beginner") {
                            $difficultyOrder = ['intermediate' => 1, 'advanced' => 2, 'beginner' => 0];
                        }

                        //reorder array according to specified difficulty order
                        usort($data, function ($a, $b) use ($difficultyOrder) {
                            $diffA = $difficultyOrder[strtolower($a['difficulty'])] ?? PHP_INT_MAX;
                            $diffB = $difficultyOrder[strtolower($b['difficulty'])] ?? PHP_INT_MAX;
                            return $diffA <=> $diffB;
                        });
                        
                        $courseTemplateArray = [];

                        if (empty($data)) {
                            $courseTemplateArray[] = "<div>No courses to display for this field yet.</div>";
                        } else {
                            foreach ($data as $course) {
                                $text = in_array($course['course_id'], $bookmarkCourses) ? "&check;" : "+";
                                if ($text === "&check;") {
                                    $height = "3rem";
                                } else {
                                    $height = "2rem";
                                }
                                $courseTemplate = 
                                '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                                    <div class="coursePins">
                                        <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                                    </div>
                                    <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(`'.$course['course_id'].'`)">
                                        <div class="courseImgContainer">
                                            <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                            <div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, `tick'.$course['course_id'].'`)"><p class="tick tick'.$course['course_id'].'">'.$text.'</p></div>
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
                                //push everything in an array to implode in another echo statement
                                $courseTemplateArray[] = $courseTemplate;
                            }
                        }

                        $dashedName = str_replace(' ', '-', $field['name']);
                        
                        echo
                        '<div id="'.$dashedName.'" class="tabContent">
                            <div class="courseList tabCourse">'.implode('', $courseTemplateArray).'</div>
                        </div>';
                    }
                }
                ?>
            </div>
            
            <!--courses based on career preferences-->
            <div class="courseForYouContainer" id="careerContainer">
                <div class="tab"> <!--list of tabs-->
                    <button class="tablinks" id="careerAllTab" onclick="openTab(event, 'careerAll')">All</button>
                    <?php
                    //field preferences of the user, made into tabs
                    //show courses based on the field
                    if (!$guest) {
                        foreach ($careers as $career) {
                            $dashedName = str_replace(' ', '-', $career['name']);
                            echo $tabTemplate = 
                            '<button class="tablinks" id="'.$dashedName.'Tab" onclick="openTab(event, `'.$dashedName.'`)">'.$career['name'].'</button>';
                        }
                    }
                    ?>
                </div>

                <div id="careerAll" class="tabContent"> <!--first tab-->
                    <div class="courseList tabCourse"> 
                        <?php 
                        $count = 0; 
                        if (empty($careerFields) || $guest) {
                            foreach ($allSuggestedCourses as $course) {
                                echo $course;
                            }
                        } else {
                            $highPriorityCareerArray = [];
                            $midPriorityCareerArray = [];
                            $lowPriorityCareerArray = [];

                            //can remove
                            $orderMap = [
                                "beginner" => ["beginner", "intermediate", "advanced"],
                                "intermediate" => ["intermediate", "advanced", "beginner"],
                                "advanced" => ["advanced", "intermediate", "beginner"]
                            ];

                            foreach ($careers as $career) {
                                foreach ($careerFields as $careerField) {
                                    if ($careerField["career_id"] === $career["career_id"]) {
                                        $jsonData = file_get_contents("http://localhost/CapstoneAssignment/course.php?fieldId=".$careerField["field_id"]);
                                        if ($jsonData !== false) {
                                            $data = json_decode($jsonData, true);
                                        } else {
                                            $data = [];
                                        }
            
                                        $priorityOrder = $orderMap[$career["experience"]] ?? ["beginner", "intermediate", "advanced"]; 
                                        //fallback in case of invalid experience (null), replacing isset() checking
            
                                        foreach ($data as $course) {
                                            switch (strtolower($course['difficulty'])) {
                                                case $priorityOrder[0]:
                                                    $highPriorityCareerArray[] = $course;
                                                    break;
                                                case $priorityOrder[1]:
                                                    $midPriorityCareerArray[] = $course;
                                                    break;
                                                case $priorityOrder[2]:
                                                    $lowPriorityCareerArray[] = $course;
                                                    break;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            //to remove duplicate courses within arrays
                            
                            $highPriorityCareerArray = uniqueByCourseId($highPriorityCareerArray);
                            $midPriorityCareerArray = uniqueByCourseId($midPriorityCareerArray);
                            $lowPriorityCareerArray = uniqueByCourseId($lowPriorityCareerArray);
                            
                            //array to store course_ids that already exists
                            //to check for duplicates
                            $seenCourseIds = [];

                            //filter high priority (keep all and mark as seen by adding into array)
                            $highPriorityCareerArray = array_filter($highPriorityCareerArray, function ($course) use (&$seenCourseIds) {
                                if (in_array($course['course_id'], $seenCourseIds)) {
                                    return false; // Already exists
                                }
                                $seenCourseIds[] = $course['course_id'];
                                return true;
                            });

                            //check mid priority array for duplicate course
                            $midPriorityCareerArray = array_filter($midPriorityCareerArray, function ($course) use (&$seenCourseIds) {
                                if (in_array($course['course_id'], $seenCourseIds)) {
                                    return false;
                                }
                                $seenCourseIds[] = $course['course_id'];
                                return true;
                            });

                            //check low priority array for duplicate course
                            $lowPriorityCareerArray = array_filter($lowPriorityCareerArray, function ($course) use (&$seenCourseIds) {
                                if (in_array($course['course_id'], $seenCourseIds)) {
                                    return false;
                                }
                                $seenCourseIds[] = $course['course_id'];
                                return true;
                            });

                            //randomize display
                            shuffle($highPriorityCareerArray);
                            shuffle($midPriorityCareerArray);
                            shuffle($lowPriorityCareerArray);

                            foreach ([$highPriorityCareerArray, $midPriorityCareerArray, $lowPriorityCareerArray] as $courseArray) {
                                foreach ($courseArray as $course) {
                                    $text = in_array($course['course_id'], $bookmarkCourses) ? "&check;" : "+";
                                    if ($text === "&check;") {
                                        $height = "3rem";
                                    } else {
                                        $height = "2rem";
                                    }
                                    $suggestedCourse = 
                                    '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                                        <div class="coursePins">
                                            <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                                        </div>
                                        <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(`'.$course['course_id'].'`)">
                                            <div class="courseImgContainer">
                                                <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                                <div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, `tick'.$course['course_id'].'`)"><p class="tick tick'.$course['course_id'].'">'.$text.'</p></div>
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
                                    $count++;
                                    echo $suggestedCourse;
                                }
                            }
                        }
                        ?>
                    </div>
                    <?php
                    if ($count === 0) {
                        echo "<div style='margin-bottom: 2rem;'>No courses to display yet.</div>";
                    }
                    ?>
                </div>

                <?php
                
                if (!$guest) {
                    //pages displaying courses of each career
                    foreach ($careers as $career) {
                        //retrieve all courses based on the career's related fields
                        $careerData = [];
                        foreach ($careerFields as $careerField) {
                            if ($career["career_id"] === $careerField["career_id"]) {
                                $jsonData = file_get_contents("http://localhost/CapstoneAssignment/course.php?fieldId=".$careerField["field_id"]);
                                if ($jsonData !== false) {
                                    $data = json_decode($jsonData, true);
                                    if (is_array($data)) {
                                        $careerData = array_merge($careerData, $data);
                                    }
                                }
                            }
                        }

                        //courses done retrieved and stored in $data
                        //make sure no repeating courses
                        $careerData = uniqueByCourseId($careerData);

                        $difficultyOrder = [];

                        //different priorities based on user's experience level in that particular career
                        if ($career["experience"] === "intermediate") {
                            $difficultyOrder = ['intermediate' => 0, 'advanced' => 1, 'beginner' => 2];
                        } else if ($career["experience"] === "advanced") {
                            $difficultyOrder = ['intermediate' => 1, 'advanced' => 0, 'beginner' => 2];
                        } else if ($career["experience"] === "beginner") {
                            $difficultyOrder = ['intermediate' => 1, 'advanced' => 2, 'beginner' => 0];
                        }

                        //reorder array according to specified difficulty order
                        usort($careerData, function ($a, $b) use ($difficultyOrder) {
                            $diffA = $difficultyOrder[strtolower($a['difficulty'])] ?? PHP_INT_MAX;
                            $diffB = $difficultyOrder[strtolower($b['difficulty'])] ?? PHP_INT_MAX;
                            return $diffA <=> $diffB;
                        });
                        
                        $courseTemplateArray = [];

                        if (empty($careerData)) {
                            $courseTemplateArray[] = "<div>No courses to display for this career yet.</div>";
                        } else {
                            foreach ($careerData as $course) {
                                $text = in_array($courseId, $bookmarkCourses) ? "&check;" : "+";
                                if ($text === "&check;") {
                                    $height = "3rem";
                                } else {
                                    $height = "2rem";
                                }

                                $courseTemplate = 
                                '<div class="courseCard '.strtolower($course['difficulty']).'Border '.strtolower($course['difficulty']).'Bg">
                                    <div class="coursePins">
                                        <div class="difficulty '.strtolower($course['difficulty']).'Bg">'.ucfirst($course['difficulty']).'</div>
                                    </div>
                                    <div class="courseMain" id="courseMain'.$course['course_id'].'" onclick="clickCourse(`'.$course['course_id'].'`)">
                                        <div class="courseImgContainer">
                                            <img onerror="this.onerror=null; this.src=\'img/defaultCourse.jpg\';" class="courseImg" src="'.$course['cover_img_url'].'" alt="">
                                            <div style="height:'.$height.';" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, `tick'.$course['course_id'].'`)"><p class="tick tick'.$course['course_id'].'">'.$text.'</p></div>
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
                                //push everything in an array to implode in another echo statement
                                $courseTemplateArray[] = $courseTemplate;
                            }
                        }

                        $dashedName = str_replace(' ', '-', $career['name']);
                        echo
                        '<div id="'.$dashedName.'" class="tabContent">
                            <div class="courseList tabCourse">'.implode('', $courseTemplateArray).'</div>
                        </div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
    <script>
        function openTab(evt, subjectName) {
            let tabcontent = document.getElementsByClassName("tabContent");
            for (let i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            let tablinks = document.getElementsByClassName("tablinks");
            for (let i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }

            //show the current tab, add an "active" class to the button that opened the tab
            document.getElementById(subjectName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        let guest = <?php echo $guest ? 'true' : 'false'; ?>;

        if (guest) {
            document.getElementById("lecturerPendingMsg").style.display = "flex";
        }
        
        let recentCount = <?php echo $recentCount;?>;
        if (recentCount === 0) {
            document.getElementById("recentSection").remove();
        }
        document.getElementsByClassName("preferTypeToggle")[0].click();
        document.getElementById("fieldAllTab").click();

        function setActive(button, type) {
            document.querySelectorAll(".preferTypeToggle").forEach(btn => {
                btn.classList.remove("preferActive");
            });
            button.classList.add("preferActive");

            document.querySelectorAll(".courseForYouContainer").forEach(container => {
                container.style.display = "none";
            });
            document.getElementById(type+"Container").style.display = "block";
            document.getElementById(type+"AllTab").click();
        }

        let recentCourseSlider = "";
        let recentCourseCards = "";
        let recentTotalCards = "";
        let recentCardGap = 32;
        let recentCardsShown = 3; //3 courses is visible at a time
        let recentCurrentSlide = 0

        if (recentCount !== 0) {
            recentCourseSlider = document.getElementById("recentCourseSlider");
            recentCourseCards = recentCourseSlider.querySelectorAll(".courseCard");
            recentTotalCards = recentCourseCards.length;
            
            let recentMaxSlide = recentTotalCards - recentCardsShown;
        }

        let progressCurrentSlide = 0;

        const progressCourseSlider = document.getElementById("progressCourseSlider");
        const progressCourseSlides = progressCourseSlider.querySelectorAll(".inProgressCourse");
        const progressTotalSlides = progressCourseSlides.length;

        let progressCardsShown = 1; //1 inProgressCourse (4 courses) is visible at a time
        let progressMaxSlide = progressTotalSlides - progressCardsShown;

        const mediaQuery = window.matchMedia("(max-width: 700px)");

        function handleChange(e) {
            if (recentCount !== 0) {
                if (e.matches) {
                    recentCardsShown = 2;
                    recentMaxSlide = recentTotalCards - recentCardsShown;
                    recentCardGap = 16;
                } else {
                    recentCardsShown = 3;
                    recentMaxSlide = recentTotalCards - recentCardsShown;
                    recentCardGap = 32;
                }
            }
        }

        mediaQuery.addEventListener("change", handleChange);
        handleChange(mediaQuery);

        //recent course display
        function scrollRecentCourses(direction) {
            const cardsPerView = 1; //move 1 course at a time
            const cardWidth = recentCourseCards[0].offsetWidth + recentCardGap;

            recentCurrentSlide += direction;
            recentCurrentSlide = Math.max(0, Math.min(recentCurrentSlide, recentMaxSlide));

            const offset = recentCurrentSlide * cardsPerView * cardWidth;
            recentCourseSlider.style.transform = `translateX(-${offset}px)`;

            leftRightVisbilityControl("recent");
        }
        
        //in-progress course display
        function scrollProgressCourses(direction) {
            const element = document.getElementById("progressCourseSlider");
            const slideGap = parseFloat(getComputedStyle(element).gap);

            const slideWidth = progressCourseSlides[0].offsetWidth + slideGap;

            progressCurrentSlide += direction;
            progressCurrentSlide = Math.max(0, Math.min(progressCurrentSlide, progressMaxSlide));

            const offset = progressCurrentSlide * slideWidth;
            progressCourseSlider.style.transform = `translateX(-${offset}px)`;

            leftRightVisbilityControl("progress");
        }

        function leftRightVisbilityControl(type) {
            let left = document.getElementsByClassName(type + "LeftBtn")[0];
            let right = document.getElementsByClassName(type + "RightBtn")[0];

            if (type === "recent" && recentTotalCards > 3) {
                if (recentCurrentSlide === 0) {
                    left.style.visibility = "hidden";
                } else {
                    left.style.visibility = "visible";
                }

                if (recentCurrentSlide === recentMaxSlide) {
                    right.style.visibility = "hidden";
                } else {
                    right.style.visibility = "visible";
                }
            } else if (type === "progress" && progressTotalSlides > 1) {
                if (progressCurrentSlide === 0) {
                    left.style.visibility = "hidden";
                } else {
                    left.style.visibility = "visible";
                }

                if (progressCurrentSlide === progressMaxSlide) {
                    right.style.visibility = "hidden";
                } else {
                    right.style.visibility = "visible";
                }
            } else {
                left.style.visibility = "hidden";
                right.style.visibility = "hidden";
            }
        }
        if (recentCount !== 0) {
            leftRightVisbilityControl("recent");
        }
        leftRightVisbilityControl("progress");

    

        document.querySelectorAll('.courseMain').forEach(main => {
            main.addEventListener('mouseenter', () => {
                main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
            });
            main.addEventListener('mouseleave', () => {
                main.closest('.courseCard').style.boxShadow = 'none';
            });
        });
        
        if (!guest) {
            function bookmarkCourse(evt, tickCourseId) { //save course
                let check = evt.currentTarget.children[0].innerHTML;
                console.log(check);
                // check = "tick hidden" / "tick visible"
                const request = new XMLHttpRequest();
                request.onload = function() {
                    const obj = JSON.parse(this.responseText);
                    console.log(`${obj.student}  ${obj.course}  ${obj.status}`);
                }

                let text = "\u2713";
                let courseId = tickCourseId.replace("tick", "");

                //find all .bookmark elements
                let allBookmarks = document.querySelectorAll(".bookmark");
                let allProgressBookmarks = document.querySelectorAll(".inProgressBookmark");

                //loop through them
                allBookmarks.forEach(bookmark => {
                    //check if the bookmark contains a child <p> with the same tickCourseId class
                    let tickElement = bookmark.querySelector("." + tickCourseId);
                    
                    if (tickElement) {
                        if (check === "+") { // bookmark
                            console.log("visible");
                            tickElement.innerHTML = text;
                            bookmark.style.height = "3rem";
                        } else { // don't bookmark
                            console.log("hidden");
                            tickElement.innerHTML = "+";
                            bookmark.style.height = "2rem";
                        }
                    }
                });

                allProgressBookmarks.forEach(bookmark => {
                    //check if the bookmark contains a child <p> with the same tickCourseId class
                    let tickElement = bookmark.querySelector("." + tickCourseId);
                    
                    if (tickElement) {
                        if (check === "+") { // bookmark
                            console.log("visible");
                            tickElement.innerHTML = text;
                            bookmark.style.height = "3rem";
                        } else { // don't bookmark
                            console.log("hidden");
                            tickElement.innerHTML = "+";
                            bookmark.style.height = "2rem";
                        }
                    }
                });

                //request open
                request.open("POST", "bookmark.php?course=" + courseId + "&student=" + <?php echo $studentId;?>);
                request.send();
            }

            
            const bookmarks = document.querySelectorAll('.bookmark');
            const inProgressBookmarks = document.querySelectorAll('.inProgressBookmark');
            //used for courseCardBookmark-css.js

            bookmarks.forEach(bookmark => {
                // Initial styles (matches your .bookmark CSS)
                bookmark.style.backgroundColor = "rgb(41, 41, 255)";
                bookmark.style.cursor = "pointer";
                bookmark.style.transition = "all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55)";
                bookmark.style.borderRadius = "0px 0px 4px 4px";
                bookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";

                // Set a custom property to track "ticked" status
                bookmark.dataset.ticked = "false";

                // On hover — simulate :hover
                bookmark.addEventListener('mouseenter', () => {
                    bookmark.style.backgroundColor = "rgb(71, 71, 255)";
                    bookmark.style.transform = "scale(1.1)";
                    bookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
                });

                // On mouse leave — undo hover effect
                bookmark.addEventListener('mouseleave', () => {
                    bookmark.style.backgroundColor = "rgb(41, 41, 255)";
                    bookmark.style.transform = "scale(1)";
                    bookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";
                });

                // On click (simulate :active)
                bookmark.addEventListener('mousedown', () => {
                    bookmark.style.transform = "scale(0.95)";
                    bookmark.style.boxShadow = "0 1px 4px rgba(0, 0, 0, 0.2)";
                });
                

                // On mouse up (return to hover style)
                bookmark.addEventListener('mouseup', () => {
                    bookmark.style.transform = "scale(1.1)";
                    bookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
                
                });
            });


            inProgressBookmarks.forEach(inProgressBookmark => {
                // Initial styles (matches your .inProgressBookmark CSS)
                inProgressBookmark.style.backgroundColor = "rgb(41, 41, 255)";
                inProgressBookmark.style.cursor = "pointer";
                inProgressBookmark.style.transition = "all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55)";
                inProgressBookmark.style.borderRadius = "0px 0px 4px 4px";
                inProgressBookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";

                // On hover — simulate :hover
                inProgressBookmark.addEventListener('mouseenter', () => {
                    inProgressBookmark.style.backgroundColor = "rgb(71, 71, 255)";
                    inProgressBookmark.style.transform = "scale(1.1)";
                    inProgressBookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
                });

                // On mouse leave — undo hover effect
                inProgressBookmark.addEventListener('mouseleave', () => {
                    inProgressBookmark.style.backgroundColor = "rgb(41, 41, 255)";
                    inProgressBookmark.style.transform = "scale(1)";
                    inProgressBookmark.style.boxShadow = "0 2px 6px rgba(0, 0, 0, 0.2)";
                });

                // On click (simulate :active)
                inProgressBookmark.addEventListener('mousedown', () => { 
                    inProgressBookmark.style.transform = "scale(0.95)";
                    inProgressBookmark.style.boxShadow = "0 1px 4px rgba(0, 0, 0, 0.2)";

                });

                // On mouse up (return to hover style)
                inProgressBookmark.addEventListener('mouseup', () => {
                    inProgressBookmark.style.transform = "scale(1.1)";
                    inProgressBookmark.style.boxShadow = "0 4px 10px rgba(0, 0, 0, 0.3)";
                });
            });
        } 

        function clickCourse(courseId) {
            console.log(`Click ${courseId}`);
            if (!guest) {
                const request = new XMLHttpRequest();
                request.open("POST", "save-recent-course.php?course=" + courseId + "&student=" + <?php echo $studentId;?>);
                request.send();
            }
            window.location.href = "stu-course-detail.php?courseId=" + courseId; //go page
        }

        function validateSearch() {
            const searchTerm = document.getElementById('courseSearch').value.trim();
            if (searchTerm.length < 2) {
                alert("Please enter at least 2 characters.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>