<?php
// get_post_votes.php - API to get vote counts and user's vote for posts

session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php");
    exit();
}

$host = 'localhost';
$dbname = 'cocdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get post_ids from GET parameter as comma separated string
    $post_ids_str = $_GET['post_ids'] ?? '';
    if (!$post_ids_str) {
        echo json_encode(['success' => false, 'error' => 'Missing post_ids parameter.']);
        exit;
    }

    $post_ids = array_filter(array_map('intval', explode(',', $post_ids_str)));
    if (empty($post_ids)) {
        echo json_encode(['success' => false, 'error' => 'Invalid post_ids parameter.']);
        exit;
    }

    // Use logged-in user's email from session if available
    $author_email = $_SESSION['user_email'] ?? null;

    if (!$author_email) {
        echo json_encode(['success' => false, 'error' => 'User not logged in.']);
        exit;
    }

    // Prepare placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));

    // Get vote counts per post
    $stmt = $pdo->prepare("
        SELECT post_id,
            SUM(CASE WHEN is_up_vote = 1 THEN 1 ELSE 0 END) AS upVotes,
            SUM(CASE WHEN is_up_vote = 0 THEN 1 ELSE 0 END) AS downVotes
        FROM post_votes
        WHERE post_id IN ($placeholders)
        GROUP BY post_id
    ");
    $stmt->execute($post_ids);
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map counts by post_id
    $countsMap = [];
    foreach ($counts as $row) {
        $countsMap[$row['post_id']] = [
            'upVotes' => (int)$row['upVotes'],
            'downVotes' => (int)$row['downVotes'],
        ];
    }

    // Get user's votes for posts
    $stmt2 = $pdo->prepare("
        SELECT post_id, is_up_vote
        FROM post_votes
        WHERE post_id IN ($placeholders) AND author_email = ?
    ");
    $params = array_merge($post_ids, [$author_email]);
    $stmt2->execute($params);
    $userVotes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $userVotesMap = [];
    foreach ($userVotes as $row) {
        $userVotesMap[$row['post_id']] = (int)$row['is_up_vote'];
    }

    echo json_encode([
        'success' => true,
        'counts' => $countsMap,
        'userVotes' => $userVotesMap,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
