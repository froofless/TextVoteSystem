<?php
require_once("graphs.inc.php");
require_once("mysql.php");

$query = "SELECT value FROM textvote_setting WHERE setting = 'enabled'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$enabled = $row[0];

$query = "SELECT value FROM textvote_setting WHERE setting = 'timetoquit'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$timetoquit = (int) $row[0];
$timeleft = $timetoquit - time();
$timeleftmin = abs(floor($timeleft / 60));
$timeleftsec = $timeleft % 60;


$query = "SELECT value FROM textvote_setting WHERE setting = 'phonenumber'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$phone = $row[0];

$query = "SELECT value FROM textvote_setting WHERE setting = 'realphonenumber'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$realphone = $row[0];

$query = "SELECT value FROM textvote_setting WHERE setting = 'title'";
$result = $mysqli->query($query);
$row = $result->fetch_array(MYSQLI_NUM);
$title = $row[0];



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
    die("Error on retrieving candidates. Insert some candidates into the database to continue.");
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php print $title; ?> Voting Results!</title>
<style type="text/css">
.header {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 36px;
  color: #0000ff;
}
body {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 24px;
  color: #000000;
  font-style: none;
}

.vote {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 40px;
  color: #000000;
  font-style: none;
}

.disclaimer {
  font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 24px;
  color: #000000;
  font-style: none;
}

.credits {
  font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 22px;
  color: #000000;
  font-style: italic;

}

.time {
	font-family: Calibri, Verdana, Geneva, sans-serif;
  font-size: 30px;
  color: #000000;
  font-style: none;
}

</style>
</head>

<body>
<table border="0">
<tr>
<td width="10%"><img src="images/swe_logo.jpg" /></td>
<td width="80%"><div align="center" class="header"><strong><em><?php print $title; ?> </em><br /></strong></div></td>
<td width="10%"><img src="images/swe_logo.jpg" /></td>
</tr>
</table>
<div>&nbsp;</div>
<?php


//print "Time to quit: $timetoquit<br />";
//print "Time left to vote: $timeleft<br />";
//print "Time left min: $timeleftmin<br />";
//print "Time left sec: $timeleftsec<br />";

// If it's past time, update script to disable
if($timeleft <= 0) {
  $query = "UPDATE textvote_setting SET value='0' WHERE setting = 'enabled'";
  $result = $mysqli->query($query);
  if(!$result)
    print "ERROR on disable";
}

// Not enabled? Immediately send invalid response
if($enabled == "0") {
  print "<div align=\"center\" class=\"vote\"><em id=\"voting-message\">Voting system is currently closed.</em></div>";
}
else {
  print "<div align=\"center\" class=\"vote\"><em id=\"voting-message\">Text your votes (1-" . $numCandidates . ") to <strong>" . $phone . "</strong> " .
      "<span style=\"font-size:75%\"> / "  . $realphone . "</span></em></div>";
  print "<div>&nbsp;</div>";
  print "<div align=\"center\" class=\"time\">Time remaining: <strong id=\"timer\">" .$timeleftmin . ":";
  printf("%02s",$timeleftsec);
  print "</strong></div>\n";

  print "<div id=\"current-standings\">\n";
  print "<div><strong>Current standings:</strong></div>\n";
  print "<div>Total votes: $totalVotes\n";
  if($totalVotes >= 1)
  {
      print "<div>In the lead: ";
      $winner = 1;
      $tie = false;
      for($i = 2; $i <= $numCandidates; $i++)
      {
          if ($vote[$winner] == $vote[$i])
          {
             $winner = $i;
             $tie = true;
          }
          else if($vote[$winner] < $vote[$i])
          {
             $winner = $i;
             $tie = false;
          }
      }
      if($tie ==  true)
      {
        print "Multiple candidates tied";
      }
      else
      {
        print $candidates[$winner] . "</strong> with " . $vote[$winner] . " votes (";
        print (int) ($vote[$winner] / $totalVotes * 100);
        print "%)";

      }
      print "</div>\n";
  }
  print "</div><!-- #current-standings -->\n";


}

?>


<div>&nbsp;</div>

<?php // <br /><div align="center"><u>Current Rankings</u></div>  ?>
<br />
<div align="center">
<?php

