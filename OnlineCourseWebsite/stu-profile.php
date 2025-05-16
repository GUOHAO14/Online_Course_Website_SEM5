<?php
// use SESSION variable to identify if the user is viewing his/her own profile
// toggle display of the edit buttons
session_start();
//check if vistor is logged in
if (!isset($_SESSION["user_email"])) {
    header("Location: index.php");
    session_write_close();
    exit();
} else {
    $userEmail = $_SESSION["user_email"];
}
//from form submission for profile update
//used for dialog display
if (isset($_SESSION["profileUpdateStatus"]) && $_SESSION["profileUpdateStatus"] === true) {
    $updateStatus = true;
    $_SESSION["profileUpdateStatus"] = false;
} else {
    $updateStatus = false;
}
$backLocation = "";
//url to visit other's profile
if (isset($_REQUEST["user_email"]) && !isset($_REQUEST["type"])) {
    $userEmail = $_REQUEST["user_email"];
    $origin = $_REQUEST["origin"];
    $courseId = $_REQUEST["courseId"];
    $backLocation = $origin.'?courseId='.$courseId;
} else if (isset($_REQUEST["user_email"]) && isset($_REQUEST["type"])) {
    $userEmail = $_REQUEST["user_email"];
}

$sessionUser = false;

if ($userEmail === $_SESSION["user_email"]) {
    $sessionUser = true;
}

include "conn.php";

$updateStatus = false;
$failStatus = false;
$repeatStatus = false;
$fileSizeError = false;
$fileUploadError = false;
$fileTypeError = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $_SESSION["profileUpdateStatus"] = false;
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    //password hashed for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $oldEmail = $userEmail;
    $edu = $_POST["eduInput"];
    $learn = $_POST["learnInput"];
    $file = $_FILES["profilePic"]; 
    $defaultpfp = $_POST["defaultpfp"]; 

    
    if ($email !== $oldEmail) { //check the changed email, if it duplicates with existing ones
        $checkEmailSQL = "SELECT * FROM user WHERE user_email = '$email';";
        $checkExe = mysqli_query($conn, $checkEmailSQL);

        if (mysqli_num_rows($checkExe) !== 0) {
            $repeatStatus = true; //email already exist, cannot store in database
        } else { 
            $repeatStatus = false; //email is unique
        }
    }

    if ($repeatStatus === false) {//email unique, can update

        $fileLocation = "0";
        //save pfp properly and create path to save in database
        if ($defaultpfp === "") {
            if ($_FILES['profilePic']['name'] === "") {
                $fileLocation = "";
            } else {
                $fileName = $file['name'];
                $fileTmpName = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileError = $file['error'];
                $fileType = $file['type'];
        
                //identify file extension/type
                $fileExt = explode('.', $fileName);
                $fileActualExt = strtolower(end($fileExt));
        
                //image file types
                $allowedFileType = array('jpg', 'jpeg', 'png');
        
                if (in_array($fileActualExt, $allowedFileType)) {
                    if ($fileError === 0) {
                        if ($fileSize < 1000000) { //smaller than 1MB

                            $safeEmail = str_replace(['@', '.'], ['_at_', '_dot_'], $email);
                            $fileBaseName = $safeEmail; //unique icon name based on modified user email
        
                            //delete all image files with the same base name
                            foreach ($allowedFileType as $ext) {
                                $existingFile = "profile/".$fileBaseName.".".$ext;
                                if (file_exists($existingFile)) {
                                    if (!unlink($existingFile)) {
                                        echo "<script>console.log('Failed to delete $existingFile.');</script>";
                                    } else {
                                        echo "<script>console.log('$existingFile deleted successfully.');</script>";
                                    }
                                }
                            }
        
                            $fileNameNew = $fileBaseName.".".$fileActualExt;
                            $fileLocation = "profile/".$fileNameNew;
                            //file location obtained
                            //save later
                        } else {
                            $fileSizeError = true;
                        }
                    } else {
                        $fileUploadError = true;
                    }
                } else {
                    $fileTypeError = true;
                }
            }
        } else {
            $fileLocation = "profile/defaultProfile.jpg";
        }

        if (!($fileTypeError || $fileUploadError || $fileSizeError)) { //all false to save 
            if ($fileLocation === ""    ) { //pfp remain
                if ($password === "") { //password remain
                    $sql = "UPDATE user SET user_email = '$email', name = '$name' WHERE user_email = '$oldEmail';";
                    mysqli_query($conn, $sql);  //execute query
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail1 = true;
                    } else {
                        $fail1 = false;
                    }

                    $sql = 
                    "UPDATE student SET edu_level = '$edu', learning_style = '$learn' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }
                    
                    if (!($fail1 && $fail2)) {
                        // $_SESSION["profileUpdateStatus"] = true;
                        // $_SESSION["user_email"] = $email;

                        //reload page
                        $updateStatus = true;
                    } else {
                        $failStatus = true;
                    }
                } else { //password change
                    $sql = "UPDATE user SET user_email = '$email', name = '$name', password = '$hashedPassword' WHERE user_email = '$oldEmail';";
                    mysqli_query($conn, $sql);  //execute query
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail1 = true;
                    } else {
                        $fail1 = false;
                    }

                    $sql = 
                    "UPDATE student SET edu_level = '$edu', learning_style = '$learn' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }
                    
                    if (!($fail1 && $fail2)) {
                        // $_SESSION["profileUpdateStatus"] = true;
                        // $_SESSION["user_email"] = $email;

                        //reload page
                        $updateStatus = true;
                    } else {
                        $failStatus = true;
                    }
                }
                
            } else { //pfp change
                if ($password === "") { //password remain
                    $sql = "UPDATE user SET user_email = '$email', name = '$name', pfp = '$fileLocation' WHERE user_email = '$oldEmail';";
                    mysqli_query($conn, $sql);  //execute query
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail1 = true;
                    } else {
                        $fail1 = false;
                    }

                    $sql = 
                    "UPDATE student SET edu_level = '$edu', learning_style = '$learn' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }
                    
                    if (!($fail1 && $fail2)) {
                        if ($fileLocation !== "profile/defaultProfile.jpg") {
                            //moving only when real image is uploaded
                            move_uploaded_file($fileTmpName, $fileLocation);
                        }
                        // $_SESSION["profileUpdateStatus"] = true;
                        // $_SESSION["user_email"] = $email;

                        //reload page
                        $updateStatus = true;
                    } else {
                        $failStatus = true;
                    }
                } else { //password change
                    $sql = "UPDATE user SET user_email = '$email', name = '$name', password = '$hashedPassword', pfp = '$fileLocation' WHERE user_email = '$oldEmail';";
                    mysqli_query($conn, $sql);  //execute query
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail1 = true;
                    } else {
                        $fail1 = false;
                    }

                    $sql = 
                    "UPDATE student SET edu_level = '$edu', learning_style = '$learn' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }
                    
                    if (!($fail1 && $fail2)) {
                        if ($fileLocation !== "profile/defaultProfile.jpg") {
                            //moving only when real image is uploaded
                            move_uploaded_file($fileTmpName, $fileLocation);
                        }
                        // $_SESSION["profileUpdateStatus"] = true;
                        // $_SESSION["user_email"] = $email;
                        //reload page
                        $updateStatus = true;
                    } else {
                        $failStatus = true;
                    }
                }
            }
        }
    }
}

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

