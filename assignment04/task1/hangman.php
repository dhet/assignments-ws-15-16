<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>Hangman</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<style>
		body{
			margin: 2em;
		}
		.word{
			font-family: monospace;
			font-size: 1.5em;
		}
		.won{
			font-size: 1.3em;
			color: blue;
		}
	</style>
</head>
<body>
	<h2>Hangman</h2>
	<span style='display:inline;'>
	<form method="post" class='form-inline'>
		Enter a letter or try to solve:<br/>
		<div class='form-group'>
			<input required name="letter" type="text" class="col-xs-2 form-control"></input>
			<button type="submit" name="send" class='btn btn-primary'>Submit</button>
		</div>
	</form>
	<form method="post">
		<button type="submit" class='btn btn-default' name="reset">Reset</button>
		<br><br>
	</form>
	</div>
	<p>

	<?php 
		session_start();

		$word = "Jazz";
		$guess = $_POST["letter"];

		function br(){
			echo "<br/>";
		}

		function getDbConnection(){
			$url = "???";  
			$user = "??";
			$password = "???";
			$db = "???";
			return new mysqli($url, $user, $password, $db);
		}

		if(isset($_POST['reset'])){
			session_destroy();
			$_SESSION = array();
		}

		if(!isset($_SESSION["tries"])){
			$_SESSION["tries"]  = 0;
		}

		if(!isset($_SESSION['won'])){
			$_SESSION['won']  = false;
		}

		if(!isset($_SESSION["guesses"])){
			$_SESSION["guesses"] = array();
		}

		if(!$_SESSION["won"] && isset($_POST["send"])){
			if(strlen($guess) == strlen($word)){ 
				if(strcasecmp($word, $guess) == 0){
					$_SESSION["guesses"][strtolower($guess)] = 1;
					echo "Exact match!";
				} else{
					echo "Nope, that wasn't the word!";
				}
				$_SESSION["tries"]++;
			} elseif(strlen($guess) == 1){
				if(stristr($word, $guess)){
					echo "Yay! The letter \"" . $guess . "\" is in the word!";
					$_SESSION["guesses"][strtolower($guess)] = 1;
				} else{
					echo "Nope, the letter \"" . $guess . "\" is not in the word.";
				}
				$_SESSION["tries"]++;
			} else{
				echo "Invalid Input! Only single letters or words of the length of the code word are allowed.";
			}
			br();
		}

		if(isset($_POST["submitHighscore"])){
			$conn = getDbConnection();
			$query = "INSERT INTO score (username, tries, word) values(?, ?, ?);";
			$statement = $conn->prepare($query);
			$statement->bind_param("sss", $_POST["username"], $_SESSION["tries"], $word);
			$statement->execute();
			$statement->close();
			$conn->close();
			// prevent double submit
			header("Location: " . $_SERVER["REQUEST_URI"]);
			exit;
		}

		$result = "";
		$exactMatch = array_key_exists(strtolower($word), $_SESSION["guesses"]);

		if($exactMatch){
			$result = $word;
			$_SESSION["won"] = true;
		} else{
			$gap = false;
			for($i = 0; $i < strlen($word); $i++){
				if(array_key_exists(strtolower($word[$i]), $_SESSION["guesses"])){
					$result = $result . $word[$i];
				} else{
					$result = $result . "_";
					$gap = true;
				}
			}
			$_SESSION["won"] = !$gap;
		}

		$result = implode(" ", str_split($result)); // insert spaces between letters

		echo "<span class='word'>" . $result . "</span>";
		br();
		br();
		echo "Tries: " . $_SESSION["tries"];
		br();
		br();	

		if($_SESSION["won"]){
			$tryTries = $_SESSION["tries"] == 1 ? "try" : "tries";
			echo "<span class='won'>Congratulations! You guessed the word \"" . $word . "\" in " . $_SESSION["tries"] . " " . $tryTries . "!</span>";
		}
	?>
	</p>
	
	<?php 

	if($_SESSION["won"]){ 
	?>
	<p>
		<form class='form-inline' method="post">
			Do you want to submit your Score?<br/>Then enter your name:
			<div class='form-group'>
				<input type="text" name="username" class='form-control' required></input>
				<button type="submit" class='btn btn-primary' name="submitHighscore">Go</button>
			</div>
		</form>
	</p>
	<?php } ?>


	<?php
		$conn = getDbConnection();

		if($conn->connect_error){
			die("Database error! " . $conn->connect_error);
		}
		$query = "SELECT * FROM score ORDER BY tries, date ASC;";
		$results = $conn->query($query); 
		if(count($results) > 0){
			echo "<h3>Highscores</h3>";
		}
		echo "<table class='table'><tr><th>#</th><th>Word</th><th>User</th><th>Tries</th><th>Date</th></tr>";
		$i = 1;
		foreach($results as $entry){
			echo "<tr>";
			echo "<td>" . $i . "</td>";
			echo "<td>" . (($_SESSION["won"] == true) ? $entry["word"] : "???") . "</td>";
			echo "<td>" . $entry["username"] . "</td>";
			echo "<td>" . $entry["tries"] . "</td>";
			echo "<td>" . $entry["date"] . "</td>";
			echo "</tr>";
			$i++;
		}
		echo "</table>";

		$conn->close();
	?>
</body>
</html>
