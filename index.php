<?php
session_start();

$pdo = new PDO('sqlite:forum.db');

// Створення таблиць, якщо вони ще не існують
$pdo->exec("CREATE TABLE IF NOT EXISTS topics (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content TEXT NOT NULL,
    topic_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(topic_id) REFERENCES topics(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
)");

// Перевірка існування таблиці users перед запитом
$checkUsersTable = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
if (!$checkUsersTable->fetchColumn()) {
    // Створення таблиці users, якщо вона не існує
    $pdo->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        admin INTEGER DEFAULT 0
    )");
}

// Вихід користувача
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: /');
    exit;
}

// Перевірка авторизації
$loggedIn = isset($_SESSION['user_id']);

// Додавання нової теми
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_topic'])) {
    $title = $_POST['title'];
    $stmt = $pdo->prepare("INSERT INTO topics (title) VALUES (:title)");
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->execute();
    header('Location: /');
    exit;
}

// Додавання нового допису
if ($loggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post'])) {
    $content = $_POST['content'];
    $topic_id = $_POST['topic_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO posts (content, topic_id, user_id) VALUES (:content, :topic_id, :user_id)");
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        header("Location: /?topic_id=$topic_id");
        exit;
    } else {
        echo "Помилка при додаванні допису";
    }
}

// Отримання тем
$topics = $pdo->query("SELECT * FROM topics")->fetchAll(PDO::FETCH_ASSOC);

// Отримання дописів по темі (якщо вказано)
$posts = [];
$topic = null;
if (isset($_GET['topic_id'])) {
    $topic_id = $_GET['topic_id'];
    $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = :topic_id");
    $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
    $stmt->execute();
    $topic = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($topic) {
        $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE topic_id = :topic_id");
        $stmt->bindParam(':topic_id', $topic_id, PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$isAdmin = false;
if ($loggedIn) {
    $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $admin = $stmt->fetchColumn();

    if ($admin == 1) {
        $isAdmin = true;
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Форум</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Форум</h1>

    <!-- Кнопки для реєстрації, входу і виходу -->
    <?php if (!$loggedIn): ?>
        <a href="/register.php" class="register-button">Реєстрація</a>
        <a href="/login.php" class="login-button">Вхід</a>
    <?php else: ?>
        <span class="user-name">Вітаємо, <b><?php echo $_SESSION['username']; ?></b>!</span>
        <?php if ($isAdmin): ?> <!-- Перевірка на адміна -->
            <a href="/admin.php" class="admin-button">Адмін-Панель</a> <!-- Додана кнопка для адміна -->
        <?php endif; ?>
        <a href="/?action=logout" class="logout-button">Вихід</a>
    <?php endif; ?>

    <!-- Форма для створення нової теми (доступно для авторизованих користувачів) -->
    <?php if ($loggedIn): ?>
        <div class="topic">
            <h2>Створити нову тему</h2>
            <form action="/" method="post">
                <input class="input-field" type="text" name="title" placeholder="Назва теми" required>
                <input class="submit-button" type="submit" name="new_topic" value="Створити">
            </form>
        </div>
    <?php endif; ?>

    <!-- Список тем -->
    <div class="topic">
        <h2>Теми:</h2>
        <ul>
            <?php foreach ($topics as $topicItem): ?>
                <li><a href="/?topic_id=<?= $topicItem['id'] ?>"><?= htmlspecialchars($topicItem['title']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Виведення дописів -->
    <?php if ($topic): ?>
        <div class="topic">
            <h2><?= htmlspecialchars($topic['title']) ?></h2>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <strong><?= htmlspecialchars($post['username']) ?>:</strong>
                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                    <small><?= $post['created_at'] ?></small>
                </div>
            <?php endforeach; ?>

            <!-- Форма для додавання допису (доступно для авторизованих користувачів) -->
            <?php if ($loggedIn): ?>
                <form class="make-post" action="/" method="post">
                    <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
                    <textarea name="content" placeholder="Введіть текст допису..." required></textarea>
                    <input type="submit" name="new_post" value="Опублікувати">
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>