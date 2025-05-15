<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email']) && !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$section_id = $_SESSION['section_id'];
$section_ids = $_SESSION['section_id'];
$course_id = $_SESSION['course_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_question_id"])) {
    $delete_question_id = $_POST["delete_question_id"];
    $sql = "DELETE FROM question WHERE question_id = '$delete_question_id'";
    $result = mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_answer_id"])) {
    $delete_answer_id = $_POST["delete_answer_id"];
    $sql = "DELETE FROM answer WHERE answer_id = '$delete_answer_id'";
    $result = mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_section_id"])) {
    $delete_section_id = $_POST["delete_section_id"];
    $sql = "DELETE FROM section WHERE section_id = '$delete_section_id'";
    $result = mysqli_query($conn, $sql);
    header("Location: lec_edit_sidebar.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["input_ques_type"])) {
    $question_type = $_POST["input_ques_type"];

    $sql = "INSERT INTO question (question_type, section_id) VALUES ('$question_type', '$section_id');";
    mysqli_query($conn, $sql);

    $insert_question_id = mysqli_insert_id($conn);
    $sql = "INSERT INTO answer (accuracy, question_id) VALUES ('1', '$insert_question_id');";
    mysqli_query($conn, $sql);
    if($question_type == "mcq"){
        for($i = 0; $i < 3; $i++){
            $sql = "INSERT INTO answer (accuracy, question_id) VALUES ('0', '$insert_question_id');";
            mysqli_query($conn, $sql);
        }
    }
    
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["file_name_post"])) {
    $file_name_post = $_POST['file_name_post'];
    $file_desc_post = $_POST['file_desc_post'];

    $sql = "UPDATE section SET section_title = '$file_name_post', subtopic_description = '$file_desc_post' WHERE section_id = '$section_id'";
    mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ques_text"])) {
    $ques_photo = [];
    $img_folder = "lec_uploads/";
    // Loop through all uploaded images
    for ($i = 0; $i < count($_FILES['ques_photo']['name']); $i++) {
        $img_name = $_FILES['ques_photo']['name'][$i];
        $img_tmp = $_FILES['ques_photo']['tmp_name'][$i];

        if (!empty($img_name)) {
            // Create a unique file name
            $img_new_name = time() . "_" . $i . "_" . basename($img_name);
            $target_path = $img_folder . $img_new_name;

            // Move uploaded file to target folder
            if (move_uploaded_file($img_tmp, $target_path)) {
                $ques_photo[] = $target_path; // Store file path into array
            } else {
                $ques_photo[] = $_POST['existing_media_url'][$i];
            }
        } else {
            $ques_photo[] = $_POST['existing_media_url'][$i];
        }
    }
    
    //arrays
    $question_id = $_POST['question_id'];
    $ques_text = $_POST['ques_text'];
    $ques_type = $_POST['ques_type'];
    $answer_id = [];
    $option = [];
    $answer = [];
    for($i = 0; $i < count($question_id); $i++){
        $answer_id[$i] = $_POST['answer_id' . $i];
        $option[$i] = $_POST['option' . $i];
        $answer[] = $_POST['answer' . $i];
    }

    for($i = 0; $i < count($question_id); $i++){
        $sql = "UPDATE question SET question_text = '{$ques_text[$i]}', media_url = '{$ques_photo[$i]}' WHERE question_id = '{$question_id[$i]}'";
        mysqli_query($conn, $sql);

        if($ques_type[$i] == "mcq"){
            for($j = 0; $j < count($answer_id[$i]); $j++){
                if($answer_id[$i][$j] == "new"){
                    if($j == $answer[$i]){
                        $accuracy = 1;
                    }else{
                        $accuracy = 0;
                    }
                    $sql = "INSERT INTO answer (answer, accuracy, question_id) VALUES ('{$option[$i][$j]}', '$accuracy', '{$question_id[$i]}')";
                    mysqli_query($conn, $sql);
                }else{
                    if($j == $answer[$i]){
                        $accuracy = 1;
                    }else{
                        $accuracy = 0;
                    }
                    $sql = "UPDATE answer SET answer = '{$option[$i][$j]}', accuracy = '$accuracy' WHERE answer_id = '{$answer_id[$i][$j]}'";
                    mysqli_query($conn, $sql);
                }
            }
        }else if($ques_type[$i] == "saq"){
            $sql = "UPDATE answer SET answer = '{$answer[$i]}', accuracy = '1' WHERE answer_id = '{$answer_id[$i][0]}'";
            mysqli_query($conn, $sql);
        }
    }
}

$sql = "SELECT * FROM section WHERE section_id = '$section_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$section_title_post = $row["section_title"];
$section_desc = $row["subtopic_description"];

