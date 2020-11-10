<?php
require_once("mysql.php");

$numCandidates = 1;
// Run query to get candidate names
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

// Output results

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

?>


<table border=0 cellspacing=0 cellpadding=0 align="center">
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