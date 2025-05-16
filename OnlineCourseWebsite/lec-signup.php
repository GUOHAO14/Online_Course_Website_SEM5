<?php
session_start();
$resetSignal = false;
//session variable from signup-submission
//signal the program that there was a previous signup => inform program to wipe all previous input
//by reloading the form
if (isset($_SESSION["signupStatus"])) {
    session_unset();
    session_destroy();
    session_start();
    $resetSignal = true;
}
include "check-duplicate-email.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up as Lecturer</title>
    <link rel="stylesheet" href="preferences-1.css">
    <link rel="stylesheet" href="stu-shared.css">
    <script src="https://cdn.jsdelivr.net/npm/validator@13.6.0/validator.min.js"></script>
    <style>
        .removePdfBtn:hover {
            background-color: lightgrey;
        }

        body {
            padding-top: 0;
        }
    </style>
</head>
<body>
    <form id="lecturerApplication" action="signup-submission.php" method="post" enctype="multipart/form-data" onsubmit="preventSubmission(event)">
        <?php include "signup-personal-details.php";?>
        <input type="hidden" name="formType" value="lecturer">
        <div class="signup-container" id="lecDetailSlide">
            <h2>Tell us about you as a lecturer</h2>
        
            <div class="edu-selection" id="lecturerDetailContainer">
                <h4>Teaching Experience</h4>
                <div class="customDropdown" id="expDropdownContainer">
                    <button id="expDropdown" onclick="controlDropdown('expDropdownContainer')">
                        <div id="expDpText">Select</div>
                        <div class="fullTableDpImg">
                            <img id="edSrchDown" src="system_img/down.png" alt="Down Arrow">
                        </div>
                    </button>
                    <div id="expOptions">
                        <div class="option" data-exp="Less than 1 year" onclick="chooseExp(this)">Less than 1 year</div>
                        <div class="option" data-exp="1 year" onclick="chooseExp(this)">1 year</div>
                        <div class="option" data-exp="2 years" onclick="chooseExp(this)">2 year</div>
                        <div class="option" data-exp="3 years" onclick="chooseExp(this)">3 year</div>
                        <div class="option" data-exp="4 years" onclick="chooseExp(this)">4 year</div>
                        <div class="option" data-exp="5 - 7 years" onclick="chooseExp(this)">5 - 7 years</div>
                        <div class="option" data-exp="8 - 10 years" onclick="chooseExp(this)">8 - 10 years</div>
                        <div class="option" data-exp="10 - 15 years" onclick="chooseExp(this)">10 - 15 years</div>
                        <div class="option" data-exp="15 years or more" onclick="chooseExp(this)">15 years or more</div>
                    </div>
                    <input type="hidden" name="expInput" id="expInput">
                    <div style="color: red;" id="expError"></div>
                </div>
                
                <h4>Current University</h4>
                <div class="customDropdown" id="uniDropdownContainer">
                    <button id="uniDropdown" onclick="controlDropdown('uniDropdownContainer')">
                        <div id="uniDpText">Select</div>
                        <div class="fullTableDpImg">
                            <img id="edSrchDown" src="system_img/down.png" alt="Down Arrow">
                        </div>
                    </button>
                    <div class="uniContainer">
                        <input type="text" name="" id="uniSearch" onkeyup="filterSearch(id, 'uniOptions')" placeholder="Search University Name">
                    </div>
                    <div id="uniOptions">
                    </div>
                    <input type="hidden" name="uniInput" id="uniInput">
                    <div style="color: red;" id="uniError"></div>
                </div>

                <h4><label for="jobInput">Job Title</label></h4>
                <input oninput="checkSubmit();" type="text" name="job" id="jobInput">
                <div style="color: red; margin-top: -1rem;" id="jobError"></div>
                
                <h4><label for="pdfInput">Supporting Document</label></h4>
                <div>You must submit <b>at least one</b> supporting document in PDF format.</div>
                <div>Document can include teaching certification, university lecturer proving document, or other alike for qualification assessment.</div>
                <div class="pdfUploadContainer">
                    <label for="pdfInput" class="pdfUploadLabel">Choose PDFs</label>
                    <input onchange="checkSubmit();" type="file" id="pdfInput" name="pdfInput[]" accept="application/pdf" multiple hidden>
                    <div id="uploadPdfStatus" class="uploadingMsg"></div>
                    <div id="pdfPreviewContainer" class="pdfPreviewContainer"></div>
                </div>
                <div class="buttons">
                    <button onclick="revealSelection('lecDetail', 'back');">Back</button>
                    <button type="submit" class="nextBtn" id="nextBtn2" onclick="revealSelection('lecDetail', 'next');">Submit</button>
                </div>
            </div>
        </div>
    </form>

    <script src="create-profile.js"></script>
    <script>
        // window.onload = function() {
        //     document.querySelectorAll("input").forEach(input => {
        //         input.value = "";
        //     });
        // };
        let reset = <?php echo $resetSignal? 'true': 'false';?>;
        //reload and wipe previous data if finish signup
        if (reset) {
            window.location.reload(true);
            console.log("nice");
        } else {
            console.log("failed");
        }
        document.addEventListener("DOMContentLoaded", function() {
            document.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    const buttons = document.querySelectorAll(".nextBtn"); 
                    buttons.forEach(button => {
                        button.click();
                    });
                }
            });
        });
        
        function preventSubmission(event) {
            event.preventDefault();
        }

        const nextBtn = document.querySelectorAll(".nextBtn");
        nextBtn.forEach(function(btn) {
            btn.disabled = true;
        });

        const educationRadios = document.querySelectorAll('input[name="education"]');
        const learningRadios = document.querySelectorAll('input[name="learning"]');

        educationRadios.forEach(function(radio) {
            radio.addEventListener("click", function() { //enable next button when education selected
                document.getElementById("nextBtn2").disabled = false;
            });
        });

        learningRadios.forEach(function(radio) {
            radio.addEventListener("click", function() { //enable next button when learning style selected
                document.getElementById("nextBtn3").disabled = false;
            });
        });
        
        const job = document.getElementById("jobInput");
        const submitBtn = document.getElementById("nextBtn2");
        const expError = document.getElementById("expError");
        const uniError = document.getElementById("uniError");
        const pdfError = document.getElementById("uploadPdfStatus");

        function revealSelection(type, direction) {
            //slide between selections
            const detailCard = document.getElementById("detailSlide");
            const lecDetailCard = document.getElementById("lecDetailSlide");
            const learnCard = document.getElementById("learnSlide");
            
            if (type === "details" && direction === "next") {
                detailCard.style.opacity = "0";
                setTimeout(() => {
                    detailCard.style.display = "none";
                    detailCard.style.opacity = "unset";
                    lecDetailCard.style.opacity = "0";
                    lecDetailCard.style.display = "flex";
                    void lecDetailCard.offsetWidth;
                    lecDetailCard.style.opacity = "1";
                }, 500);
            } else if (type === "lecDetail" && direction === "back") {
                lecDetailCard.style.opacity = "0";
                setTimeout(() => {
                    lecDetailCard.style.display = "none";
                    lecDetailCard.style.opacity = "unset";
                    detailCard.style.opacity = "0";
                    detailCard.style.display = "flex";
                    void detailCard.offsetWidth;
                    detailCard.style.opacity = "1";
                }, 500);
            } else if (type === "lecDetail" && direction === "next") {
                document.getElementById("lecturerApplication").submit();
            } 
        }

        function controlDropdown(containerId) {
            const containers = document.querySelectorAll('.customDropdown');
            
            containers.forEach(container => {
                if (container.id === containerId) {
                    container.classList.toggle('open');
                } else {
                    container.classList.remove('open');
                }
            });
        }
        
        const allUniversities = [];
        fetch('malaysian-universities.json')
        .then(response => {
            if (!response.ok) {
            throw new Error('Fetching failed.');
            }
            return response.json();
        })
        .then(data => {
            // data is now a JavaScript array of university objects
            data.forEach(university => {
                console.log(university.name);
                allUniversities.push({
                    name: `${university.name}, ${university.shortName}`
                });
            });

            populateUniversityDropdown(allUniversities);

        })
        .catch(error => {
            console.error("JSON failed somehow.");
        });

        function populateUniversityDropdown(universities) {
            const container = document.getElementById("uniOptions");
            container.innerHTML = ''; // Clear previous entries

            universities.forEach((uni, index) => {
                const option = document.createElement("div");
                option.className = "option";
                option.innerText = uni["name"];
                option.dataset.uni = uni["name"];
                option.onclick = function () {
                    chooseUni(this);
                };
                container.appendChild(option);
            });
        }

        function filterSearch(id, optionList) {
            const input = document.getElementById(id);
            let filter = input.value.toLowerCase();

            const filtered = allUniversities.filter(uni =>
                uni.name.toLowerCase().includes(filter)
            );

            populateUniversityDropdown(filtered);
        }

        function chooseUni(element) {
            let newText = element.innerText;
            document.getElementById("uniDpText").textContent = newText;
            controlDropdown("uniDropdownContainer");

            let uni = element.dataset.uni;

            let uniInput = document.getElementById("uniInput");
            uniInput.value = uni;

            checkSubmit();
        }

        function chooseExp(element) {
            let newText = element.innerText;
            document.getElementById("expDpText").textContent = newText;
            controlDropdown("expDropdownContainer");

            let exp = element.dataset.exp;

            let expInput = document.getElementById("expInput");
            expInput.value = exp;

            checkSubmit();
        }

        //existing emails in database from signup-personal-details
        //used for checking email uniqueness
        let existingEmails = <?php echo json_encode($existingEmails);?>;
        let emails = [];
        existingEmails.forEach(function(item) {
            emails.push(item.user_email);
        });

        //list of banned emails in database from signup-personal-details
        //used to check if email is banned => cannot signup
        let bannedEmails = <?php echo json_encode($bannedEmails);?>;
        console.dir(bannedEmails);
        let banEmails = [];
        bannedEmails.forEach(bannedEmail => {
            banEmails.push({
                email: bannedEmail.user_email, 
                status: bannedEmail.removed_status
            });
        });

        const pdfInput = document.getElementById("pdfInput");
        const pdfPreviewContainer = document.getElementById("pdfPreviewContainer");
        const uploadPdfStatus = document.getElementById("uploadPdfStatus");

        const selectedFiles = new Map(); // {filename: File}

        pdfInput.addEventListener("change", () => {
            const files = Array.from(pdfInput.files);
            let duplicateFound = false;

            files.forEach(file => {
                if (file.type !== "application/pdf") return;

                if (selectedFiles.has(file.name)) {
                duplicateFound = true;
                return;
                }

                selectedFiles.set(file.name, file);
                renderFilePreview(file);
            });

            pdfInput.value = "";
            updateInputFiles();

            uploadPdfStatus.textContent = duplicateFound ? "Duplicate file(s) ignored." : "";
            uploadPdfStatus.style.color = duplicateFound ? "red" : "gray";
        });

        function renderFilePreview(file) {
            const box = document.createElement("div");
            box.className = "pdfPreviewBox";

            const fileURL = URL.createObjectURL(file);

            box.innerHTML = `
                <div style="display: flex; align-items: center;">
                <a href="${fileURL}" target="_blank"><img src="system_img/pdf-icon.png" alt="PDF" title="PDF"></a>
                <a href="${fileURL}" target="_blank" style="margin-left: 10px;">${file.name}</a>
                </div>
            `;

            const removeBtn = document.createElement("button");
            removeBtn.className = "removePdfBtn";
            removeBtn.innerHTML = "&times;";
            removeBtn.title = "Remove this file";
            removeBtn.onclick = () => {
                selectedFiles.delete(file.name);
                pdfPreviewContainer.removeChild(box);
                URL.revokeObjectURL(fileURL);
                updateInputFiles();
            };

            box.appendChild(removeBtn);
            pdfPreviewContainer.appendChild(box);
        }

        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            pdfInput.files = dataTransfer.files;

            checkSubmit();
        }

        
        function checkSubmit() {
            pdfError.style.color = "red";
            let expStatus, uniStatus, pdfStatus, jobStatus = checkItem('job');

            if (document.getElementById("expInput").value) {
                expStatus = false;
                expError.innerHTML = "";
            } else {
                expStatus = true;
                expError.innerHTML = "Please select a value.";
            }

            if (document.getElementById("uniInput").value) {
                uniStatus = false;
                uniError.innerHTML = "";
            } else {
                uniStatus = true;
                uniError.innerHTML = "Please select a value.";
            }
            
            if (selectedFiles.size === 0) { //no file input
                pdfError.innerHTML = "Upload a file.";
                pdfStatus = true;
            } else {
                let validSizes = true;
                selectedFiles.forEach(file => {
                    if (file.size > 5 * 1024 * 1024) { //5 MB limit
                        validSizes = false;
                    }
                });

                if (!validSizes) {
                    pdfError.innerHTML = "Each file must be smaller than 5MB.";
                    pdfStatus = true;
                } else {
                    pdfError.innerHTML = "";
                    pdfStatus = false;
                }
            }

            console.log(selectedFiles.size);
            console.log(document.getElementById("pdfInput").length);
            submitBtn.disabled = expStatus || uniStatus || pdfStatus || jobStatus;
        }

        checkSubmit();

    </script>
</body>
</html>