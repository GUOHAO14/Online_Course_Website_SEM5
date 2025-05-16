<?php
session_start();
if (!isset($_SESSION['user_email']) || !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$proposal_id = $_GET['proposal_id'];
$status = $_GET['status'];

$lecturer_id = $_SESSION["lecturer_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_proposal"])) {
    $cancel_proposal = $_POST['cancel_proposal'];
    $sql = "DELETE FROM course_proposal WHERE proposal_id = '$cancel_proposal'";
    $result = mysqli_query($conn, $sql);
    header("Location: lec_activity.php");
    exit();
}

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
    }else{
        $cover_img = $_POST['existing_cover_img'];
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

    $sql = "UPDATE course_proposal SET title = '$course_title', `description` = '$course_desc', cover_img_url = '$cover_img', course_style = '$course_style', difficulty = '$difficulty', scope = '$scope', objective = '$objective', completion_time = '$completion_time', submit_date = '$submit_date', approval_status = 'Pending', lecturer_id = '$lecturer_id' WHERE proposal_id = '$proposal_id'";
    mysqli_query($conn, $sql);

    $sql = "DELETE FROM course_field WHERE proposal_id = '$proposal_id'";
    $result = mysqli_query($conn, $sql);

    foreach ($fields as $field) {
        $sql = "INSERT INTO course_field (proposal_id, field_id) VALUES ('$proposal_id', '$field');";
        mysqli_query($conn, $sql);
    }
}

$sql = "SELECT * FROM field";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $field_id[] = $row["field_id"];
    $field_name[] = $row["name"];
}

$sql = "SELECT * FROM course_proposal WHERE proposal_id = '$proposal_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$course_name = $row["title"];
$course_desc = $row["description"];
$cover_img = $row["cover_img_url"];
$course_style = $row["course_style"];
$difficulty = $row["difficulty"];
$scope = $row["scope"];
$objective = $row["objective"];
$completion_time = $row["completion_time"];

$sql = "SELECT * FROM course_field WHERE proposal_id = '$proposal_id'";
$result = mysqli_query($conn, $sql);
$selected_field_id = [];
while ($row = mysqli_fetch_assoc($result)) {
    $selected_field_id[] = $row["field_id"];
}

$selected_field_name = [];
for($i = 0; $i < count($selected_field_id); $i++){
    $sql = "SELECT * FROM field WHERE field_id = '$selected_field_id[$i]'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $selected_field_name[] = $row["name"];
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
            <h1 class="title">Proposal</h1>
        </div>

        <form class="proposal" method="post" enctype="multipart/form-data" onsubmit="return validateEditorInputs()">
            <?php
            if(!empty($cover_img)){
                echo '<img src="'.$cover_img.'" alt="Cover Image" class="cover_image">';
            }
            ?>
            <div class="course_title_area">
                <p class="input_title1">Course Name:</p>
                <input type="text" name="course_title" class="course_title" value="<?php echo $course_name; ?>" required>
                <div class="cover_img_area">
                    <?php
                    echo '
                    <input type="hidden" name="existing_cover_img" value="'.$cover_img.'">';
                    ?>
                    <p class="input_title3">Select Cover Image:</p>
                    <input type="file" accept="image/*" name="cover_img" class="cover_img">
                </div>
            </div>

            <p class="input_title2">Description:</p>
            <textarea name="course_desc" class="course_desc" rows="3" required><?php echo $course_desc; ?></textarea>
            
            <div class="selection_area">
                <select name="course_style" class="course_style" required>
                    <option value="Visual" <?php echo ($course_style == "Visual" ? 'selected' : '') ?>>Visual</option>
                    <option value="Audio" <?php echo ($course_style == "Audio" ? 'selected' : '') ?>>Audio</option>
                    <option value="Text-Based" <?php echo ($course_style == "Text-Based" ? 'selected' : '') ?>>Text-Based</option>
                    <option value="Mixed" <?php echo ($course_style == "Mixed" ? 'selected' : '') ?>>Mixed</option>
                </select>
                <select name="difficulty" class="difficulty" required>
                    <option value="beginner" <?php echo ($difficulty == "beginner" ? 'selected' : '') ?>>Beginner</option>
                    <option value="intermediate" <?php echo ($difficulty == "intermediate" ? 'selected' : '') ?>>Intermediate</option>
                    <option value="advanced" <?php echo ($difficulty == "advanced" ? 'selected' : '') ?>>Advanced</option>
                </select>
                <div class="completion_time_area">
                    <p class="input_title3">Select Completion Time:</p>
                    <input type="number" name="completion_time" class="completion_time" value="<?php echo $completion_time; ?>" step="1" min="1" required>
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
                <textarea name="scope" id="scope_editor" class="scope_area"><?php echo $scope; ?></textarea>
            </div>

            <div class="editor_box">
                <p class="input_title2">Objective:</p>
                <textarea name="objective" id="objective_editor" class="objective_area"><?php echo $objective; ?></textarea>
            </div>

            <div class="proposal_btn">
                <button type="submit" class="submit_proposal_btn" title="Resubmit Proposal">Resubmit</button>
                <?php
                if($status == "pending"){
                    echo'
                    <button type="button" class="submit_proposal_btn" title="Cancel Proposal" onclick="cancel('.$proposal_id.')">Cancel</button>';
                }
                ?>
            </div>
        </form>

    </section>

    <script>
        function cancel(proposal_id){
            let create_proposal = document.getElementsByClassName("create_proposal")[0];
            create_proposal.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" method="POST" style="display: none;">
                    <input type="hidden" name="cancel_proposal" value="${proposal_id}">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }

        function back(){
            window.location.href = "lec_activity.php"; 
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

        function addField(fieldId, fieldName) {
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

            const hiddenInput = document.createElement('input');
            hiddenInput.type = "hidden";
            hiddenInput.name = "selected_fields[]";
            hiddenInput.value = fieldId;
            fieldBox.appendChild(hiddenInput);

            insertFieldDiv.appendChild(fieldBox);
            fieldSelector.selectedIndex = 0;
        }

        fieldSelector.addEventListener('change', () => {
            const fieldId = fieldSelector.value;
            const fieldName = fieldSelector.options[fieldSelector.selectedIndex].dataset.name;
            if (fieldId) {
                addField(fieldId, fieldName);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const presetFields = [
                <?php
                for($i = 0; $i < count($selected_field_id); $i++){
                    echo '{ id: "'.$selected_field_id[$i].'", name: "'.$selected_field_name[$i].'" },';
                }
                ?>
            ];

            presetFields.forEach(field => {
                addField(field.id, field.name);
            });
        });

    </script>

</body>

</html>