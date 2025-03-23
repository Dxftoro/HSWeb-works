<?php
if (!empty($_COOKIE['error_messages'])) {
	$error_messages = unserialize($_COOKIE['error_messages']);

	print('<div class="error" id="messages">');
	foreach($error_messages as $message) {
		print($message . "<br/>");
	}
	print('</div>');
}
elseif (!empty($_COOKIE['save'])) {
	print('<div class="correct" id="cor-messages">');
	foreach($corMessages as $message) {
		print($message . "<br/>");
	}
	print('</div>');
}

$error_class = 'is-invalid';
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>TASK 5</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="styles.css" />
</head>

<body>
	<div class="container mt-5 col-md-6 border border-secondary rounded p-3" id="form-wrapper">
		<h2><strong>Задание 5 (Sessions)</strong></h2>
		<form id="main-form" action="" method="post">
			<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
			
			<div class="form-group">
				<label for="fio">Фамилия, имя, отчество:</label>
				<input class="form-control <?php if ($errors['fio']) {print($error_class);} ?>" type="text" id="fio" name="fio" value="<?php print $values['fio']; ?>" required>
			</div>

			<div class="form-group">
				<label for="phone">Телефон:</label>
				<input class="form-control <?php if ($errors['phone']) {print($error_class);} ?>" type="tel" id="phone" name="phone" value="<?php print $values['phone']; ?>" required>
			</div>

			<div class="form-group">
				<label for="email">Адрес электронной почты:</label>
				<input class="form-control <?php if ($errors['email']) {print($error_class);} ?>" type="email" id="email" name="email" value="<?php print $values['email']; ?>" required>
			</div>

			<div class="form-group">
				<label for="date">Дата рождения:</label>
				<input class="form-control <?php if ($errors['date']) {print($error_class);} ?>" type="date" id="date" name="date" value="<?php print $values['date']; ?>" required>
			</div>

			<div class="form-group">
                		<label for="progLanguage">Любимый язык программирования</label>
                		<select id="progLanguage" class="form-select" name="abilities[]" multiple="multiple" aria-label="Выбрать язык" required>
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
			            <input class="form-check-input" type="radio" id="male" name="gender" value="Паркет" <?php echo ($values['gender'] == 'Паркет') ? 'checked' : ''; ?> required>
			            <label class="form-check-label" for="male">Паркет</label>
			        </div>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="female" name="gender" value="Линолеум" <?php echo ($values['gender'] == 'Линолеум') ? 'checked' : ''; ?> required>
			            <label class="form-check-label" for="female">Линолеум</label>
			        </div>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="other" name="gender" value="Ламинат" <?php echo ($values['gender'] == 'Ламинат') ? 'checked' : ''; ?> required>
			            <label class="form-check-label" for="other">Ламинат</label>
			        </div>
			</div>

            		<div class="form-group">
	            		<label for="message">Сообщение</label>
		    		<textarea class="form-control <?php if ($errors['message']) {print($error_class);} ?>" id="message" name="message" rows="4" required><?php print $values['message'] ?></textarea>
            		</div>

			<div class="form-check">
                <input type="checkbox" class="form-check-input" id="consent" required>
                <label class="form-check-label" for="consent">Соглашаюсь с политикой обработки персональных данных</label>
            </div><br />

			<div class="form-group">
				<input class="btn btn-light" type="submit">
			</div>
		</form>
	</div>
	<a href="admin.php">Для администраторов</a>
</body>

</html>