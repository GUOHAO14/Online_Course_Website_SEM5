<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_email']) && !isset($_SESSION["lecturer_id"])) {
    header("Location: index.php");
    exit();
}
include("conn.php");

$course_id = $_SESSION['course_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["pass_section_id"])) {
    $_SESSION['section_id'] = $_POST['pass_section_id'];
    $section_type = $_POST['section_type'];

    if ($section_type == "content") {
        header("Location: lec_edit_post.php?course_id=" . $course_id);
        exit();
    } else if ($section_type == "question") {
        header("Location: lec_edit_quiz.php?course_id=" . $course_id);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_chapter_id"])) {
    $delete_chapter_id = $_POST["delete_chapter_id"];
    $sql = "DELETE FROM chapter WHERE chapter_id = '$delete_chapter_id'";
    $result = mysqli_query($conn, $sql);
    header("Location: lec_edit_sidebar.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_section_id"])) {
    $delete_section_id = $_POST["delete_section_id"];
    $sql = "DELETE FROM section WHERE section_id = '$delete_section_id'";
    $result = mysqli_query($conn, $sql);
    header("Location: lec_edit_sidebar.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["filename_input_side"])) {
    $filename_input = $_POST["filename_input_side"];
    $sql = "INSERT INTO chapter (chapter_title, course_id) VALUES ('$filename_input', '$course_id');";
    mysqli_query($conn, $sql);

    $insert_chapter_id = mysqli_insert_id($conn);
    $sql = "INSERT INTO section (section_title, `type`, chapter_id) VALUES ('Subtopic 1', 'content', '$insert_chapter_id');";
    mysqli_query($conn, $sql);

    $insert_section_id = mysqli_insert_id($conn);
    $sql = "INSERT INTO content (section_id) VALUES ('$insert_section_id');";
    mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["subfilename_input_side"])) {
    $subfilename_input = $_POST["subfilename_input_side"];
    $chapter_id = $_POST["chapter_id"];
    $section_type = $_POST["section_type_side"];
    $sql = "INSERT INTO section (section_title, `type`, chapter_id) VALUES ('$subfilename_input','$section_type', '$chapter_id');";
    mysqli_query($conn, $sql);

    $insert_section_id = mysqli_insert_id($conn);
    if($section_type == "content") {
        $sql = "INSERT INTO content (section_id) VALUES ('$insert_section_id');";
        mysqli_query($conn, $sql);
    }else if($section_type == "question") {
        $sql = "INSERT INTO question (question_type, section_id) VALUES ('mcq', '$insert_section_id');";
        mysqli_query($conn, $sql);

        $insert_question_id = mysqli_insert_id($conn);
        $sql = "INSERT INTO answer (accuracy, question_id) VALUES ('1', '$insert_question_id');";
        mysqli_query($conn, $sql);
        for($i = 0; $i < 3; $i++){
            $sql = "INSERT INTO answer (accuracy, question_id) VALUES ('0', '$insert_question_id');";
            mysqli_query($conn, $sql);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["rename_file_input"])) {
    $rename_file = $_POST["rename_file_input"];
    $chapter_id = $_POST["chapter_id"];
    $sql = "UPDATE chapter SET chapter_title = '$rename_file' WHERE chapter_id = '$chapter_id'";
    mysqli_query($conn, $sql);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["rename_subfile_input"])) {
    $rename_subfile = $_POST["rename_subfile_input"];
    $section_id = $_POST["section_id"];
    $sql = "UPDATE section SET section_title = '$rename_subfile' WHERE section_id = '$section_id'";
    mysqli_query($conn, $sql);

    $_SESSION['section_id'] = $section_id;
    $section_type = $_POST['section_type'];
    if ($section_type == "content") {
        header("Location: lec_edit_post.php");
        exit();
    } else if ($section_type == "question") {
        header("Location: lec_edit_quiz.php");
        exit();
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

if(count($chapter_id) <= 1){
    $approve_chapter = "no";
}else{
    $approve_chapter = "yes";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_file"])) {
    echo '
    <form class="file_input_side" method="post">
        <input type="text" name="filename_input_side" class="filename_input_side" placeholder="Enter Chapter Name" required>
    </form>';
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morning Quiznos</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <link rel="stylesheet" href="lec_side.css">
</head>

<body>
    <?php
    if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
        //running directly
        echo '<main>';
    }
    ?>
    <section class="sidebar">
        <div class="hide" onclick="hide_menu()"><img src="images/menu.png" class="menu" alt="Hide Menu" width="20px" height="20px"> Hide Menu</div>
        
        <?php
        for ($i = 0; $i < count($chapter_id); $i++) {
            echo '
            <div class="file">
                <p class="filename_side" onclick="open_chapter('.$chapter_id[$i].')">'.$chapter_title[$i].'</p>
                <div class="popup_wrap">
                    <img class="more_file" src="images/more.png" alt="More" width="15px" height="15px" title="More" onclick="file_popup('.$i.')">

                    <div class="file_popup">
                        <div class="more_option" onclick="delete_file('.$chapter_id[$i].', \''.$approve_chapter.'\')">Delete</div>
                        <div class="more_option" onclick="rename_file('.$i.', '.$chapter_id[$i].')">Rename</div>
                    </div>
                </div>
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
            
            if(count($section_id) <= 1){
                $approve_section = "no";
            }else{
                $approve_section = "yes";
            }

            for ($j = 0; $j < count($section_id); $j++) {
                echo '
                <div class="subfile" data-file="'.$chapter_id[$i].'">
                    <p class="subfilename_side" onclick="open_section('.$section_id[$j].', \''.$section_type[$j].'\')">'.$section_title[$j].'</p>
                    <div class="popup_wrap">
                        <img class="more_file" src="images/more.png" alt="More" width="12px" height="12px" title="More" onclick="subfile_popup('.$j.', '.$chapter_id[$i].')">

                        <div class="subfile_popup">
                            <div class="more_option" onclick="delete_subfile('.$section_id[$j].', \''.$approve_section.'\')">Delete</div>
                            <div class="more_option" onclick="rename_subfile('.$j.', '.$section_id[$j].', '.$chapter_id[$i].', \''.$section_type[$j].'\')">Rename</div>
                        </div>
                    </div>
                </div>';
            }
            echo '
            <div class="add_subfile" data-file="'.$chapter_id[$i].'" onclick="add_subfile('.$chapter_id[$i].', '.$i.', '.$j.')"><img src="images/add.png" alt="Add New Subfile" width="18px" height="18px"></div>';   
        }
        echo '
        <div class="add_file" onclick="add_file('.$i.')"><img src="images/add.png" alt="Add New File" width="25px" height="25px"></div>';
        ?>
    </section>

    <?php
    if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
        //running directly
        echo '
        <section class="no_selection">
            <button class="back" title="Back" onclick="back('.$course_id.')">Back</button>
            <h1 class="no_selection_text">Please Select A Section</h1>
        </section>
    </main>';
    }
    ?>

    <script>
        let click_chapter;
        let renaming_file;
        let renaming_subfile;
        let renaming_subfile_chapter;

        function back(course_id) {
            window.location.href = "lec_course.php?course_id=" + course_id;
        }

        function hide_menu() {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.style.display = "none";
        }

        function file_popup(num) {
            let file_popup = document.getElementsByClassName("file_popup")[num];
            document.querySelectorAll(".file_popup, .subfile_popup").forEach(element => {
                if (element == file_popup) {
                    element.style.display = element.style.display === "block" ? "none" : "block";
                } else {
                    element.style.display = "none";
                }
            });
        }

        function subfile_popup(num, id) {
            let subfiles_popup = Array.from(document.querySelectorAll('.subfile[data-file="' + id + '"] .subfile_popup'));
            let subfile_popup = subfiles_popup[num];
            document.querySelectorAll(".file_popup, .subfile_popup").forEach(element => {
                if (element == subfile_popup) {
                    element.style.display = element.style.display === "block" ? "none" : "block";
                } else {
                    element.style.display = "none";
                }
            });
        }

        function open_chapter(id) {
            setTimeout(() => {
                let subfiles = Array.from(document.querySelectorAll('[data-file="' + id + '"]'));
                document.querySelectorAll(".subfile, .add_subfile").forEach(element => {
                    if (subfiles.includes(element)) {
                        element.style.display = element.style.display === "flex" ? "none" : "flex";
                    } else {
                        element.style.display = "none";
                    }
                });
            }, 0);
        }

        function open_section(section_id, section_type) {
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="hiddenForm" method="POST" style="display: none;">
                    <input type="hidden" name="pass_section_id" value="${section_id}">
                    <input type="hidden" name="section_type" value="${section_type}">
                </form>
            `);
            document.getElementById("hiddenForm").submit();
        }

        function delete_file(chapter_id, approve_chapter){
            let sidebar = document.getElementsByClassName("sidebar")[0];
            if(approve_chapter == "no"){
                sidebar.insertAdjacentHTML("afterend", `
                    <div class="box_overlay">
                        <form class="box" method="post">
                            <h2 class="remove_title">At least one chapter must included</h2>
                            <div class="box_btn_area">
                                <button type="button" class="box_btn" onclick="no()">OK</button>
                            </div>
                        </form>
                    </div>
                `);
            }else{
                sidebar.insertAdjacentHTML("afterend", `
                    <div class="box_overlay">
                        <form class="box" method="post">
                            <h2 class="remove_title">Confirm to delete?</h2>
                            <div class="box_btn_area">
                                <button type="button" class="box_btn" onclick="yes('${chapter_id}')">YES</button>
                                <button type="button" class="box_btn" onclick="no()">No</button>
                            </div>
                        </form>
                    </div>
                `);
            }
            document.getElementsByClassName("box_overlay")[0].style.display = "flex";
        }

        function yes(chapter_id) {
            document.getElementsByClassName("box_overlay")[0].remove();
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="delete_form" method="POST" style="display: none;">
                    <input type="hidden" name="delete_chapter_id" value="${chapter_id}">
                </form>
            `);
            document.getElementById("delete_form").submit();
        }

        function no() {
            document.getElementsByClassName("box_overlay")[0].remove();
        }

        function delete_subfile(section_id, approve_section){
            let sidebar = document.getElementsByClassName("sidebar")[0];
            if(approve_section == "no"){
                sidebar.insertAdjacentHTML("afterend", `
                    <div class="box_overlay">
                        <form class="box" method="post">
                            <h2 class="remove_title">At least one section must included</h2>
                            <div class="box_btn_area">
                                <button type="button" class="box_btn" onclick="no()">OK</button>
                            </div>
                        </form>
                    </div>
                `);
            }else if(approve_section == "yes"){
                sidebar.insertAdjacentHTML("afterend", `
                    <div class="box_overlay">
                        <form class="box" method="post">
                            <h2 class="remove_title">Confirm to delete?</h2>
                            <div class="box_btn_area">
                                <button type="button" class="box_btn" onclick="yes2('${section_id}')">YES</button>
                                <button type="button" class="box_btn" onclick="no()">No</button>
                            </div>
                        </form>
                    </div>
                `);
            }
            document.getElementsByClassName("box_overlay")[0].style.display = "flex";
        }

        function yes2(section_id) {
            document.getElementsByClassName("box_overlay")[0].remove();
            let sidebar = document.getElementsByClassName("sidebar")[0];
            sidebar.insertAdjacentHTML("afterend", `
                <form id="delete_subform" method="POST" style="display: none;">
                    <input type="hidden" name="delete_section_id" value="${section_id}">
                </form>
            `);
            document.getElementById("delete_subform").submit();
        }

        function rename_file(index, chapter_id) {
            renaming_file = index;
            setTimeout(() => {
                let selected_file = document.getElementsByClassName("file")[index];
                selected_file.querySelector(".filename_side").style.display = "none";
                selected_file.querySelector(".popup_wrap").style.display = "none";
                selected_file.insertAdjacentHTML("beforeend", `
                    <form class="rename_file_form" method="post">
                        <input type="text" name="rename_file_input" class="rename_file_input" placeholder="Enter New Chapter Name" required>
                        <input type="hidden" name="chapter_id" value="${chapter_id}">
                    </form>
                `);
            }, 0);
        }

        function rename_subfile(index, section_id, chapter_id, section_type) {
            renaming_subfile = index;
            renaming_subfile_chapter = chapter_id;
            setTimeout(() => {
                let all_subfiles = Array.from(document.querySelectorAll('.subfile[data-file="' + chapter_id + '"]'));
                let selected_subfile = all_subfiles[index];
                selected_subfile.querySelector(".subfilename_side").style.display = "none";
                selected_subfile.querySelector(".popup_wrap").style.display = "none";
                selected_subfile.insertAdjacentHTML("beforeend", `
                    <form class="rename_subfile_form" method="post">
                        <input type="text" name="rename_subfile_input" class="rename_subfile_input" placeholder="Enter New Section Name" required>
                        <input type="hidden" name="section_id" value="${section_id}">
                        <input type="hidden" name="section_type" value="${section_type}">
                    </form>
                `);
            }, 0);
        }

        function add_file(index) {
            let add_file = document.getElementsByClassName("add_file")[0];
            add_file.style.display = "none";
            fetch("lec_edit_sidebar.php", {
                method: "POST",
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                body: "add_file=" + encodeURIComponent(index)
            })
            .then(response => response.text())
            .then(data => {
                document.getElementsByClassName("sidebar")[0].innerHTML += data;
            });
        }

        function add_subfile(chapter_id, file_num, section_num) {
            chapter_click = file_num;
            setTimeout(() => {
                let section = document.querySelectorAll('.subfile[data-file="' + chapter_id + '"]');
                let section_add = section[section_num - 1];
                let add_subfile = document.getElementsByClassName("add_subfile")[file_num];
                add_subfile.style.display = "none";

                section_add.insertAdjacentHTML("afterend", `
                    <form class="subfile_input_side" method="post">
                        <input type="text" name="subfilename_input_side" class="subfilename_input_side" placeholder="Enter Section Name" required>
                        <label class="section_type_side"><input type="radio" name="section_type_side" value="content" required>Content</label>
                        <label class="section_type_side"><input type="radio" name="section_type_side" value="question" required>Quiz</label>
                        <input type="hidden" name="chapter_id" value="${chapter_id}">
                    </form>
                `);
            }, 0);
        }

        document.addEventListener("click", function(event) {
            let file_input_side = document.getElementsByClassName("file_input_side")[0];
            if (file_input_side && !file_input_side.contains(event.target)) {
                let input = document.getElementsByClassName("filename_input_side")[0];
                let add_file = document.getElementsByClassName("add_file")[0];
                if (input && input.value.trim() !== "") {
                    file_input_side.submit();
                    file_input_side.remove();
                }else {
                    file_input_side.remove();
                    add_file.style.display = "flex";
                }
            }

            let subfile_input_side = document.getElementsByClassName("subfile_input_side")[0];
            if (subfile_input_side && !subfile_input_side.contains(event.target)) {
                let subinput = document.getElementsByClassName("subfilename_input_side")[0];
                let add_subfile = document.getElementsByClassName("add_subfile")[chapter_click];
                subfile_input_side.style.display !== "none"

                let sectionTypeChecked = subfile_input_side.querySelector('input[name="section_type_side"]:checked');
                if (subinput && subinput.value.trim() !== "" && sectionTypeChecked) {
                    subfile_input_side.submit();
                    subfile_input_side.remove();
                }else {
                    subfile_input_side.remove();
                    add_subfile.style.display = "flex";
                }
            }

            let rename_file_form = document.getElementsByClassName("rename_file_form")[0];
            if (rename_file_form && !rename_file_form.contains(event.target)) {
                let rename_input = document.getElementsByClassName("rename_file_input")[0];
                if (rename_input && rename_input.value.trim() !== "") {
                    rename_file_form.submit();
                    rename_file_form.remove();
                }else {
                    rename_file_form.remove();
                    let selected_file = document.getElementsByClassName("file")[renaming_file];
                    selected_file.querySelector(".filename_side").style.display = "flex";
                    selected_file.querySelector(".popup_wrap").style.display = "inline-block";
                }
            }

            let rename_subfile_form = document.getElementsByClassName("rename_subfile_form")[0];
            if (rename_subfile_form && !rename_subfile_form.contains(event.target)) {
                let rename_subinput = document.getElementsByClassName("rename_subfile_input")[0];
                if (rename_subinput && rename_subinput.value.trim() !== "") {
                    rename_subfile_form.submit();
                    rename_subfile_form.remove();
                }else {
                    rename_subfile_form.remove();
                    let all_subfiles = Array.from(document.querySelectorAll('.subfile[data-file="' + renaming_subfile_chapter + '"]'));
                    let selected_subfile = all_subfiles[renaming_subfile];
                    selected_subfile.querySelector(".subfilename_side").style.display = "flex";
                    selected_subfile.querySelector(".popup_wrap").style.display = "inline-block";
                }
            }
        });

    </script>
</body>

</html>