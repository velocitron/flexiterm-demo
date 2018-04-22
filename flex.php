<?php
header('Access-Control-Allow-Origin', '*');
header('Access-Control-Allow-Credentials', 'true');
header('Access-Control-Allow-Methods', 'GET,HEAD,OPTIONS,POST,PUT');
header('Access-Control-Allow-Headers', 'Access-Control-Allow-Headers, Origin,Accept, X-Requested-With,Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers');
session_start();
$id = session_id();
$_SESSION['id'] = $id;
/*
	Only one user can write to the db at a time
	So we create a Semphore.txt file when a user is writing to the db.
	The server is marked busy as long as this file exists.
	Sempahore.txt is deleted when the results are displayed/saved to the session.
*/
if (file_exists('./assets/semaphore.txt') == 0) {
	$semaphore = fopen('./assets/semaphore.txt', 'w') or die('Unable to open file!');
	fclose($semaphore);
	// Copy textbox input to sample.txt 
	if (isset($_POST['textbox'])) {
		$data = $_POST['textbox'];
		$_SESSION['data'] = $data;
		$file = fopen('./assets/src/text/sample.txt', 'w') or die('Unable to open file!');
		fwrite($file, $data);
		fclose($file);
		processInput();
		echo 'Success';
	}
	else if (isset($_POST['url'])) {
		// Copy text in url text file to sample.txt
		$path = $_POST['url'];
		$file1 = file_get_contents($path);
		$path2 = './assets/src/text/sample.txt';
		$file2 = file_get_contents($path2);
	  file_put_contents($path2, $file1);
		processInput();
		echo 'Success';
	}
	elseif (isset($_FILES['file'])) {
		// Copy uploaded file as sample.txt
		$target = './assets/src/text/sample.txt';
		move_uploaded_file( $_FILES['file']['tmp_name'], $target);
		processInput();
		echo 'Success';
	}
	else {
		echo 'Error with upload';
		if (file_exists('./assets/semaphore.txt') == 1) unlink('./assets/semaphore.txt');
	}
}
else {
	echo 'Busy';
}
// Run flexiTerm Java app on sample.txt
function processInput() {
	if (file_exists("./assets/src/text/sample.txt")==1) {
		shell_exec("cd /Applications/MAMP/htdocs/Flexi/flexiterm-demo/assets/src/ ; sh FlexiTerm.sh");
	}
	saveResultsToSession();
	copyOutputFiles();
}
function copyOutputFiles() {
	// Copy all output files to new folder (to prevent overwriting by subsequent users)
	if (file_exists('./assets/src/output.html') == 1) {
		copy('./assets/src/output.html', './assets/output/'.$_SESSION["id"].'_output.html');
	}
	if (file_exists('./assets/src/output.mixup') == 1) {
		copy('./assets/src/output.mixup', './assets/output/'.$_SESSION["id"].'_output.mixup');
	}
	if (file_exists('./assets/src/output.csv') == 1) {
		copy('./assets/src/output.csv', './assets/output/'.$_SESSION["id"].'_output.csv');
	}
	if (file_exists('./assets/src/output.txt') == 1) {
		copy('./assets/src/output.txt', './assets/output/'.$_SESSION["id"].'_output.txt');
	}
}
function saveResultsToSession() {
	try {
    $conn  = new PDO('sqlite:./assets/src/flexiterm.sqlite') or die("cannot open the database");
    $tableResult = $conn->query("SELECT  DISTINCT LOWER(phrase) as phrase, t1.expanded, ROUND(c,3) as c FROM term_termhood t1
			LEFT JOIN term_normalised t2 on t1.expanded = t2.expanded
			LEFT JOIN term_phrase t3 on t3.normalised = t2.normalised
			GROUP BY t1.expanded
			ORDER BY c DESC");
    // Get number of results
    $countResult = $conn->query("SELECT count(*) AS count FROM term_termhood ;");
    // Get original text
    $textResult = $conn->query("SELECT document FROM data_document ;");

    $table = array();
    $count = $countResult->fetch(); 
    $count = $count["count"];
    $text =  $textResult->fetch();
   	$text = $text["document"];

		while ($row = $tableResult->fetch(PDO::FETCH_ASSOC)) {
			$table[] = $row;
		}
		// Save data as Session vaiables
	  $_SESSION["count"] = $count;
	  $_SESSION["text"] = $text;
	  $_SESSION["table"] = $table;
  }
  catch(PDOException $e) {
    print 'Exception : '.$e->getMessage();
  }
}
?>
