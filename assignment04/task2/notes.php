<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8"/>
	<title>Note Webapp</title>
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<style>
		body{
			margin: 2em;
		}
		.login{
			text-align: right;
			padding-top: 2em;
			display: inline-block;
		}
		.container{
			display: flex;
			flex-wrap: wrap;
			align-items: center;
		}
		.note-container{
			margin: 0 3em 3em 0;
		}
		.welcome{
			font-size: 1.2em;
		}
		.header{
			margin-bottom: 20px;
		}
	</style>
</head>
<body>
	<?php 
		session_start();

		if(!isset($_SESSION["loggedIn"])){
			$_SESSION["loggedIn"] = "";
		}

		$loginError;
		$cookieName = "user";

		function br(){
			echo "<br/>";
 		}

		function createNoteContainer($id, $title, $content){
			$ret = "<div class='panel panel-default note-container'>";
			$ret = $ret . (strlen($title) < 1 ? "" : ("<div class='panel-heading'>" . $title . "</div>"));
			$ret = $ret . "<div class='panel-body'>";
			$ret = $ret . "<form method='post'>" . $content . "<br/><br/>";
			$ret = $ret . "<input type='hidden' name='id' value='" . $id . "'></input>";
			$ret = $ret . "<button type='submit' class='btn btn-default' name='deleteNote'>Delete</button>";
			$ret = $ret . "</form></div></div>";

			return $ret;
		}

		function setLoginCookie($uname){
	 		global $cookieName;
	 		$expiry = 1 * 60 * 60;
			setcookie($cookieName, $uname, time() + $expiry);
			$_SESSION["loggedIn"] = $uname;
		}

		function getDbConnection(){
			$url = "???";  
			$user = "???";
			$password = "???";
			$db = "???";
			$conn = new mysqli($url, $user, $password, $db); 
			if($conn->connect_error)
			{
				die($conn->connect_error);
			}
			return $conn;
		}

		function handleSignUp(){
			global $loginError;
			$uname = $_POST["username"];
			$hash = password_hash($_POST["pw"], PASSWORD_DEFAULT); 
			$conn = getDbConnection();
			$query = "INSERT INTO user (name, password) values(?, ?);";
			$stmt = $conn->prepare($query);
			$stmt->bind_param("ss", $uname, $hash);
			if(!$stmt->execute()){
				if($stmt->errno == 1062){
					$loginError = "User already exists. Please choose another user name.";
				} else{
					$loginError = "An Error Occured: " . $stmt->error;
				}
			}
			else{
				setLoginCookie($uname);
			}
			$stmt->close();
			$conn->close();
		}

		function fetchNotes($user){
			$conn = getDbConnection();
			$query = "select id, title, content from note where user like (select id from user where name like ?);";
			$stmt = $conn->prepare($query);
			$stmt->bind_param("s", $user);
			$stmt->execute();
			$stmt->bind_result($id, $title, $content);
			$arr = array();
			$i = 0;
			while($stmt->fetch()){
				$arr[$i]["id"] = $id;
				$arr[$i]["title"] = $title;
				$arr[$i]["content"] = $content;
				$i++;
			}
			$stmt->close();			
			$conn->close();
			return $arr;
		}

		function handleLogIn(){
			global $loginError;
			$uname = $_POST["username"];
			$password = $_POST["pw"];
			$conn = getDbConnection();
			$query = "select password from user where name like ?;";
			$stmt = $conn->prepare($query);

			$stmt->bind_param("s", $uname);
			$stmt->execute();
			$stmt->bind_result($hash);
			$stmt->fetch();

		 	if($hash && password_verify($password, $hash)){
		 		setLoginCookie($uname);
		 	} else{
		 		$loginError = "You entered a wrong user name / password combination.";
		 	}

			$stmt->close();			
			$conn->close();
		}

		function handleLogOut(){
			global $cookieName;
			setcookie($cookieName, "", time() - 1000);
			session_destroy();
			$_SESSION = array();
		}

		function handleNewNote(){
			global $cookieName;
			if(isset($_COOKIE[$cookieName])){
				$uname = $_COOKIE[$cookieName];
				$title = $_POST["title"];
				$content = nl2br($_POST["content"]);
				$conn = getDbConnection();
				$query = "insert into note (title, content, user) values (?, ?, (select id from user where name like ?));";
				$stmt = $conn->prepare($query);
				$stmt->bind_param("sss", $title, $content, $uname);
				if(!$stmt->execute()){
					echo "Error: " . $stmt->error;
				}
				else{

				}
				$stmt->close();
				$conn->close();		
			}	
		}		

		function handleDeleteNote($id){
			$conn = getDbConnection();
			$query = "delete from note where id = ? and user = (select id from user where name like ?);";
			$stmt = $conn->prepare($query);
			$stmt->bind_param("is", $id, $_SESSION["loggedIn"]);
			$stmt->execute();
			$stmt->close();			
			$conn->close();
		}

		if($_POST){
			if(isset($_POST["logIn"])){
				handleLogIn();
			} elseif(isset($_POST["logOut"])){
				handleLogOut();
			} elseif(isset($_POST["signUp"])){
				handleSignUp();
			} elseif(isset($_POST["newNote"])){
				handleNewNote();
			} elseif(isset($_POST["deleteNote"])){
				handleDeleteNote($_POST["id"]);
			}

			// prevent double submit
			header("Location: " . $_SERVER["REQUEST_URI"]);
			exit;
		}
	?>

	<div class="row header">
		<div class="col-md-4">
			<h1>Note Webapp</h1>
		</div>
		<div class="col-md-8 login">
	<?php if(!$_SESSION["loggedIn"]){ ?>
		<form class="form-inline" method="post">
			<div class="form-group">
				<label for="username">Username:</label> 
				<input class="form-control" type="text" name="username" required></input>
			</div>&nbsp;&nbsp;&nbsp;
			<div class="form-group">
				<label for="pw">Password:</label> 
				<input class="form-control" type="password" name="pw" required></input>
			</div>

			<button type="submit" class="btn btn-primary" name="logIn">Log in</button>
			<button type="submit" class="btn btn-primary" name="signUp">Sign up</button>
		</form>

	<?php

		if($loginError){
			echo "<h4><span class='label label-danger'>" . $loginError . "</span></h4>";
		}

	} else{
		echo "<form class='form-inline' method='post'><span class='welcome'>Hi, " . $_SESSION["loggedIn"] . "!&nbsp;&nbsp;&nbsp;</span>";
		echo "<button class='btn btn-default' type='submit' name='logOut'>Log out</button></form>";
	?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			<div class="panel panel-default">
				<div class="panel-heading">Create a New Note</div>
				<div class="panel-body">
					<form method="post">
						<label for="title">Title:</label>
						<input type="text" name="title" class="form-control"></input><br/>
						<textarea required name="content" class="form-control"></textarea><br/>
						<button type="submit" class="btn btn-primary" name="newNote">Create</button>
					</form>
				</div>
			</div>
		</div>
	</div>


	<div class="container row">

<!-- 	default container	
			<div class="panel panel-default note-container">
				<div class="panel-heading">Title</div>
				<div class="panel-body" >
					<form method="post">
							LOREMlaskdjfalsdjflasdkjf asdlkfj asldkfj asldkfj laskdfjlkasdf öasldj flsadjkf lsajd flaskjdf asdflk asdlfj
						<br/>
						<br/>						
						<button type="submit" class="btn btn-danger" name="deleteNote">Delete</button>
					</form>
				</div>
			</div> -->
		<?php

			$notes = fetchNotes($_SESSION["loggedIn"]);		
			foreach($notes as $note){
				echo createNoteContainer($note["id"], $note["title"], $note["content"]);
			}	

		?>
	</div>

	<?php } ?>

</body>
</html>
