<!DOCTYPE html>
<html>
<head>
    <title>Реєстрація</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <h1>Реєстрація</h1>

    <!-- Форма реєстрації -->
    <form class="register-form" action="/register_process.php" method="post">
        <label>Ім'я користувача:</label>
        <input type="text" name="username" required><br>
        
        <label>Пароль:</label>
        <input type="password" name="password" required><br>
        
        <input type="submit" value="Зареєструватися">
    </form>
</body>
</html>
