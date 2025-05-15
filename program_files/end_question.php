<?php
session_start();
if (!isset($_SESSION['user_email']) || !isset($_SESSION["student_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$course_id = $_SESSION["course_id"];
$enrol_id = $_SESSION["enrol_id"];
$selected_section_id = $_SESSION["selected_section_id"];

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["end"])) {
    $end = $_GET['end'];
}else{
    $end = 'no';
}

$sql = "SELECT * FROM progression WHERE enrol_id = '$enrol_id' and section_id = '$selected_section_id' and progress = '1'";
$result = mysqli_query($conn, $sql);
if(!mysqli_fetch_assoc($result)){
    $sql = "UPDATE progression SET progress = '1' WHERE enrol_id = '$enrol_id' and section_id = '$selected_section_id'";
    mysqli_query($conn, $sql);
}

$sql = "SELECT * FROM section WHERE section_id = '$selected_section_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$selected_section_title = $row["section_title"];
$selected_section_desc = $row["subtopic_description"];
$selected_section_type = $row["type"];

$chapter_id = [];
$chapter_title = [];
$sql = "SELECT * FROM chapter WHERE course_id = '$course_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $chapter_id[] = $row["chapter_id"];
    $chapter_title[] = $row["chapter_title"];
}

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

if($end == "no"){
    $correct_ans = [];
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

        if($question_type[$i] == "mcq"){
            for($j = 0; $j < count($answer_id); $j++){
                if($accuracy[$j] == "1"){
                    $correct_ans[] = $answer_id[$j];
                }
            }
        }else if($question_type[$i] == "saq"){
        $correct_ans[] = $answer_text[0]; 
        }
    }

    $user_score = [];
    for($i = 0; $i < count($question_id); $i++){
        if($_POST[$question_id[$i]] == $correct_ans[$i]){
            $user_score[] = 1;
        }else{
            $user_score[] = 0;
        }
    }

    $score = count(array_filter($user_score, function ($value) {
        return $value == 1;
    }));

    $percentage = round(($score / count($question_id)) * 100, 2);

    $sql = "INSERT INTO quiz_performance (`percentage`, enrol_id, section_id) VALUES ('$percentage', '$enrol_id', '$selected_section_id');";
    mysqli_query($conn, $sql);
}

