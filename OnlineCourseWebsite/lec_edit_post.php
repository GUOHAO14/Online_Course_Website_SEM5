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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_section_id"])) {
    $delete_section_id = $_POST["delete_section_id"];
    $sql = "DELETE FROM section WHERE section_id = '$delete_section_id'";
    $result = mysqli_query($conn, $sql);
    header("Location: lec_edit_sidebar.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["file_name_post"])) {
    $file_name_post = $_POST['file_name_post'];
    $file_desc_post = $_POST['file_desc_post'];
    $content = $_POST['content'];

    $sql = "UPDATE section SET section_title = '$file_name_post', subtopic_description = '$file_desc_post' WHERE section_id = '$section_id'";
    mysqli_query($conn, $sql);
    $sql = "UPDATE content SET content = '$content' WHERE section_id = '$section_id'";
    mysqli_query($conn, $sql);
}


$sql = "SELECT * FROM section WHERE section_id = '$section_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$section_title_post = $row["section_title"];
$section_desc = $row["subtopic_description"];

$sql = "SELECT * FROM content WHERE section_id = '$section_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$content_get = $row["content"];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morning</title>
    <link rel="icon" type="image/x-icon" href="images/logo.png">
    <link rel="stylesheet" href="lec_post.css">
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
    <main>
        <?php include "lec_edit_sidebar.php"?>

        <section class="edit_course">
            <div class="title">
                <button class="back" title="Back" onclick="back(<?php echo $course_id; ?>)">Back</button>
                <h1 class="title">Edit Course</h1>
            </div>

            <form class="file_detail" method="post">

                <div class="manage_file">
                    <button type="button" id="submit_btn" class="manage_file_btn" title="Save"><img src="images/save.png" alt="Save" width="20px" height= "20px"></button>
                    
                    <button type="button" class="manage_file_btn" title="Delete" onclick="delete_subfile('<?php echo $section_ids; ?>', '<?php echo $approve_section; ?>')"><img src="images/delete.png" alt="Delete" width="20px" height= "20px"></button>
                </div>

                <div class="file_title">
                    <input type="text" name="file_name_post" class="file_name" value="<?php echo $section_title_post;?>" placeholder="Enter Post Title" required>
                    <input type="text" name="file_desc_post" class="file_desc" value="<?php echo $section_desc;?>" placeholder="Enter Post Description" required>
                </div>

                <div class="content_area">
                    <textarea name="content" id="file_content_editor" class="file_content"><?php echo $content_get; ?></textarea>
                </div>

            </form>

            <script>
                let original_name, original_desc, original_content;

                tinymce.init({
                    selector: ".file_content",
                    menubar: false,
                    height: 500,
                    toolbar_sticky: true,
                    toolbar: "undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | forecolor backcolor | outdent indent | link image media",
                    plugins: "image media link code",
                    content_style: "body { font-size: 14px; font-family: Arial; }",
                    style_formats: [
                        { title: 'Heading 1', block: 'h1' },
                        { title: 'Heading 2', block: 'h2' },
                        { title: 'Heading 3', block: 'h3' },
                        { title: 'Paragraph', block: 'p' },
                        { title: 'Superscript', inline: 'sup' },
                        { title: 'Subscript', inline: 'sub' }
                    ],
                    setup: function (editor) {
                        editor.on('init', function () {
                            original_name = document.querySelector(".file_name").value.trim();
                            original_desc = document.querySelector(".file_desc").value.trim();
                            original_content = editor.getContent();
                        });
                    }
                });

                document.addEventListener("click", function (event) {
                    let file_detail = document.querySelector(".file_detail");
                    let submit_btn = document.getElementById("submit_btn");

                    if (file_detail && (!file_detail.contains(event.target) || submit_btn.contains(event.target))) {
                        let file_name = document.querySelector(".file_name").value.trim();
                        let file_desc = document.querySelector(".file_desc").value.trim();
                        let file_content = tinymce.get("file_content_editor").getContent();

                        let changed = (
                            file_name !== original_name ||
                            file_desc !== original_desc ||
                            file_content !== original_content
                        );

                        if (changed && file_name && file_desc && file_content) {
                            file_detail.submit();
                        }
                    }
                });

            </script>
            
        </section>
    </main>
        

</body>

</html>
