<?php
	session_start();

	require_once "securimage/securimage.php";
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		// form validation

		$image = new Securimage();
		if (! $image->check($_POST["captcha_code"])) {
			$captchaError = "Wrong CAPTCHA";
		}

		if(!filter_var($_POST["mail"], FILTER_VALIDATE_EMAIL)){
			$mailError = "Invalid email address";
		}

		if($_POST["password"] != $_POST["passwordConfirm"]){
			$passwordError = "Your passwords didn't match";
		}

		if(strlen($_POST["password"]) < 16){
			$passwordError = "Your password needs to be at least 16 characters long";
		}		
	}

	$anyError = $captchaError || $passwordError || $mailError;

	$_SESSION["firstName"] = $_POST["firstName"];
	$_SESSION["lastName"] = $_POST["lastName"];
	$_SESSION["mail"] = $_POST["mail"];
	$_SESSION["message"] = $_POST["message"];
?>
<html>
<head>
	<meta charset="utf-8"/>
	<title>MMN</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<script type="text/javascript" src="Scripts/bootstrap.min.js"></script>
	<script type="text/javascript" src="Scripts/jquery-2.1.1.min.js"></script>
</head>
<body>
	<div class="col-md-offset-4">
		<div class="row col-md-6" >
			<?php 
				if(!$anyError && $_SERVER["REQUEST_METHOD"] == "POST"){
					echo '<div class="alert alert-success" role="alert">' .
						"Thank you for your Feedback, " . $_POST['firstName'] . "!" .
						'</div>';
				} else {
			?>

			<div class="/*col-md-3*/">
				<form method="post">
					<div class="form-group">
						<label for="firstName">First Name:</label>
						<input required type="text" class="form-control" name="firstName" value="<?= $_SESSION['firstName'] ?>">
					</div>
					<div class="form-group">
						<label for="lastName">Last name:</label>
						<input required type="text" class="form-control" name="lastName" value="<?= $_SESSION['lastName'] ?>">
					</div>
					<div class="form-group <?php if($mailError) echo 'has-error'; ?>">
						<label for="mail">Email:</label>
						<input required type="email" class="form-control" name="mail" value="<?= $_SESSION['mail'] ?>">
					</div>
					<?php
					if($mailError){
					echo '<div class="alert alert-danger" role="alert">
						<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
						<span class="sr-only">Error:</span>'
						. $mailError .
					"</div>";
					}
					?>

					<div class="form-group <?php if($passwordError) echo "has-error"; ?>">
						<label for="password">Password:</label>
						<input required type="password" class="form-control" name="password">
					</div>
					<div class="form-group <?php if($passwordError) echo "has-error"; ?>">
						<label for="passwordConfirm">Password (confirm):</label>
						<input required type="password" class="form-control" name="passwordConfirm">
					</div>
					<?php
					if($passwordError){
						echo '<div class="alert alert-danger" role="alert">
							<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
							<span class="sr-only">Error:</span>'
							. $passwordError .
						"</div>";
						}
					?>
					<select name="feedbackType">
						<option value="criticism">criticism</option>
						<option value="criticism">praise</option>
						<option value="criticism">other</option>
					</select>
					<br/>
					<br/>
					<div class="form-group">
						<label for="message">Your Message:</label>
						<textarea rows="5" class="form-control" name="message" ><?php echo $_SESSION["message"] ?></textarea>
					</div>
					<div class="form-group">
						<img id="captcha" src="securimage/securimage_show.php" alt="CAPTCHA Image" />
						<a href="#" onclick="document.getElementById('captcha').src = 'securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>
						<input required class="form-control <?php if($captchaError) echo 'has-error'; ?>" type="text" name="captcha_code" size="10" maxlength="6" />
					</div>
					<?php
						if($captchaError){
						echo '<div class="alert alert-danger" role="alert">
							<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
							<span class="sr-only">Error:</span>'
							. $captchaError .
						"</div>";
						}
					?>

					<button class="btn btn-default" type="submit">Submit</button>
				</form>
			</div>

			<?php } ?>
		</div>
	</div>

</body>
</html>
