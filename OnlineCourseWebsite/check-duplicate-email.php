<?php
include "conn.php";
//retrieve all user emails
//used to check for duplicates => ensure unique email
$stmt = $conn->prepare("SELECT user_email FROM user;");
$stmt->execute();
$result = $stmt->get_result();
$existingEmails = [];
while ($row = $result->fetch_assoc()) {
    $existingEmails[] = $row;
}
$stmt->close();

//retrieve users who are removed
//if status "Banned" => not allow email to be used to sign up
//esentially banned from website access using the email
$stmt = $conn->prepare("SELECT user_email, removed_status FROM removed_user;");
$stmt->execute();
$result = $stmt->get_result();
$bannedEmails = [];
while ($row = $result->fetch_assoc()) {
    $bannedEmails[] = $row;
}
$stmt->close();
$conn->close();
?>