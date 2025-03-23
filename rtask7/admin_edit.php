<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$dbconn = 'mysql:host=localhost;dbname=uxxxxx';
$user = 'uxxxxx';
$pass = 'xxxxxxx';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    	die('CSRF токен не прошёл валидацию!');
	}

	if (!isset($_SESSION['selected_app_id'])) exit();
	$app_id = $_SESSION['selected_app_id'];

	$db = new PDO($dbconn, $user, $pass,
		[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
	$db->beginTransaction();

	$stmt = $db->prepare("
		UPDATE Application
		SET fio = :fio, phone = :phone, email = :email,
			bdate = :bdate, gender = :gender, message = :message
		WHERE id = :id
	");

	$stmt->bindParam(':fio', $_POST['fio']);
	$stmt->bindParam(':phone', $_POST['phone']);
	$stmt->bindParam(':email', $_POST['email']);
	$stmt->bindParam(':bdate', $_POST['date']);
	$stmt->bindParam(':gender', $_POST['gender']);
	$stmt->bindParam(':message', $_POST['message']);
	$stmt->bindParam(':id', $app_id);
	$stmt->execute();

	$stmt = $db->prepare("DELETE FROM AppLang WHERE app_id = :app_id");
	$stmt->bindParam(':app_id', $app_id);
	$stmt->execute();

	$langs = $_POST['abilities'];
	foreach ($langs as $lang_id) {
		$stmt = $db->prepare("INSERT INTO AppLang (app_id, lang_id) values (:app_id, :lang_id)");
		$stmt->bindParam(':app_id', $app_id);
		$stmt->bindParam(':lang_id', $lang_id);
		$stmt->execute();
	}

	$db->commit();
	header('Location: admin.php');
}

if (isset($_GET['app_id']) && is_numeric($_GET['app_id'])) {
	$_SESSION['selected_app_id'] = $_GET['app_id'];
	$db = new PDO($dbconn, $user, $pass,
		[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

	$stmt = $db->prepare("SELECT * FROM Application WHERE id = :id");
	$stmt->bindParam(':id', $_GET['app_id']);
	$stmt->execute();
	$app = $stmt->fetch(PDO::FETCH_ASSOC);

	$values = [
		'fio' => $app['fio'],
		'phone' => $app['phone'],
		'email' => $app['email'],
		'date' => $app['bdate'],
		'gender' => $app['gender'],
		'message' => $app['message']
	];

	$stmt = $db->prepare("SELECT * FROM AppLang WHERE app_id = :id");
	$stmt->bindParam(':id', $_GET['app_id']);
	$stmt->execute();
	$values['abilities'] = array_column(
		$stmt->fetchAll(PDO::FETCH_ASSOC),
		'lang_id'
	);
}
else {
	header('Location: admin.php');
	exit();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>TASK 6</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="styles.css" />
</head>

<body>
	<div class="container mt-5 col-md-6 border border-secondary rounded p-3" id="form-wrapper">
		<h2><strong>Редактирование заявки</strong></h2>
		<form id="main-form" action="" method="post">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

			<div class="form-group">
				<label for="fio">Фамилия, имя, отчество:</label>
				<input class="form-control" type="text" id="fio" name="fio" value="<?php print $values['fio']; ?>" >
			</div>

			<div class="form-group">
				<label for="phone">Телефон:</label>
				<input class="form-control" type="tel" id="phone" name="phone" value="<?php print $values['phone']; ?>" >
			</div>

			<div class="form-group">
				<label for="email">Адрес электронной почты:</label>
				<input class="form-control" type="email" id="email" name="email" value="<?php print $values['email']; ?>" >
			</div>

			<div class="form-group">
				<label for="date">Дата рождения:</label>
				<input class="form-control" type="date" id="date" name="date" value="<?php print $values['date']; ?>" >
			</div>

			<div class="form-group">
                		<label for="progLanguage">Любимый язык программирования</label>
                		<select id="progLanguage" class="form-select" name="abilities[]" multiple="multiple" aria-label="Выбрать язык" >
							<option value=1 <?php echo in_array(1, $values['abilities']) ? 'selected' : ''; ?> >C</option>
                			<option value=2 <?php echo in_array(2, $values['abilities']) ? 'selected' : ''; ?> >C++</option>
                			<option value=3 <?php echo in_array(3, $values['abilities']) ? 'selected' : ''; ?> >Lua</option>
                			<option value=4 <?php echo in_array(4, $values['abilities']) ? 'selected' : ''; ?> >Python</option>
                			<option value=5 <?php echo in_array(5, $values['abilities']) ? 'selected' : ''; ?> >JavaScript</option>
                			<option value=6 <?php echo in_array(6, $values['abilities']) ? 'selected' : ''; ?> >PHP</option>
                			<option value=7 <?php echo in_array(7, $values['abilities']) ? 'selected' : ''; ?> >Java</option>
                			<option value=8 <?php echo in_array(8, $values['abilities']) ? 'selected' : ''; ?> >Pascal</option>
                			<option value=9 <?php echo in_array(9, $values['abilities']) ? 'selected' : ''; ?> >Haskel</option>
                			<option value=10 <?php echo in_array(10, $values['abilities']) ? 'selected' : ''; ?> >Rust</option>
                			<option value=11 <?php echo in_array(11, $values['abilities']) ? 'selected' : ''; ?> >Clojure</option>
                			<option value=12 <?php echo in_array(12, $values['abilities']) ? 'selected' : ''; ?> >Prolog</option>
                			<option value=13 <?php echo in_array(13, $values['abilities']) ? 'selected' : ''; ?> >Scala</option>
                		</select>
            		</div>

			<div class="form-group <?php if ($errors['gender']) {print($error_class);} ?>">
			        <label>Ваш пол:</label><br>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="male" name="gender" value="Паркет" <?php echo ($values['gender'] == 'Паркет') ? 'checked' : ''; ?> >
			            <label class="form-check-label" for="male">Паркет</label>
			        </div>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="female" name="gender" value="Линолеум" <?php echo ($values['gender'] == 'Линолеум') ? 'checked' : ''; ?> >
			            <label class="form-check-label" for="female">Линолеум</label>
			        </div>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="other" name="gender" value="Ламинат" <?php echo ($values['gender'] == 'Ламинат') ? 'checked' : ''; ?> >
			            <label class="form-check-label" for="other">Ламинат</label>
			        </div>
			</div>

            		<div class="form-group">
	            		<label for="message">Сообщение</label>
		    		<textarea class="form-control" id="message" name="message" rows="4" required><?php print $values['message'] ?></textarea>
            		</div>

			<div class="form-group">
				<input class="btn btn-light" type="submit" value="Готово">
			</div>
			<a href="admin.php">Вернуться к панели администратора</a>
		</form>
	</div>
</body>

</html>