$sql = "SELECT * FROM quiz_performance WHERE section_id = '$selected_section_id' and enrol_id = '$enrol_id'";
$result = mysqli_query($conn, $sql);
$quiz_performance_id = [];
$quiz_percentage = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quiz_performance_id[] = $row["quiz_perform_id"];
    $quiz_percentage[] = $row["percentage"];
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

            <div class="section_title_area">
                <h2 class="section_title"><?php echo $selected_section_title; ?></h2>
                <p class="section_desc"><?php echo $selected_section_desc; ?></p>
            </div>

            <div class="end-quiz">
                <div class="end-quiz-title">
                    <h2 class="end-quiz-title">Congratulations ! ! !</h2>
                    <p class="end-quiz-title">You successfully completed the quiz.</p>
                </div>
                
                <?php
                if($end == "no"){
                    echo '
                    <h2 class="result-title">Results</h2>

                    <div class="quiz-accuracy">
                        <p class="quiz-accuracy" style="background: linear-gradient(to right, #5ced73 '.$percentage.'%, red 0);">Well Done !</p>
                        <p class="percentage">'.$percentage.'%</p>
                    </div>';
                }
                ?>
                
                <h2 class="title1">Past Attempts</h2>
                <div class="past-attempt">
                    <table class="attempt">
                        <tr>
                            <th>No.</th>
                            <th colspan="3">Score</th>
                        </tr>

                    </table>
                    <button class="MoreResult" title="Show More" onclick="MoreResult()"><img src="images/down.png" alt="Show More" width="30px" height="30px"></button>
                </div> 

                <button class="re-attempt" title="Re-Attempt" onclick="start(<?php echo $selected_section_id; ?>)"><img src="images/run.png" alt="Run" width="30px" height="30px">Re-Attempt</button>

            </div>

            <?php
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

            if($end == "no"){
                echo '
                <div class="review">
                    <h2 class="result-title">Review Questions</h2>';

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

                        if($question_type[$i] == "mcq"){
                            if($user_score[$i] == 0){
                                echo '
                                <div class="bar">
                                    <div class="barw"></div>
                                    <div class ="review-area">
                                        <p class="question2">'.($i+1).') '.$question_text[$i].'</p>';
                                    for($j = 0; $j < count($answer_id); $j++){
                                        if($answer_id[$j] == $correct_ans[$i]){
                                            echo '
                                            <div class="mcq">
                                                <img src="images/correct.png" alt="Check Box" width="15px" height= "15px">
                                                <p>'.$answer_text[$j].'</p>
                                            </div>';
                                        }else if($answer_id[$j] == $_POST[$question_id[$i]]){
                                            echo '
                                            <p class="u-ans-w">Your Answer</p>
                                            <div class="mcq">
                                                <img src="images/wrong.png" alt="Check Box" width="15px" height= "15px">
                                                <p>'.$answer_text[$j].'</p>
                                            </div>';
                                        }else{
                                            echo '
                                            <div class="mcq">
                                                <img src="images/box.png" alt="Check Box" width="15px" height= "15px">
                                                <p>'.$answer_text[$j].'</p>
                                            </div>';
                                        }
                                    }
                                echo '</div>
                                </div>';
                            }else if($user_score[$i] == 1){
                                echo '
                                <div class="bar">
                                    <div class="barc"></div>
                                    <div class ="review-area">
                                        <p class="question2">'.($i+1).') '.$question_text[$i].'</p>';
                                    for($j = 0; $j < count($answer_id); $j++){
                                        if($answer_id[$j] == $correct_ans[$i]){
                                            echo '
                                            <p class="u-ans-c">Your Answer</p>
                                            <div class="mcq">
                                                <img src="images/correct.png" alt="Check Box" width="15px" height= "15px">
                                                <p>'.$answer_text[$j].'</p>
                                            </div>';
                                        }else{
                                            echo '
                                            <div class="mcq">
                                                <img src="images/box.png" alt="Check Box" width="15px" height= "15px">
                                                <p>'.$answer_text[$j].'</p>
                                            </div>';
                                        }
                                    }
                                echo '</div>
                                </div>';  
                            }
                        }else if($question_type[$i] == "saq"){
                            if($user_score[$i] == 0){
                                echo '
                                <div class="bar">
                                    <div class="barw"></div>
                                    <div class ="review-area">
                                        <p class="question2">'.($i+1).') '.$question_text[$i].'</p>
                                        <p class="u-ans-w">Your Answer</p>
                                        <p class="text">'.$_POST[$question_id[$i]].'</p>
                                        <p class="u-ans-c">Correct Answer</p>
                                        <p class="text">'.$answer_text[0].'</p>
                                    </div>
                                </div>';
                            }else if($user_score[$i] == 1){
                                echo '
                                <div class="bar">
                                    <div class="barc"></div>
                                    <div class ="review-area">
                                        <p class="question2">'.($i+1).') '.$question_text[$i].'</p>
                                        <p class="u-ans-c">Your Answer</p>
                                        <p class="text">'.$_POST[$question_id[$i]].'</p>
                                        <p class="u-ans-c">Correct Answer</p>
                                        <p class="text">'.$answer_text[0].'</p>
                                    </div>
                                </div>';
                            }
                        }        
                    }
                echo '</div>';
            }
            ?>
        </section>

    </main>

    <script>
        function next(next_section_id){
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" action="stu_section.php" method="POST" style="display: none;">
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
                <form id="hiddenForm" action="stu_section.php" method="POST" style="display: none;">
                    <input type="hidden" name="selected_section_id" value="${section_id}">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }

        let moreresult = document.getElementsByClassName("MoreResult")[0];
        let area = document.getElementsByClassName("attempt")[0];
        function MoreResult() {
            area.innerHTML += `<?php
        
                for($i = 0; $i < count($quiz_performance_id); $i++){                        
                    echo
                    '<tr>
                        <th>'.($i+1).'</th>
                        <td class="score1"><div class="result" style="background: linear-gradient(to right, #5ced73 '.$quiz_percentage[$i].'%, red 0);"></div></td>
                        <td class="score2">'.$quiz_percentage[$i].'%</td>
                        <td class="score2">'.(count($question_id) * $quiz_percentage[$i] / 100).'/'.count($question_id).'</td>
                    </tr>';
                }
            ?>`;
            moreresult.style.display = "none";
        }

        function start(selected_section_id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'stu_section.php';
            form.style.display = 'none';

            const input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'selected_section_id';
            input1.value = selected_section_id;

            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'end';
            input2.value = 'yes';

            form.appendChild(input1);
            form.appendChild(input2);

            document.body.appendChild(form);
            form.submit();
        }
    </script>

</body>
</html>