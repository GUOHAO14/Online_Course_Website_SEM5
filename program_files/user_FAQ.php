<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

include "conn.php";
date_default_timezone_set('Asia/Kuala_Lumpur');

$faqSearch = $_GET['faq_search'] ?? '';

$faqSql = "SELECT question, answer, support_id FROM help_support_qna";
if (!empty($faqSearch)) {
    $faqSql .= " WHERE question LIKE '%$faqSearch%'";
}
$faqResult = $conn->query($faqSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User FAQ Page</title>
    <link rel="stylesheet" href="user_php.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@700&display=swap" rel="stylesheet">
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
            width: 100%;
            position: relative; 
            padding-top: 7rem;
            padding: 7rem 24%;
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

        .purpleBtn {
            width: fit-content;
            padding: 0.7rem 1.4rem;
            border: none;
            border-radius: 0.4rem;
            background-color: rgb(84, 0, 200);
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease; /* animate everything */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        /* Hover effect */
        .purpleBtn:hover {
            background-color: rgb(65, 31, 111);
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .purpleBtn:active {
            background-color: rgb(84, 0, 200);
            transform: scale(1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #search {
            padding: 1rem;
            height: 100%;
            width: 20rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<header>
    <?php
    if (isset($_SESSION['student_id'])) {
        include "header.php";
    } else if (isset($_SESSION['student_id'])) {
        include ".php";
    }
    ?>
</header>

<h1>FAQs</h1>

<form method="GET">
    <input id="search" type="text" name="faq_search" placeholder="Search by question..." value="<?php echo htmlspecialchars($faqSearch); ?>">
    <button class="purpleBtn" type="submit">Search</button>
    <button class="purpleBtn" type="button" onclick="window.location.href='<?php echo basename($_SERVER['PHP_SELF']); ?>'">Reset</button>
</form>

<?php while ($row = $faqResult->fetch_assoc()): ?>
    <div class="faq-item">
        <div class="faq-question">
            <span><?php echo htmlspecialchars($row["question"]); ?></span>
            <span class="toggle-sign">+</span>
        </div>
        <div class="faq-answer">
            <?php echo htmlspecialchars($row["answer"]); ?>
        </div>
    </div>
<?php endwhile; ?>
</div>


<script>
    // Toggle FAQ answer
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const toggleSign = question.querySelector('.toggle-sign');
            
            question.classList.toggle('active');
            answer.classList.toggle('show');
        });
    });

    // Contact support redirect
    document.getElementById('actionButton').addEventListener('click', function () {
        window.location.href = "https://example.com/contact-support"; // Change to actual support URL
    });
</script>

</body>
</html>
