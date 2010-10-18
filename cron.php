<?php
$link = mysql_connect('localhost', 'sashe_20666', 'Df2AnCvGBZCPGtrR');
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
//echo 'Connected successfully';

$db_selected = mysql_select_db('sashe_20666', $link);
if (!$db_selected) {
    die ('Can\'t use driftteam : ' . mysql_error());
}


$URL="/?prfr";

$zcheck_start=microtime(true);
$lines = file('http://www.sashe.sk'.$URL);
$zcheck=round((microtime(true)-$zcheck_start)*1000);

$lastline=$lines[count($lines)-1];
echo htmlentities($lines[count($lines)-1]);

ereg ("(.*)ZCHECK OK ([0-9]*)ms (.*) ",$lastline,$regs);
print_r($regs);

echo $sql="INSERT INTO prfr (datetime,ms,mstotal) VALUES (NOW(),'".$regs[2]."','".$zcheck."')";
mysql_query($sql);



mysql_close($link);
?>
