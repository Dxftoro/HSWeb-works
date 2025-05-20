<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$dbconn = 'mysql:host=localhost;dbname=uxxxxx';
$user = 'uxxxxx';
$pass = 'xxxxxxx';

$db = new PDO($dbconn, $user, $pass,
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Получаем JSON данные из тела запроса
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Проверяем CSRF токен
if (!isset($data['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF token validation failed']);
    exit();
}

// Функции валидации из index.php
function validateData($data) {
    $errors = [];
    
    // Проверка ФИО
    if (empty($data['fio'])) {
        $errors['fio'] = 'Заполните ФИО!';
    } elseif (strlen($data['fio']) > 150) {
        $errors['fio'] = 'Слишком длинное ФИО!';
    } elseif (!preg_match('/^[a-zA-Za-яА-ЯёЁ\s]+$/u', $data['fio'])) {
        $errors['fio'] = 'ФИО должно содержать только буквы и пробелы!';
    }
    
    // Проверка телефона
    if (empty($data['phone'])) {
        $errors['phone'] = 'Заполните телефон!';
    } elseif (!preg_match('/^[\d\s+]+$/', $data['phone'])) {
        $errors['phone'] = 'Номер телефона может содержать только цифры, пробелы и символ +!';
    }
    
    // Проверка email
    if (empty($data['email'])) {
        $errors['email'] = 'Заполните почту!';
    } elseif (!preg_match('/@/', $data['email'])) {
        $errors['email'] = 'Адрес электронной почты обязательно должен содержать символ @!';
    }
    
    // Проверка даты
    if (empty($data['date'])) {
        $errors['date'] = 'Заполните дату!';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
        $errors['date'] = 'Запись даты должна быть записана в формате год-месяц-день!';
    }
    
    // Проверка пола
    $possible_genders = ["Паркет", "Линолеум", "Ламинат"];
    if (empty($data['gender'])) {
        $errors['gender'] = 'Укажите пол!';
    } elseif (!in_array($data['gender'], $possible_genders)) {
        $errors['gender'] = 'Выберите один из указанных полов!';
    }
    
    // Проверка сообщения
    if (empty($data['message'])) {
        $errors['message'] = 'Заполните сообщение!';
    }
    
    // Проверка языков программирования
    if (empty($data['abilities']) || !is_array($data['abilities'])) {
        $errors['abilities'] = 'Укажите хотя бы один любимый язык программирования!';
    } else {
        foreach($data['abilities'] as $abil) {
            if ($abil < 1 || $abil > 13) {
                $errors['abilities'] = 'Некорректный язык программирования! Выбирайте только из предоставленного списка.';
                break;
            }
        }
    }
    
    return $errors;
}

// Валидация данных
$errors = validateData($data);
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit();
}

// Обработка данных
$fio = $data['fio'];
$phone = $data['phone'];
$email = $data['email'];
$date = $data['date'];
$gender = $data['gender'];
$message = $data['message'];
$langs = $data['abilities'];

$response = [];
$db->beginTransaction();

if (!isset($_SESSION['user_id'])) {
    // Создание новой заявки для неавторизованного пользователя
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
    
    // Генерация логина и пароля
    $login = explode("@", $email)[0] . rand(1000, 9999);
    $password = '';
    $chars = '_-0123456789ABCDEFGHJKLMNOPQRSTUVWXYZabcdefghjklmnopqrstuvwxyz';
    for ($i = 0; $i < 10; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Сохранение пользователя
    $stmt = $db->prepare("
        INSERT INTO Users (login, password, app_id)
        VALUES (:login, :password, :app_id)
    ");
    $stmt->bindParam(':login', $login);
    $stmt->bindParam(':password', $password_hash);
    $stmt->bindParam(':app_id', $app_id);
    $stmt->execute();
    
    $response = [
        'success' => true,
        'login' => $login,
        'password' => $password,
        'profile_url' => 'index.php'
    ];
} else {
    // Обновление данных для авторизованного пользователя
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
    
    // Удаление старых языков
    $stmt = $db->prepare("DELETE FROM AppLang WHERE app_id = :app_id");
    $stmt->bindParam(':app_id', $app_id);
    $stmt->execute();
    
    $response = [
        'success' => true,
        'message' => 'Данные успешно обновлены'
    ];
}

// Добавление языков программирования
foreach ($langs as $lang_id) {
    $stmt = $db->prepare("INSERT INTO AppLang (app_id, lang_id) values (:app_id, :lang_id)");
    $stmt->bindParam(':app_id', $app_id);
    $stmt->bindParam(':lang_id', $lang_id);
    $stmt->execute();
}

$db->commit();

// Для авторизованных пользователей также возвращаем обновленные данные
$response['fio'] = $fio;
$response['phone'] = $phone;
$response['email'] = $email;
$response['date'] = $date;
$response['gender'] = $gender;
$response['message'] = $message;
$response['abilities'] = $langs;

echo json_encode($response);
?>