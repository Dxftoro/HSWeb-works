<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<title>TASK 3</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
	<link rel="stylesheet" href="styles.css" />
</head>

<body>
	<div class="container mt-5 col-md-6 border border-secondary rounded p-3" id="form-wrapper">
		<h2><strong>Задание 3 (форма)</strong></h2>
		<form id="main-form" action="" method="post">
			<div class="form-group">
				<label for="fio">Фамилия, имя, отчество:</label>
				<input class="form-control" type="text" id="fio" name="fio" required>
			</div>

			<div class="form-group">
				<label for="phone">Телефон:</label>
				<input class="form-control" type="tel" id="phone" name="phone" required>
			</div>

			<div class="form-group">
				<label for="email">Адрес электронной почты:</label>
				<input class="form-control" type="email" id="email" name="email" required>
			</div>

			<div class="form-group">
				<label for="email">Дата рождения:</label>
				<input class="form-control" type="date" id="date" name="date" required>
			</div>

			<div class="form-group">
                		<label for="progLanguage">Любимый язык программирования</label>
                		<select id="progLanguage" class="form-select" name="abilities[]" multiple="multiple" aria-label="Выбрать язык" required>
                    			<option value=1>C</option>
                    			<option value=2>C++</option>
                    			<option value=3>Lua</option>
                    			<option value=4>Python</option>
                    			<option value=5>JavaScript</option>
                    			<option value=6>PHP</option>
                    			<option value=7>Java</option>
                    			<option value=8>Pascal</option>
                    			<option value=9>Haskel</option>
                    			<option value=10>Rust</option>
                    			<option value=11>Clojure</option>
                    			<option value=12>Prolog</option>
                    			<option value=13>Scala</option>
                		</select>
            		</div>

			<div class="form-group">
			        <label>Ваш пол:</label><br>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="male" name="gender" value="Паркет" required>
			            <label class="form-check-label" for="male">Паркет</label>
			        </div>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="female" name="gender" value="Линолеум" required>
			            <label class="form-check-label" for="female">Линолеум</label>
			        </div>
			        <div class="form-check">
			            <input class="form-check-input" type="radio" id="other" name="gender" value="Ламинат" required>
			            <label class="form-check-label" for="other">Ламинат</label>
			        </div>
			</div>

            <div class="form-group">
	            <label for="message">Сообщение</label>
	            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>

			<div class="form-check">
                <input type="checkbox" class="form-check-input" id="consent" required>
                <label class="form-check-label" for="consent">Соглашаюсь с политикой обработки персональных данных</label>
            </div><br />

			<div class="form-group">
				<input type="submit">
			</div>
		</form>
	</div>
</body>

</html>
