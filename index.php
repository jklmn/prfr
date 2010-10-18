<title>prfr</title>
<style type="text/css" media="all">td{vertical-align:bottom;font-size:10px;}</style>
<meta http-equiv="refresh" content="600">

<?php
include("dibi.compact.php");
$db_config = array('driver'=>'mysql', 'host'=>'localhost', 'username'=>'sashe_20666', 'password'=>'Df2AnCvGBZCPGtrR', 'database'=>'sashe_20666', 'charset'=>'utf8');
dibi::connect($db_config);

$prfr=dibi::fetchAll("SELECT * FROM prfr ORDER BY datetime DESC LIMIT 1000");
$prfr=array_reverse ($prfr);
//print_r($prfr);
echo '<table cellspacing=0 cellpadding=0 style="background:url(http://static.sashe.cz/images/gbg.gif);height:300px;"><tr>';

foreach ($prfr as $sample){
     echo '<td>';
     echo '<img src="http://static.sashe.cz/images/black_50.png" width=1 height="'.round($sample['ms']/50).'" title="'.$sample['datetime'].' '.$sample['ms'].'ms">';
	 echo '<br>';
     echo '<img src="http://static.sashe.cz/images/black_88.png" width=1 height="'.round(($sample['mstotal']-$sample['ms'])/50).'" >';
     echo '</td>';
}
echo '</tr></table>';



define("DIVIDER",24);

unset($prfr);
$prfr1=dibi::fetchAll("SELECT * FROM prfr ORDER BY datetime DESC LIMIT 40000");
$prfr1=array_reverse ($prfr1);

echo '<table cellspacing=0 cellpadding=0 style="background:url(http://static.sashe.cz/images/gbg.gif);height:300px;"><tr>';

foreach ($prfr1 as $key=>$sample){
$mstotal=$mstotal+$sample['ms'];

$w++;
if ($w==DIVIDER){

     $prfr[$w2]['mstotal']=$mstotal;
     $prfr[$w2]['datetime']=$sample['datetime'];
     $w=$ms=$mstotal=0;
     $w2++;
}
}

foreach ($prfr as $sample){
     if ($sample['mstotal']/DIVIDER>3000)$sample['mstotal']=3000;
     echo '<td>';
     echo '<img src="http://static.sashe.cz/images/black_88.png" width=1 height="'.round($sample['mstotal']/20/DIVIDER).'" title="'.$sample['datetime'].' '.round($sample['mstotal']/DIVIDER).' ms">';
     echo '</td>';
}
echo '</tr></table>';


unset($prfr);
echo '<table cellspacing=0 cellpadding=0 style="background:url(http://static.sashe.cz/images/gbg.gif);height:300px;"><tr>';

foreach ($prfr1 as $key=>$sample){
$mstotal=$mstotal+$sample['mstotal']-$sample['ms'];

$w++;
if ($w==DIVIDER){

     $prfr[$w2]['mstotal']=$mstotal;
     $prfr[$w2]['datetime']=$sample['datetime'];
     $w=$ms=$mstotal=0;
     $w2++;
}
}

foreach ($prfr as $sample){
     echo '<td>';
     echo '<img src="http://static.sashe.cz/images/black_88.png" width=1 height="'.round($sample['mstotal']/50/DIVIDER).'" title="'.$sample['datetime'].' '.round($sample['mstotal']/DIVIDER).' ms">';
     echo '</td>';
}
echo '</tr></table>';


?>
