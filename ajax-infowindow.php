<?php
require_once('inc-config.php');

$id = (int)$_GET['id'];

$qry = mysql_query("SELECT * FROM notices WHERE id=$id");
$obj = mysql_fetch_object($qry);
$data = json_decode($obj->data);
$o = $data->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT;

$date = $o->DOMESTIC_CONTRACT_RELATING_CONDITIONS->RECEIPT_LIMIT_DATE;
$location = $o->DOMESTIC_AUTHORITY_INFORMATION->DOMESTIC_NAME_ADDRESSES->TOWN;
$organization = $o->DOMESTIC_AUTHORITY_INFORMATION->DOMESTIC_NAME_ADDRESSES->ORGANISATION;
#$organization = $o->DOMESTIC_OBJECT_INFORMATION->TITLE_CONTRACT;
$title = $o->DOMESTIC_OBJECT_INFORMATION->TITLE_CONTRACT;

echo '<div id="infowindow">';

echo '<table>';
#echo '<tr><th>Hankinnan nimi</th><td>'.$o->DOMESTIC_OBJECT_INFORMATION->TITLE_CONTRACT.'</td></tr>';

echo '<tr><th>Paikkakunta</th><td>'.$location.'</td></tr>';

echo '<tr><th>Hankintayksikkö</th><td>'.$organization.'</td></tr>';

echo '<tr><th>Sulkeutuu</th><td>'.$date->DAY.'.'.$date->MONTH.'.'.$date->YEAR.' '.$date->TIME.'</td></tr>';

echo '<tr><th>Kuvaus</th><td>';
if(is_array($o->DOMESTIC_OBJECT_INFORMATION->SHORT_CONTRACT_DESCRIPTION->P))
  foreach($o->DOMESTIC_OBJECT_INFORMATION->SHORT_CONTRACT_DESCRIPTION->P AS $p) echo $p.' ';
else 
  echo $o->DOMESTIC_OBJECT_INFORMATION->SHORT_CONTRACT_DESCRIPTION->P;
echo '</td></tr>';

echo '<tr><th>Liitteet</th><td>';
echo '<ul>';
if(is_array($o->ATTACHMENTS->ATTACHMENT))
  foreach($o->ATTACHMENTS->ATTACHMENT AS $atc) 
    echo '<li><a href="'.$atc.'" target="_blank">'.$atc.'</a></li>';
else if($o->ATTACHMENTS->ATTACHMENT)
  echo '<li><a href="'.$o->ATTACHMENTS->ATTACHMENT.'" target="_blank">'.$o->ATTACHMENTS->ATTACHMENT.'</a></li>';
else 
echo 'Ei liitteitä';
echo '</ul>';
echo '</td></tr>';
echo '<tr><th></th><td>';
echo '<strong><a href="http://www.hankintailmoitukset.fi/fi/notice/view/'.$obj->hid.'" target="_blank">&raquo; Näytä ilmoitus HILMA:ssa</a></strong>';
echo '</td></tr>';

echo '</table>';



echo '</div>';


/*
echo '<p>&nbsp;</p><p>&nbsp;</p><hr />';

echo '<h2>Hirveetä debuggidataa jota et ehkä tarvitse</h2>';
echo '<pre>';
print_r($_GET);
print_r($data);
*/
?>