<?php
session_start();
if (!isset($_SESSION['user_email']) && !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$course_id = $_SESSION['course_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["selected_quiz_id"])) {
    $selected_quiz_id = $_POST['selected_quiz_id'];
}else if($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['selected_quiz_id'])) {
    $selected_quiz_id = $_GET['selected_quiz_id'];
}else{
    $selected_quiz_id = "overall";
}

$search = "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search_stu'])) {
    $search = trim($_GET['search_stu']);
}

$chapter_id = [];
$sql = "SELECT * FROM chapter WHERE course_id = '$course_id'";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    $chapter_id[] = $row["chapter_id"];
}

$section_id = [];
$section_title = [];
$section_type = [];
for ($i = 0; $i < count($chapter_id); $i++) {
    $sql = "SELECT * FROM section WHERE chapter_id = '$chapter_id[$i]'";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $section_id[] = $row["section_id"];
        $section_title[] = $row["section_title"];
        $section_type[] = $row["type"];
    }
}

$quiz_id = [];
$quiz_title = [];
for ($i = 0; $i < count($section_id); $i++) {
    if($section_type[$i] == "question"){
        $quiz_id[] = $section_id[$i];
        $quiz_title[] = $section_title[$i];
    }
}

$sql = "SELECT * FROM course_enrolment WHERE course_id = '$course_id'";
$result = mysqli_query($conn, $sql);
$enrol_id = [];
$student_id = [];
while ($row = mysqli_fetch_assoc($result)) {
    $enrol_id[] = $row["enrol_id"];
    $student_id[] = $row["student_id"];
}

$user_email = [];
for($i = 0; $i < count($student_id); $i++){
    $sql = "SELECT * FROM student WHERE student_id = '$student_id[$i]'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $user_email[] = $row["user_email"];
}

$username = [];
for($i = 0; $i < count($user_email); $i++){
    $sql = "SELECT * FROM user WHERE user_email = '$user_email[$i]'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $username[] = $row["name"];
}

$stu_progression = [];
for($i = 0; $i < count($enrol_id); $i++){
    $sql = "SELECT * FROM progression WHERE enrol_id = '$enrol_id[$i]' and progress = '1'";
    $result = mysqli_query($conn, $sql);
    $stu_progression[] = round(((mysqli_num_rows($result) / count($section_id)) * 100), 2);
}

$quiz_percentage = [];
for($i = 0; $i < count($enrol_id); $i++){
    for($j = 0; $j < count($quiz_id); $j++){
        $sql = "SELECT * FROM quiz_performance WHERE enrol_id = '$enrol_id[$i]' and section_id = '$quiz_id[$j]'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $percentage = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $percentage[] = $row["percentage"];
            }
            $quiz_percentage[$i][] = round((array_sum($percentage) / count($percentage)), 2);
        } else {
            $quiz_percentage[$i][] = "no";
        }
        
    }
}

