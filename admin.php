<?php
//require("votes.inc.php");
require_once("mysql.php");


// Pull query string
if(isset($_GET['enable'])) {
    $enable = $_GET['enable'];
}
else {
    $enable = -1;
}
if(isset($_GET['deleteAllVotes'])) {
    $deleteAllVotes = $_GET['deleteAllVotes'];
}
else {
    $deleteAllVotes = -1;
}


// Get candidates
$numCandidates = 1;
//$candidates[0] = "";
//$images[0] = "";
$query = "SELECT * FROM textvote_choices ORDER BY id";
if($result = $mysqli->query($query))
{
    while($obj = $result->fetch_object())
    {
        $candidates[$numCandidates] = $obj->firstname . " " . $obj->lastname;
        $images[$numCandidates] = $obj->imgsrc;
        $numCandidates++;
    }
    $numCandidates--;    // off by one error
}
else
{
    die("Error on retrieving candidates");
}

// Get votes
$totalVotes = 0;
for($i = 1; $i <= $numCandidates; $i++) {
  $query = "SELECT count(*) AS 'count' FROM textvote_votes WHERE vote = '$i'";
  if($result = $mysqli->query($query))
  {
      while($obj = $result->fetch_object())
      {
        $vote[$i] = $obj->count;
        $totalVotes += $obj->count;
      }
  }
  else
  {
    die("Error on vote counting");
  }
}


if($enable == "5") {
  // UPDATE textvote_setting SET value='1' WHERE setting = 'enabled';
  $query = "UPDATE textvote_setting SET value='1' WHERE setting = 'enabled'";
  $result = $mysqli->query($query);
  if($result == false)
    print "ERROR on enable update for 3 minutes";

  // UPDATE textvote_setting SET value='time' WHERE setting = 'timetoquit';
  $query = "UPDATE textvote_setting SET value='";
  $newtime = time() + (60 * 3);
  $query .= $newtime;
  $query .= "' WHERE setting = 'timetoquit'";
  $result = $mysqli->query($query);
  if($result == false)
    print "ERROR on time update for 3 minutes";

  print "<div>Updated to open in 3 minutes! ($newtime)</div>\n\n";
}
else if($enable == "10") {
  // UPDATE textvote_setting SET value='1' WHERE setting = 'enabled';
  $query = "UPDATE textvote_setting SET value='1' WHERE setting = 'enabled'";
  $result = $mysqli->query($query);
  if($result == false)
    print "ERROR on enable update for 2 minutes";

  // UPDATE textvote_setting SET value='time' WHERE setting = 'timetoquit';
  $query = "UPDATE textvote_setting SET value='";
  $newtime = time() + (60 * 2);
  $query .= $newtime;
  $query .= "' WHERE setting = 'timetoquit'";
  $result = $mysqli->query($query);
  if($result == false)
    print "ERROR on update for 2 minutes";

  print "<div>Updated to open in 2 minutes! ($newtime)</div>\n\n";
}
else if($enable == "0") {
  // UPDATE textvote_setting SET value='1' WHERE setting = 'enabled';
  $query = "UPDATE textvote_setting SET value='0' WHERE setting = 'enabled'";
  $result = $mysqli->query($query);
  if($result == false)
    print "ERROR on disable update";

  print "<div>Disabled!</div>\n\n";
}

else if($deleteAllVotes == "1")
{
    $query = "TRUNCATE TABLE textvote_votes";
    $result = $mysqli->query($query);
    if($result == false)
        print "ERROR on deleting votes: " . $mysqli->error;

    print "<div>Votes deleted!</div>\n\n";
}

$query = "SELECT value FROM textvote_setting WHERE setting = 'enabled'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$enabled = $row[0];

$query = "SELECT value FROM textvote_setting WHERE setting = 'timetoquit'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$time_to_quit = (int) $row[0];




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="15;url=http://64.225.124.123/admin.php" />
<title>Halloween Vote System Console</title>
<style type="text/css">
.header {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 36px;
  color: #0000ff;
}
body {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 14px;
  color: #000000;
  font-style: none;
}

.vote {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 28px;
  color: #000000;
  font-style: none;
}

.disclaimer {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 18px;
  color: #000000;
  font-style: none;
}

</style>
</head>

<body>

<h2>Halloween Vote System Console</h2>

<div>Voting system: Currently <?php
if($enabled == "0") print "disabled";
else if ($enabled == "1") print "enabled";
else print "Uh, not sure..."; ?></div>

<div><a href="admin.php?enable=5">Enable for 5 minutes</a></div>
<div><a href="admin.php?enable=10">Enable for 10 minutes</a></div>

<div><a href="admin.php?enable=0">Disable now</a></div>

<div>&nbsp;</div>

<div><a href="admin.php">Refresh</a></div>
<div>Time now: <?php
print date("h:i:s a",time());
?></div>

<div>Time until disabled: <?php
print date("h:i:s a",$time_to_quit);
?>
</div>
<div>&nbsp;</div>

<div>Current results:</div>
<?php
for($i = 1; $i <= $numCandidates; $i++) {
  $name[$i] = str_replace("<br />", " ", $candidates[$i]);
  $name[$i] = str_replace("&nbsp;", "", $candidates[$i]);
  print "<div>$i: " . $candidates[$i] . " - " . $vote[$i] . "</div>\n";
} ?>

<div>&nbsp;</div>
<div>&nbsp;</div>
<div>&nbsp;</div>
<div>WARNING!!! DESTRUCTIVE ACTION!</div>
<div><a href="admin.php?deleteAllVotes=1">Delete votes</a></div>

</body>
</html>
