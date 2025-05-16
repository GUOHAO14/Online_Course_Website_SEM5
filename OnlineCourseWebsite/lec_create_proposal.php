<?php
session_start();
if (!isset($_SESSION['user_email']) && !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$lecturer_id = $_SESSION["lecturer_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["course_title"])) {
    $cover_img = null;
    $img_folder = "img/";

    if (!empty($_FILES['cover_img']['name'])) {
        $img_name = $_FILES['cover_img']['name'];
        $img_tmp = $_FILES['cover_img']['tmp_name'];

        // Create a unique file name
        $img_new_name = time() . "_" . basename($img_name);
        $target_path = $img_folder . $img_new_name;

        // Move uploaded file to target folder
        if (move_uploaded_file($img_tmp, $target_path)) {
            $cover_img = $target_path; // Store single image path
        }
    }
    
    $course_title = $_POST['course_title'];
    $course_desc = $_POST['course_desc'];
    $course_style = $_POST['course_style'];
    $difficulty = $_POST['difficulty'];
    $completion_time = $_POST['completion_time'];
    $fields = $_POST['selected_fields'];

    $scope = $_POST['scope'];
    $objective = $_POST['objective'];
    $submit_date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO course_proposal (title, `description`, cover_img_url, course_style, difficulty, scope, objective, completion_time, submit_date, approval_status, lecturer_id) VALUES ('$course_title', '$course_desc', '$cover_img', '$course_style', '$difficulty', '$scope', '$objective', '$completion_time', '$submit_date', 'Pending', '$lecturer_id');";
    mysqli_query($conn, $sql);

    $insert_proposal_id = mysqli_insert_id($conn);
    foreach ($fields as $field) {
        $sql = "INSERT INTO course_field (proposal_id, field_id) VALUES ('$insert_proposal_id', '$field');";
        mysqli_query($conn, $sql);
    }
}

$sql = "SELECT * FROM field";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $field_id[] = $row["field_id"];
    $field_name[] = $row["name"];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morning Quiznos</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <link rel="stylesheet" href="lec_proposal.css">
    <script src="https://cdn.tiny.cloud/1/h2rdedpwtxsl5c6e3ldciuieszav8emm9x53pge4rda87s7k/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 10000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* optional nice effect */
        }

        body {
            position: relative; 
            padding-top: 7rem;
        }

        .whiteSectionBg {
            background-color: white;
            padding: 2.5rem 0;
            margin: 0 0 2.5rem 0;
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
    <section class="create_proposal">
        <div class="title">
            <button class="back" title="Back" onclick="back()">Back</button>
            <h1 class="title">Create Proposal</h1>
        </div>

        <form class="proposal" method="post" enctype="multipart/form-data" onsubmit="return validateEditorInputs()">
            <div class="course_title_area">
                <p class="input_title1">Course Name:</p>
                <input type="text" name="course_title" class="course_title" placeholder="Please Enter Course Name" required>
                <div class="cover_img_area">
                    <p class="input_title3">Select Cover Image:</p>
                    <input type="file" accept="image/*" name="cover_img" class="cover_img" required>
                </div>
            </div>

            <p class="input_title2">Description:</p>
            <textarea name="course_desc" class="course_desc" rows="3" placeholder="Please Enter Course Description" required></textarea>

            <div class="selection_area">
                <select name="course_style" class="course_style" required>
                    <option value="" disabled selected>Select Course Style</option>
                    <option value="Visual">Visual</option>
                    <option value="Audio">Audio</option>
                    <option value="Text-Based">Text-Based</option>
                    <option value="Mixed">Mixed</option>
                </select>
                <select name="difficulty" class="difficulty" required>
                    <option value="" disabled selected>Select Course Difficulty</option>
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
                <div class="completion_time_area">
                    <p class="input_title3">Select Completion Time:</p>
                    <input type="number" name="completion_time" class="completion_time" placeholder="Minutes" step="1" min="1" required>
                </div>
            </div>

            <div class="insert_field"></div>

            <select id="field_selector">
                <option value="" disabled selected>Select a field</option>
                <?php
                for($i = 0; $i < count($field_id); $i++){
                    echo '<option value="'.$field_id[$i].'" data-name="'.$field_name[$i].'">'.$field_name[$i].'</option>';
                }
                ?>
            </select>

            <div class="editor_box">
                <p class="input_title2">Scope:</p>
                <textarea name="scope" id="scope_editor" class="scope_area"></textarea>
            </div>

            <div class="editor_box">
                <p class="input_title2">Objective:</p>
                <textarea name="objective" id="objective_editor" class="objective_area"></textarea>
            </div>

            <button type="submit" class="submit_proposal_btn2" title="Submit Proposal"><img src="images/save.png" alt="Submit" width="20px" height= "20px">Submit Proposal</button>
        </form>

    </section>

    <script>
        function back(){
            window.location.href = "lec_home.php"; 
        }

        tinymce.init({
            selector: ".scope_area",
            menubar: false,
            height: 300,
            width: 900,
            toolbar_sticky: true,
            toolbar: "undo redo | bullist numlist",
            plugins: "lists",
            content_style: "body { font-size: 14px; font-family: Arial; }",
        });

        tinymce.init({
            selector: ".objective_area",
            menubar: false,
            height: 300,
            width: 900,
            toolbar_sticky: true,
            toolbar: "undo redo | bullist numlist",
            plugins: "lists",
            content_style: "body { font-size: 14px; font-family: Arial; }",
        });

        function validateEditorInputs() {
            let scopeContent = tinymce.get("scope_editor").getContent({ format: "text" }).trim();
            let objectiveContent = tinymce.get("objective_editor").getContent({ format: "text" }).trim();
            let courseTitleInput = document.getElementsByClassName("course_title")[0];
            let course_title = courseTitleInput.value.trim();
            let selectedFields = document.querySelectorAll('input[name="selected_fields[]"]');

            if (scopeContent === "" || objectiveContent === "" || course_title === "") {
                alert("Please fill in all the input");
                return false;
            }

            if (/^\d+$/.test(course_title)) {
                alert("Course Title cannot be only numbers");
                return false;
            }

            if (selectedFields.length === 0) {
                alert("Please select at least one field.");
                document.querySelector('.insert_field').style.outline = '2px solid red';
                return false;
            } else {
                document.querySelector('.insert_field').style.outline = 'none';
            }

            return true;
        }

        const maxFields = 10;
        const selectedFields = new Set();
        const insertFieldDiv = document.querySelector('.insert_field');
        const fieldSelector = document.getElementById('field_selector');

        fieldSelector.addEventListener('change', () => {
            const fieldId = fieldSelector.value;
            const fieldName = fieldSelector.options[fieldSelector.selectedIndex].dataset.name;

            if (selectedFields.has(fieldId)) {
                alert("This field is already selected.");
                return;
            }

            if (selectedFields.size >= maxFields) {
                alert(`You can select a maximum of ${maxFields} fields.`);
                fieldSelector.selectedIndex = 0;
                return;
            }

            selectedFields.add(fieldId);

            const fieldBox = document.createElement('div');
            fieldBox.className = 'field_tag';
            fieldBox.textContent = fieldName;

            const removeBtn = document.createElement('span');
            removeBtn.textContent = " ×";
            removeBtn.className = "remove_field";
            removeBtn.onclick = () => {
                selectedFields.delete(fieldId);
                insertFieldDiv.removeChild(fieldBox);
            };

            fieldBox.appendChild(removeBtn);
            insertFieldDiv.appendChild(fieldBox);

            // Hidden input for form submission
            const hiddenInput = document.createElement('input');
            hiddenInput.type = "hidden";
            hiddenInput.name = "selected_fields[]";
            hiddenInput.value = fieldId;
            fieldBox.appendChild(hiddenInput);

            fieldSelector.selectedIndex = 0;
        });

    </script>

</body>

</html>