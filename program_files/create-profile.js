function checkEmail() {
    const email = document.getElementById("emailInput").value;
    const error = document.getElementById("emailError");
    if (!validator.isEmail(email)) { //validate using third-party online script - validator.min.js
        error.innerHTML = "Email is invalid.";
        return true;
    } else if (emails.includes(email)) {
        error.innerHTML = "Email already exists.";
        return true;
    } else if (banEmails.some(b => b.email === email && b.status === "Banned")) {
        error.innerHTML = "This email address has been banned.";
        return true;
    } else {
        error.innerHTML = "";
        return false; 
    }
}

function checkItem(type) { //name or job title
    let formattedItem, charMax;
    switch (type) {
        case "job": formattedItem = "Job Title"; charMax = 50; break;
        case "name": formattedItem = "Name"; charMax = 100; break;
    }

    let item = document.getElementById(type+"Input").value.trim();
    if (item.length > charMax) {
        document.getElementById(type+"Error").innerHTML = formattedItem+" is too long.";
        return true;
    } else if (item.length < 3) {
        document.getElementById(type+"Error").innerHTML = formattedItem+" is too short.";
        return true;
    } else {
        document.getElementById(type+"Error").innerHTML = "";
        return false;
    }
}

function checkDOB() {
    const dob = document.getElementById("dobInput").value;
    const error = document.getElementById("dobError");
    if (dob !== "") { //dob chosen
        error.innerHTML = "";
        return false;
    }
    error.innerHTML = "Choose or type your date of birth.";
    return true;
}

function checkPassword() {
    let pw = document.getElementById("passwordInput").value;
    //check length and character variety
    let pwCon1 = pwCheckCharAndCase(pw), pwCon2 = pwCheckLen(pw);
    if (pwCon1 && pwCon2) {
        return false;
    } else {
        return true;
    }
}

function turnRed(id) {
    document.getElementById(id).style.color = "red";
}

function turnGreen(id) {
    document.getElementById(id).style.color = "green";
}

//ensure password has uppercase and lowercase letters, numbers and symbols
function pwCheckCharAndCase(pw) {
    //check ascii 33 - 126 and check alphabet case
    //charSign used to indicate that all three types of characters are used
    //caseSign used to indicate that uppercase and lowercase alphabets are used
    //alp = alphabets, sym = symbol, num = number, up = uppercase alp, low = lowercase alp
    let charSign = 0, caseSign = 0, sym = 0, alp = 0, num = 0, up = 0, low = 0;
    for (let char of pw) {
        let code = char.charCodeAt(0);  //using ascii code to check
        if (!(code >= 33 && code <= 126)) {
            break;
        } 
        else {

            if ((code >= 33 && code <= 47) || (code >= 58 && code <= 64) || (code >= 91 && code <= 96) || (code >= 123 && code <= 126))
            {
                sym++;
            }
            else if ((code >= 48 && code <= 57)) {
                num++;
            }
            else if ((code >= 65 && code <= 90) || (code >= 97 && code <= 122)) {
                alp++;
                if (code >= 65 && code <= 90) {
                    up++;
                } else {
                    low++;
                }
            } 
        }
    } 

    if (sym > 0 && alp > 0 && num > 0) {
        charSign++;
        turnGreen("pw1");
    } else {
        turnRed("pw1");
    }

    if (up > 0 && low > 0) {
        caseSign++;
        turnGreen("pw2");
    } else {
        turnRed("pw2");
    }

    if (charSign == 1 && caseSign == 1) {
        return true;
    } else {
        return false;
    }
}

//validate password length
function pwCheckLen(pw) {
    //check length
    let len = pw.length;
    if (len < 8) {
        turnRed("pw3");
        return false;
    } else {
        turnGreen("pw3");
        return true;
    }
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

function checkPersonalDetails() {
    document.getElementById("nextBtn1").disabled = checkEmail() | checkItem("name") | checkPassword() | checkDOB();
    //all must be false to enable next button
    //constraint checking in create-profile.js
}