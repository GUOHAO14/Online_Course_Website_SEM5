<?php
session_start();
if (!isset($_SESSION['user_email']) || !isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$course_id = $_SESSION["course_id"];
$enrol_id = $_SESSION["enrol_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["selected_section_id"])) {
    $selected_section_id = $_POST['selected_section_id'];
    $_SESSION["selected_section_id"] = $selected_section_id;

    $sql = "SELECT * FROM section WHERE section_id = '$selected_section_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $selected_section_title = $row["section_title"];
    $selected_section_desc = $row["subtopic_description"];
    $selected_section_type = $row["type"];
}else{
    $sql = "SELECT * FROM chapter WHERE course_id = '$course_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $selected_chapter_id = $row["chapter_id"];

    $sql = "SELECT * FROM section WHERE chapter_id = '$selected_chapter_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $selected_section_id = $row["section_id"];
    $selected_section_title = $row["section_title"];
    $selected_section_desc = $row["subtopic_description"];
    $selected_section_type = $row["type"];

    $_SESSION["selected_section_id"] = $selected_section_id;
}

$sql = "SELECT * FROM progression WHERE enrol_id = '$enrol_id' and section_id = '$selected_section_id' and progress = '1'";
$result = mysqli_query($conn, $sql);
if($selected_section_type == "content"){
    $sql = "UPDATE progression SET progress = '1' WHERE enrol_id = '$enrol_id' and section_id = '$selected_section_id'";
    mysqli_query($conn, $sql);
}else if($selected_section_type == "question"){
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["end"])) {
    
    }else{
        if(mysqli_fetch_assoc($result)){
            header("Location: end_question.php?end=yes");
            exit();
        }
    }
}