$savedCourseSQL = 
"SELECT cp.*, c.course_id
FROM course AS c
JOIN course_proposal AS cp
ON c.proposal_id = cp.proposal_id
JOIN saved_course as sc 
ON c.course_id = sc.course_id 
WHERE c.status IN ('Published', 'Removal Pending') AND sc.student_id = $studentId;"; 
//courses that are published for recent courses
$savedCourseExe = mysqli_query($conn, $savedCourseSQL);

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
    <title>Profile - <?php echo $infoRow['name']?></title>
    <link rel="icon" type="image/x-icon" href="system_img/Capstone real logo.png">
    <link rel="stylesheet" href="stu-home.css">
    <link rel="stylesheet" href="stu-shared.css">
    <link rel="stylesheet" href="stu-profile.css">
    <script src="https://cdn.jsdelivr.net/npm/validator@13.6.0/validator.min.js"></script>
</head>
<body>
    <header>
        <?php include "header.php";?>
    </header>
    <div class="blurOverlay"></div>
    <div class="contentSection">
        <button style="margin-bottom: 1.4rem;" class="purpleBtn" id="backButton" onclick="let back = '<?php echo $backLocation;?>'; if (back === '') window.history.back(); else window.location.href=back">back</button>
        <div id="mainContainer">
            <div class="personalInfo">
                <div class="editContainer">
                    <img class="editBtn" width="25px" height="25px" title="Edit Personal Details" onclick="openDialog('editPersonalDetails');" src="system_img/edit.png" alt="Edit Personal Details">
                </div>
                <div id="profileHeader">
                    <h1>Personal Details</h1>
                </div>
                <div class="profileContainer">
                    <img class="profile" src=<?php echo $infoRow['pfp'];?> alt="Profile Picture">
                </div>
                <?php
                $dob = new DateTime($infoRow['DOB']);
                $dobFormatted = $dob->format('F j, Y');
                $personalInfoTemplate = 
                '<div id="name">'.$infoRow['name'].'</div>
                <div id="email">'.$infoRow['user_email'].'</div>
                <div id="dob">DOB: '.$dobFormatted.'</div>
                <div id="learn">'.$stuRow['learning_style'].' Learning</div>
                <div id="role">'.ucfirst($stuRow['edu_level']).'</div>';

                echo $personalInfoTemplate;
                ?>
            </div>
            <div class="preferences">
                <div id="fieldPreferences">
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

            <div class="courseContainer">
                <h1>My Bookmarked Courses</h1>
                <div id="bookmarkNavContainer" class="courseNavContainer">
                    <button class="navBtn recentLeftBtn" onclick="scrollRecentCourses(-1)"><img src="system_img/leftArrow.png" alt=""></button>
                    <div class="courseViewWrapper">
                        <div class="recentCourse" id="recentCourseSlider"> <!--actually is saved course-->
                            <!--confusion in wording because directly taken from home page-->
                            <?php 
                            $recentCount = 0;
                            while ($row = mysqli_fetch_assoc($savedCourseExe)) {
                                $text = in_array($row['course_id'], $bookmarkCourses) ? "&check;" : "+";
                                if ($text === "&check;") {
                                    $height = "3rem";
                                } else {
                                    $height = "2rem";
                                }
                                $savedCourse = 
                                '<div class="courseCard '.strtolower($row['difficulty']).'Border '.strtolower($row['difficulty']).'Bg">
                                    <div class="coursePins">
                                        <div class="difficulty '.strtolower($row['difficulty']).'Bg">'.ucfirst($row['difficulty']).'</div>
                                    </div>
                                    <div class="courseMain courseMain'.$row['course_id'].'" onclick="clickCourse(`'.$row['course_id'].'`)">
                                        <div class="courseImgContainer">
                                            <img onerror="this.onerror=null; this.src=`img/defaultCourse.jpg`;" class="courseImg" src="'.$row['cover_img_url'].'" alt="">
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
                                echo $savedCourse;
                            }
                            ?>
                        </div>
                    </div>
                    <button class="navBtn recentRightBtn" onclick="scrollRecentCourses(1)"><img src="system_img/rightArrow.png" alt=""></button>
                    
                </div>
                <?php
                if ($recentCount === 0) {
                    echo "<div id='noBookmarkLabel' style='margin-bottom: 2rem;'>You haven't bookmarked any course yet.</div>";
                }
                ?>
            </div>

            <div style="scroll-margin-top: 5rem;" id="inProgressContainer" class="courseContainer">
                <h1>In Progress</h1>
                <div class="courseNavContainer">
                    <button class="navBtn progressLeftBtn" onclick="scrollProgressCourses(-1)"><img src="system_img/leftArrow.png" alt=""></button>
                    <div class="courseViewWrapper">
                        <div id="progressCourseSlider">
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

                                //check bookmarked or not
                                $text = in_array($courseId, $bookmarkCourses) ? "&check;" : "+";
                                if ($text === "&check;") {
                                    $height = "3rem";
                                } else {
                                    $height = "2rem";
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
                                                <div style="height:'.$height.'" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, `tick'.$courseId.'`)"><p class="tick tick'.$courseId.'">'.$text.'</p></div>
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

                                } else {
                                    $inProgressTemplate = 
                                    '<div class="courseCard '.strtolower($row['difficulty']).'Border '.strtolower($row['difficulty']).'Bg">
                                        <div class="coursePins">
                                            <div class="difficulty '.strtolower($row['difficulty']).'Bg">'.ucfirst($row['difficulty']).'</div>
                                        </div>
                                        <div class="courseMain inProgress courseMain'.$courseId.'" onclick="clickCourse(`'.$courseId.'`)">
                                            <div style="border-radius: 0.5rem 0 0 0.5rem;" class="courseImgContainer">
                                                <img onerror="this.onerror=null; this.src=`img/defaultCourse.jpg`;" class="courseImg" src="'.$row['cover_img_url'].'" alt=""><div style="height:'.$height.'" title="Bookmark" class="inProgressBookmark" onclick="bookmarkCourse(event, `tick'.$courseId.'`)"><p class="tick tick'.$courseId.'">'.$text.'</p></div>
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
                                    $inProgressCount++;
                                    $progressCourseArray[] = $inProgressTemplate;
                                }
                            }
                            $conn->close();

                            $chunks = array_chunk($progressCourseArray, 4); // Split into chunks of 4

                            foreach ($chunks as $group) {
                                echo '<div class="inProgressCourse">'.implode($group).'</div>';
                            }
                            ?>
                        </div>
                    </div>
                    <button class="navBtn progressRightBtn" onclick="scrollProgressCourses(1)"><img src="system_img/rightArrow.png" alt=""></button>
                </div>
                <?php
                if ($inProgressCount === 0) {
                    echo "<div style='margin-bottom: 1rem; text-align: center;'>No courses enrolled yet.</div>";
                }
                ?>
            </div>

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

    <form id="personalDataForm" action="" method="POST" onsubmit="preventSubmission(event)" enctype="multipart/form-data">
        <dialog id="editPersonalDetails">
            <div class="dialogHeader">
                <h2>Edit Personal Details</h2>
            </div>
            <div class="editDataForm">
                <div class="profileContainer">
                    <input type="hidden" name="defaultpfp" id="defaultpfp" value="">

                    <img id="profile" class="profile" src=<?php echo $infoRow['pfp'];?> alt="Profile Picture">
                    <img id="editProfilePen" src="system_img/edit.png" alt="Edit Profile" title="Edit Profile" onclick="openDialog('editProfileIcon');">
                    <input type="file" name="profilePic" id="profilePic" accept="image/*" style="display: none;" onchange="previewImage(); updateSaveButtonState(); document.getElementById('defaultpfp').value = '';">

                </div>

                <?php
                echo 
                '<h4><label for="nameInput">Name</label></h4>
                <input oninput="checkName(); updateSaveButtonState();" type="text" name="name" id="nameInput">
                <div id="nameError"></div>
                <h4><label for="emailInput">Email</label></h4>
                <input oninput="checkEmail(); updateSaveButtonState();" type="text" name="email" id="emailInput">
                <div style="color: red;" id="emailError"></div>
                <h4><label for="passwordInput">New Password</label></h4>
                <div class="pwContainer">
                    <input type="password" name="password" id="passwordInput" onfocus="checkPassword();" oninput="checkPassword(); updateSaveButtonState()" onfocusout="checkPassword();">

                    <img id="pwVisible" src="system_img/visibilityOff.png" alt="VisibilityOff" onclick="hidePW()"><img id="pwNotVisible" src="system_img/visibilityOn.png" alt="VisibilityOn" onclick="hidePW()">
                </div>
                <div>
                    <p class="msgPW" id="msgPW">To change old password, type on the input field.<br></p>
                    <div id="ulPW" >
                        <ul id="pwList" style="padding-left: 0.9rem;">
                            <li id="pw1">Combination of English alphabets, numbers, and symbols</li>
                            <li id="pw2">Use both uppercase and lowercase alphabets</li>
                            <li id="pw3">More than 8 characters</li>
                        </ul>
                        <p class="msgPW">Clear the input field to remain old password.</p>
                    </div>
                </div>';
                ?>
                <h4>Learning Style</h4>
                <div class="customDropdown" id="learnDropdownContainer">
                    <button id="learnDropdown" onclick="controlDropdown('learnDropdownContainer')">
                        <div id="learnDpText">Select</div>
                        <div class="fullTableDpImg">
                            <img id="edSrchDown" src="system_img/down.png" alt="Down Arrow">
                        </div>
                    </button>
                    <div id="learnOptions">
                        <div class="option" data-learn="Mixed" onclick="chooseLearn(this)">Mixed Learning</div>
                        <div class="option" data-learn="Text-Based" onclick="chooseLearn(this)">Text-Based Learning</div>
                        <div class="option" data-learn="Visual" onclick="chooseLearn(this)">Visual Learning</div>
                        <div class="option" data-learn="Audio" onclick="chooseLearn(this)">Audio Learning</div>
                    </div>
                </div>
                <input type="hidden" name="learnInput" id="learnInput">
                <div></div>
                <h4>Education</h4>
                <div class="customDropdown" id="eduDropdownContainer">
                    <button id="eduDropdown" onclick="controlDropdown('eduDropdownContainer')">
                        <div id="eduDpText">Select</div>
                        <div class="fullTableDpImg">
                            <img id="edSrchDown" src="system_img/down.png" alt="Down Arrow">
                        </div>
                    </button>
                    <div class="eduContainer">
                        <input type="text" name="" id="eduSearch" onkeyup="filterSearch(id, 'eduOptions')" placeholder="Search education level">
                    </div>
                    <div id="eduOptions">
                        <div class="option" data-edu="High School" onclick="chooseEdu(this)">High School</div>
                        <div class="option" data-edu="Foundation" onclick="chooseEdu(this)">Foundation</div>
                        <div class="option" data-edu="Diploma" onclick="chooseEdu(this)">Diploma</div>
                        <div class="option" data-edu="Bachelor\'s Degree" onclick="chooseEdu(this)">Bachelor's Degree</div>
                        <div class="option" data-edu="Master\'s Degree" onclick="chooseEdu(this)">Master's Degree</div>
                        <div class="option" data-edu="Doctorate Degree" onclick="chooseEdu(this)">Doctorate Degree</div>
                        <div class="option" data-edu="Other" onclick="chooseEdu(this)">Other</div>
                    </div>
                </div>
                <input type="hidden" name="eduInput" id="eduInput">
            </div>
            <div class="dialogFooter">
                <button onclick="exitDialog('editPersonalDetails');">cancel</button>
                <button id="saveBtn" type="submit" onclick="validateDataForm();">Save</button>
            </div>
        </dialog>
    </form>

    <dialog id="editProfileIcon">
        <div class="dialogHeader">
            <h2>Change Profile Icon</h2>
            <br>
        </div>
        <div class="dialogFooter">
            <button onclick="exitDialog('editProfileIcon');">cancel</button>
            <button onclick="document.getElementById('profilePic').click(); exitDialog('editProfileIcon');">Browse My Files</button>
            <button id="unsetBtn" onclick="unsetPhoto(); exitDialog('editProfileIcon');">Unset Icon</button>
        </div>
    </dialog>

    <dialog class="exitDialog" id="fileSizeErrorMsg">
        <div class="dialogHeader">
            <h3>The profile chosen cannot be uploaded because it is too large in size.</h3><br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('fileSizeErrorMsg')">Okay</button>
        </div>
    </dialog>

    <dialog class="exitDialog" id="fileUploadErrorMsg">
        <div class="dialogHeader">
            Your profile chosen cannot be uploaded at this time. Try using another file or try again later.<br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('fileUploadErrorMsg')">Okay</button>
        </div>
    </dialog>

    <dialog class="exitDialog" id="fileTypeErrorMsg">
        <div class="dialogHeader">
            Profile chosen must be an image (.jpg, .jpeg, .png).<br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('fileTypeErrorMsg')">Okay</button>
        </div>
    </dialog>

    <dialog class="exitDialog" id="repeatMsg">
        <div class="dialogHeader">
            Changes cannot be saved because email already exists in the database.<br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('repeatMsg')">Okay</button>    
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

    <dialog class="exitDialog" id="updateMsg">
        <div class="dialogHeader">
            Profile updated successfully.
            <br>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('updateMsg')">Okay</button>
        </div>
    </dialog>

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

        let sessionUser = <?php echo $sessionUser? 'true': 'false';?>;
        if (!sessionUser) {
            document.querySelectorAll('.editContainer').forEach(edit => {
                edit.remove();
            });

            const containers = document.querySelectorAll('.courseContainer');
            containers[0].remove();
            containers[1].remove();

            document.querySelectorAll('.bookmark').forEach(bookmark => {
                bookmark.remove();
            });
        }

        let recentCardsShown = 3; //3 courses is visible at a time
        let progressCardsShown = 1;

        let recentCurrentSlide = 0;
        let progressCurrentSlide = 0;
        //collect course card metadata for navigation
        if (document.getElementById("recentCourseSlider") && document.getElementById("progressCourseSlider")) {
            let recentCourseSlider = document.getElementById("recentCourseSlider");
            let recentCourseCards = recentCourseSlider.querySelectorAll(".courseCard");
            let recentTotalCards = recentCourseCards.length;

            let recentCardGap = 32; 
            let recentMaxSlide = recentTotalCards - recentCardsShown;

            let mediaQuery = window.matchMedia("(max-width: 700px)");

            function handleChange(e) {
                let recentCourseCards = recentCourseSlider.querySelectorAll(".courseCard");
                let recentTotalCards = recentCourseCards.length;
                let recentMaxSlide = recentTotalCards - recentCardsShown;
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

            mediaQuery.addEventListener("change", handleChange);
            handleChange(mediaQuery);
            
            leftRightVisbilityControl("recent");

            const progressCourseSlider = document.getElementById("progressCourseSlider");
            let progressCourseSlides = progressCourseSlider.querySelectorAll(".inProgressCourse");
            let progressTotalSlides = progressCourseSlides.length;
            let progressMaxSlide = progressTotalSlides - progressCardsShown;

            leftRightVisbilityControl("progress");
        


            //saved course display
            function scrollRecentCourses(direction) {
                let recentCourseCards = recentCourseSlider.querySelectorAll(".courseCard");
                let recentTotalCards = recentCourseCards.length;
                let recentMaxSlide = recentTotalCards - recentCardsShown;
                
                if (recentTotalCards <= 0) {
                    document.getElementById("bookmarkNavContainer").innerHTML += "<div id='noBookmarkLabel' style='margin-bottom: 2rem;'>You haven't bookmarked any course yet.</div>";
                } else {
                    let label = document.getElementById("noBookmarkLabel")
                    if (label) {
                        label.remove();
                    }
                    let cardsPerView = 1; //move 1 course at a time
                    let cardWidth = recentCourseCards[0].offsetWidth + recentCardGap;

                    recentCurrentSlide += direction;
                    recentCurrentSlide = Math.max(0, Math.min(recentCurrentSlide, recentMaxSlide));

                    let offset = recentCurrentSlide * cardsPerView * cardWidth;
                    recentCourseSlider.style.transform = `translateX(-${offset}px)`;
                }

                leftRightVisbilityControl("recent");
            }

            //in-progress course display
            function scrollProgressCourses(direction) {
                let element = document.getElementById("progressCourseSlider");
                let slideGap = parseFloat(getComputedStyle(element).gap);

                let slideWidth = progressCourseSlides[0].offsetWidth + slideGap;

                progressCurrentSlide += direction;
                progressCurrentSlide = Math.max(0, Math.min(progressCurrentSlide, progressMaxSlide));

                let offset = progressCurrentSlide * slideWidth;
                progressCourseSlider.style.transform = `translateX(-${offset}px)`;

                leftRightVisbilityControl("progress");
            }

            function leftRightVisbilityControl(type) {
                let recentCourseSlider = document.getElementById("recentCourseSlider");
                let recentCourseCards = recentCourseSlider.querySelectorAll(".courseCard");
                let recentTotalCards = recentCourseCards.length;
                let recentMaxSlide = recentTotalCards - recentCardsShown;
                
                console.log("Num of saved cards: "+recentTotalCards);

                let progressCourseSlider = document.getElementById("progressCourseSlider");
                let progressCourseSlides = progressCourseSlider.querySelectorAll(".inProgressCourse");
                let progressTotalSlides = progressCourseSlides.length;
                let progressMaxSlide = progressTotalSlides - progressCardsShown;

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
        }

        let updateStatus = <?php echo $updateStatus ? 'true' : 'false'; ?>;
        let failStatus = <?php echo $failStatus ? 'true' : 'false'; ?>;
        let repeatStatus = <?php echo $repeatStatus ? 'true' : 'false'; ?>;
        let fileSizeError = <?php echo $fileSizeError ? 'true' : 'false'; ?>;
        let fileUploadError = <?php echo $fileUploadError ? 'true' : 'false'; ?>;
        let fileTypeError = <?php echo $fileTypeError ? 'true' : 'false'; ?>;

        //show dialog as feedback to user
        if (repeatStatus) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("repeatMsg").showModal();
        }
        if (failStatus) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("failMsg").showModal();
        }
        if (updateStatus) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("updateMsg").showModal();
        }
        if (fileSizeError) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("fileSizeErrorMsg").showModal();
        }
        if (fileUploadError) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("fileUploadErrorMsg").showModal();
        }
        if (fileTypeError) {
            document.querySelector(".blurOverlay").style.visibility = "hidden";
            document.getElementById("fileTypeErrorMsg").showModal();
        }

        function clickCourse(courseId) {
            const request = new XMLHttpRequest();
            request.open("POST", "save-recent-course.php?course=" + courseId + "&student=" + <?php echo $studentId;?>);

            request.onload = function() {
                window.location.href = "stu-course-detail.php?courseId=" + courseId; //go page after server receives
            };

            //when error
            request.onerror = function() {
                console.error("Failed to save recent course");
                window.location.href = "stu-course-detail.php?courseId=" + courseId;
            };
            request.send();
            window.location.href = "stu-course-detail.php?courseId=" + courseId; //go page
        }

        function openDialog(dialogId) {
            document.querySelector(".blurOverlay").style.visibility = "visible";
            document.getElementById(dialogId).showModal();
            
            if (dialogId === "editPersonalDetails") {
                let profile = "<?php echo $infoRow["pfp"];?>";
                let name = "<?php echo $infoRow["name"];?>";
                let email = "<?php echo $infoRow["user_email"];?>";
                document.getElementById("profile").src = profile;
                document.getElementById("nameInput").value = name;
                document.getElementById("emailInput").value = email;
                document.getElementById("passwordInput").value = "";
                document.getElementById("profilePic").value = "";

                let edu = "<?php echo $stuRow["edu_level"];?>";
                let learn = "<?php echo $stuRow["learning_style"];?>";
                simulateOptionClick('edu', edu);
                simulateOptionClick('learn', learn);

                controlDropdown("eduDropdownContainer");
                controlDropdown("learnDropdownContainer");
            }
            updateSaveButtonState();
        }

        function exitDialog(dialogId) {
            const dialog = document.getElementById(dialogId);
            dialog.close();
            if (dialogId !== "editProfileIcon") {
                document.querySelector(".blurOverlay").style.visibility = "hidden";
            }
        }

        function controlDropdown(containerId) {
            const container = document.getElementById(containerId);
            if (!container) {
                console.log("Dropdown container not found:", containerId);
                return;
            }
            container.classList.toggle('open');
        }

        function chooseLearn(element) {
            let newText = element.innerText;
            document.getElementById("learnDpText").textContent = newText;
            controlDropdown("learnDropdownContainer");

            let learn = element.dataset.learn;

            let learnInput = document.getElementById("learnInput");
            learnInput.value = learn;

            updateSaveButtonState();
        }

        function chooseEdu(element) {
            let newText = element.innerText;
            document.getElementById("eduDpText").textContent = newText;
            controlDropdown("eduDropdownContainer");

            let edu = element.dataset.edu;

            let eduInput = document.getElementById("eduInput");
            eduInput.value = edu;

            updateSaveButtonState();
        }

        function filterSearch(id, optionList) {
            const input = document.getElementById(id);
            let filter = input.value.toUpperCase();
            const options = document.getElementsByClassName(optionList)[0];
            const div = options.getElementsByTagName("div");
            if (filter !== "") {
                for (let i = 0; i < div.length; i++) {
                    txtValue = div[i].textContent || div[i].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) >= 0) {
                        div[i].style.display = "";
                    } else {
                        div[i].style.display = "none";
                    }
                }
            }
        }

        function simulateOptionClick(type, eduValue) {
            let option = document.querySelector(`.option[data-${type}="${eduValue}"]`);
            if (option) {
                option.click();
            }
        }

        function preventSubmission(event) {
            event.preventDefault();
        }

        function checkName() {
            let oldName = "<?php echo $infoRow['name'];?>";
            let name = document.getElementById("nameInput").value.trim();
            if (name.length > 100) {
                document.getElementById("nameError").innerHTML = "Name is too long.";
                return 0;
            } else if (name.length < 3) {
                document.getElementById("nameError").innerHTML = "Name is too short.";
                return 0;
            } else if (name === oldName) {
                document.getElementById("nameError").innerHTML = "";
                return 1;
            } else {
                document.getElementById("nameError").innerHTML = "";
                return 2;
            }
        }

        function checkEmail() {
            let oldEmail = "<?php echo $infoRow['user_email'];?>";
            let email = document.getElementById("emailInput").value;
            if (!validator.isEmail(email)) {
                document.getElementById("emailError").innerHTML = "Email is invalid.";
                return 0;
            } else if (email === oldEmail) {
                document.getElementById("emailError").innerHTML = "";
                return 1;
            } else {
                document.getElementById("emailError").innerHTML = "";
                return 2;
            }
        }

        function checkPassword() {
            let pw = document.getElementById("passwordInput").value;
            
            if (pw === "") {
                document.getElementById("ulPW").style.display = "none";
                document.getElementById("msgPW").innerHTML = "To change old password, type on the input field.";
                return 1;
            } else {
                document.getElementById("ulPW").style.display = "block";
                document.getElementById("msgPW").innerHTML = "Password requirement:";
                let pwCon1 = pwCheckCharAndCase(pw), pwCon2 = pwCheckLen(pw);
                if (pwCon1 && pwCon2) {
                    return 2;
                } else {
                    return 0;
                }
            }
        }

        function turnRed(id) {
            document.getElementById(id).style.color = "red";
        }


        function turnGreen(id) {
            document.getElementById(id).style.color = "green";
        }

        //ensure password has uppercase and lowercase letters, numbers and symbols
        function pwCheckCharAndCase(pw) {
            //check ascii 33 - 126 and check alphabet case
            //charSign used to indicate that all three types of characters are used
            //caseSign used to indicate that uppercase and lowercase alphabets are used
            //alp = alphabets, sym = symbol, num = number, up = uppercase alp, low = lowercase alp
            let charSign = 0, caseSign = 0, sym = 0, alp = 0, num = 0, up = 0, low = 0;
            for (let char of pw) {
                let code = char.charCodeAt(0);  //using ascii code to check
                if (!(code >= 33 && code <= 126)) {
                    let sym = 0, alp = 0, num = 0;
                    break;
                } 
                else {

                    if ((code >= 33 && code <= 47) || (code >= 58 && code <= 64) || (code >= 91 && code <= 96) || (code >= 123 && code <= 126))
                    {
                        sym++;
                    }
                    else if ((code >= 48 && code <= 57)) {
                        num++;
                    }
                    else if ((code >= 65 && code <= 90) || (code >= 97 && code <= 122)) {
                        alp++;
                        if (code >= 65 && code <= 90) {
                            up++;
                        } else {
                            low++;
                        }
                    } 
                }
            } 

            if (sym > 0 && alp > 0 && num > 0) {
                charSign++;
                turnGreen("pw1");
            } else {
                turnRed("pw1");
            }

            if (up > 0 && low > 0) {
                caseSign++;
                turnGreen("pw2");
            } else {
                turnRed("pw2");
            }

            if (charSign == 1 && caseSign == 1) {
                return true;
            } else {
                return false;
            }
        }
            

        //validate password length
        function pwCheckLen(pw) {
            //check length
            let len = pw.length;
            if (len < 8) {
                turnRed("pw3");
                return false;
            } else {
                turnGreen("pw3");
                return true;
            }
        }

        function hidePW() {
            var id = document.getElementById("passwordInput");
            let pwShow = document.getElementById("pwVisible");
            let pwHide = document.getElementById("pwNotVisible");
            if (id.type === "password") {
            id.type = "text";
            pwHide.style.display = "none";
            pwShow.style.display = "block";
            } else {
            id.type = "password";
            pwHide.style.display = "block";
            pwShow.style.display = "none";
            }
        }

        //to disable save button 
        function updateSaveButtonState() {
            const saveBtn = document.getElementById("saveBtn");

            //validate the name and email inputs
            let nameValid = checkName();
            let emailValid = checkEmail();
            let pwValid = checkPassword();

            //get the current selections of the dropdowns
            const learnSelection = document.getElementById("learnInput").value;
            const eduSelection = document.getElementById("eduInput").value;

            //cannot be same as old values
            const invalidEdu = "<?php echo $stuRow["edu_level"];?>";
            const invalidLearn = "<?php echo $stuRow["learning_style"];?>";
            const invalidIconPath = "<?php echo $infoRow["pfp"];?>";
            const invalidIcon = document.getElementById("profilePic").value;

            let eduStatus;
            let learnStatus;
            let iconStatus;
            let iconStatus2;

            if (eduSelection === invalidEdu) {
                eduStatus = 1;
            } else {
                eduStatus = 2;
            }

            if (learnSelection === invalidLearn) {
                learnStatus = 1;
            } else {
                learnStatus = 2;
            }

            if (invalidIcon === "") {
                iconStatus = 1;
            } else {
                iconStatus = 2;
            }

            let previewPfp = document.getElementById('defaultpfp').value;
            if (invalidIconPath !== "profile/defaultProfile.jpg" && previewPfp === "default" || invalidIconPath === "profile/defaultProfile.jpg" && previewPfp !== "default") {
                iconStatus2 = 2;
            } else {
                iconStatus2 = 1;
            }

            // console.log(nameValid);
            // console.log(emailValid);
            // console.log(learnSelection);
            // console.log(invalidLearn);
            // console.log(learnStatus);
            // console.log(eduStatus);
            // console.log(iconStatus);
            //check if the Save button should be disabled
            if ((nameValid * emailValid * pwValid * learnStatus * eduStatus * iconStatus * iconStatus2) > 1) {
                saveBtn.disabled = false;
            } else {
                saveBtn.disabled = true;
            }
        }

        function previewImage() {
            //preview image chosen in dialog
            let file = document.getElementById('profilePic').files[0];
            let reader = new FileReader();

            reader.onload = function(e) {
                //update the image src to the selected file
                document.getElementById('profile').src = e.target.result;
            };

            if (file) {
                reader.readAsDataURL(file); //convert the image file to a data URL
            }
        }

        function unsetPhoto() {
            document.getElementById('defaultpfp').value = "default";
            //default image if users unset
            document.getElementById('profile').src = "profile/defaultProfile.jpg";
            updateSaveButtonState();
        }

        function validateDataForm() {
            document.getElementById("personalDataForm").submit(); 
        }

        function chat() {
            //go ivan's chat page
        }

        function changePreference() {
            //go colwyn's choose preference page
        }

        function ucfirst(str) {
            if (!str) return str; // return the string if it's empty
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function bookmarkCourse(evt, tickCourseId) { //save course
            let courseId = tickCourseId.replace("tick", "");
            let check = evt.currentTarget.children[0].innerHTML;
            // check = "tick hidden" / "tick visible"
            const request = new XMLHttpRequest();
            request.onload = function() {
                const obj = JSON.parse(this.responseText);

                if (obj.status === "success") {
                    const request2 = new XMLHttpRequest();
                    request2.onload = function() {
                        const obj = JSON.parse(this.responseText);

                        let avgRatings = <?php echo json_encode($avgRatings);?>;
                        obj.forEach(course => {

                            document.getElementById("recentCourseSlider").innerHTML +=
                            `<div class="courseCard ${course.difficulty}Border ${course.difficulty}Bg">
                                <div class="coursePins">
                                    <div class="difficulty ${course.difficulty}Bg">${ucfirst(course.difficulty)}</div>
                                </div>
                                <div class="courseMain courseMain${course.course_id}" onclick="clickCourse('${course.course_id}')">
                                    <div class="courseImgContainer">
                                        <img onerror="this.onerror=null; this.src='img/defaultCourse.jpg';" class="courseImg" src="${course.cover_img_url}" alt="">
                                        <div style="height:3rem;" title="Bookmark" class="bookmark" onclick="bookmarkCourse(event, 'tick${course.course_id}')"><p class="tick tick${course.course_id}">\u2713</p></div>
                                    </div>
                                    <div class="courseMetadata">
                                        <p class="courseTitle">${course.title}</p>
                                        <p class="courseStyle">${course.course_style} Learning</p>
                                        <p class="courseTime">${course.completion_time} hours</p>
                                        <p class="courseRating">${avgRatings[course.course_id]}&#x02B50;</p>
                                    </div>
                                </div>
                            </div>`;

                            addEventHandlers();
                            document.getElementsByClassName("recentRightBtn")[0].click();
                        });
                    }
                    request2.open("POST", "course.php?courseId=" + courseId);
                    request2.send();
                }
            }

            let text = "\u2713";

            //find all .bookmark elements
            let allBookmarks = document.querySelectorAll(".bookmark");
            let allProgressBookmarks = document.querySelectorAll(".inProgressBookmark");
            let delSign = "";
            //loop through them
            allBookmarks.forEach(bookmark => {
                //check if the bookmark contains a child <p> with the same tickCourseId class
                let tickElement = bookmark.querySelector("." + tickCourseId);
                
                if (tickElement) {
                    if (check === "+") { // bookmark
                        console.log("bookmark");
                        tickElement.innerHTML = text;
                        bookmark.style.height = "3rem";
                    } else { // don't bookmark
                        console.log("no bookmark");
                        tickElement.innerHTML = "+";
                        bookmark.style.height = "2rem";
                        delSign = "yes";
                    }
                }
            });

            allProgressBookmarks.forEach(bookmark => {
                //check if the bookmark contains a child <p> with the same tickCourseId class
                let tickElement = bookmark.querySelector("." + tickCourseId);
                
                if (tickElement) {
                    if (check === "+") { // bookmark
                        console.log("bookmark progress");
                        tickElement.innerHTML = text;
                        bookmark.style.height = "3rem";
                    } else { // don't bookmark
                        console.log("no bookmark progress");
                        tickElement.innerHTML = "+";
                        bookmark.style.height = "2rem";
                        delSign = "yes";
                    }
                }
            });

            if (delSign === "yes") {
                //remove course from display
                let recentSlider = document.getElementById('recentCourseSlider');
                let courseMain = recentSlider.querySelector('.courseMain' + courseId);
                let parentDiv = courseMain.parentElement;

                console.dir("Parent: "+parentDiv)
                parentDiv.style.transition = "opacity 0.5s ease"; // fade over 0.5s
                parentDiv.style.opacity = "0";

                //set display to none, slowly fade (0.5s)
                setTimeout(() => {
                    parentDiv.style.display = "none";
                    parentDiv.style.opacity = "unset";

                    parentDiv.remove();
                    document.getElementsByClassName("recentLeftBtn")[0].click();
                    document.getElementsByClassName("recentLeftBtn")[0].click();
                }, 500); 
            }
            //send request
            request.open("POST", "bookmark.php?course=" + courseId + "&student=" + <?php echo $studentId;?>);
            request.send();
        }

        //dynamically add event handlers
        function addEventHandlers() {
            const bookmarks = document.querySelectorAll('.bookmark');
            const inProgressBookmarks = document.querySelectorAll('.inProgressBookmark');
            let bookmark = document.getElementsByClassName("inProgressBookmark");
            for (let i = 0; i < bookmark.length; i++) {
                bookmark[i].addEventListener("click", function (event) { 
                    event.stopPropagation(); //prevents parent click
                });
            }

            let bookmark2 = document.getElementsByClassName("bookmark");
            for (let i = 0; i < bookmark2.length; i++) {
                bookmark2[i].addEventListener("click", function (event) {
                    event.stopPropagation(); //prevents parent click
                });
            }

            document.querySelectorAll('.courseMain').forEach(main => {
                main.addEventListener('mouseenter', () => {
                    main.closest('.courseCard').style.boxShadow = '1px 1px 15px grey, -1px -1px 15px grey';
                });
                main.addEventListener('mouseleave', () => {
                    main.closest('.courseCard').style.boxShadow = 'none';
                });
            });

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

        addEventHandlers();
    </script>
    <script></script>
</body>
</html>