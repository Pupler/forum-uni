<?php
session_start();

try {
    // Підключення до бази даних
    $pdo = new PDO('sqlite:forum.db');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Пошук користувача в базі даних
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Перевірка пароля
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: /');
        } else {
            throw new Exception("Невірне ім'я користувача або пароль");
        }
    }
} catch (PDOException $e) {
    echo 'Помилка підключення до бази даних: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Помилка: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вхід</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Вхід</h1>

    <!-- Форма для входу -->
    <form class="login-form" action="/login.php" method="post">
        <label>Ім'я користувача:</label>
        <input type="text" name="username" required><br>

        <label>Пароль:</label>
        <input type="password" name="password" required><br>

        <input type="submit" value="Увійти">
    </form>
</body>
</html>