<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$dbconn = 'mysql:host=localhost;dbname=uxxxxx';
$user = 'uxxxxx';
$pass = 'xxxxxxx';

function error_print($errors, $keyname, &$messages, $message) {
	if ($errors[$keyname]) {
		setcookie($keyname . '_error', '', 100000);
		setcookie($keyname . '_value', '', 100000);
		$messages[] = '<div class="error">' . $message . '</div>';
	}
}

function makeLogin($email) {
	return explode("@", $email)[0] . rand(1000, 9999);
}

function makePassword($size) {
	$password = [];
	$chars = '_-0123456789ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghjklmnopqrstuvwxyz';

	$password['string'] = '';
	for ($i = 0; $i < $size; $i++) {
		$password['string'] .= $chars[rand(0, strlen($chars) - 1)];
	}

	$password['hash'] = password_hash($password['string'], PASSWORD_DEFAULT);
	return $password;
}

function ercookie($field_name) {
	setcookie($field_name . '_error', '1', time() + 24 * 60 * 60);
}

function vacookie($field_name) {
	setcookie($field_name . '_value', $_POST[$field_name], time() + 30 * 24 * 60 * 60);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$messages = array();
	$corMessages = array();

	if (!empty($_COOKIE['save'])) {
		setcookie('save', '', 100000);

		$corMessages[] = 'Спасибо, результаты сохранены.';

		if (!empty($_COOKIE['login'])) {
			$corMessages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
	            и паролем <strong>%s</strong> для изменения данных.',
	            strip_tags($_COOKIE['login']),
	            strip_tags($_COOKIE['password']));
		}

		setcookie('login', '', 100000);
		setcookie('password', '', 100000);
	}

	$errors = array();

	$errors['fio'] = !empty($_COOKIE['fio_error']);
	$errors['phone'] = !empty($_COOKIE['phone_error']);
	$errors['email'] = !empty($_COOKIE['email_error']);
	$errors['date'] = !empty($_COOKIE['date_error']);
	$errors['abilities'] = !empty($_COOKIE['abilities_error']);
	$errors['gender'] = !empty($_COOKIE['gender_error']);
	$errors['message'] = !empty($_COOKIE['message_error']);

	error_print($errors, 'fio', $messages, 'Нужно ввести фамилию имя и отчество!');
	error_print($errors, 'phone', $messages, 'Заполните поле телефона!');
	error_print($errors, 'email', $messages, 'Заполните поле электронной почты!');
	error_print($errors, 'date', $messages, 'Заполните поле даты!');
	error_print($errors, 'abilities', $messages, 'Обязательно укажите хотя бы один любимый язык программирования!');
	error_print($errors, 'gender', $messages, 'Укажите ваш пол!');
	error_print($errors, 'message', $messages, 'Напишите сообщение.');

	$values = array();
	$values['abilities'] = array();
	
	if (!empty($_COOKIE['abilities_value'])) {$values['abilities'] = unserialize($_COOKIE['abilities_value']);}

	$values['fio'] = empty($_COOKIE['fio_value']) ? '' : $_COOKIE['fio_value'];
	$values['phone'] = empty($_COOKIE['phone_value']) ? '' : $_COOKIE['phone_value'];
	$values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
	$values['date'] = empty($_COOKIE['date_value']) ? '' : $_COOKIE['date_value'];
	//$values['abilities'] = empty($_COOKIE['abilities_value']) ? array() : unserialize($_COOKIE['abilities_value']);
	$values['gender'] = empty($_COOKIE['gender_value']) ? '' : $_COOKIE['gender_value'];
	$values['message'] = empty($_COOKIE['message_value']) ? '' : $_COOKIE['message_value'];

	// если ранее был совершён вход
	if (empty($errors) && isset($_SESSION['user_id'])) {
		$db = new PDO($dbconn, $user, $pass,
		[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
		
		$stmt = $db->prepare("
			SELECT * FROM Application
			WHERE id = :id
		");
		$stmt->bindParam(':id', $_SESSION['user_app_id']);
		$stmt->execute();

		$app = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($app) {
			$values['fio'] = $app['fio'];
			$values['phone'] = $app['phone'];
			$values['email'] = $app['email'];
			$values['date'] = $app['bdate'];
			$values['gender'] = $app['gender'];
			$values['message'] = $app['message'];

			$stmt = $db->prepare("
				SELECT lang_id FROM AppLang
				WHERE app_id = :app_id
			");
			$stmt->bindParam(':app_id', $_SESSION['user_app_id']);
			$stmt->execute();

			$values['abilities'] = array_column(
				$stmt->fetchAll(PDO::FETCH_ASSOC),
				'lang_id'
			);
		}
	}

   include('form.php');
   exit();
}

// =============| POST |=============

$error_messages = array();

function check_for_empty($field_name, $message) {
	if (empty($_POST[$field_name])) {
		//print($message);
		$error_messages[] = $message;
		setcookie($field_name . '_error', '1', time() + 24 * 60 * 60);
		return TRUE;
	}
	return FALSE;
}

$errors = FALSE;

// Проверки полей

$errors = check_for_empty('fio', 'Заполните ФИО!');
$errors = check_for_empty('phone', 'Заполните телефон!');
$errors = check_for_empty('email', 'Заполните почту!');
$errors = check_for_empty('date', 'Заполните дату!');
$errors = check_for_empty('gender', 'Укажите пол!');
$errors = check_for_empty('message', 'Заполните сообщение!');

if (strlen($_POST['fio']) > 150) {
	$error_messages[] = 'Слишком длинное ФИО!';
	ercookie('fio');
	$errors = TRUE;
}

if (!preg_match('/^[a-zA-Za-яА-ЯёЁ\s]+$/u', $_POST['fio'])) {
	$error_messages[] = 'ФИО должно содержать только буквы и пробелы!';
	ercookie('fio');
	$errors = TRUE;
}

if (!preg_match('/^[\d\s+]+$/', $_POST['phone'])) {
	$error_messages[] = 'Номер телефона может содержать только цифры, пробелы и символ +!';
	ercookie('phone');
	$errors = TRUE;
}

if (!preg_match('/@/', $_POST['email'])) {
	$error_messages[] = 'Адрес электронной почты обязательно должен содержать символ @!';
	ercookie('email');
	$errors = TRUE;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date'])) {
	$error_messages[] = 'Запись даты должна быть записана в формате год-месяц-день!';
	ercookie('date');
	$errors = TRUE;
}

$possible_genders = array("Паркет", "Линолеум", "Ламинат");
$notgender = TRUE;
foreach($possible_genders as $gender) {
	if ($gender == $_POST['gender']) { $notgender = FALSE; }
}
if ($notgender) {
	$error_messages[] = 'Выберите один из указанных полов!';
	ercookie('gender');
	$errors = TRUE;
}

if (empty($_POST['abilities'])) {
	$error_messages[] = 'Укажите хотя бы один любимый язык программирования!';
	ercookie('abilities');
	$errors = TRUE;
}

foreach($_POST['abilities'] as $abil) {
	if ($abil < 1 || $abil > 13) {
		$error_messages[] = 'Некорректный язык программирования! Выбирайте только из предоставленного списка.';
		ercookie('abilities');
		$errors = TRUE;
		break;
	}
}

vacookie('fio');
vacookie('phone');
vacookie('email');
vacookie('date');
vacookie('gender');
vacookie('message');

$str_abilities = serialize($_POST['abilities']);
setcookie('abilities_value', $str_abilities, time() + 30 * 24 * 60 * 60);

if ($errors) {
	$str_error_messages = serialize($error_messages);
	setcookie('error_messages', $str_error_messages, time() + 24 * 60 * 60);
	header('Location: index.php');
   	exit();
}
else {
	setcookie('fio_error', '', 100000);
	setcookie('phone_error', '', 100000);
	setcookie('email_error', '', 100000);
	setcookie('date_error', '', 100000);
	setcookie('gender_error', '', 100000);
	setcookie('message_error', '', 100000);
	setcookie('abilities_error', '', 100000);
	setcookie('error_messages', '', 100000);
	setcookie('save', '1');
}

$fio = $_POST['fio'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$date = $_POST['date'];
$gender = $_POST['gender'];
$message = $_POST['message'];
$langs = $_POST['abilities'];

// Сохранение в базу данных.

$db = new PDO($dbconn, $user, $pass,
	[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$db->beginTransaction();
$app_id = 0;

if (!isset($_SESSION['user_id'])) {
	$stmt = $db->prepare("
		INSERT INTO Application (fio, phone, email, bdate, gender, message)
		VALUES (:fio, :phone, :email, :bdate, :gender, :message)
	");

	$stmt->bindParam(':fio', $fio);
	$stmt->bindParam(':phone', $phone);
	$stmt->bindParam(':email', $email);
	$stmt->bindParam(':bdate', $date);
	$stmt->bindParam(':gender', $gender);
	$stmt->bindParam(':message', $message);
	$stmt->execute();

	$app_id = $db->lastInsertId();
	print('Last insert id: ');
	var_dump($langs);

	$login = makeLogin($email);
	$password = makePassword(10);

	$stmt = $db->prepare("
		INSERT INTO Users (login, password, app_id)
		VALUES (:login, :password, :app_id)
	");
	$stmt->bindParam(':login', $login);
	$stmt->bindParam(':password', $password['hash']);
	$stmt->bindParam(':app_id', $app_id);
	$stmt->execute();

	setcookie('login', $login, 0);
	setcookie('password', $password['string'], 0);
}
else {
	$app_id = $_SESSION['user_app_id'];
	$stmt = $db->prepare("
		UPDATE Application
		SET fio = :fio, phone = :phone, email = :email,
			bdate = :bdate, gender = :gender, message = :message
		WHERE id = :id
	");

	$stmt->bindParam(':fio', $fio);
	$stmt->bindParam(':phone', $phone);
	$stmt->bindParam(':email', $email);
	$stmt->bindParam(':bdate', $date);
	$stmt->bindParam(':gender', $gender);
	$stmt->bindParam(':message', $message);
	$stmt->bindParam(':id', $app_id);
	$stmt->execute();

	$stmt = $db->prepare("DELETE FROM AppLang WHERE app_id = :app_id");
	$stmt->bindParam(':app_id', $_SESSION['user_app_id']);
	$stmt->execute();
}

foreach ($langs as $lang_id) {
	$stmt = $db->prepare("INSERT INTO AppLang (app_id, lang_id) values (:app_id, :lang_id)");
	$stmt->bindParam(':app_id', $app_id);
	$stmt->bindParam(':lang_id', $lang_id);
	$stmt->execute();
}

$db->commit();

setcookie('save', '1', time() + 30 * 24 * 60 * 60);
header('Location: index.php');