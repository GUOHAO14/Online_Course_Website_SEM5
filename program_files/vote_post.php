<?php
// vote_post.php - API to handle voting on forum posts

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

    // Get POST data
    $post_id = $_POST['post_id'] ?? null;
    // Use logged-in user's email from session if available
    $author_email = $_SESSION['user_email'] ?? null;
    $is_upvote = $_POST['is_upvote'] ?? null; // '1' or '0' as string
    $remove_vote = $_POST['remove_vote'] ?? null; // '1' if vote should be removed

    if (!$post_id) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
        exit;
    }

    if (!$author_email) {
        echo json_encode(['success' => false, 'error' => 'User not logged in.']);
        exit;
    }

    $is_upvote_bool = ($is_upvote === '1') ? 1 : 0;

    // Check if user already voted on this post
    $stmt = $pdo->prepare("SELECT vote_id, is_up_vote FROM post_votes WHERE post_id = :post_id AND author_email = :author_email");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':author_email', $author_email, PDO::PARAM_STR);
    $stmt->execute();
    $existingVote = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingVote) {
        if ($remove_vote === '1') {
            // Remove vote explicitly
            $deleteStmt = $pdo->prepare("DELETE FROM post_votes WHERE vote_id = :vote_id");
            $deleteStmt->bindParam(':vote_id', $existingVote['vote_id'], PDO::PARAM_INT);
            $deleteStmt->execute();
            $action = 'removed';
        } else if ((int)$existingVote['is_up_vote'] === $is_upvote_bool) {
            // User clicked the same vote again - remove vote (toggle off)
            $deleteStmt = $pdo->prepare("DELETE FROM post_votes WHERE vote_id = :vote_id");
            $deleteStmt->bindParam(':vote_id', $existingVote['vote_id'], PDO::PARAM_INT);
            $deleteStmt->execute();
            $action = 'removed';
        } else {
            // User switched vote - update
            $updateStmt = $pdo->prepare("UPDATE post_votes SET is_up_vote = :is_up_vote WHERE vote_id = :vote_id");
            $updateStmt->bindParam(':is_up_vote', $is_upvote_bool, PDO::PARAM_INT);
            $updateStmt->bindParam(':vote_id', $existingVote['vote_id'], PDO::PARAM_INT);
            $updateStmt->execute();
            $action = 'updated';
        }
    } else {
        // Insert new vote
        $insertStmt = $pdo->prepare("INSERT INTO post_votes (post_id, author_email, is_up_vote) VALUES (:post_id, :author_email, :is_up_vote)");
        $insertStmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $insertStmt->bindParam(':author_email', $author_email, PDO::PARAM_STR);
        $insertStmt->bindParam(':is_up_vote', $is_upvote_bool, PDO::PARAM_INT);
        $insertStmt->execute();
        $action = 'added';
    }

    // Get updated vote counts
    $countUpStmt = $pdo->prepare("SELECT COUNT(*) FROM post_votes WHERE post_id = :post_id AND is_up_vote = 1");
    $countUpStmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $countUpStmt->execute();
    $upVotes = (int)$countUpStmt->fetchColumn();

    $countDownStmt = $pdo->prepare("SELECT COUNT(*) FROM post_votes WHERE post_id = :post_id AND is_up_vote = 0");
    $countDownStmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $countDownStmt->execute();
    $downVotes = (int)$countDownStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'upVotes' => $upVotes,
        'downVotes' => $downVotes,
        'userVote' => $existingVote ? (($action === 'removed') ? null : $is_upvote_bool) : $is_upvote_bool,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
