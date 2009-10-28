<?php
require_once('inc-config.php');

$startstr = "";
$start = (int)$_POST['date'];
if($start>0) {
  $startstr = ' && added>'.(time()-$start);
}

$cpvs = explode(',',$_REQUEST['cpv']);
$r = array();
foreach($cpvs AS $cpv) {
  $cpv = (int)$cpv;
  if(!$cpv || !is_int($cpv)) continue;
  
  $r[] =$cpv;
}

if($_SESSION['id']) user_flag_set('default_cpv',json_encode($r));
$_SESSION['cpv'] = $r;

$return = array();
foreach($r AS $cpv) {
  if(false && $start==0 && $val = $cache->get('CPV_'.$cpv)) {
    $val = json_decode($val);
    foreach($val AS $i) {
      $item = $i->item;
      $i = (array)$i->i;
        if(applyPersonal($item,$i)) {
          $return[] = $i;
        }
    }
    continue;
  }
  
  $max = substr($cpv,0,2).'999999';
  $qry = mysql_query("SELECT * FROM notices WHERE cpv BETWEEN $cpv AND $max $startstr ORDER BY id DESC");
  
  $cachelist = array();
  while($item = mysql_fetch_object($qry)) {
    $data = json_decode($item->data);
    $location = $data->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_AUTHORITY_INFORMATION->DOMESTIC_NAME_ADDRESSES;
    
    $geo = getGeocode($location->TOWN.', Finland');
    if($geo['lat']==0) {
      $geo['lon'] = 19;
      $geo['lat'] = 62;
    }
    
    $rand = rand(-100,100);
    $rand = $rand/1000;
    $rand2 = rand(-100,100);
    $rand2 = $rand2/1000;
    
    $i = array();
    $i['id'] = $item->id;
    $i['lat'] = $geo['lat']+$rand;
    $i['lon'] = $geo['lon']+$rand2;
    $i['location'] = $location;
    $i['title'] = $data->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_AUTHORITY_INFORMATION->DOMESTIC_NAME_ADDRESSES->ORGANISATION;
    $i['desc'] = $data->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_OBJECT_INFORMATION->TITLE_CONTRACT;
    
    $i['uuid'] = $item->id;
    
    $i['date'] = $data->MODIFIED;
    
    $cachelist[] = array('item'=>$item,'i'=>$i);
    
    if($_SESSION['id'] && PAID) {
      if(!applyPersonal($item,$i)) continue;
    } else {
      $date = $data->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_CONTRACT_RELATING_CONDITIONS->RECEIPT_LIMIT_DATE;
      $close = mktime(24,0,0,$date->MONTH,$date->DAY,$date->YEAR);
      if(time()>$close) {continue;}
    }
    
    $return[] = $i;
  }
  
  $cache->set('CPV_'.$cpv,json_encode($cachelist),1800);
}


echo json_encode($return);

function applyPersonal($item,&$i) {
  $data = json_decode($item->data);
  $date = $data->DOMESTIC_CONTRACT->FD_DOMESTIC_CONTRACT->DOMESTIC_CONTRACT_RELATING_CONDITIONS->RECEIPT_LIMIT_DATE;
  $close = mktime(24,0,0,$date->MONTH,$date->DAY,$date->YEAR);
  if(time()>$close) {return false;}

  return true;
}
?>