$question_id = [];
$question_text = [];
$media_url = [];
$question_type = [];
$sql = "SELECT * FROM question WHERE section_id = '$section_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $question_id[] = $row["question_id"];
    $question_text[] = $row["question_text"];
    $media_url[] = $row["media_url"];
    $question_type[] = $row["question_type"];
}

if(count($question_id) <= 1){
    $approve_question = "no";
}else{
    $approve_question = "yes";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morning Quiznos</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <link rel="stylesheet" href="lec_quiz.css">
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

    <main>
        <?php include "lec_edit_sidebar.php"?>

        <section class="edit_course">
            <div class="title">
                <button class="back" title="Back" onclick="back(<?php echo $course_id; ?>)">Back</button>
                <h1 class="title">Edit Course</h1>
            </div>

            <form class="file_detail" method="post" enctype="multipart/form-data">

                <div class="manage_file">
                    <button type="submit" class="manage_file_btn" title="Save"><img src="images/save.png" alt="Save" width="20px" height= "20px"></button>
                    
                    <button type="button" class="manage_file_btn" title="Delete" onclick="delete_subfile('<?php echo $section_ids; ?>', '<?php echo $approve_section; ?>')"><img src="images/delete.png" alt="Delete" width="20px" height= "20px"></button>
                </div>

                <div class="file_title">
                    <input type="text" name="file_name_post" class="file_name" value="<?php echo $section_title_post;?>" placeholder="Enter Quiz Name" required>
                    <input type="text" name="file_desc_post" class="file_desc" value="<?php echo $section_desc;?>" placeholder="Enter Quiz Description" required>
                </div>

                <div class="quiz_area">
                    <?php
                    for ($i = 0; $i < count($question_id); $i++) {
                        echo '
                        <div class="quiz_box">
                            <button type="button" class="manage_ques_btn" title="Delete Question" onclick="delete_question('.$question_id[$i].', \''.$approve_question.'\')"><img src="images/delete.png" alt="Delete Question" width="20px" height= "20px"></button>
                            <div class="ques_title">';
                                if(!empty($media_url[$i])){
                                    echo '<img src="'.$media_url[$i].'" alt="Uploaded Image" class="uploaded_image">';
                                }
                                echo '
                                <p class="quiz_num">'.($i + 1).'</p>
                                <input type="file" accept="image/*" name="ques_photo[]" class="ques_photo">
                                <input type="text" name="ques_text[]" class="ques_text" value="'.$question_text[$i].'" placeholder="Enter Question Text" required>
                                <input type="hidden" name="existing_media_url[]" value="'.$media_url[$i].'">
                                <input type="hidden" name="question_id[]" value="'.$question_id[$i].'">
                            </div>';
                        
                        $answer_id = [];
                        $answer_text = [];
                        $accuracy = [];
                        $sql = "SELECT * FROM answer WHERE question_id = '{$question_id[$i]}'";
                        $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $answer_id[] = $row["answer_id"];
                            $answer_text[] = $row["answer"];
                            $accuracy[] = $row["accuracy"];
                        }

                        if(count($answer_id) <= 2){
                            $approve_answer = "no";
                        }else{
                            $approve_answer = "yes";
                        }

                        if($question_type[$i] == "mcq") {
                            echo '<input type="hidden" name="ques_type[]" value="mcq">';
                            for($j = 0; $j < count($answer_id); $j++){
                                echo '
                                <div class="option_area" data-option="ques'.$i.'">
                                    <input type="radio" name="answer'.$i.'" value="'.$j.'" '.($accuracy[$j] == 1 ? 'checked' : '').' required>
                                    <input type="text" name="option'.$i.'[]" class="option_box" value="'.$answer_text[$j].'" placeholder="Enter Option Text" required>
                                    <input type="hidden" name="answer_id'.$i.'[]" value="'.$answer_id[$j].'">
                                    <button type="button" class="option_delete" title="Delete Option" onclick="delete_option('.$answer_id[$j].', \''.$approve_answer.'\')">Delete</button>
                                </div>';
                            }
                            echo '
                            <button type="button" class="add_option" data-add="ques'.$i.'" title="Add Option" onclick="add_option('.$j.', \'ques'.$i.'\', '.$i.')">+ Add Option</button>';
                        }else if($question_type[$i] == "saq") {
                            echo '
                            <input type="hidden" name="ques_type[]" value="saq">
                            <p class="stu_ans_box">Student will type their answer here</p>
                            <p class="option_title">Correct Answers:</p>
                            <input type="text" name="answer'.$i.'" class="ans_box" value="'.$answer_text[0].'" placeholder="Enter Correct Answer" required>
                            <input type="hidden" name="answer_id'.$i.'[]" value="'.$answer_id[0].'">
                            <input type="hidden" name="option'.$i.'[]" value="">';
                        }
                        echo '</div>';
                    }
                    echo '<button type="button" class="add_ques_btn" title="Add Question" onclick="add_question()"><img src="images/add.png" alt="Add Question" width="20px" height= "20px"></button>';
                    ?>


                </div>

            </form>

            <script>

                function delete_question(question_id, approve_question){
                    let course_area = document.getElementsByClassName("edit_course")[0];
                    if(approve_question == "no"){
                        course_area.insertAdjacentHTML("afterend", `
                            <div class="box_overlay">
                                <form class="box" method="post">
                                    <h2 class="remove_title">At least one question must included</h2>
                                    <div class="box_btn_area">
                                        <button type="button" class="box_btn" onclick="no()">OK</button>
                                    </div>
                                </form>
                            </div>
                        `);
                        document.getElementsByClassName("box_overlay")[0].style.display = "flex";
                    }else{
                        course_area.insertAdjacentHTML("afterend", `
                            <form id="delete_form" method="POST" style="display: none;">
                                <input type="hidden" name="delete_question_id" value="${question_id}">
                            </form>
                        `);
                        document.getElementById("delete_form").submit();
                    }
                }

                function delete_option(answer_id, approve_answer){
                    let course_area = document.getElementsByClassName("edit_course")[0];
                    if(approve_answer == "no"){
                        course_area.insertAdjacentHTML("afterend", `
                            <div class="box_overlay">
                                <form class="box" method="post">
                                    <h2 class="remove_title">At least two options must included</h2>
                                    <div class="box_btn_area">
                                        <button type="button" class="box_btn" onclick="no()">OK</button>
                                    </div>
                                </form>
                            </div>
                        `);
                        document.getElementsByClassName("box_overlay")[0].style.display = "flex";
                    }else{
                        course_area.insertAdjacentHTML("afterend", `
                            <form id="delete_form" method="POST" style="display: none;">
                                <input type="hidden" name="delete_answer_id" value="${answer_id}">
                            </form>
                        `);
                        document.getElementById("delete_form").submit();
                    }
                }

                function add_option(index, ques, ques_num) {
                    let add_btn = document.querySelector(`[data-add="${ques}"]`);
                    add_btn.style.display = "none";

                    let last_option = document.querySelectorAll(`[data-option="${ques}"]`)[index - 1];
                    last_option.insertAdjacentHTML("afterend", `
                        <div class="option_area" data-option="${ques}">
                            <input type="radio" name="answer${ques_num}" value="${index}" required>
                            <input type="text" name="option${ques_num}[]" class="option_box" required>
                            <input type="hidden" name="answer_id${ques_num}[]" value="new">
                            <button type="button" class="option_delete" title="Delete Option" onclick="cancel_option(${index}, '${ques}')">Delete</button>
                        </div>
                    `);
                }

                function cancel_option(index, ques) {
                    let option = document.querySelectorAll(`[data-option="${ques}"]`)[index];
                    if (option) option.remove();

                    let add_btn = document.querySelector(`[data-add="${ques}"]`);
                    if (add_btn) add_btn.style.display = "flex";
                }

                function add_question(){
                    setTimeout(() => {
                        let quiz_form = document.getElementsByClassName("file_detail")[0];
                        let add_ques_btn = document.getElementsByClassName("add_ques_btn")[0];
                        add_ques_btn.style.display = "none";
                        quiz_form.insertAdjacentHTML("afterend", `
                            <form class="create_ques" method="post">
                                <label class="input_ques_type"><input type="radio" name="input_ques_type" value="mcq" required>MCQ</label>
                                <label class="input_ques_type"><input type="radio" name="input_ques_type" value="saq" required>SAQ</label>
                            </form>
                        `);
                    }, 0);
                }

                document.addEventListener("click", function(event) {                    
                    let create_ques = document.getElementsByClassName("create_ques")[0];
                    if (create_ques && !create_ques.contains(event.target)) {
                        let radios = create_ques.querySelectorAll('input[name="input_ques_type"]');
                        let selected = false;
                        radios.forEach(radio => {
                            if (radio.checked) {
                                selected = true;
                            }
                        });

                        let add_ques_btn = document.getElementsByClassName("add_ques_btn")[0];
                        if (selected) {
                            create_ques.submit();
                        } else {
                            create_ques.remove();
                            add_ques_btn.style.display = "flex";
                        }
                    }
                });

            </script>
        </section>
    </main>

</body>

</html>