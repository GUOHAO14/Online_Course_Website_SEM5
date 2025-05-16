<?php
session_start();
$removedDialogStatus = false;
$status = "";
$email = "";
$name = "";
$reason = "";
$signupStatus = "";
//directed from login
//when user found out that his/her is removed (searched from removed_user table)
if (isset($_REQUEST["email"])) {
    $removedDialogStatus = true;
    $status = $_REQUEST["status"];
    $email = $_REQUEST["email"];
    $name = $_REQUEST["name"];
    $reason = $_REQUEST["reason"];
} 
//directed from signup
//when user finished signup, informing user that they succeeded or failed to create account
else if (isset($_REQUEST["signupStatus"])) {
    $signupStatus = $_REQUEST["signupStatus"];
}
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <link rel="stylesheet" href="stu-shared.css">
    <style>
        dialog[open] {
            padding: 0rem;
        }

        .dialogHeader, .dialogFooter {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .dialogContent {
            text-align: left;
            font-weight: normal;
            padding: 1.8rem;
            background-color: #e2f8ff;
        }

        .redBg {
            background-color: #ffc7c7;
        }

        .dialogContent div{
            text-wrap: auto;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        @media screen and (max-width: 700px) {
            .dialogHeader, .dialogFooter {
                padding: 0.75rem;
            }

            .dialogContent {
                padding: 1rem;
            }
        }
        @media screen and (max-width: 530px) {
            .dialogContent {
                padding: 0.6rem;
            }
        }
    </style>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <?php include "indexheader.php";?>
    </header>
    <div class="blurOverlay"></div>
    <section id="students" class="hero" style="background-image: url('system_img/stuback.png')">
    `   <div class="hero-overlay fade-in">
            <img src="system_img/Capstone real logo.png" alt="Capstone Logo" class="logo"/>
            <h1>Welcome Students</h1>
            <p>Discover a world of knowledge at your fingertips. Dive into interactive courses and collaborate with peers to enhance your learning experience.</p>
            <a href="#lecturers" class="btn-scroll">For Lecturers ↓</a>
            <a href="http://"></a>
        </div>

        <div class="features-section fade-in">
        <div class="feature-card">
            <h3>Explore Courses</h3>
            <p>Find learning paths tailored to your interests and career goals.</p>
        </div>
        <div class="feature-card">
            <h3>Interactive Learning</h3>
            <p>Engage in quizzes, forums, and hands-on projects to deepen your understanding.</p>
        </div>
        <div class="feature-card">
            <h3>Peer Collaboration</h3>
            <p>Connect with classmates and work together on real-world challenges.</p>
        </div>
        <div class="feature-card">
            <h3>Progress Tracking</h3>
            <p>Monitor your learning journey and celebrate your milestones.</p>
        </div>
        </div>
    </section>
    <div style="background-color: white; height: 2rem;"></div>
    <!-- Lecturer Section -->
    <section id="lecturers" class="hero" style="background-image: url('system_img/dontask.jpg')">
        <div class="hero-overlay fade-in">
        <img src="system_img/Capstone real logo.png" alt="Capstone Logo" class="logo"/>
        <h1>Calling All Lecturers</h1>
        <p>Join a passionate platform of educators. Share your expertise, shape young minds, and inspire curiosity across disciplines.</p>
        <a href="#students" class="btn-scroll">↑ Back to Students</a>
        </div>

        <div class="features-section fade-in">
        <div class="feature-card">
            <h3>Build Your Course</h3>
            <p>Create structured modules with multimedia, assessments, and resources.</p>
        </div>
        <div class="feature-card">
            <h3>Mentor Students</h3>
            <p>Provide guidance and feedback to students across your field of study.</p>
        </div>
        <div class="feature-card">
            <h3>Collaborate with Peers</h3>
            <p>Network and co-create knowledge with fellow lecturers and academics.</p>
        </div>
        <div class="feature-card">
            <h3>Track Impact</h3>
            <p>See how your teaching influences learner progress and engagement.</p>
        </div>
        </div>
    </section>

    <dialog class="exitLongDialog" id="removedDialog">
        <div class="dialogHeader">
            <h3 id="removedHeader"></h3>
            <br>
        </div>
        <div class="dialogContent redBg">
            <div id="email"><b>Email:&nbsp;&nbsp;</b>afsdfsdasdfsdfadfs</div>
            <br>
            <div id="name"><b>Username:&nbsp;&nbsp;</b></div>
            <br>
            <h4>Reason: </h4>
            <div id="removedMsg"></div>
            <br>
            <div id="removedFooter"></div>
        </div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('removedDialog')">Okay</button>
        </div>
    </dialog>

    <dialog class="exitLongDialog" id="signupDialog">
        <div class="dialogHeader">
            <h3 id="signupHeader"></h3>
            <br>
        </div>
        <div class="dialogContent" id="signupMsg"></div>
        <div class="dialogFooter exit">
            <button id="msgExit" onclick="exitDialog('signupDialog')">Okay</button>
        </div>
    </dialog>

    <!-- Script for fade-in animation -->
    <script>
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                entry.target.classList.add('show');
                }
            });
            }, {
            threshold: 0.1
        });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
        //var to control dialog
        let removedDialogStatus = <?php echo $removedDialogStatus ? 'true' : 'false'; ?>;
        let signupStatus = "<?php echo $signupStatus;?>";
        
        const overlay = document.querySelector(".blurOverlay");
        //for removed user
        if (removedDialogStatus) {
            overlay.style.visibility = "visible";
            const status = "<?php echo $status;?>";
            const header = document.getElementById("removedHeader");
            const footer = document.getElementById("removedFooter");
            if (status === "Removed") {
                header.innerHTML = "Lecturer Account Removal/Rejection Notice";
                footer.innerHTML = "";
            } else {
                header.innerHTML = "Student Account Ban Notice";
                footer.innerHTML = "You are banned from signing up an account again.";
            }
            document.getElementById("email").innerHTML += "<?php echo $email;?>";
            document.getElementById("name").innerHTML += "<?php echo $name;?>";
            document.getElementById("removedMsg").innerHTML = "<?php echo $reason;?>";
            document.getElementById("removedDialog").showModal();
        }

        //for signup user
        if (signupStatus) {
            overlay.style.visibility = "visible";
            const header = document.getElementById("signupHeader");
            const msg = document.getElementById("signupMsg");

            if (signupStatus === "lecturer success") {
               header.innerHTML = `Lecturer Account Sign-Up Complete`;
               msg.innerHTML = `Please wait patiently for your account approval while we review your profile.<br>If you are still not given lecturer access after 3 business days, please contact us through Help & Support.`; 
            } 
            else if (signupStatus === "lecturer fail" || signupStatus === "student fail") {
                header.innerHTML = `Fail to Complete Sign-Up`;
                msg.innerHTML = `We encountered a problem causing your sign-up to be unsuccessful.<br>Try to sign up again and follow the instructions closely.`; 
            } 
            else if (signupStatus === "student success") {
                header.innerHTML = `Student Account Sign-Up Complete`;
                msg.innerHTML = `You're all set!<br>Log in to start exploring our website and unlock your potential in IT through our courses!`; 
            } 
            else { //impossible scenario, for fun though
                header.innerHTML = `Error 404`;
                msg.innerHTML = `Unknown error.`; 
            }
            document.getElementById("signupDialog").showModal();
        }

        function exitDialog(dialogId) {
            const dialog = document.getElementById(dialogId);
            dialog.close();
            overlay.style.visibility = "hidden";
        }


    </script>
</body>
</html>