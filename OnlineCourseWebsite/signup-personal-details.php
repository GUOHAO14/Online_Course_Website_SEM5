<div class="signup-container" id="detailSlide">
    <h2>Set Up Your Account</h2>
    <div class="edu-selection" id="detailsContainer">
        <h4><label for="nameInput">Name</label></h4>
        <input oninput="checkItem('name'); checkPersonalDetails();" type="text" name="name" id="nameInput">
        <div style="color: red;" id="nameError"></div>
        <h4><label for="nameInput">Date of Birth</label></h4>
        <input oninput="checkDOB(); checkPersonalDetails();" type="date" name="dob" id="dobInput">
        <div style="color: red;" id="dobError"></div>
        <h4><label for="emailInput">Email</label></h4>
        <input oninput="checkEmail(); checkPersonalDetails();" type="text" name="email" id="emailInput">
        <div style="color: red;" id="emailError"></div>
        <h4><label for="passwordInput">Password</label></h4>
        <div class="pwContainer">
            <input type="password" name="password" id="passwordInput" onfocus="checkPassword();" oninput="checkPassword(); checkPersonalDetails();" onfocusout="checkPassword();">

            <img id="pwVisible" src="system_img/visibilityOff.png" alt="VisibilityOff" onclick="hidePW()"><img id="pwNotVisible" src="system_img/visibilityOn.png" alt="VisibilityOn" onclick="hidePW()">
        </div>
        <div>
            <p class="msgPW" id="msgPW">Password requirement:<br></p>
            <div id="ulPW" >
                <ul id="pwList" style="padding-left: 0.9rem;">
                    <li id="pw1">Combination of English alphabets, numbers, and symbols</li>
                    <li id="pw2">Use both uppercase and lowercase alphabets</li>
                    <li id="pw3">More than 8 characters</li>
                </ul>
            </div>
        </div>
        <div class="buttons">
            <button type="button" onclick="window.location.href='index.php';">Back</button><span></span>
            <button class="nextBtn" id="nextBtn1" onclick="revealSelection('details', 'next');">Next</button>
        </div>
    </div>
</div>