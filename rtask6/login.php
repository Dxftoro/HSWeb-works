<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$dbconn = 'mysql:host=localhost;dbname=uxxxxx';
$user = 'uxxxxx';
$pass = 'xxxxxxx';

$db = new PDO($dbconn, $user, $pass,
	[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$login = $_POST['login'];
	$password = $_POST['password'];

	if (empty($login) || empty($password)) {
		$error = 'Введите логин и пароль!';
		exit();
	}

	$stmt = $db->prepare("SELECT * FROM Users WHERE login = :login");
	$stmt->bindParam(':login', $login);
	$stmt->execute();

	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($user && password_verify($password, $user['password'])) {
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['user_app_id'] = $user['app_id'];
		header('Location: index.php');
		exit();
	}
	else {
		$error = 'Некорректный логин или пароль!';
	}
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>TASK 5 - Login</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="styles.css" />
</head>

<body>
	<?php
		if (!empty($error)) {
			echo '<div class="error" id="messages">';
				echo '<strong>' . $error . '</strong>';
			echo '</div>';
		}
	?>

	<div class="container mt-5 col-md-5 border border-secondary rounded p-3" id="form-wrapper">
		<h2><strong>Вход</strong></h2>

		<form id="main-form" action="" method="post">
			<div class="form-group">
				<label for="login">Логин</label>
				<input class="form-control" type="text" id="login" name="login" required>
			</div>

			<div class="form-group">
				<label for="password">Пароль</label>
				<input class="form-control" type="password" id="password" name="password" required>
			</div>

			<div class="form-group">
				<input class="btn btn-light" type="submit">
			</div>
		</form>
	</div>
</body>