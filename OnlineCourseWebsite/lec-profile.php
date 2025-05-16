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

if (isset($_SESSION["profileUpdateStatus"]) && $_SESSION["profileUpdateStatus"] === true) {
    $updateStatus = true;
    $_SESSION["profileUpdateStatus"] = false;
} else {
    $updateStatus = false;
}

//url to visit other's profile
if (isset($_REQUEST["user_email"])) {
    $userEmail = $_REQUEST["user_email"];
    $origin = $_REQUEST["origin"];
    $courseId = $_REQUEST["courseId"];
} 
$sessionUser = false;
//turn true if the lec profile belongs to the session user
//can access to more features => edit profile
if ($userEmail === $_SESSION["user_email"]) {
    $sessionUser = true;
    $origin = "";
    $courseId = "";
}

include "conn.php";
$failStatus = false;
$repeatStatus = false;
$fileSizeError = false;
$fileUploadError = false;
$fileTypeError = false;

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $_SESSION["profileUpdateStatus"] = false;
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $job = trim($_POST["job"]);
    $desc = trim($_POST["desc"]);
    $password = $_POST["password"];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $oldEmail = $userEmail;
    $uni = $_POST["uniInput"];
    $exp = $_POST["expInput"];
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
                    "UPDATE lecturer SET description = '$desc' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }

                    $sql = 
                    "UPDATE lecturer_applicant SET current_uni_name = '$uni', teaching_exp = '$exp', job_title = '$job' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail3 = true;
                    } else {
                        $fail3 = false;
                    }
                    
                    if (!($fail1 && $fail2 && $fail3)) {
                        $_SESSION["profileUpdateStatus"] = true;
                        $_SESSION["user_email"] = $email;

                        //reload page
                        header("Location: lec-profile.php?user_email=" . urlencode($email) . "&origin=" . urlencode($origin) . "&courseId=" . urlencode($courseId));
                        exit();
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
                    "UPDATE lecturer SET description = '$desc' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }

                    $sql = 
                    "UPDATE lecturer_applicant SET current_uni_name = '$uni', teaching_exp = '$exp', job_title = '$job' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail3 = true;
                    } else {
                        $fail3 = false;
                    }
                    
                    if (!($fail1 && $fail2 && $fail3)) {
                        $_SESSION["profileUpdateStatus"] = true;
                        $_SESSION["user_email"] = $email;

                        //reload page
                        header("Location: lec-profile.php?user_email=" . urlencode($email) . "&origin=" . urlencode($origin) . "&courseId=" . urlencode($courseId));
                        exit();
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
                    "UPDATE lecturer SET description = '$desc' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }

                    $sql = 
                    "UPDATE lecturer_applicant SET current_uni_name = '$uni', teaching_exp = '$exp', job_title = '$job' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail3 = true;
                    } else {
                        $fail3 = false;
                    }
                    
                    if (!($fail1 && $fail2 && $fail3)) {
                        if ($fileLocation !== "profile/defaultProfile.jpg") {
                            //moving only when real image is uploaded
                            move_uploaded_file($fileTmpName, $fileLocation);
                        }
                        $_SESSION["profileUpdateStatus"] = true;
                        $_SESSION["user_email"] = $email;

                        //reload page
                        header("Location: lec-profile.php?user_email=" . urlencode($email) . "&origin=" . urlencode($origin) . "&courseId=" . urlencode($courseId));
                        exit();
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
                    "UPDATE lecturer SET description = '$desc' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail2 = true;
                    } else {
                        $fail2 = false;
                    }

                    $sql = 
                    "UPDATE lecturer_applicant SET current_uni_name = '$uni', teaching_exp = '$exp', job_title = '$job' WHERE user_email = '$email';";
                    mysqli_query($conn, $sql);  //execute query
                    
                    if (mysqli_affected_rows($conn) <= 0) {
                        $fail3 = true;
                    } else {
                        $fail3 = false;
                    }
                    
                    if (!($fail1 && $fail2 && $fail3)) {
                        if ($fileLocation !== "profile/defaultProfile.jpg") {
                            //moving only when real image is uploaded
                            move_uploaded_file($fileTmpName, $fileLocation);
                        }
                        $_SESSION["profileUpdateStatus"] = true;
                        $_SESSION["user_email"] = $email;
                        //reload page
                        header("Location: lec-profile.php?user_email=" . urlencode($email) . "&origin=" . urlencode($origin) . "&courseId=" . urlencode($courseId));
                        exit();
                    } else {
                        $failStatus = true;
                    }
                }
            }
        }
    }
}

