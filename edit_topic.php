<?php
session_start();

try {
    // Підключення до бази даних
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
    if (isset($_GET['action']) && $_GET['action'] === 'delete_post' && isset($_GET['post_id'])) {
        $post_id = $_GET['post_id'];

        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :post_id");
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: edit_topic.php?topic_id=" . $_GET['topic_id']);
        exit();
    }

    // Отримання інформації про тему для редагування
    if (isset($_GET['topic_id'])) {
        $topic_id = $_GET['topic_id'];
        $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = :topic_id");
        $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
        $stmt->execute();
        $topic = $stmt->fetch();

        // Отримання дописів для даної теми
        $stmtPosts = $pdo->prepare("SELECT * FROM posts WHERE topic_id = :topic_id");
        $stmtPosts->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
        $stmtPosts->execute();
        $posts = $stmtPosts->fetchAll();
    }

} catch (PDOException $e) {
    echo 'Помилка підключення до бази даних: ' . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Редагування теми</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<h1>Редагування теми</h1>

<!-- Форма для редагування теми -->
<div class="topic">
    <form action="edit_topic.php" method="post">
        <input type="hidden" name="topic_id" value="<?= $topic_id ?>">
        <input class="input-field" type="text" name="title" value="<?= htmlspecialchars($topic['title']) ?>" required>
        <input class="submit-button" type="submit" name="edit_topic" value="Зберегти зміни">
    </form>
</div>

<!-- Список дописів -->
<div class="posts">
    <h2 style="display:inline-block; margin-left:20px">Дописи:</h2>
    <ul>
        <?php foreach ($posts as $post): ?>
            <li>
                <?= '<div class="post-content">' . nl2br(htmlspecialchars($post['content'])) . '</div>' ?>
                <div>
                    <a href="edit_post.php?action=edit&post_id=<?= $post['id'] ?>">Редагувати</a>
                    <a href="edit_topic.php?action=delete_post&post_id=<?= $post['id'] ?>&topic_id=<?= $topic_id ?>" onclick="return confirm('Ви впевнені, що хочете видалити цей допис?')">Видалити</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

</body>
</html>