// Build up labels for graph
/*
$valString = "";
$titleString = "title";

for($i = 1; $i <= $numCandidates; $i++) {
  $valString .= $vote[$i];
  $titleString .= "<img src=\"images/" . $images[$i] . "\" /><br />Text <strong>$i</strong> for<br />" . $candidates[$i];
  if($i != $numCandidates) {
    $valString .= ",";
    $titleString .= ",";
  }
}

$graph = new BAR_GRAPH("vBar");
$graph->values = $valString;

$graph->labels = $titleString;
$graph->labelFont = "Calibri, Arial Black, Arial, Helvetica";
$graph->labelSize = 20;
$graph->labelBGColor = "#eeeeee";
$graph->labelAlign = "center";
$graph->labelBorder = "2px #000000";
$graph->labelSpace = 10;

$graph->barWidth = 50;
$graph->barColors = "#990099";
$graph->showValues = 1;       //

$graph->absValuesBGColor = "#eeeeee";
$graph->absValuesspan = "Calibri, Arial Black, Arial, Helvetica";
$graph->absValuesSize = 20;
$graph->absValuesSuffix = " votes";
$graph->percValuesspan = "Calibri, Arial Black, Arial, Helvetica";
$graph->percValuesSize = 20;
echo $graph->create();
 */

?>

<table border=0 cellspacing=0 cellpadding=0>
  <tr>
  <td>
  <table border=0 cellspacing=2 cellpadding=0>
  <tr align=center valign=bottom>
  <td>
  <table border=0 cellspacing=0 cellpadding=0 width=100%>
  <tr>
  <td width=54 height=1></td>
  </tr>
  </table>
  </td>
<?php
for($i = 1; $i <= $numCandidates; $i++)
{
?>
      <td width=10>
      </td>
      <td>
      <table border=0 cellspacing=0 cellpadding=0 width=100%>
      <tr>
      <td width=54 height=1></td>
      </tr>
      </table>
      </td>
<?php
}
?>
  <td width=10>
  </td>
  </tr>
  <tr>

<?php
// Table cells for each candidate
for($i = 1; $i <= count($candidates); $i++)
{
  ?>
<td style="color:black;background-color:#eeeeee;border:2px #000000;font-family:Calibri, Arial Black, Arial, Helvetica;font-size:30px;text-align:center;">&nbsp;<?php

  print "<img src=\"images/" . $images[$i] . "\" /><br />Text <strong>";
  print $i . "</strong> for<br />";
  print $candidates[$i] . "&nbsp;</td>\n";
  print "<td width=\"10\"></td>\n";
}
?>
  </tr>
  </table>
  </td>
  </tr>
</table>
</div>




</div>
<div>&nbsp;</div>
<div align="center" class="disclaimer">Data rates may apply.</div>
<div>&nbsp;</div>
<div align="center" class="credits"><img src="images/twilio_logo.png" width="150" height="45" /><br />Developed by Alex Coates</div>
<script src="//cdnjs.cloudflare.com/ajax/libs/zepto/1.1.4/zepto.min.js"></script>
<script>
  Zepto(function($){
    var secondsLeft = <?php echo $timeleftmin * 60 + $timeleftsec; ?>,
             $timer = $('#timer'),
         $standings = $('#current-standings'),
           $message = $('#voting-message'),
        timeUp = secondsLeft <= 0;

    function updateTimer () {
      secondsLeft--;
      var minutes = Math.floor(secondsLeft / 60.0),
          seconds = secondsLeft % 60;
      $timer.text(minutes + ":" + ((seconds < 10 ) ? "0" : "") + seconds);
      if(secondsLeft <= 0) {
        clearInterval(timerInterval);
        $timer.hide();
        $message.text('Voting system is currently closed.');
        $standings.hide();
        $('.time').hide();
        timeUp = true;
      }
    }
    updateTimer();
    var timerInterval = setInterval(updateTimer, 1000);

    function updateStandings () {
      $.get('standings.php', function(html){
        $standings.html(html);
        if(! timeUp) {
          setTimeout(updateStandings, 2000);
        }
      });
    }
    updateStandings();

  });
</script>
</body>
</html>
