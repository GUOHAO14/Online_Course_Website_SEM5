<?php
include "check-duplicate-email.php";

include "conn.php";
$stmt = $conn->prepare("SELECT * FROM field");
$stmt->execute();
$exe = $stmt->get_result();
$fieldList = [];
while($row = $exe->fetch_assoc()) {
    $fieldList[] = $row;
}
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM career");
$stmt->execute();
$exe = $stmt->get_result();
$careerList = [];
while($row = $exe->fetch_assoc()) {
    $careerList[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up as Student</title>
    <link rel="stylesheet" href="prefrences-2.css">
    <link rel="stylesheet" href="preferences-1.css">
    <link rel="stylesheet" href="stu-shared.css">
    <script src="https://cdn.jsdelivr.net/npm/validator@13.6.0/validator.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            padding-top: 0;
        }
    </style>
</head>
<body>
    <div class="blurOverlay"></div>
    <form action="signup-submission.php" method="post" id="stuSignupForm" onsubmit="preventSubmission(event)">
        <?php include "signup-personal-details.php";?>
        <input type="hidden" name="formType" value="student">
        <div class="signup-container" id="eduSlide">
            <h2>What's your highest level of education?</h2>
    
            <div class="edu-selection">
                <label class="option">
                    <input type="radio" name="education" value="High School">
                    High School
                </label>
                <label class="option">
                    <input type="radio" name="education" value="Foundation">
                    Foundation
                </label>
                <label class="option">
                    <input type="radio" name="education" value="Diploma">
                    Diploma
                </label>
                <label class="option">
                    <input type="radio" name="education" value="Bachelor's Degree">
                    Bachelor's Degree
                </label>
                <label class="option">
                    <input type="radio" name="education" value="Master's Degree">
                    Master's Degree
                </label>
                <label class="option">
                    <input type="radio" name="education" value="Doctorate Degree">
                    Doctorate Degree
                </label>
                <label class="option">
                    <input type="radio" name="education" value="Other">
                    Other
                </label>
                
                <div class="buttons">
                    <button onclick="revealSelection('edu', 'back');">Back</button>
                    <button class="nextBtn" id="nextBtn2" onclick="revealSelection('edu', 'next');">Next</button>
                </div>
            </div>
        </div>

        <div class="signup-container" id="learnSlide">
            <h2>What's your learning style?</h2>
    
            <div class="edu-selection">
                <label class="option">
                    <input type="radio" name="learning" value="Text-Based">
                    Text-Based Learning
                </label>
                <label class="option">
                    <input type="radio" name="learning" value="Visual">
                    Visual Learning
                </label>
                <label class="option">
                    <input type="radio" name="learning" value="Audio">
                    Audio Learning
                </label>
                <label class="option">
                    <input type="radio" name="learning" value="Mixed">
                    Mixed Learning (no specific style)
                </label>
                
                <div class="buttons">
                    <button onclick="revealSelection('learn', 'back');">Back</button>
                    <button class="nextBtn" id="nextBtn3" onclick="revealSelection('learn', 'next');">Next</button>
                </div>
            </div>
        </div>

        <div class="step-subtitle"></div>
        <div class="signup-container" id="fieldSlide">
            <h2>Which field(s) are you interested in?</h2>
            
            <div class="limit"><span class="min">Min: 1</span> <span class="max">Max: 8</span> Fields</div>

            <input type="text" class="field-search-bar" placeholder="Find a field">

            <div class="fields-grid-wrapper">
                <div class="fields-grid" id="fieldsGrid">
                    <!-- Main fields -->
                    <?php
                        $first9Fields = [];
                        $extraFields = [];
                        $count = 0;
                        foreach ($fieldList as $field) {
                            $template = 
                            '<div onclick="clickField(this, '.$field["field_id"].')" class="field-card">
                                <div class="field_name">'.$field["name"].'</div>
                                <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
                            </div>';

                            if ($count < 9) {
                                $first9Fields [] = $template;
                                $count++;
                            } else {
                                $extraFields [] = $template;
                            }
                        }

                        foreach ($first9Fields as $field) {
                            echo $field;
                        }
                    ?>
                </div>
                
                <!-- Extra fields -->
                <div class="extra-fields" id="extraFields">
                    <div class="fields-grid">
                        <?php
                            foreach ($extraFields as $field) {
                                echo $field;
                            }
                        ?>
                    </div>
                </div>
            </div>
            <input type="hidden" name="selectedFields" id="selectedFieldsInput">
            <div style="width: 100%; padding: 0 26%;" class="buttons">
                <button onclick="revealSelection('field', 'back');">Back</button>
                <button class="nextBtn" id="nextBtn4" onclick="revealSelection('field', 'next');">Next</button>
            </div>
            <div class="view-all" id="moreFieldsBtn">+ View More Fields</div>
        </div>


        <div class="signup-container" id="careerSlide">
            <h2>Which career(s) are you interested in?</h2>
            
            <div class="limit"><span class="min">Min: 1</span> <span class="max">Max: 8</span>Career</div>

            <input type="text" class="career-search-bar" placeholder="Find a field">

            <div class="fields-grid-wrapper">
                <div class="fields-grid" id="careersGrid">
                    <!-- Main fields -->
                    <?php
                        $first9Careers = [];
                        $extraCareers = [];
                        $count = 0;
                        foreach ($careerList as $career) {
                            $template = 
                            '<div onclick="clickCareer(this, '.$career["career_id"].')" class="career-card">
                                <div class="career_name">'.$career["name"].'</div>
                                <div class="icon-wrapper"><i class="fas fa-plus plus-icon"></i><i class="fas fa-check check-icon"></i></div>
                            </div>';

                            if ($count < 9) {
                                $first9Careers [] = $template;
                                $count++;
                            } else {
                                $extraCareers [] = $template;
                            }
                        }

                        foreach ($first9Careers as $career) {
                            echo $career;
                        }
                    ?>
                </div>
                
                <!-- Extra fields -->
                <div class="extra-fields" id="extraCareers">
                    <div class="fields-grid">
                        <?php
                            foreach ($extraCareers as $career) {
                                echo $career;
                            }
                        ?>
                    </div>
                </div>
            </div>
            <input type="hidden" name="selectedCareers" id="selectedCareersInput">
            <div style="width: 100%; padding: 0 26%;" class="buttons">
                <button onclick="revealSelection('career', 'back');">Back</button>
                <button class="nextBtn" id="nextBtn5" onclick="revealSelection('career', 'next');">Finish</button>
            </div>
            <div class="view-all" id="moreCareersBtn">+ View More Careers</div>
        </div>
        <input type="hidden" name="fieldExpInput" id="fieldExpInput">
        <input type="hidden" name="careerExpInput" id="careerExpInput">
    </form>
    
    <dialog style="height: fit-content; padding-bottom: 2rem; padding-right: 1.2rem; padding-left: 1.2rem;" id="chooseFieldExp">
        <input type="hidden" name="specificFieldId" id="specificFieldId">
        <h3>Choose your experience level in this field</h3>
        <label class="option">
            <input type="radio" name="fieldExp" value="beginner">
            Beginner
        </label>
        <label class="option">
            <input type="radio" name="fieldExp" value="intermediate">
            Intermediate
        </label>
        <label class="option">
            <input type="radio" name="fieldExp" value="advanced">
            Advanced
        </label>
    </dialog>

    <dialog style="height: fit-content; padding-bottom: 2rem; padding-right: 1.2rem; padding-left: 1.2rem;" id="chooseCareerExp">
        <input type="hidden" name="specificCareerId" id="specificCareerId">
        <h3>Choose your experience level in this career</h3>
        <label class="option">
            <input type="radio" name="careerExp" value="beginner">
            Beginner
        </label>
        <label class="option">
            <input type="radio" name="careerExp" value="intermediate">
            Intermediate
        </label>
        <label class="option">
            <input type="radio" name="careerExp" value="advanced">
            Advanced
        </label>
    </dialog>

    <script>
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
        const fieldExpRadios = document.querySelectorAll('input[name="fieldExp"]');
        const careerExpRadios = document.querySelectorAll('input[name="careerExp"]');

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

        const fieldExpArray = new Map();
        fieldExpRadios.forEach(function(radio) {
            radio.addEventListener("click", function() {
                let id = document.getElementById("specificFieldId").value;
                fieldExpArray.set(id, radio.value);
                document.querySelectorAll('input[type="radio"][name="fieldExp"]').forEach(radio => {
                    radio.checked = false;
                });
                const obj = Object.fromEntries(fieldExpArray);
                const jsonStr = JSON.stringify(obj);

                document.getElementById("fieldExpInput").value = jsonStr;
                document.getElementById("chooseFieldExp").close();
                document.querySelector(".blurOverlay").style.visibility = "hidden";

                console.dir(document.getElementById("fieldExpInput").value);
                console.dir(fieldExpArray);
            });
        });

        const careerExpArray = new Map();
        careerExpRadios.forEach(function(radio) {
            radio.addEventListener("click", function() {
                let id = document.getElementById("specificCareerId").value;
                careerExpArray.set(id, radio.value);
                document.querySelectorAll('input[type="radio"][name="careerExp"]').forEach(radio => {
                    radio.checked = false;
                });
                const obj = Object.fromEntries(careerExpArray);
                const jsonStr = JSON.stringify(obj);
                document.getElementById("careerExpInput").value = jsonStr;
                document.getElementById("chooseCareerExp").close();
                document.querySelector(".blurOverlay").style.visibility = "hidden";

                console.dir(document.getElementById("careerExpInput").value);
                console.dir(careerExpArray);
            });
        });

        function revealSelection(type, direction) {
            //slide between selections
            const detailCard = document.getElementById("detailSlide");
            const eduCard = document.getElementById("eduSlide");
            const learnCard = document.getElementById("learnSlide");
            const fieldCard = document.getElementById("fieldSlide");
            const careerCard = document.getElementById("careerSlide");
            
            if (type === "details" && direction === "next") {
                detailCard.style.opacity = "0";
                setTimeout(() => {
                    detailCard.style.display = "none";
                    detailCard.style.opacity = "unset";
                    eduCard.style.opacity = "0";
                    eduCard.style.display = "flex";
                    void eduCard.offsetWidth;
                    eduCard.style.opacity = "1";
                }, 500);
            } else if (type === "edu" && direction === "back") {
                eduCard.style.opacity = "0";
                setTimeout(() => {
                    eduCard.style.display = "none";
                    eduCard.style.opacity = "unset";
                    detailCard.style.opacity = "0";
                    detailCard.style.display = "flex";
                    void detailCard.offsetWidth;
                    detailCard.style.opacity = "1";
                }, 500);
            } else if (type === "edu" && direction === "next") {
                eduCard.style.opacity = "0";
                setTimeout(() => {
                    eduCard.style.display = "none";
                    eduCard.style.opacity = "unset";
                    learnCard.style.opacity = "0";
                    learnCard.style.display = "flex";
                    void learnCard.offsetWidth;
                    learnCard.style.opacity = "1";
                }, 500);
            } else if (type === "learn" && direction === "back") {
                learnCard.style.opacity = "0";
                setTimeout(() => {
                    learnCard.style.display = "none";
                    learnCard.style.opacity = "unset";
                    eduCard.style.opacity = "0";
                    eduCard.style.display = "flex";
                    void eduCard.offsetWidth;
                    eduCard.style.opacity = "1";
                }, 500);
            } else if (type === "learn" && direction === "next") {
                learnCard.style.opacity = "0";
                setTimeout(() => {
                    learnCard.style.display = "none";
                    learnCard.style.opacity = "unset";
                    fieldSlide.style.opacity = "0";
                    fieldSlide.style.display = "flex";
                    void fieldSlide.offsetWidth;
                    fieldSlide.style.opacity = "1";
                }, 500);
            } else if (type === "field" && direction === "back") {
                fieldCard.style.opacity = "0";
                setTimeout(() => {
                    fieldCard.style.display = "none";
                    fieldCard.style.opacity = "unset";
                    learnCard.style.opacity = "0";
                    learnCard.style.display = "flex";
                    void learnCard.offsetWidth;
                    learnCard.style.opacity = "1";
                }, 500);
            } else if (type === "field" && direction === "next") {
                fieldCard.style.opacity = "0";
                setTimeout(() => {
                    fieldCard.style.display = "none";
                    fieldCard.style.opacity = "unset";
                    careerCard.style.opacity = "0";
                    careerCard.style.display = "flex";
                    void careerCard.offsetWidth;
                    careerCard.style.opacity = "1";
                }, 500);
            } else if (type === "career" && direction === "back") {
                careerCard.style.opacity = "0";
                setTimeout(() => {
                    careerCard.style.display = "none";
                    careerCard.style.opacity = "unset";
                    fieldCard.style.opacity = "0";
                    fieldCard.style.display = "flex";
                    void fieldCard.offsetWidth;
                    fieldCard.style.opacity = "1";
                }, 500);
            } else if (type === "career" && direction === "next") {
                document.getElementById("stuSignupForm").submit();
            }
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
        let banEmails = [];
        bannedEmails.forEach(bannedEmail => {
            banEmails.push({
                email: bannedEmail.user_email, 
                status: bannedEmail.removed_status
            });
        });

        const moreFieldsBtn = document.getElementById('moreFieldsBtn');
        const extraFields = document.getElementById('extraFields');
        const fieldCards = document.querySelectorAll('.field-card'); // Get all field cards
        const fieldsPerPage = 9; // Number of fields to show per click
        const maxSelections = 8;
        const minSelections = 1;
        const fieldSearch = document.querySelector('.field-search-bar');
        
            // Total number of fields
        const totalFields = fieldCards.length;
        let currentPage = 0; // Track the current page of fields

        // Initially hide all extra fields
        fieldCards.forEach((field, index) => {
            if (index >= fieldsPerPage) {
                field.style.display = 'none';
            }
        });
    
        // Show the next set of fields
        moreFieldsBtn.addEventListener('click', () => {
        extraFields.classList.add('show');

        const allFieldCards = document.querySelectorAll('.field-card');
        const visibleFields = Array.from(allFieldCards).filter(card => card.style.display !== 'none');
        
        const start = visibleFields.length;
        const end = start + fieldsPerPage;

        let shownCount = 0;
        for (let i = start; i < allFieldCards.length && shownCount < fieldsPerPage; i++) {
            allFieldCards[i].style.display = 'flex';
            shownCount++;
        }

        setTimeout(() => {
            allFieldCards[start]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);

            if (document.querySelectorAll('.field-card:not([style*="display: none"])').length >= allFieldCards.length) {
                moreFieldsBtn.style.display = 'none';
            }
        });

        
        // fieldCards.forEach(card => {
        //     card.addEventListener('click', () => {
        //         card.classList.toggle('selected');
        //         const selectedCards = document.querySelectorAll('.field-card.selected');
        //         if (selectedCards.length <= maxSelections && selectedCards.length >= minSelections) {
        //             document.getElementById("nextBtn4").disabled = false;
        //         } else {
        //             document.getElementById("nextBtn4").disabled = true;
        //         }
        //     });
        // });
      
        // Live search with highlight and scroll
        fieldSearch.addEventListener('input', () => {
            const fieldSearchValue = fieldSearch.value.toLowerCase();
            let firstMatch = null;

            fieldCards.forEach((card, index) => {
                const fieldNameDiv = card.querySelector('.field_name');
                const originalText = fieldNameDiv.getAttribute('data-name') || fieldNameDiv.textContent.trim();
                fieldNameDiv.setAttribute('data-name', originalText); // Save original name if not saved yet

                if (fieldSearchValue === '') {
                    // Reset everything
                    fieldNameDiv.innerHTML = originalText;
                    card.style.display = index < fieldsPerPage ? 'flex' : 'none';
                } else if (originalText.toLowerCase().includes(fieldSearchValue)) {
                    card.style.display = 'flex';
                    if (!firstMatch) firstMatch = card;

                    const regex = new RegExp(`(${fieldSearchValue})`, 'gi');
                    const highlightedText = originalText.replace(regex, '<strong>$1</strong>');
                    fieldNameDiv.innerHTML = highlightedText;
                } else {
                    card.style.display = 'none';
                }
            });

            if (fieldSearchValue === '') {
                moreFieldsBtn.style.display = 'block';
            } else {
                moreFieldsBtn.style.display = 'none'; // hide "More Fields" button during search
            }

            if (firstMatch) {
                firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        function clickField(element, id) {
            console.log("Clicked field with id:", id);
            
            // Ensure the id is correctly passed as a string
            const idStr = String(id);
            console.log("ID as string:", idStr);
            
            if (element.classList.contains("selected")) {
                if (fieldExpArray.has(idStr)) {
                    console.log("Before delete operation: ", fieldExpArray);
                    fieldExpArray.delete(idStr); // Remove the key-value pair
                    console.log("After delete operation: ", fieldExpArray);
                } else {
                    console.log("Key not found:", idStr);
                }

                const obj = Object.fromEntries(fieldExpArray);
                const jsonStr = JSON.stringify(obj);
                document.getElementById("fieldExpInput").value = jsonStr;
            } else {
                document.getElementById("specificFieldId").value = idStr;
                document.getElementById("chooseFieldExp").showModal();
                document.querySelector(".blurOverlay").style.visibility = "visible";
            }

            element.classList.toggle('selected');
            const selectedCards = document.querySelectorAll('.field-card.selected');
            if (selectedCards.length <= maxSelections && selectedCards.length >= minSelections) {
                document.getElementById("nextBtn4").disabled = false;
            } else {
                document.getElementById("nextBtn4").disabled = true;
            }

            console.dir(document.getElementById("fieldExpInput").value);
            console.dir(fieldExpArray);
        }
        // document.getElementById('finishBtn').addEventListener('click', () => {
        //     const selected = document.querySelectorAll('.field-card.selected');
        //     if (selected.length < minSelections) {
        //         alert(`Please select at least ${minSelections} field(s).`);
        //     } else {
        //         // Proceed to next step or submit form
        //         alert('Selections submitted successfully!');
        //     }
        // });


        const moreCareerBtn = document.getElementById('moreCareerBtn');
        const extraCareer = document.getElementById('extraCareer');
        const careerCards = document.querySelectorAll('.career-card'); // Get all career cards
        const careersPerPage = 9; // Number of fields to show per click
        const maxCareerSelections = 8;
        const minCareerSelections = 1;
        const careerSearch = document.querySelector('.career-search-bar');
        
            // Total number of fields
        const totalCareers = careerCards.length;
        let currentCareerPage = 0; // Track the current page of fields

        // Initially hide all extra fields
        careerCards.forEach((career, index) => {
            if (index >= careersPerPage) {
                career.style.display = 'none';
            }
        });
    
        // Show the next set of fields
        moreCareersBtn.addEventListener('click', () => {
            extraCareers.classList.add('show');

            const allCareerCards = document.querySelectorAll('.career-card');
            const visibleCareers = Array.from(allCareerCards).filter(card => card.style.display !== 'none');
            
            const start = visibleCareers.length;
            const end = start + careersPerPage;

            let shownCount = 0;
            for (let i = start; i < allCareerCards.length && shownCount < careersPerPage; i++) {
                allCareerCards[i].style.display = 'flex';
                shownCount++;
            }

            setTimeout(() => {
                allCareerCards[start]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 300);

            if (document.querySelectorAll('.career-card:not([style*="display: none"])').length >= allCareerCards.length) {
                moreCareersBtn.style.display = 'none';
            }
        });

        careerSearch.addEventListener('input', () => {
            const careerSearchValue = careerSearch.value.toLowerCase();
            let firstMatch = null;

            careerCards.forEach((card, index) => {
                const careerNameDiv = card.querySelector('.career_name');
                const originalText = careerNameDiv.getAttribute('data-name') || careerNameDiv.textContent.trim();
                careerNameDiv.setAttribute('data-name', originalText); // Save original name if not saved yet

                if (careerSearchValue === '') {
                    // Reset everything
                    careerNameDiv.innerHTML = originalText;
                    card.style.display = index < fieldsPerPage ? 'flex' : 'none';
                } else if (originalText.toLowerCase().includes(careerSearchValue)) {
                    card.style.display = 'flex';
                    if (!firstMatch) firstMatch = card;

                    const regex = new RegExp(`(${careerSearchValue})`, 'gi');
                    const highlightedText = originalText.replace(regex, '<strong>$1</strong>');
                    careerNameDiv.innerHTML = highlightedText;
                } else {
                    card.style.display = 'none';
                }
            });

            if (careerSearchValue === '') {
                moreFieldsBtn.style.display = 'block';
            } else {
                moreFieldsBtn.style.display = 'none'; // hide "More Fields" button during search
            }

            if (firstMatch) {
                firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        function clickCareer(element, id) {
            console.log("Clicked career with id:", id);
            
            // Ensure the id is correctly passed as a string
            const idStr = String(id);
            console.log("ID as string:", idStr);
            
            if (element.classList.contains("selected")) {
                if (careerExpArray.has(idStr)) {
                    console.log("Before delete operation: ", careerExpArray);
                    careerExpArray.delete(idStr); // Remove the key-value pair
                    console.log("After delete operation: ", careerExpArray);
                } else {
                    console.log("Key not found:", idStr);
                }

                const obj = Object.fromEntries(careerExpArray);
                const jsonStr = JSON.stringify(obj);
                document.getElementById("careerExpInput").value = jsonStr;
            } else {
                document.getElementById("specificCareerId").value = idStr;
                document.getElementById("chooseCareerExp").showModal();
                document.querySelector(".blurOverlay").style.visibility = "visible";
            }

            element.classList.toggle('selected');
            const selectedCards = document.querySelectorAll('.career-card.selected');
            if (selectedCards.length <= maxSelections && selectedCards.length >= minSelections) {
                document.getElementById("nextBtn5").disabled = false;
            } else {
                document.getElementById("nextBtn5").disabled = true;
            }

            console.dir(document.getElementById("careerExpInput").value);
            console.dir(careerExpArray);
        }

    </script>
    <script src="create-profile.js"></script>
</body>
</html>