$idSQL = "SELECT lecturer_id FROM lecturer WHERE user_email = '$userEmail';";
$idExe = mysqli_query($conn, $idSQL);
$lecturerId = mysqli_fetch_assoc($idExe)["lecturer_id"];

//obtain user's personal info from user table
$userSQL = 
"SELECT * FROM user WHERE user_email = '$userEmail'";
$userExe = mysqli_query($conn, $userSQL);
while ($row = mysqli_fetch_assoc($userExe)) {
    $infoRow = $row;
}

$lecSQL = 
"SELECT description FROM lecturer WHERE user_email = '$userEmail'";
$lecExe = mysqli_query($conn, $lecSQL);
$lecRow = mysqli_fetch_assoc($lecExe);

$lecAppSQL = 
"SELECT teaching_exp, current_uni_name, job_title FROM lecturer_applicant WHERE user_email = '$userEmail'";
$lecAppExe = mysqli_query($conn, $lecAppSQL);

while ($row = mysqli_fetch_assoc($lecAppExe)) {
    $lecAppRow = $row;
}

//retrieve courses that are published by lecturer
$publishSQL = 
"SELECT DISTINCT c.course_id, cp.*
FROM course AS c
JOIN course_proposal AS cp
ON c.proposal_id = cp.proposal_id
WHERE c.status IN ('Published', 'Removal Pending') AND c.lecturer_id = $lecturerId;";
$publishExe = mysqli_query($conn, $publishSQL);

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
    <title>Document</title>
    <link rel="stylesheet" href="stu-home.css">
    <link rel="stylesheet" href="stu-profile.css">
    <link rel="stylesheet" href="stu-shared.css">
    <script src="https://cdn.jsdelivr.net/npm/validator@13.6.0/validator.min.js"></script>
    <style>
        .contentSection {
            padding: 0;
            width: 100%;
        }

        #mainContainer {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: max-content;
            column-gap: 0;
            row-gap: 1.5rem;
        }

        #mainContainer > div {
            border-radius: 0;
            padding: 4% 10%;
        }

        .personalLecturerInfo {
            display: grid;
            grid-template-rows: repeat(9, max-content);
            grid-template-columns: 2fr 5fr;
            row-gap: 1rem;
            column-gap: 1rem;
        }

        .personalLecturerInfo > .editContainer, #profileHeader, #desc {
            grid-column: 1 / 3;
        }

        .personalLecturerInfo > .profileContainer {
            grid-column: 1 / 2;
            grid-row: 3 / 7;
        }

        #social {
            display: flex;
            justify-content: center;
            align-items: center;
            grid-column: 1 / 2;
            grid-row: 7 / 9;
        }

        #desc {
            margin-top: 1rem;
        }

        #chat {
            margin: 0;
        }

        #chat:active {
            background-color: grey;
        }

        #lecDoc {
            margin-top: auto;
            margin-bottom: auto;
            margin-left: 0;
            grid-column: 1 / 2;
            grid-row: 8 / 9;
        }

        #karma {
            width: fit-content;
            height: fit-content;
        }

        .editBtn {
            width: 2rem;
            height: 2rem;
        }

        @media screen and (max-width: 630px) {
            #mainContainer > div {
                border-radius: 0;
                padding: 4% 6%;
            }
            .personalLecturerInfo {
                display: grid;
                grid-template-rows: repeat(11, max-content);
                grid-template-columns: 3fr 4fr;
                row-gap: 1rem;
                column-gap: 1rem;
            }

            .personalLecturerInfo > div:not(#social):not(#lecDoc) {
                grid-column: 1 / 3;
            }

            .personalLecturerInfo > .profileContainer {
                grid-column: 1 / 2;
                grid-row: 3 / 4;
            }

            #social {
                justify-content: none;
                grid-column: 2 / 3;
                grid-row: 4 / 5;
            }

            #lecDoc {
                margin-left: unset;
                margin-right: 0;
                grid-column: 1 / 2;
                grid-row: 4 / 5;
            }
        }
    </style>
