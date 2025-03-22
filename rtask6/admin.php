<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$dbconn = 'mysql:host=localhost;dbname=uxxxxx';
$user = 'uxxxxx';
$pass = 'xxxxxxx';

$db = new PDO($dbconn, $user, $pass,
	[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Area"');
    echo '<h1>401: Вы не авторизованы!</h1>';
    exit();
}

$stmt = $db->prepare("SELECT * FROM Admin WHERE login = :login");
$stmt->bindParam(':login', $_SERVER['PHP_AUTH_USER']);
$stmt->execute();
$tempp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tempp || !password_verify($_SERVER['PHP_AUTH_PW'], $tempp['password'])) {
	echo '<h1>Доступ запрещён!</h1>';
	exit();
}

if (isset($_GET['del_id'])) {
	$stmt = $db->prepare("
		DELETE app, appLang, user
		FROM Application app
		INNER JOIN AppLang appLang ON app.id = appLang.app_id
		INNER JOIN Users user ON app.id = user.app_id
		WHERE app.id = :del_id
	");
	$stmt->bindParam(':del_id', $_GET['del_id']);
	$stmt->execute();
}

$stmt = $db->prepare("SELECT * FROM Application");
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("
	SELECT lang.name, count(*) as count 
	FROM Application app 
	INNER JOIN AppLang alg ON app.id = alg.app_id 
	INNER JOIN Language lang ON lang.id = alg.lang_id 
	GROUP BY lang.id
");
$stmt->execute();
$languages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$best_lang = ['name' => '', 'count' => 0];
foreach ($languages as $lang) {
	if ($best_lang['count'] < $lang['count']) {
		$best_lang['name'] = $lang['name'];
		$best_lang['count'] = $lang['count'];
	}
}

$raz_prompt = '';
if ($best_lang['count'] % 10 >= 2 && $best_lang['count'] % 10 <= 4) {
	$raz_prompt = 'раза';
}
else $raz_prompt = 'раз';

?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>TASK 6 - Admin</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="styles.css" />
</head>

<body>
	<div class="container mt-4 col-md-12" id="main-wrapper">
		<h2 align="center"><strong>Панель администратора</strong></h2>
		<hr>

		<div class="container mt-5 justify-content-center align-items-center" id="stats-wrapper">
			<h3 class="col-md-12 text-center"><strong>Статистика популярности языков программирования</strong></h3>

			<div class="container row">
				<div class="container col-md-5" id="lang-table-wrapper">
					<table class="table text-white" id="table-main">
						<thead>
							<tr>
								<th scope="col">Язык</th>
								<th scope="col">Людей выбрало</th>
							</tr>
						</thead>

						<tbody>
						<?php foreach ($languages as $lang): ?>
							<tr>
								<td><?= $lang['name'] ?></td>
								<td><?= $lang['count'] ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>

				<div class="container col-md-4" id="lang-best-wrapper">
					<h3>Самый популярный:</h3>
					<strong><?= $best_lang['name'] ?></strong> - выбрано <?= $best_lang['count'] . ' ' . $raz_prompt ?>
				</div>
			</div>
		</div>
		<hr>

		<div class="container mt-5" id="applications-table-wrapper">
			<table class="table text-white" id="table-main">
				<h3 align="center"><strong>Заявки</strong></h3>

				<thead>
					<tr>
						<th scope="col">ФИО</th>
						<th scope="col">Номер телефона</th>
						<th scope="col">Электронная почта</th>
						<th scope="col">Д/Р</th>
						<th scope="col">Пол</th>
						<th scope="col">Сообщение</th>
						<th scope="col"></th>
					</tr>
				</thead>

				<tbody>
				<?php foreach ($applications as $app): ?>
					<tr>
						<td><?= $app['fio'] ?></td>
						<td><?= $app['phone'] ?></td>
						<td><?= $app['email'] ?></td>
						<td><?= $app['bdate'] ?></td>
						<td><?= $app['gender'] ?></td>
						<td><?= $app['message'] ?></td>
						<td>
							<a href="<?= 'admin_edit.php?app_id=' . $app['id'] ?>">Редактировать</a><br/>
							<a class="deletion" href="<?= 'admin.php?del_id=' . $app['id'] ?>">Удалить</a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</body>