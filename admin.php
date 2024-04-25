<?php
session_start();

$pdo = new PDO('sqlite:forum.db');

// Перевірка чи користувач має права адміна
$stmt = $pdo->prepare("SELECT admin FROM users WHERE username = :username");
$stmt->bindParam(':username', $_SESSION['username']);
$stmt->execute();
$user = $stmt->fetch();

if (!$user || $user['admin'] != 1) {
    echo "У вас немає прав адміністратора.";
    exit();
}

// Редагування теми
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_topic'])) {
    $topic_id = $_POST['topic_id'];
    $title = $_POST['title'];

    $stmt = $pdo->prepare("UPDATE topics SET title = :title WHERE id = :topic_id");
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}

// Видалення теми та пов'язаних дописів
if (isset($_GET['action']) && $_GET['action'] === 'delete_topic' && isset($_GET['topic_id'])) {
    $topic_id = $_GET['topic_id'];

    // Видалення дописів
    $stmt = $pdo->prepare("DELETE FROM posts WHERE topic_id = :topic_id");
    $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
    $stmt->execute();

    // Видалення теми
    $stmt = $pdo->prepare("DELETE FROM topics WHERE id = :topic_id");
    $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}

// Отримання тем для відображення
$topics = $pdo->query("SELECT * FROM topics")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Адмін-панель</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<h1>Адмін-панель</h1>

<!-- Кнопка виходу -->
<a href="index.php" class="logout-button">На головну</a>

<!-- Список тем -->
<div class="topic">
    <h2>Теми:</h2>
    <ul>
        <?php foreach ($topics as $topicItem): ?>
            <li>
                <?= htmlspecialchars($topicItem['title']) ?>
                <form style="display:inline-block; margin-left:20px" action="admin.php?action=delete_topic&topic_id=<?= $topicItem['id'] ?>" method="post" onsubmit="return confirm('Ви впевнені?')">
                    <input type="submit" value="Видалити">
                </form>
                <a href="edit_topic.php?topic_id=<?= $topicItem['id'] ?>">Редагувати</a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
