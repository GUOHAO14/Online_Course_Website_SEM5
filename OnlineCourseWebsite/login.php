<?php
session_start();

$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "capstone";

$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email_error = "";
$password_error = "";
$user_found = false;
$password_correct = false;

if (isset($_POST["login"])) {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $input_email = $_POST["email"];
        $input_password = $_POST["password"];

        // Prepare statement to select user by email or username
        $stmt = $conn->prepare("SELECT user_id, unique_id, user_name, user_email, user_password, user_role, image, status FROM people WHERE user_email = ? OR user_name = ? LIMIT 1");
        $stmt->bind_param("ss", $input_email, $input_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $user_found = true;

            // For now, assuming plain text password comparison as before
            if ($user['user_password'] === $input_password) {
                $password_correct = true;
                $_SESSION["isLoggedIn"] = true;
                $_SESSION["user_id"] = $user['user_id'];
                $_SESSION["unique_id"] = $user['unique_id'];
                $_SESSION["user_name"] = $user['user_name'];
                $_SESSION["user_email"] = $user['user_email'];
                $_SESSION["user_role"] = $user['user_role'];
                $_SESSION["image"] = $user['image'];
                $_SESSION["status"] = $user['status'];

                // Redirect based on user_role or specific email/password
                if ($user['user_email'] === "shadowgarden@gmail.com" && $input_password === "Hokage04") {
                    header("Location: lecturedash.php");
                    exit;
                } else {
                    header("Location: forum.php");
                    exit;
                }
            } else {
                $password_error = "Incorrect password";
            }
        } else {
            $email_error = "Account does not exist";
        }

        $stmt->close();
    } else {
        if (empty($_POST["email"])) {
            $email_error = "Email is required";
        }
        if (empty($_POST["password"])) {
            $password_error = "Password is required";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Page</title>
    <style>
        :root {
            --bg-primary: #171717;
            --bg-secondary: #2a3935;
            --text-primary: #d3efe9;
            --text-secondary: #47b298;
            --accent: #19715c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #171717;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 500px;
            aspect-ratio: 1/1;
            max-height: 500px;
            padding: 40px;
            background: linear-gradient(135deg, #19715c 50%, #47b298 50%);
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .container h2 {
            color: #d3efe9;
            font-size: clamp(24px, 5vw, 30px);
            margin-bottom: 40px;
            margin-top: 10px;
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
            color: #19715c;
            font-size: clamp(12px, 2.5vw, 14px);
            text-decoration: none;
        }

        .remember-me {
            color: #ffffff;
            font-size: clamp(12px, 2.5vw, 14px);
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 10px;
        }

        .login-btn {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #d3efe9;
            color: #171717;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s ease;
        }

        .login-btn:hover {
            background-color: #171717;
            color: #d3efe9;
            transition: 0.3s ease;
        }

        .social-login {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .social-login a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            text-decoration: none;
            font-size: clamp(12px, 2.5vw, 14px);
            transition: 0.3s ease;
        }

        .social-login a img {
            margin-right: 8px;
            width: 20px;
            height: 20px;
        }

        .google-login {
            background-color: transparent;
            border: 1px solid #d3efe9;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .google-login:hover {
            background-color: #d3efe9;
            color: #171717;
            transition: 0.3s ease;
        }

        .insta-login {
            background-color: transparent;
            border: 1px solid #d3efe9;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .insta-login:hover {
            background-color: #d3efe9;
            color: #171717;
            transition: 0.3s ease;
        }

        .signup {
            text-align: center;
            margin-top: 15px;
        }

        .signup a {
            font-weight: bold;
            text-decoration: none;
            font-size: clamp(12px, 2.5vw, 14px);
        }

        .signup p {
            color: #171717;
            font-weight: bold;
            text-decoration: none;
        }

        .input-group {
            margin-bottom: 15px;
            position: relative;
        }

        .error-message {
            color: #ff4d4d;
            font-size: 12px;
            position: absolute;
            right: 0;
            top: 0;
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
</head>

<body>
    <div class="container">
        <h2>Welcome back!</h2>

        <form action="login.php" method="post">
            <div class="input-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                <?php if (!empty($email_error)): ?>
                    <span class="error-message"><?php echo $email_error; ?></span>
                <?php endif; ?>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <?php if (!empty($password_error)): ?>
                    <span class="error-message"><?php echo $password_error; ?></span>
                <?php endif; ?>
            </div>
            <div class="forgot-password">
                <a href="#">Forgot password?</a>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me for 30 days</label>
            </div>
            <button type="submit" name="login" class="login-btn">Login</button>
        </form>
        <div class="social-login">
            <a href="https://accounts.google.com/signin/v2/identifier?client_id=YOUR_GOOGLE_CLIENT_ID&redirect_uri=YOUR_REDIRECT_URI&response_type=code&scope=email" class="google-login" target="_blank">
                <img src="https://imagepng.org/wp-content/uploads/2019/08/google-icon-5.png" alt="Google icon"> Sign in with Google
            </a>
            <a href="https://www.instagram.com/accounts/login/" class="insta-login" target="_blank">
                <img src="https://imagepng.org/wp-content/uploads/2017/08/instagram-icone-icon-7.png" alt="Insta icon"> Sign in with Instagram
            </a>
        </div>
        <div class="signup">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</body>

</html>
</create_file>
