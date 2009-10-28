<?php
require('/var/sql-connect/hilma.php');
require('inc-functions.php');
require('inc-cache.php');

$start = time();

for($i=$start;$i<=time();$i+=3600*24) {
  $url = 'http://www.hankintailmoitukset.fi/fi/notice/export?notice=DOMESTIC_CONTRACT&start='.date('Ymd',$i).'000001&end='.date('Ymd',$i).'235959';
  $data = file_get_contents($url);

  $xml = simplexml_load_string($data);
  $xml = $xml->WRAPPED_NOTICE;

  // Loop through notices
  foreach($xml AS $item) {
    $location = $item->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_AUTHORITY_INFORMATION->DOMESTIC_NAME_ADDRESSES;
    $hid = $item->ID;
    
    $mod = $item->MODIFIED;
    $month = (int)$mod->MONTH;
    $day = (int)$mod->DAY;
    $year = (int)$mod->YEAR;
    list($h,$m,$s) = explode(':',$mod->TIME);
    $modified = mktime($h,$m,$s,$month,$day,$year);
    
    $cpv = (int)$item->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_OBJECT_INFORMATION->CPV->CPV_MAIN->CPV_CODE['CODE'];
    
    $qry = mysql_query("SELECT id FROM notices WHERE hid='$hid'");
    if(mysql_num_rows($qry)==0) {
      $data = mysql_real_escape_string(json_encode($item));
      mysql_query("INSERT INTO notices SET cpv=$cpv, hid='$hid', location='".$location->TOWN."', added=$modified, data='".$data."'") or die(mysql_error().'<br /><pre>'.print_r($item,1));
      $id = mysql_insert_id();
    }
  }
}
?>