$chapter_id = [];
$chapter_title = [];
$sql = "SELECT * FROM chapter WHERE course_id = '$course_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $chapter_id[] = $row["chapter_id"];
    $chapter_title[] = $row["chapter_title"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morning Quiznos</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <link rel="stylesheet" href="stu_section.css">
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
        <?php include "header.php";?>
    </header>

    <main>
        <section class="sidebar">
            <div class="hide" onclick="hide_menu()"><img src="images/menu.png" class="hide" alt="Hide Menu" width="20px" height="20px">Hide Menu</div>

            <?php
            for ($i = 0; $i < count($chapter_id); $i++) {
                echo '
                <div class="file">
                    <p class="filename_side" onclick="open_chapter('.$chapter_id[$i].')">'.$chapter_title[$i].'</p>
                </div>';
    
                $section_id = [];
                $section_title = [];
                $section_type = [];
                $sql = "SELECT * FROM section WHERE chapter_id = '$chapter_id[$i]'";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $section_id[] = $row["section_id"];
                    $section_title[] = $row["section_title"];
                    $section_type[] = $row["type"];
                }
    
                for ($j = 0; $j < count($section_id); $j++) {
                    echo '
                    <div class="subfile" data-file="'.$chapter_id[$i].'">
                        <p class="subfilename_side" onclick="open_section('.$section_id[$j].', \''.$section_type[$j].'\')">'.$section_title[$j].'</p>
                    </div>';
                }   
            }
            ?>
        </section>

        <section class="section_area">
            <div class="title">
                <button class="back" title="Back" onclick="back()">Back to Home</button>
                <h1 class="title">Section</h1>
            </div>

            <?php
            echo '
            <div class="section_title_area">
                <h2 class="section_title">'.$selected_section_title.'</h2>
                <p class="section_desc">'.$selected_section_desc.'</p>
            </div>';

            if($selected_section_type == "question"){
                $question_id = [];
                $question_text = [];
                $ques_photo = [];
                $question_type = [];
                $sql = "SELECT * FROM question WHERE section_id = '$selected_section_id'";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $question_id[] = $row["question_id"];
                    $question_text[] = $row["question_text"];
                    $ques_photo[] = $row["media_url"];
                    $question_type[] = $row["question_type"];
                }

                echo '<form class="qform" action="end_question.php" method="post">';
                for($i = 0; $i < count($question_id); $i++){
                    $sql = "SELECT * FROM answer WHERE question_id = '".$question_id[$i]."'";
                    $result = mysqli_query($conn, $sql);
                    $answer_id = [];
                    $answer_text = [];
                    $accuracy = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $answer_id[] = $row["answer_id"];
                        $answer_text[] = $row["answer"];
                        $accuracy[] = $row["accuracy"];
                    }
                    echo '
                    <div class ="ques_area" id="'.$question_id[$i].'">';
                        if(!empty($ques_photo[$i])){
                            echo '<img src="'.$ques_photo[$i].'" alt="Uploaded Image" class="uploaded_image">';
                        }
                        echo '
                        <p class="question">'.($i+1).') '.$question_text[$i].'</p>';

                        if(count($answer_id) > 1){
                            for($j = 0; $j < count($answer_id); $j++){
                                echo '
                                <label class="mcq">
                                    <input type="radio" name="'.$question_id[$i].'" value="'.$answer_id[$j].'" class="mcq" required> '.$answer_text[$j].'
                                </label>';
                            }
                        }else if(count($answer_id) == 1){
                            echo '
                            <input type="text" name="'.$question_id[$i].'" class="saq" placeholder="Type your answer" required>';
                        }
                    echo '
                    </div>';
                }

                echo '
                    <div class="manage_ques_btn">
                        <input class="reset" type="reset" value="Clear All" onclick="return con_clear()">
                        <input class="submit" type="submit" value="Submit" onclick="return con_submit()" id="submit">
                    </div>
                </form>';

            }else if($selected_section_type == "content"){
                $sql = "SELECT * FROM content WHERE section_id = '$selected_section_id'";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_assoc($result);
                $content = $row["content"];

                echo '
                <div class="content_area">
                    '.$content.'
                </div>';
                
                $all_section_id = [];
                for ($i = 0; $i < count($chapter_id); $i++) {
                    $sql = "SELECT * FROM section WHERE chapter_id = '$chapter_id[$i]'";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $all_section_id[] = $row["section_id"];
                    }
                }

                $nextValue = "no";
                foreach ($all_section_id as $index => $value) {
                    if ($value == $selected_section_id && isset($all_section_id[$index + 1])) {
                        $nextValue = $all_section_id[$index + 1];
                        break;
                    }
                }

                if ($nextValue !== "no") {
                    echo '<button class="next" title="Next" onclick="next('.$nextValue.')">Next</button>';
                } else {
                    $sql = "SELECT * FROM progression WHERE enrol_id = '$enrol_id' and progress = '1'";
                    $result = mysqli_query($conn, $sql);
                    $enrol_section_id = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $enrol_section_id[] = $row["section_id"];
                    }

                    $finish = "yes";
                    foreach ($all_section_id as $value) {
                        if (!in_array($value, $enrol_section_id)) {
                            echo '<button class="next" title="Finish" onclick="haven_finish('.$value.')">Finish</button>';
                            $finish = "no";
                            break;
                        }
                    }
                    if($finish == "yes"){
                        echo '<button class="next" title="Finish" onclick="finish()">Finish</button>';
                    }
                }
            }
            ?>
        </section>
    </main>

    <script>
        function next(next_section_id){
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" method="POST" style="display: none;">
                    <input type="hidden" name="selected_section_id" value="${next_section_id}">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }

        function haven_finish(haven_section_id){
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <div class="box_overlay">
                    <div class="box">
                        <p class="nodone">U haven done the whole course</p>
                        <div class="box_btn_area">
                            <button type="button" class="box_btn" onclick="next(${haven_section_id})">OK</button>
                        </div>
                    </div>
                </div>
            `);
        }

        function finish(){
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <div class="box_overlay">
                    <div class="box">
                        <h2 class="remove_title">Congratulations ! ! !</h2>
                        <p class="done">U done the whole course !</p>
                        <div class="box_btn_area">
                            <button type="button" class="box_btn" onclick="back()">OK</button>
                        </div>
                    </div>
                </div>
            `);
        }

        function con_submit() {
            if (!window.confirm("Are you sure you want to submit the answers?")) {
                return false;
            }
            return true;
        }

        function con_clear() {
            if (!window.confirm("Are you sure you want to clear all the answers?")) {
                return false;
            }
            document.querySelector("form").reset();
            save_ans();
            return true;
        }

        function hide_menu() {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.style.display = "none";
        }

        function back() {
            window.location.href = 'stu-home.php';
        }

        function open_chapter(id) {
            let subfiles = Array.from(document.querySelectorAll('[data-file="' + id + '"]'));
            document.querySelectorAll(".subfile, .add_subfile").forEach(element => {
                if (subfiles.includes(element)) {
                    element.style.display = element.style.display === "flex" ? "none" : "flex";
                } else {
                    element.style.display = "none";
                }
            });
        }

        function open_section(section_id, section_type) {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" method="POST" style="display: none;">
                    <input type="hidden" name="selected_section_id" value="${section_id}">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }
    </script>

</body>
</html>