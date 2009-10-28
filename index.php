<?php
require_once('inc-config.php');
?>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="keywords" content="hilmappi,hilma,kartta,hankintailmoitus,julkinen,gemilo" />
<meta name="description" content="Hilmappi piirtää HILMA-hankintailmoitukset kartalle ja mahdollistaa hankintailmoitusten vaivattoman selailun." />
<title>Hilmappi</title>
<link rel="shortcut icon" href="fav.ico"/>
<script src="http://artoliukkonen.mysites.com/get_file/javascript/jquery-1-3-2-min.js" type="text/javascript"></script>
<script src="http://www.openlayers.org/api/OpenLayers.js" type="text/javascript"></script>
<script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js" type="text/javascript"></script>
<script src="http://artoliukkonen.mysites.com/get_file/javascript/thickbox-compressed.js" type="text/javascript" ></script>
<link href="style.css?v=1.0.5" rel="stylesheet" type="text/css" media="screen" />
<link href="front.css?v=1.0.5" rel="stylesheet" type="text/css" media="screen" />
<link href="thickbox.css?v=1.0.5" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript">
var CPV = [];
<?php
$defaultcpv = array();
if(!is_array($_SESSION['cpv'])) $_SESSION['cpv'] = array();
$defaultcpv = $_SESSION['cpv'];
if(!is_array($defaultcpv)) $defaultcpv = array();
foreach($defaultcpv AS $cpv) echo 'CPV.push('.$cpv.');';
?>
</script>
<script type="text/javascript" src="hilmappi.js?v=1.0.3"></script>

</head>
<body onLoad="init();">

<div id="mapContainer"></div>

&nbsp;


<div id="topbuttons">
    <div class="topbutton_wrap">
    	<div id="updateMarkers" class="topbutton" onClick="hideall()">Päivitä markkerit</div>
	    <div class="topbutton_right"></div>
    </div>
</div>


<div id="favs">
<h2>Suosikit</h2>
<div id="favs_container"></div>
</div>

<div id="settings">
<h2>Asetukset</h2>
<div id="settings_container"></div>
</div>

<div id="logobox">
</div>

<img id="cpvlist-ajax" src="ajax.gif" />

<div id="cpvlist" onMouseOver="$(this).css('opacity','1')" onMouseOut="$(this).css('opacity',0.5)">
<table id="cpvlist-table" style="opacity:0" cellpadding=0 cellspacing=0>
<tr><td id="cpv-timespan">
Aikarajaus: 
  <select id="date" onChange="hideall()">
    <option value="0">Kaikki</option>
    <option value="86400">Viimeisen vuorokauden aikana</option>
    <option value="604800">Viikon aikana</option>
    <option value="2678400">Kuukauden aikana</option>
  </select>
</td></tr>
<?php
$qry = mysql_query("SELECT * FROM cpv ORDER BY code");
while($c = mysql_fetch_object($qry)) {
  if(!is_int($c->code/1000000)) continue;
  $select = "";
  if(in_array($c->code,$defaultcpv)) $select = 'cpv_select';
  echo '<tr id="cpv_'.$c->code.'" onclick="selectCPV(\''.$c->code.'\')" class="'.$select.'"><td><a title="'.$c->name.'" class="cpv_noselect">'.$c->name.'</a><br/><em>('.$c->code.'-'.$c->check.')</em></td></tr>';
}
?>
</table>
</div>

<ul id="sideContainer"></ul>

</body>
</html>