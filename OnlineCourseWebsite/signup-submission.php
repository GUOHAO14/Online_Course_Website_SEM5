<?php
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    include "conn.php";
    $formType = $_POST["formType"];
    $name = $_POST["name"];
    $dob = $_POST["dob"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    //password hashing for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($formType === "lecturer") {
        $role = "pending lecturer";
        $status = "Pending";
        $exp = $_POST["expInput"];
        $uni = $_POST["uniInput"];
        $job = $_POST["job"];

        //dedicated folder for pdf doc submitted by applicants
        $uploadFileDir = 'applicant_document/';
        //allow PDF uploads only
        $allowedExt = ["pdf"];
        $allFilesUploaded = true;
        $uploadedPdfPaths = [];

        //all $_FILES values are in array form because multiple pdfs allowed
        foreach ($_FILES["pdfInput"]["name"] as $index => $pdfName) {
            $pdfTmpPath = $_FILES["pdfInput"]["tmp_name"][$index];
            $pdfSize = $_FILES["pdfInput"]["size"][$index];
            $pdfNameCmps = explode(".", $pdfName);
            $pdfExtension = strtolower(end($pdfNameCmps));

            if (in_array($pdfExtension, $allowedExt)) {
                $newFileName = uniqid() . '.' . $pdfExtension;
                $pdfPath = $uploadFileDir . $newFileName;

                if (move_uploaded_file($pdfTmpPath, $pdfPath)) {
                    $uploadedPdfPaths[] = [
                        "path" => $pdfPath, 
                        "name" => $pdfName
                    ];
                } else {
                    $allFilesUploaded = false;
                    $signupStatus = "lecturer fail";
                    break;
                }
            } else {
                $allFilesUploaded = false;
                $signupStatus = "lecturer fail";
                break;
            }
        }
        
        //all files are saved, can proceed to create account
        if ($allFilesUploaded) {
            //insert into lecturer_applicant and user tables
            $stmt = $conn->prepare("INSERT INTO user (user_email, name, password, dob, role) VALUES (?, ?, ?, ?, ?);");
            $stmt->bind_param("sssss", $email, $name, $hashedPassword, $dob, $role);
            $result = $stmt->execute();

            if ($result) {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO lecturer_applicant (teaching_exp, current_uni_name, job_title, application_status, user_email) VALUES (?, ?, ?, ?, ?);");
                $stmt->bind_param("sssss", $exp, $uni, $job, $status, $email);
                $result = $stmt->execute();

                if ($result) {
                    $docStatus = true;
                    $stmt->close();
                    $stmt = $conn->prepare("SELECT applicant_id FROM lecturer_applicant WHERE user_email = ?;");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $exe = $stmt->get_result();
                    $result = $exe->fetch_assoc();
                    $appId = $result["applicant_id"];
                    $stmt->close();

                    foreach ($uploadedPdfPaths as $pdf) {
                        $path = $pdf["path"];
                        $pathName = $pdf["name"];

                        $pathStmt = $conn->prepare("INSERT INTO applicant_document (directory, doc_name, applicant_id) VALUES (?, ?, ?);");
                        $pathStmt->bind_param("ssi", $path, $pathName, $appId);
                        $pathResult = $pathStmt->execute();
                        $pathStmt->close();
                        if (!$pathResult) {
                            $docStatus = false;
                        }
                    }

                    if ($result && $docStatus) {
                        $signupStatus = "lecturer success";
                        //saved pdf in the applicant_document folder ++ inserted all details in database
                    } else {
                        //saved failed
                        $signupStatus = "lecturer fail";
                        //delete incomplete user
                        $stmt = $conn->prepare("DELETE FROM user WHERE user_email = ?");
                        $stmt->bind_param("s", $email);
                        $result = $stmt->execute();
                        $stmt->close();
                        //delete incomplete user's application
                        $stmt = $conn->prepare("DELETE FROM lecturer_applicant WHERE user_email = ?");
                        $stmt->bind_param("s", $email);
                        $result = $stmt->execute();
                        $stmt->close();
                        //delete incomplete applicant documents
                        $stmt = $conn->prepare("DELETE FROM applicant_document WHERE applicant_id = ?");
                        $stmt->bind_param("i", $appId);
                        $result = $stmt->execute();
                        $stmt->close();
                    }
                } else {
                    $stmt->close();
                    //delete incomplete user
                    $signupStatus = "lecturer fail";
                    $stmt = $conn->prepare("DELETE FROM user WHERE user_email = ?");
                    $stmt->bind_param("s", $email);
                    $result = $stmt->execute();
                    $stmt->close();
                }
            } else {
                $signupStatus = "lecturer fail";
            }
        } else {
            $signupStatus = "lecturer fail";
        }
    }

    else if ($formType === "student") {
        $role = "student";
        $stmt = $conn->prepare("INSERT INTO user (user_email, name, password, dob, role) VALUES (?, ?, ?, ?, ?);");
        $stmt->bind_param("sssss", $email, $name, $hashedPassword, $dob, $role);
        $result = $stmt->execute();

        if ($result) {
            $stmt->close();
            $edu = $_POST["education"];
            $learn = $_POST["learning"];

            $stmt = $conn->prepare("INSERT INTO student (edu_level, learning_style, user_email) VALUES (?, ?, ?);");
            $stmt->bind_param("sss", $edu, $learn, $email);
            $result = $stmt->execute();

            if ($result) {
                $studentId = $stmt->insert_id;
                $stmt->close();

                $json = $_POST['fieldExpInput'];
                $fieldExp = json_decode($json, true);
                $selectedFields = json_decode($_POST['selectedFields'], true); // true = return as array

                $stmt = $conn->prepare("INSERT INTO field_preference (student_id, field_id, experience) VALUES (?, ?, ?)");
                $fieldId = (int) $fieldId;
                $stmt->bind_param("iis", $studentId, $fieldId, $exp);

                foreach ($fieldExp as $fieldId => $exp) {
                    $stmt->execute();
                }

                $stmt->close();
                $json = $_POST['careerExpInput'];
                $careerExp = json_decode($json, true);
                $selectedCareers = json_decode($_POST['selectedCareers'], true); // true = return as array

                $stmt = $conn->prepare("INSERT INTO career_preference (student_id, career_id, experience) VALUES (?, ?, ?)");
                $careerId = (int) $careerId;
                $stmt->bind_param("iis", $studentId, $careerId, $exp);

                foreach ($careerExp as $careerId => $exp) {
                    $stmt->execute();
                }

                $stmt->close();

                var_dump($_POST['fieldExpInput'], $_POST['careerExpInput']);
                var_dump($fieldExp, $careerExp);

            } else {
                $stmt->close();
                //delete incomplete user
                $signupStatus = "lecturer fail";
                $stmt = $conn->prepare("DELETE FROM user WHERE user_email = ?");
                $stmt->bind_param("s", $email);
                $result = $stmt->execute();
                $stmt->close();
            }

            $selectedCareers = json_decode($_POST['selectedCareers'], true); // true = return as array

            //loop through all careers array
            foreach ($selectedCareers as $careerId) {
                echo "Selected career ID: " . (int)$careerId . "<br>";
            }
        } else {
            $signupStatus = "student fail";
        }
    }
    $conn->close();
    session_start();
    $_SESSION["signupStatus"] = $signupStatus;
    header("Location: index.php?signupStatus=".$signupStatus);
}
?>