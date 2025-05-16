<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        :root {
            --bg-primary: #171717;
            --bg-secondary: #2a3935;
            --text-primary: #d3efe9;
            --text-secondary: #47b298;
            --accent: #19715c;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #ffffff;
        }

        .container {
            position: relative;
            width: 100%;
            max-width: 32.2rem;
            padding: 3.5rem;
            background: linear-gradient(135deg, #4A148C 0%, #311B92 100%);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .container p {
            color: #ffffff;
            margin-bottom: 10px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            color: #ffffff;
            margin-bottom: 5px;
            font-size: clamp(14px, 3vw, 16px);
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #d3efe9;
            border-radius: 5px;
            /* background-color: #e0e0e0; */
            background: transparent;
            color: var(--text-primary);
            font-size: clamp(14px, 3vw, 16px);
        }

        .input-group input[type="email"],
        .input-group input[type="password"] {
            color: #d3efe9;
        }

        input::placeholder {
            color: #d3efe9;
            opacity: 0.7;
        }

        .forgot-password {
            text-align: right;

        }

        .forgot-password a {
            color: #ff4f4f;
            font-size: clamp(12px, 2.5vw, 14px);
            text-decoration: none;
        }

        .login-btn {
            width: 35%;
            padding: 0.7rem;
            border: none;
            border-radius: 0.3rem;
            background-color: #d3efe9;
            color: #171717;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
            transition: 0.3s ease;
            outline: 3px outset lightgrey;
        }

        .login-btn:hover {
            background-color: rgb(125, 81, 183);
            color: #d3efe9;
            transition: 0.3s ease;
        }

        .dialog-btn:hover {
            background-color:rgb(153, 158, 157);
            color: #d3efe9;
        }

        .login-btn:active {
            transform: scale(0.95);
            background-color: rgb(182, 136, 243);
        }

        .top-left-btn {
            position: absolute;
            top: 0.6rem;
            left: 0.6rem;
            padding: 0.5rem 0.6rem;
            font-size: 0.9rem;
            z-index: 2;
            width: fit-content;
            margin-left: 1rem;
        }

        .signup {
            text-align: center;
            margin-top: 15px;
        }

        .signup a {
            color: #ff4f4f;
            font-weight: bold;
            text-decoration: none;
            font-size: clamp(12px, 2.5vw, 14px);
            cursor: pointer;
        }

        .signup p {
            color: #ffffff;
            font-weight: bold;
            text-decoration: none;
        }

        .input-group {
            margin-bottom: 15px;
            position: relative;
        }

        .error-message {
            color: #ff4d4d;
            font-weight: bold;
            font-size: 14px;
            width: fit-content;
            margin-right: 0;
            margin-bottom: 5px;
            right: 0;
            top: 0;
            border: 2px solid #ff4d4d;
            border-radius: 8px;
            padding: 5px;
        }

        .logo {
            width: 6.2rem;
            height: auto;
            display: block;
            margin: 0 auto 1.3rem auto;
            border-radius: 0.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        #chooseRole {
            padding: 1.5rem;
            min-width: fit-content;
            height: fit-content;
        }

        #dialogMsg {
            padding: 5%;
            border-bottom: 2px solid lightgrey;
        }

        .dialogBtn {
            display: flex;
            justify-content: space-between;
            padding: 4%;
        }

        .dialog-btn {
            font-size: 100%;
        }

        #closeDialog {
            padding: 0.24rem 0.4rem 0.2rem 0.4rem;
            border-radius: 50%;
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: transparent;
            font-size: 1rem;
            cursor: pointer;
        }

        #closeDialog:focus {
            outline: none;
        }

        #closeDialog:active {
            background-color: grey;
        }

        #closeDialog:hover {
            background-color: lightgrey;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            .social-login {
                flex-direction: column;
            }

            .social-login a {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
    <link rel="shortcut icon" href="system_img/Capstone real logo.png" type="image/x-icon">
    <link rel="stylesheet" href="stu-shared.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            padding: 0; /*erase all previous paddings*/
            margin-top: -1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <button onclick="window.location.href = 'index.php';" class="login-btn top-left-btn"><i class="bi bi-arrow-left-circle-fill"></i>&nbsp;&nbsp;Homepage</button>
        <img src="system_img/Capstone real logo.png" alt="Core of Courses" class="logo">
        <div class="input-group">
            <label for="email">Email address</label>
            <input oninput="clearError();" type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div id="emailError" style="display: none;" class="error-message">Invalid email.</div>

        <div class="input-group">
            <label for="passwordInput">Password</label>
            <div class="pwContainer">
                <input oninput="clearError();" type="password" id="passwordInput" name="password" placeholder="Enter your password" required>
                <img style="height: 75%;" id="pwVisible" src="system_img/visibilityOffWhite.png" alt="VisibilityOff" onclick="hidePW()"><img style="height: 75%;" id="pwNotVisible" src="system_img/visibilityOnWhite.png" alt="VisibilityOn" onclick="hidePW()">
            </div>
        </div>
        <div id="passwordError" style="display: none;" class="error-message">Invalid password.</div>

        <div class="forgot-password">
            <a href="#">Forgot password?</a>
        </div>

        <div style="text-align: center">
            <button id="login" onclick="checkCredentials();" name="login" class="login-btn">Login</button>
        </div>

        <!-- Moved INSIDE container -->
        <div class="signup">
            <p>Don't have an account? <a onclick="document.getElementById('chooseRole').showModal();">Sign Up</a></p>
        </div>
    </div>

    <dialog id="chooseRole">
        <div id="dialogMsg">
            <button onclick="document.getElementById('chooseRole').close();" id="closeDialog" aria-label="Close dialog"><i class="bi bi-x-lg" style="color: red;"></i></button>
            <h3>Who are you signing up as?</h3>
        </div>
        <div class="dialogBtn">
            <button onclick="window.location.href='stu-signup.php';" class="login-btn dialog-btn">Student</button>
            <button onclick="window.location.href='lec-signup.php';" class="login-btn dialog-btn">Lecturer</button>
        </div>
    </dialog>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault(); //prevents default form submission
                    document.querySelector("#login").click();
                }
            });
        });

        function checkCredentials() {
            const email = document.getElementById("email").value;
            const password = document.getElementById("passwordInput").value;
            
            const request = new XMLHttpRequest();

            request.onload = function() {
                const response = JSON.parse(this.responseText);

                document.getElementById("emailError").style.display = "none";
                document.getElementById("passwordError").style.display = "none";

                if (response.status === "success") {
                    if (response.role === "student") {
                        window.location.href = "stu-home.php";
                    } else if (response.role === "lecturer") {
                        window.location.href = "lec_home.php";
                    } else if (response.role === "admin") {
                        window.location.href = "admin_proposal.php";
                    } else if (response.role === "pending lecturer") {
                        window.location.href = "stu-home.php";
                    } else {
                        //guest user
                        window.location.href = "index.php";
                    }
                    // window.location.href = "home.php";
                } else if (response.status === "email_error") {
                    document.getElementById("emailError").style.display = "block";
                } else if (response.status === "password_error") {
                    document.getElementById("passwordError").style.display = "block";
                } else { //for removed user
                    window.location.href = "index.php?email="+response.user_email+"&name="+response.name+"&reason="+response.removed_reason+"&status="+response.removed_status;
                }
            };

            const data = new FormData();
            data.append("email", email);
            data.append("password", password);

            request.open("POST", "login-page2.php");
            request.send(data);
        };

        function clearError() {
            document.getElementById("emailError").style.display = "none";
            document.getElementById("passwordError").style.display = "none";
        }

        function hidePW() {
            var id = document.getElementById("passwordInput");
            let pwShow = document.getElementById("pwVisible");
            let pwHide = document.getElementById("pwNotVisible");
            if (id.type === "password") {
            id.type = "text";
            pwHide.style.display = "none";
            pwShow.style.display = "block";
            } else {
            id.type = "password";
            pwHide.style.display = "block";
            pwShow.style.display = "none";
            }
        }
    </script>
</body>
</html>
