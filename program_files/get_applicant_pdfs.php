<?php
include "conn.php";

header('Content-Type: application/json');

if (!isset($_GET['applicant_id'])) {
    echo json_encode(['error' => 'No applicant ID provided']);
    exit;
}

$applicant_id = $_GET['applicant_id'];
$applicant_id = $conn->real_escape_string($applicant_id);

$sql = "SELECT doc_name, directory FROM applicant_document WHERE applicant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $applicant_id);
$stmt->execute();
$result = $stmt->get_result();

$documents = [];

while ($row = $result->fetch_assoc()) {
    $documents[] = [
        'name' => $row['doc_name'],
        'directory' => $row['directory']
    ];
}

if (empty($documents)) {
    echo json_encode(['error' => 'No documents found for this applicant']);
} else {
    echo json_encode($documents);
}

$stmt->close();
$conn->close();
?>
