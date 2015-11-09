<html>
<head>
	<title>Codebreaker</title>
	<style>
		body {
			font-family: sans-serif;
		}
		.circle {
			border-radius: 50%;
			width: 30px;
			height: 30px; 
			line-height: 30px;
			font-size: 15px;
			border: 1px solid black;
			margin: 1em;
			float: left;
			text-align: center;
		}
		.correct {
			background: red;
			color: white;
		}
		.half-correct {
			background: black;
			color: white;
		}
	</style>
</head>
<body>

	<form method="post">
		<input name="inputOne" type="text"></input>
		<input name="inputTwo" type="text"></input>
		<input name="inputThree" type="text"></input>
		<input name="inputFour" type="text"></input>
		<button type="submit">Check</button>
		<button name="restart" type="submit">Restart</button>
	</form>
	<?php 
		session_start();

		$codeWordLen = 4;
		$candidates = array("A", "B", "C", "D", "E", "F", "G");

		function isInputValid($arr){
			global $candidates;
			foreach($arr as $input){
				if(strlen($input) != 1 || !in_array($input, $candidates)){
					return false;
				}
			}
			return true;
		}

		if(!isset($_SESSION["codeWord"])){
			$_SESSION["codeWord"]  = array();
			for($i = 0; $i < $codeWordLen; $i++){
				$candidate = $candidates[rand(0, count($candidates) - 1)];
				while(in_array($candidate, $_SESSION["codeWord"])){
					$candidate = $candidates[rand(0, count($candidates) - 1)];	
				}
				$_SESSION["codeWord"][$i] = $candidate;
			}
		}

		if(isset($_POST["restart"])){
			session_destroy();
		} else{
			$latestTry = array(strtoupper($_POST["inputOne"]), 
				strtoupper($_POST["inputTwo"]), 
				strtoupper($_POST["inputThree"]), 
				strtoupper($_POST["inputFour"]));

			if(isInputValid($latestTry)){
				$_SESSION["tries"][count($_SESSION["tries"])] = $latestTry;
			} else{
				echo "Invalid Input. Only the single letters 'A', 'B', 'C', 'D', 'E', 'F' and 'G' are allowed.<br/><br/><br/>";
			}

			if(count($_SESSION["tries"]) > 0){
				echo "<table><tr><th>Try #</th><th>Guess</th></tr>";
				for($j = count($_SESSION["tries"]) - 1; $j >= 0; $j--){
					$try = $_SESSION["tries"][$j];
					echo "<tr><td>" . ($j + 1) . "</td><td>";
					$correct = 0;
					for($i = 0; $i < count($try); $i++){
						echo "<div class='circle ";
						if(strcasecmp($try[$i], $_SESSION["codeWord"][$i]) == 0){
							echo "correct";
							$correct ++;
						} elseif(in_array($try[$i], $_SESSION["codeWord"])){
							echo "half-correct";
						}
						echo "'>" . $try[$i] . "</div>";
					}
					echo "<br style='clear:left'/></td><td>" . ($correct == $codeWordLen ? "You won, congrats!" : "") . "</td></tr>";
				}
				echo "</table>";
			}
		}
	?>

</body>
</html>