$avg_percentage = [];
for($i = 0; $i < count($enrol_id); $i++){
    $percentage = [];
    for($j = 0; $j < count($quiz_percentage[$i]); $j++){
        if($quiz_percentage[$i][$j] != "no"){
            $percentage[] = $quiz_percentage[$i][$j];
        }
    }
    if(empty($percentage)){
        $avg_percentage[] = "no result";
    }else{
        $avg_percentage[] = round((array_sum($percentage) / count($percentage)), 2);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morning Quiznos</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <link rel="stylesheet" href="lec_progression.css">
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

    <main>
        <section class="sidebar">
            <div class="hide" onclick="hide_menu()"><img src="images/menu.png" class="hide" alt="Hide Menu" width="20px" height="20px"> Hide Menu</div>
            
            <div class="file">
                <p class="filename_side" onclick="open_overall()">Overall Performance</p>
            </div>

            <?php
            for ($i = 0; $i < count($quiz_id); $i++) {
                echo '
                <div class="file">
                    <p class="filename_side" onclick="open_quiz('.$quiz_id[$i].')">'.$quiz_title[$i].'</p>
                </div>';
            }
            ?>
        </section>

        <section class="track_progression">
            <div class="title">
                <button class="back" title="Back" onclick="back(<?php echo $course_id; ?>)">Back</button>
                <h1 class="title">Track Progression</h1>
            </div>

            <?php
            if($selected_quiz_id == "overall"){
                echo '
                <div class="progression_area">
                    <h2 class="subtitle">Overall Performance</h2>

                    <form class="search_bar" method="GET">
                        <input type="text" name="search_stu" placeholder="Search..." class="search_stu">
                        <input type="hidden" name="selected_quiz_id" value="overall">
                        <button type="submit" class="search_stu_btn" title="Search"><img src="" alt="" width="" height="">Search</button>
                    </form>

                    <div class="progression_table_container">
                        <table class="progression_table">
                            <tr>
                                <th>No.</th>
                                <th>Student Name</th>
                                <th>Progression</th>
                                <th>Overall Results</th>
                            </tr>';
                    if($search != ""){
                        $search_result = "no";
                        for($i = 0; $i < count($enrol_id); $i++){
                            if((str_contains($username[$i], $search))){
                                echo '
                                <tr onclick="">
                                    <th>'.($i + 1).'</th>
                                    <td>'.$username[$i].'</td>
                                    <td>'.$stu_progression[$i].'%</td>';
                                    if($avg_percentage[$i] == "no result"){
                                        echo '<td>No Result Yet</td>';
                                    }else{
                                        echo '<td>'.$avg_percentage[$i].'%</td>';
                                    }
                                echo '</tr>';
                                $search_result = "yes";
                            }
                        }
                        if($search_result == "no"){
                            echo '
                            <tr><td colspan="4">No Search Result</td></tr>';
                        }
                    }else{
                        for($i = 0; $i < count($enrol_id); $i++){
                            echo '
                            <tr onclick="">
                                <th>'.($i + 1).'</th>
                                <td>'.$username[$i].'</td>
                                <td>'.$stu_progression[$i].'%</td>';
                                if($avg_percentage[$i] == "no result"){
                                    echo '<td>No Result Yet</td>';
                                }else{
                                    echo '<td>'.$avg_percentage[$i].'%</td>';
                                }
                            echo '</tr>';
                        }
                    }
                        echo '  
                        </table>
                    </div>
                </div>';
            }else{
                for($i = 0; $i < count($quiz_id); $i++){
                    if($quiz_id[$i] == $selected_quiz_id){
                        $sql = "SELECT * FROM question WHERE section_id = '$quiz_id[$i]'";
                        $result = mysqli_query($conn, $sql);
                        $total_question = mysqli_num_rows($result);
                        echo '
                        <div class="progression_area">
                            <h2 class="subtitle">'.$quiz_title[$i].'</h2>

                            <form class="search_bar" method="GET">
                                <input type="text" name="search_stu" placeholder="Search..." class="search_stu">
                                <input type="hidden" name="selected_quiz_id" value="'.$quiz_id[$i].'">
                                <button type="submit" class="search_stu_btn" title="Search"><img src="" alt="" width="" height="">Search</button>
                            </form>

                            <div class="progression_table_container">
                                <table class="progression_table">
                                    <tr>
                                        <th>No.</th>
                                        <th>Student Name</th>
                                        <th>Average Score</th>
                                    </tr>';
                        if($search != ""){
                            $search_result = "no";
                            for($j = 0; $j < count($enrol_id); $j++){
                                if((str_contains($username[$j], $search))){
                                    echo '
                                    <tr onclick="">
                                        <th>'.($j + 1).'</th>
                                        <td>'.$username[$j].'</td>';
                                        if($quiz_percentage[$j][$i] == "no"){
                                            echo '<td>No Score Yet</td>';
                                        }else{
                                            echo '<td>'.$quiz_percentage[$j][$i].'% &nbsp;&nbsp;&nbsp;&nbsp;'.round(($total_question * $quiz_percentage[$j][$i] / 100), 2).' / '.$total_question.'</td>';
                                        }
                                    echo '</tr>';
                                    $search_result = "yes";
                                }
                            }
                            if($search_result == "no"){
                                echo '
                                <tr><td colspan="3">No Search Result</td></tr>';
                            }
                        }else{
                            for($j = 0; $j < count($enrol_id); $j++){
                                echo '
                                <tr onclick="">
                                    <th>'.($j + 1).'</th>
                                    <td>'.$username[$j].'</td>';
                                    if($quiz_percentage[$j][$i] == "no"){
                                        echo '<td>No Score Yet</td>';
                                    }else{
                                        echo '<td>'.$quiz_percentage[$j][$i].'% &nbsp;&nbsp;&nbsp;&nbsp;'.round(($total_question * $quiz_percentage[$j][$i] / 100), 2).' / '.$total_question.'</td>';
                                    }
                                echo '</tr>';
                            }
                        } 
                                echo '
                                </table>
                            </div>
                        </div>';
                    }
                }
            }
            ?>

        </section>
    </main>

    <script>
        function back(course_id) {
            window.location.href = "lec_course.php?course_id=" + course_id;
        }

        function hide_menu() {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.style.display = "none";
        }

        function open_overall() {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" method="POST" style="display: none;">
                    <input type="hidden" name="selected_quiz_id" value="overall">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }

        function open_quiz(id) {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" method="POST" style="display: none;">
                    <input type="hidden" name="selected_quiz_id" value="${id}">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }

    </script>

</body>

</html>