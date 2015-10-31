<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>MMN</title>
</head>
<body>
	<form method="post">
		<textarea name="text" id="text"></textarea><br/>
		<input type="submit"></input>
	</form>
	<?php 
		$input = $_POST["text"]; 
		$words = explode(" ", $input);
		foreach($words as &$word){
			if(strlen($word) > 3){
				$word = $word[0] . str_shuffle(substr($word, 1, strlen($word) - 2)) . $word[strlen($word)-1];				
			}
		}
		echo join(" ", $words);
	?>
</body>
</html>
