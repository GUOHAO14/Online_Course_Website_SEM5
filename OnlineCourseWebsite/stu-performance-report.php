<?php
session_start();
if (isset($_SESSION["role"]) && isset($_SESSION["student_id"]) && $_SESSION["role"] === "student") {
    $studentId = $_SESSION["student_id"];
    $guest = false;
} else {
    header("Location: stu-home.php");
    session_write_close();
    exit();
}

include "conn.php";

$idSQL = "SELECT sign_up_date FROM student WHERE student_id = '$studentId';";
$idExe = mysqli_query($conn, $idSQL);
$date = mysqli_fetch_assoc($idExe)["sign_up_date"];
$date = new DateTime($date);
$signupDate = $date->format('F j, Y');

//retrieve enrolled courses
$enrolledStmt = $conn->prepare("SELECT cp.*, c.course_id, ce.enrol_id
FROM course AS c
JOIN course_proposal AS cp
ON c.proposal_id = cp.proposal_id
RIGHT JOIN course_enrolment AS ce
ON c.course_id = ce.course_id
WHERE ce.student_id = ?;");
$enrolledStmt->bind_param("i", $studentId);
$enrolledStmt->execute();
$result = $enrolledStmt->get_result();
$enrolledExe = [];
while ($row = $result->fetch_assoc()) {
    $enrolledExe [] = $row;
}
$enrolledStmt->close();

//retrieve quiz performance in courses
$quizStmt = $conn->prepare("SELECT * FROM quiz_performance;");
$quizStmt->execute();
$result = $quizStmt->get_result();
$scoreExe = [];
while ($row = $result->fetch_assoc()) {
    $scoreExe [] = $row;
}
$quizStmt->close();

//retrieve all progression detail of course
$progressStmt = $conn->prepare("SELECT p.enrol_id, count(*) AS total, sum(CASE WHEN progress = 1 THEN 1 ELSE 0 END) AS completed FROM progression AS p JOIN section as s ON p.section_id = s.section_id LEFT JOIN course_enrolment as ce ON p.enrol_id = ce.enrol_id WHERE student_id = ? GROUP BY p.enrol_id;");
$progressStmt->bind_param("i", $studentId);
$progressStmt->execute();
$result = $progressStmt->get_result();
$progressExe = []; //grouped based on enrol id => which can be mapped to course id
while ($row = $result->fetch_assoc()) {
    $progressExe [] = $row;
}
$progressStmt->close();

//student's field preference
$fieldSQL = 
"SELECT fp.field_id, experience, name 
FROM field_preference as fp
LEFT JOIN field as f
ON fp.field_id = f.field_id
WHERE student_id = $studentId;";
$fieldExe = mysqli_query($conn, $fieldSQL);

$fields = []; //reusable array
while ($row = mysqli_fetch_assoc($fieldExe)) {
    $fields[] = $row;
}

//student's career of preference
$careerSQL = 
"SELECT cp.career_id, name, experience 
FROM career_preference as cp
LEFT JOIN career as c
ON cp.career_id = c.career_id
WHERE student_id = $studentId;";
$careerExe = mysqli_query($conn, $careerSQL);

$careers = []; //reusable array
while ($row = mysqli_fetch_assoc($careerExe)) {
    $careers[] = $row;
}

$careerIds = array_column($careers, 'career_id'); //extract career_id only
$careerIdList = implode(',', $careerIds);

if (mysqli_num_rows($fieldExe) !== 0) {
    //retrieve fields of career used for display
    $careerFieldSQL =
    "SELECT career_id, field_id 
    FROM career_field_relation
    WHERE career_id IN ($careerIdList);";
    $careerFieldExe = mysqli_query($conn, $careerFieldSQL);

    $careerFields = []; //reusable array
    while ($row = mysqli_fetch_assoc($careerFieldExe)) {
        $careerFields[] = $row;
    }
} else {
    $careerFields = [];
}

$completedNum = 0;
foreach ($progressExe as $pro) {
    if ((int) $pro["total"] === (int) $pro["completed"])
        $completedNum++;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="circular-bar.css">
    <script defer src=""></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
    <header>
        <?php include "header.php";?>
    </header>
    <div class="whiteSectionBg">
        <div class="contentSection">
            <div id="studentReportHeader">
                <div class="reportMsg">You have joined us since <?php echo $signupDate;?>.</div>
                <div style="margin-top: 0;"class="reportMsg">Look at what you have achieved so far!</div>
                <div style="display: flex; flex-shrink: 0; gap: 3rem">
                    <div class="userContainer">
                        <div class="userHeader">
                            Courses Completed
                        </div>
                        <div id="completionInfo" class="userInfo"><?php echo $completedNum;?></div>
                    </div>
                </div>
            </div>
            
            <div id="scoreProgressContainer">
                <div class="circleBarGroup">
                    <div class="reportMsg">Average Quiz Performance</div>
                    <div class="performanceReport">
                        <div class="outerCircle">
                            <div class="innerCircle">
                                <div id="avgQuizScore"></div>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="20rem" height="20rem">
                            <circle id="quizCircle" cx="50%" cy="50%" r="136" stroke="url(#GradientColor)" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <button onclick="window.location.href = 'stu-profile.php#completedCourseContainer';" class="linkBtn">Details&nbsp;&nbsp;<i class="bi bi-arrow-right-circle-fill"></i></button>
                </div>
                

                <div class="circleBarGroup">
                    <div class="reportMsg">Average Current Progression</div>
                    <div class="performanceReport">
                        <div class="outerCircle">
                            <div class="innerCircle">
                                <div id="avgProgress"></div>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="20rem" height="20rem">
                            <circle id="progressCircle" cx="50%" cy="50%" r="136" stroke="url(#GradientColor)" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <button onclick="window.location.href = 'stu-profile.php#inProgressContainer';" class="linkBtn">Details&nbsp;&nbsp;<i class="bi bi-arrow-right-circle-fill"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="whiteSectionBg">
        <div class="contentSection">
            <div class="preferenceContainer">
                <div class="pieGroup">
                    <div class="reportMsg">Courses Enrolled Based on Your Field Preferences</div>
                    <div class="pieChartContainer">
                        <canvas id="fieldPieChart" width="400" height="400"></canvas>
                    </div>
                    <div id="fieldTotal" class="reportMsg"></div>
                </div>
                <div class="pieGroup">
                    <div class="reportMsg">Courses Enrolled Based on Your Career Preferences</div>
                    <div class="pieChartContainer">
                        <canvas id="careerPieChart" width="400" height="400"></canvas>
                    </div>
                    <div id="careerTotal" class="reportMsg"></div>
                </div>
            </div>
            <div style="display: flex; align-items: center; justify-content: center; height: fit-content; margin-bottom: 2rem;">
                <button onclick="window.location.href = 'stu-profile.php#inProgressContainer';" class="linkBtn">See My Enrolled Courses&nbsp;&nbsp;<i class="bi bi-arrow-right-circle-fill"></i></button>
            </div>
        </div>
    </div>

    <div class="whiteSectionBg">
        <div class="contentSection">
            <div class="reportMsg">Hooray 🎉! You did great! Keep improving yourself every day, and someday you will climb to the top. See you on the other side!</div>
        </div>
    </div>
    
    <script>
        const coursesEnrolled = <?php echo json_encode($enrolledExe);?>;
        const courseScores = <?php echo json_encode($scoreExe);?>;
        const courseProgress = <?php echo json_encode($progressExe);?>;
        const userFields = <?php echo json_encode($fields);?>;
        const userCareers = <?php echo json_encode($careers);?>;
        const userCareerFields = <?php echo json_encode($careerFields);?>;
        const coursesEnrolledIds = coursesEnrolled.map(c => c.course_id);

        //time to calculate average quiz performance
        let courseCount = 0;
        let totalPercentage = 0;
        courseScores.forEach(score => {
            courseCount++;
            totalPercentage += score.percentage;
        });

        let quizScore = 0;
        //average quiz score is here!
        if (courseCount === 0) {
            //no score can be found
            //set fixed value to avoid dividing by zero
            quizScore = 0;
        } else {
            let oriQuizScore = (totalPercentage / courseCount).toFixed(2);
            quizScore = Math.floor(oriQuizScore);
            console.log(quizScore)
        }   

        //style the circle
        //draw and color the circle of avg quiz score
        let quizCircle = document.querySelector("#quizCircle");

        let quizCounter = 0;

        //also will be used for other progression circle
        function circleColoring(percentile, circle) {
            if (percentile >= 0 && percentile < 20) {
                circle.style.stroke = "red";
            } else if (percentile >= 20 && percentile < 35) {
                circle.style.stroke = "#ff5000";
            } else if (percentile >= 35 && percentile < 47) {
                circle.style.stroke = "#ff6000";
            } else if (percentile >= 47 && percentile < 60) {
                circle.style.stroke = "#ffa700";
            } else if (percentile >= 60 && percentile < 70) {
                circle.style.stroke = "#ffd600";
            } else if (percentile >= 70 && percentile < 80) {
                circle.style.stroke = "#ffff00";
            } else if (percentile >= 80 && percentile < 89) {
                circle.style.stroke = "#a1ff00";
            } else if (percentile >= 89 && percentile <= 100) {
                circle.style.stroke = "#60ff00";
            }
        }  
        
        console.log(quizScore)
        if (quizScore !== 0) {
            let quiz = document.getElementById("avgQuizScore"); //text in the circle
            setInterval(() => {
                if (quizCounter === quizScore) {
                    clearInterval();
                } else {
                    quizCircle.style.strokeDashoffset = 854 * (100 - quizCounter) / 100;
                    circleColoring(quizCounter, quizCircle);
                    quizCounter++;
                    quiz.innerHTML = quizCounter + "%";
                    if (quizCounter === 0) {
                        quiz.innerHTML = "0%";
                    }
                }
            }, 30);
        } 
        

        let totalSections = 0;
        let completedSections = 0;
        courseProgress.forEach(progress => {
            totalSections += progress.total;
            completedSections += parseInt(progress.completed);
        });

        let progressScore = 0;
        console.log(completedSections);
        console.log(totalSections);
        if (totalSections === 0) {
            //avoid 0 division error
            //0 sections to be completed = 100%
            progressScore = 0;
        } else {
            let oriProgressScore = (completedSections * 100 / totalSections).toFixed(2);
            progressScore = Math.floor(oriProgressScore);
        }

        console.log(progressScore);

        //draw and color the circle of avg progression of user
        let progressCircle = document.querySelector("#progressCircle");

        let progressCounter = 0;

        let progress = document.getElementById("avgProgress"); //text in the circle
        setInterval(() => {
            if (progressCounter === progressScore) {
                clearInterval();
            } else {
                progressCircle.style.strokeDashoffset = 854 * (100 - progressCounter) / 100;
                circleColoring(progressCounter, progressCircle);
                progressCounter++;
                progress.innerHTML = progressCounter + "%";
            }
        }, 30);

        //piechart scripts
        //function to assign random but consistent colors to piechart sectors
        function stringToColor(str) {
            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }
            const hue = Math.abs(hash) % 360;
            return `hsl(${hue}, 70%, 60%)`;
        }

        let preferredFields = [];
        let fieldCount = [];

        let fieldPromise = userFields.map(field => {
            preferredFields.push(field.name);

            return fetch("course.php?fieldId=" + field.field_id, {
                method: "POST"
            })
            .then(response => response.json())
            .then(courses => {
                const enrolledIds = coursesEnrolled.map(c => String(c.course_id));
                const filtered = courses.filter(course => enrolledIds.includes(String(course.course_id)));
                fieldCount.push(filtered.length);
            })
            .catch(err => {
                console.error("Fetch error for field:", field.field_id, err);
                fieldCount.push(0); // fallback to 0 on error
            });
        });

        Promise.all(fieldPromise).then(() => {
            console.log("Final fieldCount length:", fieldCount.length);
            console.log("fieldCount:", fieldCount);

            new Chart(document.getElementById("fieldPieChart"), {
                type: 'pie',
                data: {
                    labels: preferredFields,
                    datasets: [{
                        label: 'Number of Enrolled Courses',
                        data: fieldCount,
                        backgroundColor: preferredFields.map(label => stringToColor(label))
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false
                }
            });
        });

        let preferredCareers = [];
        let careerCount = [];

        let careerPromise = userCareers.map(career => {
            preferredCareers.push(career.name);

            const fieldsForCareer = userCareerFields.filter(field => field.career_id === career.career_id);

            if (fieldsForCareer.length === 0) {
                careerCount.push(0);
                return Promise.resolve();
            }

            const promises = fieldsForCareer.map(field => {
                return fetch("course.php?fieldId=" + field.field_id, {
                    method: "POST"
                })
                .then(response => response.json())
                .catch(err => {
                    console.error("Fetch error for field:", field.field_id, err);
                    return []; // fallback to empty list on error
                });
            });

            return Promise.all(promises).then(results => {
                const coursesEnrolledIds = coursesEnrolled.map(c => String(c.course_id));
                let total = 0;

                results.forEach(courseList => {
                    const filtered = courseList.filter(course => coursesEnrolledIds.includes(String(course.course_id)));
                    total += filtered.length;
                });

                careerCount.push(total);
            });
        });

        Promise.all(careerPromise).then(() => {
            console.log("Final careerCount:", careerCount);

            new Chart(document.getElementById("careerPieChart"), {
                type: 'pie',
                data: {
                    labels: preferredCareers,
                    datasets: [{
                        label: 'Number of Courses',
                        data: careerCount,
                        backgroundColor: preferredCareers.map(label => stringToColor(label))
                    }]
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false
                }
            });
        });

    </script>
</body>
</html>