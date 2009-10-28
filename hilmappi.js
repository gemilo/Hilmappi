var map = null;
var map_uuid = null;
var markers = null;
var spots = [];
var select_tool = null;
var add_tool = null;
var move_tool = null;
var selectedFeature = null;

var style_unread = {
  externalGraphic: 'images/blue-dot.png',
  graphicWidth : 20,
  graphicHeight : 34,
  graphicXOffset : -12,
  graphicYOffset : -34
};
            
            
function osm_get_tile_url(bounds) {
    var res = this.map.getResolution();
    var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
    var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
    var z = this.map.getZoom();
    var limit = Math.pow(2, z);

    if (y < 0 || y >= limit) {
        return OpenLayers.Util.getImagesLocation() + "404.png";
    } else {
        x = ((x % limit) + limit) % limit;
        return this.url + z + "/" + x + "/" + y + "." + this.type;

    }
}

function load_spots() {
	markers.destroyFeatures();
	var features = [];
	spots = [];
  cpvcodes = selectedCPV();
  var startdate = $('#date').val();
  
	$.post('ajax-dots.php',  { 'cpv' : cpvcodes, 'date' : startdate }, function(data) {
		var root_url = data.root_url;
		$.each(data, function(i,spot) {
      style = style_unread;
      if(spot.read) style = style_read;
      if(spot.fav) style = style_fav;
			var coords = new OpenLayers.LonLat(spot.lon, spot.lat);
			coords.transform(map.displayProjection, map.projection);
			var feature = new OpenLayers.Feature.Vector(
					new OpenLayers.Geometry.Point(coords.lon, coords.lat),
					{ name: spot.title, uuid: spot.uuid, id: spot.id, read: spot.read, fav: spot.fav, desc: spot.desc, location: spot.location},
					style);
			feature.lonlat = coords;
			features.push(feature);
			spots[spot.id] = feature;
		});
		markers.addFeatures(features);

    $("#cpvlist-table").css('opacity','1');
    $("#cpvlist-ajax").hide();
	},'json');
}

function on_popup_close(evt) {
    select_tool.unselect(selectedFeature);
}

function on_spot_select(feature) {
  var text = '<div class="spot_popup_info"><h3 style="">'+
              feature.data.location.TOWN+
              '</h3>'+
              ''+
              feature.data.name+
              '<br />'+
              feature.data.desc+
              '</div>'+
              '<div class="spot_popup_action"><a onclick="clickclick('+feature.data.id+')" class="infobtn btn">Näytä tiedot</a>'+
              '</div>';
  

	if (selectedFeature) select_tool.unselect(selectedFeature);
    selectedFeature = feature;
    popup = new OpenLayers.Popup.FramedCloud("chicken", 
                             feature.geometry.getBounds().getCenterLonLat(),
                             null, 
                             text,
                             null, true, on_popup_close);
    feature.popup = popup;
    map.addPopup(popup);
}

function on_spot_unselect(feature) {
    map.removePopup(feature.popup);
    feature.popup.destroy();
    feature.popup = null;
    selectedFeature = null;
}

function nullify() {
  map = null;
  map_uuid = null;
  markers = null;
  spots = {};
  select_tool = null;
  add_tool = null;
  move_tool = null;
  spotStyles = null;
  selectedFeature = null;
}

function init() {
  var center = new OpenLayers.LonLat(25.4745483398, 65.0107042125);
  init_map(center, 5);
}

function init_map(center, zoom_level) {
  $("#cpvlist-table").css('opacity','0');
  $("#cpvlist-ajax").show();
    
  hideall();

	zoomLevels = 18;
	attribution = 'Powered by<a href="http://www.openstreetmap.org/"><br />OpenStreetMap</a>';
	
	var options = {
        projection: new OpenLayers.Projection("EPSG:900913"),
        displayProjection: new OpenLayers.Projection("EPSG:4326"),
        units: "m",
        transitionEffect: 'resize',
        maxResolution: 156543.0339,
        numZoomLevels: zoomLevels,
        maxExtent: new OpenLayers.Bounds(-20037508.34, -20037508.34,
                                         20037508.34, 20037508.34)
	};

	map = new OpenLayers.Map('mapContainer', options);
	
	baseLayer = new OpenLayers.Layer.TMS(
        "OpenStreetMap (Mapnik)",
        "http://b.tile.openstreetmap.org/",
        {
            type: 'png', getURL: osm_get_tile_url,
            displayOutsideMaxExtent: false,
            attribution: attribution
        }
	);
	
	map.addLayer(baseLayer);
	
	markers = new OpenLayers.Layer.Vector("Markers");
	map.addLayer(markers);

	center.transform(map.displayProjection, map.projection);
	map.setCenter(center, zoom_level);
  
  load_spots();
	
	select_tool = new OpenLayers.Control.SelectFeature(markers,
            {onSelect: on_spot_select, onUnselect: on_spot_unselect});
  
	map.addControl(select_tool);
	select_tool.activate();
}

function select_spot(uuid) {
	feature = spots[uuid];
	coords = feature.lonlat.clone();
	map.panTo(coords);
	on_spot_select(feature);
}

function selectedCPV() {
  var str = "";
  $(CPV).each(function(val) {
    str+=this+',';
  });
  return str;
}

function removeThickBoxEvents() {
  $('.thickbox').each(function(i) {
    $(this).unbind('click');
  });
}

function bindThickBoxEvents() {
  removeThickBoxEvents();
  tb_init('a.thickbox, area.thickbox, input.thickbox');
}

function clickclick(id) {
  tb_show('Hankintailmoitus','ajax-infowindow.php?id='+id+'&height=420&width=600');
	return false;
}

function hideall() {
  $("#cpvlist-table").css('opacity','0');
  $("#cpvlist-ajax").show();

  if(map) {
    if(selectedFeature) {
      select_tool.unselect(selectedFeature);
    }
    load_spots();
  }
}

function selectCPV(code) {
  var key = false;
  if(!(key = array_search(code,CPV))) {
    CPV.push(code);
    $('#cpv_'+code).addClass('cpv_select');
  } else {
    CPV[key] = false;
    $('#cpv_'+code).removeClass('cpv_select');
  }
  hideall();
}

function array_search (needle, haystack, argStrict) {
  var strict = !!argStrict;
  var key = '';
  for (key in haystack) {
    if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
      return key;
    }
  }
  return false;
}