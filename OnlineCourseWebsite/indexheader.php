<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Core of Course</title>
  <link rel="stylesheet" href="stu-shared.css">
  <style>
    :root {
      --bg-color: white;
      --text-color: black;
    }

    [data-theme='dark'] {
      --bg-color: #121212;
      --text-color: #f0f0f0;
    }

    body {
      margin: 0;
      font-family: 'Rajdhani', sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
    }

    html, body {
      height: 100%;
      padding-top: 0;
    }

    .page-container {
      display: flex;
      flex-direction: column;
      height: fit-content;
    }

    .top_header {
      background: linear-gradient(135deg, rgba(74, 20, 140, 0.8) 0%, rgba(49, 27, 146, 0.8) 100%);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 1rem;
      border-bottom: 2px solid rgba(0, 0, 0, 0.2);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      min-height: 80px;
    }

    .brand-container {
      display: flex;
      align-items: center;
      gap: 8px;
      flex: 1;
      min-width: 200px;
    }

    .logo img {
      width: 60px;
      height: 60px;
      border: 2px solid #000;
      border-radius: 8px;
      object-fit: contain;
      margin-left: 0.5rem;
    }

    .website_name {
      color: white;
      font-size: 40px;
      white-space: nowrap;
    }

    .website_name > p {
      margin: 0.2rem 0;
    }

    .auth-buttons {
      display: flex;
      gap: 10px;
    }

    .auth-buttons button {
      font-family: 'Rajdhani', sans-serif;
      font-size: 18px;
      padding: 8px 16px;
      background-color: lightcyan;
      border: 2px solid black;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
      min-width: 100px; /* Force equal width */
      text-align: center;
    }

    .auth-buttons button:hover {
      transform: scale(1.05);
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
      border-color: #ff6600;
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

    .login-btn {
        width: 35%;
        padding: 10px;
        border: none;
        border-radius: 5px;
        background-color: #d3efe9;
        color: #171717;
        font-weight: bold;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 15px;
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

    #closeDialog:active {
      background-color: grey;
    }

    #closeDialog:hover {
      background-color: lightgrey;
    }

    @media (max-width: 768px) {
      .website_name {
        font-size: 28px;
      }

      .auth-buttons button {
        font-size: 16px;
        padding: 6px 12px;
      }
    }

    @media (max-width: 525px) {
      .top_header {
        padding: 0.2rem 0 0.2rem 0.3rem;
        display: grid;
        grid-template-columns: 1fr;
      }

      .auth-buttons {
        font-size: 16px;
        padding: 6px 12px;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
  <div class="page-container">
    <div class="top_header">
      <div class="brand-container">
        <div class="logo">
          <img src="system_img/Capstone real logo.png" alt="Logo" />
        </div>
        <div class="website_name">
          <p>Core of Course</p>
        </div>
      </div>

      <div class="auth-buttons">
        <button onclick="location.href='login-page.php'">Login</button>
        <button onclick="document.getElementById('chooseRole').showModal();">Sign Up</button>
      </div>
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
  
</script>
</body>
</html>