</head>
<body>
    <header>
        <?php 
        if (isset($_SESSION["student_id"])) {
            include "header.php";
            $lecturer = 0;
        } else if (isset($_SESSION["lecturer_id"])) {
            include "lec_header.php";
            $lecturer = 1;
        }
        ?>
    </header>
    <div class="blurOverlay"></div>
    <div class="contentSection">
        <button style="margin-bottom: 1rem; margin-left: 3rem;" class="purpleBtn" id="backButton" onclick="let lecturer = <?php echo $lecturer;?>; console.log(lecturer); if (lecturer == 1) {window.history.back();} else {window.location.href = '<?php echo $origin.'?courseId='.$courseId;?>'}">Back</button>
        <div id="mainContainer">
            <div class="personalLecturerInfo">
                <div class="editContainer">
                    <img class="editBtn" width="25px" height="25px" title="Edit Personal Details" onclick="openDialog('editPersonalDetails');" src="system_img/edit.png" alt="Edit Personal Details">
                </div>
                <div id="profileHeader">
                    <h1>Personal Details</h1>
                </div>
                <div class="profileContainer">
                    <img class="profile" src=<?php echo $infoRow['pfp'];?> alt="Profile Picture">
                </div>
                <!-- <div onclick="lecAppDocuments();" id="lecDoc">
                    <p>Documents</p>
                    <img id="lecDocImg" src="system_img/attach.png" alt="" title="Message">
                </div> -->
                <div id="social">
                    <div onclick="chat();" id="chat">
                        <p>Chat</p>
                        <img id="chatImg" src="system_img/chat.png" alt="" title="Message">
                    </div>
                <?php
                $dob = new DateTime($infoRow['DOB']);
                $dobFormatted = $dob->format('F j, Y');
                $personalInfoTemplate = 
                '</div>
                <div id="name">'.$infoRow['name'].'</div>
                <div id="email">'.$infoRow['user_email'].'</div>
                <div id="dob">DOB: '.$dobFormatted.'</div>
                <div id="job">'.$lecAppRow['job_title'].'</div>
                <div id="uni">'.ucfirst($lecAppRow['current_uni_name']).'</div>
                <div id="exp">Teaching Experience: '.$lecAppRow['teaching_exp'].'</div>
                <div id="desc">'.$lecRow['description'].'</div>';

                echo $personalInfoTemplate;
                ?>
            </div>
            
            <div class="courseContainer">
                <h1>Courses Published</h1>
                <div class="completedCourse courseList">
                    <?php
                    $courseCount = 0;
                    while ($row = mysqli_fetch_assoc($publishExe)) {
                        $completedCourse = 
                        '<div class="courseCard '.strtolower($row['difficulty']).'Border '.strtolower($row['difficulty']).'Bg">
                            <div class="coursePins">
                                <div class="difficulty '.strtolower($row['difficulty']).'Bg">'.ucfirst($row['difficulty']).'</div>
                            </div>
                            <div class="courseMain" id="courseMain'.$row['course_id'].'" onclick="clickCourse(`'.$row['course_id'].'`)">
                                <div class="courseImgContainer">
                                    <img class="courseImg" onerror="this.onerror=null; this.src=`img/defaultCourse.jpg`;" src="'.$row['cover_img_url'].'" alt="">
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
                        $courseCount++;
                        echo $completedCourse;
                    }
                    ?>
                </div>
                <?php
                if ($courseCount === 0) {
                    echo "<div style='margin-bottom: 1rem; text-align: center;'>No courses published yet.</div>";
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
                <h4><label for="nameInput">Name</label></h4>
                <input oninput="checkItem('name'); updateSaveButtonState();" type="text" name="name" id="nameInput">
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
                </div>
                <h4><label for="jobInput">Job Title</label></h4>
                <input oninput="checkItem('job'); updateSaveButtonState();" type="text" name="job" id="jobInput">
                <div id="jobError"></div>
                <h4>Teaching Experience</h4>
                <div class="customDropdown" id="expDropdownContainer">
                    <button id="expDropdown" onclick="controlDropdown('expDropdownContainer')">
                        <div id="expDpText">Select</div>
                        <div class="fullTableDpImg">
                            <img id="edSrchDown" src="system_img/down.png" alt="Down Arrow">
                        </div>
                    </button>
                    <div id="expOptions">
                        <div class="option" data-exp="Less than 1 year" onclick="chooseExp(this)">Less than 1 year</div>
                        <div class="option" data-exp="1 year" onclick="chooseExp(this)">1 year</div>
                        <div class="option" data-exp="2 years" onclick="chooseExp(this)">2 year</div>
                        <div class="option" data-exp="3 years" onclick="chooseExp(this)">3 year</div>
                        <div class="option" data-exp="4 years" onclick="chooseExp(this)">4 year</div>
                        <div class="option" data-exp="5 - 7 years" onclick="chooseExp(this)">5 - 7 years</div>
                        <div class="option" data-exp="8 - 10 years" onclick="chooseExp(this)">8 - 10 years</div>
                        <div class="option" data-exp="10 - 15 years" onclick="chooseExp(this)">10 - 15 years</div>
                        <div class="option" data-exp="15 years or more" onclick="chooseExp(this)">15 years or more</div>
                    </div>
                    <input type="hidden" name="expInput" id="expInput">
                </div>
                
                <h4>Current University</h4>
                <div class="customDropdown" id="uniDropdownContainer">
                    <button id="uniDropdown" onclick="controlDropdown('uniDropdownContainer')">
                        <div id="uniDpText">Select</div>
                        <div class="fullTableDpImg">
                            <img id="edSrchDown" src="system_img/down.png" alt="Down Arrow">
                        </div>
                    </button>
                    <div class="uniContainer">
                        <input type="text" name="" id="uniSearch" onkeyup="filterSearch(id, 'uniOptions')" placeholder="Search University Name">
                    </div>
                    <div id="uniOptions">
                    </div>
                    <input type="hidden" name="uniInput" id="uniInput">
                </div>
                <h4>Description</h4>
                <textarea oninput="checkItem('desc'); updateSaveButtonState();" name="desc" id="descInput"></textarea>
                <div id="descError"></div>
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
        </div>
        <div class="dialogFooter">
            <button onclick="exitDialog('editProfileIcon');">cancel</button>
            <button onclick="document.getElementById('profilePic').click(); exitDialog('editProfileIcon');">Browse My Files</button>
            <button id="unsetBtn" onclick="unsetPhoto(); exitDialog('editProfileIcon');">Unset Icon</button>
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
        let sessionUser = <?php echo $sessionUser ? 'true' : 'false';?>;

        if (!sessionUser) {
            document.querySelector('.editBtn').remove();
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
            window.location.href = "stu-course-detail.php?courseId=" + courseId; //go page
        }

        function openDialog(dialogId) {
            document.querySelector(".blurOverlay").style.visibility = "visible";
            document.getElementById(dialogId).showModal();
            
            if (dialogId === "editPersonalDetails") {
                let profile = "<?php echo $infoRow["pfp"];?>";
                let name = "<?php echo $infoRow["name"];?>";
                let job = "<?php echo $lecAppRow['job_title'];?>";
                let email = "<?php echo $infoRow["user_email"];?>";
                let exp = "<?php echo $lecAppRow["teaching_exp"];?>";
                let uni = "<?php echo $lecAppRow["current_uni_name"];?>";
                let desc = "<?php echo $lecRow["description"];?>";

                document.getElementById("profile").src = profile;
                document.getElementById("nameInput").value = name;
                document.getElementById("jobInput").value = job;
                document.getElementById("emailInput").value = email;
                document.getElementById("passwordInput").value = "";
                document.getElementById("profilePic").value = "";
                let textarea = document.getElementById("descInput");
                textarea.value = desc;
                //expand container according to word lines
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';

                simulateOptionClick('uni', uni);
                simulateOptionClick('exp', exp);
                controlDropdown("uniDropdownContainer");
                controlDropdown("expDropdownContainer");
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

        function chooseExp(element) {
            let newText = element.innerText;
            document.getElementById("expDpText").textContent = newText;
            controlDropdown("expDropdownContainer");

            let exp = element.dataset.exp;

            let expInput = document.getElementById("expInput");
            expInput.value = exp;

            updateSaveButtonState();
        }

        const allUniversities = [];
        fetch('malaysian-universities.json')
        .then(response => {
            if (!response.ok) {
            throw new Error('Fetching failed.');
            }
            return response.json();
        })
        .then(data => {
            // data is now a JavaScript array of university objects
            console.log(data); // See the full array
            data.forEach(university => {
                allUniversities.push({
                    name: `${university.name} ${university.shortName}`
                });
            });

            populateUniversityDropdown(data);

        })
        .catch(error => {
            console.error('Failed to load JSON:', error);
        });

        function populateUniversityDropdown(universities) {
            const container = document.getElementById("uniOptions");
            container.innerHTML = ''; // Clear previous entries

            universities.forEach((uni, index) => {
                const option = document.createElement("div");
                option.className = "option";
                option.innerText = `${uni.name} ${uni.shortName}`;
                option.dataset.uni = `${uni.name} ${uni.shortName}`;
                option.onclick = function () {
                    chooseUni(this);
                };
                container.appendChild(option);
            });
        }

        function filterSearch(id, optionList) {
            const input = document.getElementById(id);
            let filter = input.value.toLowerCase();

            const filtered = allUniversities.filter(uni =>
                uni.name.toLowerCase().includes(filter)
            );

            populateUniversityDropdown(filtered);
        }

        function chooseUni(element) {
            let newText = element.innerText;
            document.getElementById("uniDpText").textContent = newText;
            controlDropdown("uniDropdownContainer");

            let uni = element.dataset.uni;

            let uniInput = document.getElementById("uniInput");
            uniInput.value = uni;

            updateSaveButtonState();
        }

        function simulateOptionClick(type, value) {
            let option = document.querySelector(`.option[data-${type}="${value}"]`);
            if (option) {
                option.click();
            }
        }

        function preventSubmission(event) {
            event.preventDefault();
        }

        function ucfirst(str) {
            if (!str) return str; // return the string if it's empty
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function checkItem(type) { //name or job title
            let formattedItem, oldItem, charMax;
            switch (type) {
                case "job": formattedItem = "Job Title"; oldItem = "<?php echo $lecAppRow['job_title'];?>"; charMax = 50; break;
                case "name": formattedItem = "Name"; oldItem = "<?php echo $infoRow['name'];?>"; charMax = 100; break;
                case "desc": formattedItem = "Description"; oldItem = "<?php echo $lecRow['description'];?>"; charMax = 300; break;
            }

            let item = document.getElementById(type+"Input").value.trim();
            if (item.length > charMax) {
                document.getElementById(type+"Error").innerHTML = formattedItem+" is too long.";
                return 0;
            } else if (item.length < 3) {
                document.getElementById(type+"Error").innerHTML = formattedItem+" is too short.";
                return 0;
            } else if (item === oldItem) {
                document.getElementById(type+"Error").innerHTML = "";
                return 1;
            } else {
                document.getElementById(type+"Error").innerHTML = "";
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
            let nameValid = checkItem("name");
            let jobValid = checkItem("job");
            let descValid = checkItem("desc");
            let emailValid = checkEmail();
            let pwValid = checkPassword();

            //get the current selections of the dropdowns
            const uniSelection = document.getElementById("uniInput").value;
            const expSelection = document.getElementById("expInput").value;

            //cannot be same as old values
            const invalidExp = "<?php echo $lecAppRow["teaching_exp"];?>";
            const invalidUni = "<?php echo $lecAppRow["current_uni_name"];?>";
            const invalidIconPath = "<?php echo $infoRow["pfp"];?>";
            const invalidIcon = document.getElementById("profilePic").value;

            let expStatus;
            let uniStatus;
            let iconStatus;
            let iconStatus2;

            if (expSelection === invalidExp) {
                expStatus = 1;
            } else {
                expStatus = 2;
            }

            if (uniSelection === invalidUni) {
                uniStatus = 1;
            } else {
                uniStatus = 2;
            }

            if (invalidIcon === "") {
                iconStatus = 1;
            } else {
                iconStatus = 2;
            }
            
            let previewPfp = document.getElementById('defaultpfp').value;
            if (invalidIconPath !== "profile/defaultProfile.jpg" && previewPfp === "default" || invalidIconPath === "profile/defaultProfile.jpg" && previewPfp !== "default" && previewPfp !== "") {
                iconStatus2 = 2;
            } else {
                iconStatus2 = 1;
            }

            console.log(nameValid);
            console.log(emailValid);
            console.log(iconStatus);
            console.log(iconStatus2);

            console.log("wrong: "+jobValid);
            console.log(descValid);
            //check if the Save button should be disabled
            if ((nameValid * descValid * jobValid * emailValid * pwValid * uniStatus * expStatus * iconStatus * iconStatus2) > 1) {
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

        //event handlers for styling
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
    </script>
</body>
</html>