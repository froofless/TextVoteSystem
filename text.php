<?php
require_once("mysql.php");

$tblSettings = "textvote_settings";
$tblCandidates = "textvote_choices";
$tblVotes = "textvote_votes";

// Response functions
function smsHeader() {
  header("content-type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
}

function smsVotingOver() {
  smsHeader();
  print "<Response>\n";
  print "<Sms>The Halloween voting system isn't currently open. Sorry!</Sms>\n";
  print "</Response>\n";
}
function smsNAN() {
  smsHeader();
  print "<Response>\n";
  print "<Sms>That's not a numeric vote, try again!</Sms>\n";
  print "</Response>\n";
}

function smsErrorBadVote() {
  smsHeader();
  print "<Response>\n";
  print "<Sms>That was not a valid vote, try again!</Sms>\n";
  print "</Response>\n";
}

function smsDuplicateVote($theVote) {
  smsHeader();
  print "<Response>\n";
  print "<Sms>Since you already voted, we updated your vote to " . $theVote . ".</Sms>\n";
  print "</Response>\n";
}

function smsErrorTooManyRows() {
  smsHeader();
  print "<Response>\n";
  print "<Sms>Error: DB pulled too many rows!</Sms>\n";
  print "</Response>\n";
}

function smsErrorAlreadyVoted() {
  smsHeader();
  print "<Response>\n";
  print "<Sms>You already voted, cheater!</Sms>\n";
  print "</Response>\n";
}

function smsThanks($theVote) {
  smsHeader();
  //$v = intval($v);
  print "<Response>\n";
  print "<Sms>Your vote for " . $theVote . " was recorded. thank you for voting!</Sms>\n";
  print "</Response>\n";
}

// Find out if voting is enabled or not
$query = "SELECT value FROM textvote_setting WHERE setting = 'enabled'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$enabled = $row[0];


// Not enabled? Immediately send invalid response
if($enabled == "0") {
  smsVotingOver();
  die();
}

//***************************************************
//                  pick back up here

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
        $Fcandidates[$numCandidates] = $obj->firstname;
        $images[$numCandidates] = $obj->imgsrc;
        $numCandidates++;
    }
    $numCandidates--;    // off by one error
}
else
{
    die("Error on retrieving candidates");
}



// Get info from Twilio
$phone = $_REQUEST['From'];
$vote = $_REQUEST['Body'];

// Record log
$query = "INSERT INTO textvote_log (phone, body) VALUES ('" . $phone . "','" . $vote . "');";
$result = $mysqli->query($query);



// DEBUG:
/*
print "phone: $phone<br />";
print "vote: $vote<br />";
*/

// Vote should only be one number, keep only one number
if(!is_numeric($vote)) {
  smsNAN();
  die();
}

// Vote should only be one number, keep only one number
if(intval($vote) < 1 || intval($vote) > $numCandidates) {
  smsErrorBadVote();
  die();
}

// PHP/MySQL security
$vote = $mysqli->real_escape_string($vote);

// Performing SQL query
$query = "SELECT * FROM textvote_votes WHERE phone LIKE '$phone'";
$result = $mysqli->query($query);

// If results >1, we have other problems
if($result->num_rows > 1) {
  smsErrorTooManyRows();
  die();
}

// If results =1, send error
if($result->num_rows == 1) {
  $query2 = "UPDATE textvote_votes SET vote = '" . $vote . "' WHERE phone = '" . $phone . "';";
  $result2 = $mysqli->query($query2);
//  smsErrorAlreadyVoted();
  smsDuplicateVote($Fcandidates[$vote]);

  die();
}

// If results = 0, insert vote
// In theory, only 0 rows left...
//if($result->num_rows == 0) {
  $query2 = "INSERT INTO textvote_votes (phone, vote) VALUES ('" . $phone . "','" . $vote . "');";
  $result2 = $mysqli->query($query2);
  smsThanks($Fcandidates[$vote]);
//}

// Free resultset
//mysql_free_result($result2);
$result->close();

// Closing connection
$mysqli->close();
