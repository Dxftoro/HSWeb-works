<?php
// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // В суперглобальном массиве $_GET PHP хранит все параметры, переданные в текущем запросе через URL.
  if (!empty($_GET['save'])) {
    // Если есть параметр save, то выводим сообщение пользователю.
    print('Спасибо, результаты сохранены.');
  }
  // Включаем содержимое файла form.php.
  include('form.php');
  // Завершаем работу скрипта.
  exit();
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в БД.

// Проверяем ошибки.

function check_for_empty($field_name, $message) {
	if (empty($_POST[$field_name])) {
		print($message);
		return TRUE;
	}
	return FALSE;
}

$errors = FALSE;

// Проверки на пустоту

$errors = check_for_empty('fio', 'Заполните ФИО!');
$errors = check_for_empty('phone', 'Заполните телефон!');
$errors = check_for_empty('email', 'Заполните почту!');
$errors = check_for_empty('date', 'Заполните дату!');
$errors = check_for_empty('gender', 'Укажите пол!');
$errors = check_for_empty('message', 'Заполните сообщение!');

if (strlen($_POST['fio']) > 150) {
	print('Слишком длинное ФИО!<br/>');
	$errors = TRUE;
}

if (!preg_match('/^[a-zA-Za-яА-ЯёЁ\s]+$/u', $_POST['fio'])) {
	print('ФИО должно содержать только буквы и пробелы!<br/>');
	$errors = TRUE;
}

if (!preg_match('/^[\d\s+]+$/', $_POST['phone'])) {
	print('Номер телефона может содержать только цифры, пробелы и символ +!');
	$errors = TRUE;
}

if (!preg_match('/@/', $_POST['email'])) {
	print('Адрес электронной почты обязательно должен содержать символ @!');
	$errors = TRUE;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date'])) {
	print('Запись даты должна быть записана в формате год-месяц-день!');
	$errors = TRUE;
}

$possible_genders = array("Паркет", "Линолеум", "Ламинат");
$notgender = TRUE;
foreach($possible_genders as $gender) {
	if ($gender == $_POST['gender']) { $notgender = FALSE; }
}
if ($notgender) {
	print('Выберите один из указанных полов!');
	$errors = TRUE;
}

if (empty($_POST['abilities'])) {
	print('Укажите хотя бы один любимый язык программирования!<br/>');
	$errors = TRUE;
}

foreach($_POST['abilities'] as $abil) {
	if ($abil < 1 || $abil > 13) {
		print('Некорректный язык программирования! Выбирайте только из предоставленного списка.<br/>');
		$errors = TRUE;
		break;
	}
}

// *************
// Тут необходимо проверить правильность заполнения всех остальных полей.
// *************

if ($errors) {
  	// При наличии ошибок завершаем работу скрипта.
  	exit();
}

$fio = $_POST['fio'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$date = $_POST['date'];
$gender = $_POST['gender'];
$message = $_POST['message'];
$langs = $_POST['abilities'];

// Сохранение в базу данных.

$user = 'uXXXXX'; // Заменить на ваш логин uXXXXX
$pass = '*******'; // Заменить на пароль
$db = new PDO('mysql:host=localhost;dbname=u68660', $user, $pass,
	[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); // Заменить test на имя БД, совпадает с логином uXXXXX

$stmt = $db->prepare("INSERT INTO Application (fio, phone, email, bdate, gender, message) values (:fio, :phone, :email, :bdate, :gender, :message)");
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

foreach ($langs as $lang_id) {
	$stmt = $db->prepare("INSERT INTO AppLang (app_id, lang_id) values (:app_id, :lang_id)");
	$stmt->bindParam(':app_id', $app_id);
	$stmt->bindParam(':lang_id', $lang_id);
	$stmt->execute();
}

// Подготовленный запрос. Не именованные метки.
/*
try {
  $stmt = $db->prepare("INSERT INTO Application SET name = ?");
  $stmt->execute([$_POST['fio']]);
}
catch(PDOException $e){
  print('Error : ' . $e->getMessage());
  exit();
}

//  stmt - это "дескриптор состояния".
 
//  Именованные метки.
//$stmt = $db->prepare("INSERT INTO test (label,color) VALUES (:label,:color)");
//$stmt -> execute(['label'=>'perfect', 'color'=>'green']);
 
//Еще вариант
/*$stmt = $db->prepare("INSERT INTO users (firstname, lastname, email) VALUES (:firstname, :lastname, :email)");
$stmt->bindParam(':firstname', $firstname);
$stmt->bindParam(':lastname', $lastname);
$stmt->bindParam(':email', $email);
$firstname = "John";
$lastname = "Smith";
$email = "john@test.com";
$stmt->execute();
*/

// Делаем перенаправление.
// Если запись не сохраняется, но ошибок не видно, то можно закомментировать эту строку чтобы увидеть ошибку.
// Если ошибок при этом не видно, то необходимо настроить параметр display_errors для PHP.
header('Location: ?save=1');
