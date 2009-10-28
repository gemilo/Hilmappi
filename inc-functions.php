<?php
function user_flag_get($flag,$uid=false) {
  if(!$uid) $uid = $_SESSION['id'];
  
  $qry = mysql_query("SELECT value FROM users_flags WHERE uid=$uid && flag='$flag'") or die(mysql_error());
  if(mysql_num_rows($qry)==0) return false;
  return mysql_result($qry,0,0);
}

function user_flag_set($flag,$val,$uid=false) {
  if(!$uid) $uid = $_SESSION['id'];
  
  mysql_query("DELETE FROM users_flags WHERE uid=$uid && flag='$flag'");
  mysql_query("INSERT INTO users_flags SET uid=$uid, flag='$flag', value='$val'");
  return true;
}

function clickable_link($text) {
  $text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1:", $text);
  $ret = ' ' . $text;
  $ret = preg_replace("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $ret);
  $ret = substr($ret, 1);
  $ret = str_replace("\n",'<br />',$ret);
  return $ret;
}



function getGeocode($location) {
  $coords = array();
  $url = sprintf('http://maps.google.com/maps/geo?&q=%s&output=csv&key=%s',rawurlencode($location),GMAPS_APIKEY);
  
  $result = false;
                
  if($result = file_get_contents($url)) {
    $result_parts = explode(',',$result);
    if($result_parts[0] != 200)
      return false;
      
    $coords['lat'] = $result_parts[2];
    $coords['lon'] = $result_parts[3];
  }
  return $coords;
}
?>