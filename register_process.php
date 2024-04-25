<?php
try {
    // Підключення до бази даних
    $pdo = new PDO('sqlite:forum.db');

    // Створення таблиці users, якщо вона ще не існує
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        admin INTEGER DEFAULT 0
    )");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $admin = isset($_POST['admin']) ? 1 : 0; // Перевірка на наявність адміністраторських прав

        // Перевірка наявності користувача
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->fetch()) {
            throw new Exception("Користувач з таким іменем вже існує!");
        }

        // Додавання нового користувача
        $stmt = $pdo->prepare("INSERT INTO users (username, password, admin) VALUES (:username, :password, :admin)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':admin', $admin, PDO::PARAM_INT);
        $stmt->execute();

        // Перенаправлення на сторінку авторизації
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    echo 'Помилка підключення до бази даних: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Помилка: ' . $e->getMessage();
}
?>