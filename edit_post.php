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

// Редагування допису
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    $stmt = $pdo->prepare("UPDATE posts SET content = :content WHERE id = :post_id");
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}

// Отримання інформації про допис для редагування
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :post_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Редагування допису</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

<h1>Редагування допису</h1>

<!-- Форма для редагування допису -->
<div class="post-form">
    <form style="margin-left:20px;" action="edit_post.php" method="post">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <textarea style="font-family: 'Montserrat', sans-serif; width: 500px;
    height: 200px;
    font-size: 14px;
    padding: 12px 15px;
    margin-bottom: 10px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff; " name="content" rows="10" required><?= htmlspecialchars($post['content']) ?></textarea><br>
        <input type="submit" name="edit_post" value="Зберегти зміни">
    </form>
</div>

</body>
</html>
