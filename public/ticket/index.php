<?php
/**
 * Ticket creation/edit page.
 *
 * @author Parsa Nikpour <pnikpour@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2015 Parsa Nikpour
 */
?>

<?php
	ini_set('display_startup_errors',1);
	ini_set('display_errors',1);
	error_reporting(-1);

	include('../assets/php/lib.php');
	dontCache();

	checkLastActivity();
	redirectIfNotLoggedIn();
	date_default_timezone_set('America/Los_Angeles');
?>

<html>
<head>
	<script src="//code.jquery.com/jquery-1.10.2.js?<?php echo time(); ?>"></script>
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js?<?php echo time(); ?>"></script>
	<script src='../assets/js/effect.js?<?php echo time(); ?>' type='text/javascript'></script>
	<script src='../assets/js/lib.js?<?php echo time(); ?>' type='text/javascript'></script>
	<link rel='stylesheet' href='../assets/css/styles.css?<?php echo time(); ?>' type='text/css' />
	<title>Create Work Order</title>
</head>

<body>

<header><img class='logo' src='../assets/images/logo.png' alt='QuikPHP Logo'></header>
<?php
	// Governs when the user submits a ticket and refreshes the page; will
	// increment the ticket number count by one
	if (isset($_POST['saveNew'])) {
		
		// Submit the ticket to the database
		$ticketNumber = getMaxTicketNumber();
		$requestor = $_SESSION['user'];
		$dateCreated = $_POST['dateCreated'];
		$problemDescription = $_POST['problemDescription'];
		$problemCode = $_POST['problemCode'];
		$assignedTo = $_POST['assignedTo'];
		$dateClosed = $_POST['dateClosed'];
		$status = $_POST['status'];
		$solutionDescription = $_POST['solutionDescription'];
		$query = "INSERT INTO tickets (ticketNumber, requestor, dateCreated, problemDescription, problemCode, assignedTo, status, dateClosed, solutionDescription) values (:ticketNumber, :requestor, :dateCreated, :problemDescription, :problemCode, :assignedTo, :status, :dateClosed, :solutionDescription);";
		
		$stmt = getDB()->prepare($query);
		$stmt->bindParam(':ticketNumber', $ticketNumber);
		$stmt->bindParam(':requestor', $requestor);
		$stmt->bindParam(':dateCreated', $dateCreated);
		$stmt->bindParam(':problemDescription', $problemDescription);
		$stmt->bindParam(':problemCode', $problemCode);
		$stmt->bindParam(':assignedTo', $assignedTo);
		$stmt->bindParam(':status', $status);
		$stmt->bindParam(':dateClosed', $dateClosed);
		$stmt->bindParam(':solutionDescription', $solutionDescription);

		$stmt->execute();
	}
	navPOST();

?>


<h1>QuikPHP Ticket</h1>

<div class='formContainer'>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" name='ticket' id='ticket' method='post'>

	<?php include '../assets/php/createNav.php'; ?>
	
	<table border=1>
		<tr>
			<th><h2>Ticket Number</h2></th>
			<th><h2>Requestor</h2></th>
			<th><h2>Date Created</h2></th>
			<th><h2>Problem Code</h2></th>
		</tr>
		<tr>
			<td>
				<?php
					$newID = getMaxTicketNumber();
					echo "<input type='text' readonly='true' name='ticketNumber' value=$newID />";
				?>
			</td>
			<td>
				<?php
					$requestor = $_SESSION['user'];
					echo "<input type='text' readonly='true' name='requestor' value=$requestor />";
				?>
			</td>
			<td>
				<?php
					$date = date("Y-m-d");
					$date = date("Y-m-d", strtotime(str_replace('-', '/', $date)));
					echo "<input type='text' name='dateCreated' value=$date readonly='true' />";
				?>
			<td>
			<select name='problemCode'>
				<?php
				try {
					$table = "tickets";
					$col = "problemCode";
					$sql = 'SHOW COLUMNS FROM '.$table.' WHERE field="'.$col.'"';
					$row = getDB()->query($sql)->fetch(PDO::FETCH_ASSOC);
					foreach(explode("','",substr($row['Type'],6,-2)) as $option) {
						echo("<option>$option</option>");
					}
				} catch (PDOException $e) {
					echo "ERROR: " . $e->getMessage();
				}	
				?>
			</select>
			</td>
		</tr>
		<tr>
			<th colspan='2'><h2>Problem Details</h2></th>
			<th colspan='2'><h2>Solution Details</h2></th>
		</tr>
		<tr>
			<td colspan='2'><textarea rows='5' name='problemDescription'></textarea></td>
			<td colspan='2'><textarea rows='5' name='solutionDescription'></textarea></td>
		</tr>
	</table>
	<br>
	<table border=1>
		<tr>
			<th><h2>Assigned To</h2></th>
			<th><h2>Status</h2></th>
			<th><h2>Date Closed</h2></th>
		</tr>
		<tr>
			<td>
			<select name='assignedTo'>
				<?php
					$table = "tickets";
					$col = 'assignedTo';
					$sql = 'SHOW COLUMNS FROM '.$table.' WHERE field="'.$col.'"';
					$row = getDB()->query($sql)->fetch(PDO::FETCH_ASSOC);
					foreach(explode("','", substr($row['Type'],6,-2)) as $option) {
						echo ("<option>$option</option>");
					}
				?>
			</select>
			</td>
			<td>
			<select name='status' id='status' onchange='readyDateClosed()'>
			<?php
				$table = "tickets";
				$col = 'status';
				$sql = 'SHOW COLUMNS FROM '.$table.' WHERE field="'.$col.'"';
				$row = getDB()->query($sql)->fetch(PDO::FETCH_ASSOC);
				foreach(explode("','", substr($row['Type'],6,-2)) as $option) {
					echo ("<option>$option</option>");
				}


			// If the ticket number POST variable is set from the main menu dashboard, populate the form fields
			// with the data from the ticket pertaining to this key
			if (isset($_POST['btnEdit'])) {
				$ticketNumberPOST = $_POST['btnEdit'];
				echo $ticketNumber;
			}
			?>
			</select>
			</td>
			<td>
				<input type='text' name='dateClosed' id='dateClosed' />
			</td>
		</tr>
		<tr>
			<td>
				<input type='submit' class='button' name='saveNew' value='Save and New' />
			</td>
		</tr>
	</table>
	</form>
</div>
</body>
</html>
