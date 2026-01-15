<?php
/*------------------------------------------------------------------------
# com_zhgooglemap - Zh GoogleMap
# ------------------------------------------------------------------------
# author:    Dmitry Zhuk
# copyright: Copyright (C) 2011 zhuk.cc. All Rights Reserved.
# license:   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
# website:   http://zhuk.cc
# Technical Support Forum: http://forum.zhuk.cc/
-------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;


$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
   ->useScript('form.validate');

$imgpath = URI::root() .'components/com_zhgooglemap/assets/icons/';
$utilspath = URI::root() .'administrator/components/com_zhgooglemap/assets/utils/';
 
$document = Factory::getDocument();

// Map Types
$maptypes = $this->mapTypeList;

$urlProtocol = "https";
if ($this->httpsprotocol != "")
{
    if ((int)$this->httpsprotocol == 1)
    {
        $urlProtocol = 'http';
    }
}

if ($this->map_height != "")
{
    if ((int)$this->map_height == 0)
    {
        $map_height = "420px";
        $map_height_wrap = "450px";
    }
    else
    {
        $map_height = ((int)$this->map_height - 30) . "px";
        $map_height_wrap = (int)$this->map_height . "px";
    }
}
else 
{
    $map_height = "420px";
    $map_height_wrap = "450px";
}


$mainScriptBegin = $urlProtocol.'://maps.googleapis.com/maps/api/js?';

$mainScriptMiddle = "callback=Function.prototype";

$mainScriptEnd = "";

if ($this->mapapiversion != "")
{
    if ($mainScriptMiddle == "")
    {
        $mainScriptMiddle = 'v='.$this->mapapiversion;
    }
    else
    {
        $mainScriptMiddle .= '&v='.$this->mapapiversion;
    }

}

if ($this->mapapikey4map != "")
{
    if ($mainScriptMiddle == "")
    {
        $mainScriptMiddle = 'key='.$this->mapapikey4map;
    }
    else
    {
        $mainScriptMiddle .= '&key='.$this->mapapikey4map;
    }        

}

$mainScriptBegin .= $mainScriptMiddle;


$mainScriptLibrary ="";

if (1==1)
{
    if ($mainScriptLibrary == "")
    {
        $mainScriptLibrary .= '&libraries=drawing';
    }
    else
    {
        $mainScriptLibrary .= ',drawing';
    }
}


if (1==2)
{
    if ($mainScriptLibrary == "")
    {
        $mainScriptLibrary .= '&libraries=places';
    }
    else
    {
        $mainScriptLibrary .= ',places';
    }
}

$mainScriptBegin .= $mainScriptLibrary;

$mainScript = $mainScriptBegin . $mainScriptEnd;

$wa->registerAndUseScript('zhgooglemaps.mappath.script', $mainScript);
      
if ((int) $this->item->id != 0)
{
    $flg_show_map = false;
}
else
{
    $flg_show_map = true;
}
      
?>
<form action="<?php echo Route::_('index.php?option=com_zhgooglemap&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

<div class="main-card">

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'pathmain', 'recall' => true, 'breakpoint' => 768]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'pathmain', Text::_('COM_ZHGOOGLEMAP_MAPPATH_PATH')); ?>
    <div class="row" id="tab0">
        <div>
            <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('pathmain') as $field): 
                    ?>                   
                    <div class="control-group">
                    <?php 
                        ?>
                        <div class="control-label">
                        <?php 
                            echo $field->label;
                        ?>
                        </div>
                        <div class="controls">
                        <?php 
                            echo $field->input;
                        ?>
                        </div>
                        <?php 
                    ?>
                    </div>

                <?php endforeach; ?>
            
            </fieldset>
        
            <div>
                    <div>
                    <?php if (!$flg_show_map) { ?>
                    <button id="zhgm-display-map" class="btn btn-primary" type="button" onclick="document.getElementById('mapDivWrapper').style.display = 'block';initializeMap();document.getElementById('zhgm-display-map').style.display = 'none';"><?php echo Text::_('COM_ZHGOOGLEMAP_MAP_SHOW_MAP_BUTTON'); ?></button>
                    <?php } ?>
                    </div>
                    
                    <div id="mapDivWrapper" style="margin:0;padding:0;width:100%;height:<?php echo $map_height_wrap ?>">

                    <div id="GMapsID" style="margin:0;padding:0;width:100%;height:<?php echo $map_height ?>">
             
                    <?php 

                    $wa->registerAndUseStyle('zhgooglemaps.mappath.style', URI::root() .'administrator/components/com_zhgooglemap/assets/css/admin.css');
                    
                    $mapDefLat = $this->mapDefLat;
                    $mapDefLng = $this->mapDefLng;


                    $mapMapTypeGoogle = $this->mapMapTypeGoogle;
                    $mapMapTypeOSM = $this->mapMapTypeOSM;
                    $mapMapTypeCustom = $this->mapMapTypeCustom;


                    //Script begin
                    $scripttext = '<script type="text/javascript" >//<![CDATA[' ."\n";

                        $scripttext .= 'var initialLocation;' ."\n";
                        $scripttext .= 'var spblocation;' ."\n";
                        $scripttext .= 'var browserSupportFlag =  new Boolean();' ."\n";
                        $scripttext .= 'var map;' ."\n";
                        $scripttext .= 'var infowindow;' ."\n";
                        $scripttext .= 'var marker;' ."\n";

                        $scripttext .= 'function initializeMap() {' ."\n";

                        $scripttext .= 'infowindow = new google.maps.InfoWindow();' ."\n";
                        
                        if ($mapDefLat != "" && $mapDefLng !="")
                        {
                            $scripttext .= 'spblocation = new google.maps.LatLng('.$mapDefLat.', '.$mapDefLng.');' ."\n";
                        }
                        else
                        {
                            $scripttext .= 'spblocation = new google.maps.LatLng(59.9388, 30.3158);' ."\n";
                        }

                        $scripttext .= 'var myOptions = {' ."\n";
                        $scripttext .= '    zoom: 14,' ."\n";
                        $scripttext .= '    mapTypeId: google.maps.MapTypeId.ROADMAP,' ."\n";
                        $scripttext .= '      panControl: true,' ."\n";
                        $scripttext .= '      scrollwheel: false,' ."\n";
                        $scripttext .= '      disableDoubleClickZoom: true,' ."\n";
                        $scripttext .= '      zoomControl: true,' ."\n";
                        $scripttext .= '      zoomControlOptions: {' ."\n";
                        $scripttext .= '            position: google.maps.ControlPosition.LEFT_TOP,' ."\n";
                        $scripttext .= '            style: google.maps.ZoomControlStyle.DEFAULT' ."\n";
                        $scripttext .= '          },' ."\n";
                        $scripttext .= '      mapTypeControl: true,' ."\n";
                        $scripttext .= '      mapTypeControlOptions: {' ."\n";
                        $scripttext .= '      mapTypeIds: [' ."\n";

                        $curr_maptype_list = '';
                        
                        if ((int)$mapMapTypeGoogle != 0
                          || ((int)$mapMapTypeCustom == 0
                             && (int)$mapMapTypeOSM == 0))
                        {
                            $curr_maptype_list .= '      google.maps.MapTypeId.ROADMAP,' ."\n";
                            $curr_maptype_list .= '      google.maps.MapTypeId.TERRAIN,' ."\n";
                            $curr_maptype_list .= '      google.maps.MapTypeId.SATELLITE,' ."\n";
                            $curr_maptype_list .= '      google.maps.MapTypeId.HYBRID' ."\n";
                        }

                        if ((int)$mapMapTypeOSM != 0)
                        {
                            if ($curr_maptype_list == '')
                            {
                                $curr_maptype_list .= '     \'osm\'' ."\n";
                            }
                            else
                            {
                                $curr_maptype_list .= '     ,\'osm\'' ."\n";
                            }
                        }
                        
                        if ((int)$mapMapTypeCustom != 0)
                        {
                            // Custom Map Type - part 0 (add to list) - begin
                            foreach ($maptypes as $key => $currentmaptype) 
                            {
                                if ($currentmaptype->gettileurl != "")
                                {
                                    if ($curr_maptype_list == '')
                                    {
                                        $curr_maptype_list .= '      \'customMapType'.$currentmaptype->id.'\'' ."\n";
                                    }
                                    else
                                    {
                                        $curr_maptype_list .= '      ,\'customMapType'.$currentmaptype->id.'\'' ."\n";
                                    }
                                }
                            }
                            // Custom Map Type - part 0 (add to list) - end
                        }

                        $scripttext .= $curr_maptype_list;

                        $scripttext .= '    ]' ."\n";
                        $scripttext .= '      },' ."\n";
                        $scripttext .= '      scaleControl: true,' ."\n";
                        $scripttext .= '      streetViewControl: true,' ."\n";
                        $scripttext .= '      rotateControl: true,' ."\n";
                        $scripttext .= '      overviewMapControl: true' ."\n";

                        $scripttext .= '  };' ."\n";
                            
                        if ((int)$mapMapTypeOSM != 0)
                        {
                            $scripttext .= ' var openStreetType = new google.maps.ImageMapType({' ."\n";
                            $scripttext .= '  getTileUrl: function(ll, z) {' ."\n";
                            $scripttext .= '    var X = ll.x % (1 << z);  // wrap' ."\n";
                            $scripttext .= '    return "'.$urlProtocol.'://tile.openstreetmap.org/" + z + "/" + X + "/" + ll.y + ".png";' ."\n";
                            $scripttext .= '  },' ."\n";
                            $scripttext .= '  tileSize: new google.maps.Size(256, 256),' ."\n";
                            $scripttext .= '  isPng: true,' ."\n";
                            $scripttext .= '  maxZoom: 18,' ."\n";
                            $scripttext .= '  name: "OSM",' ."\n";
                            $scripttext .= '  alt: "'.Text::_('COM_ZHGOOGLEMAP_MAP_OPENSTREETLAYER').'"' ."\n";
                            $scripttext .= '}); ' ."\n";
                        }

                        if ((int)$mapMapTypeCustom != 0)
                        {
                            // Custom Map Type - part 1 (define) - begin
                            foreach ($maptypes as $key => $currentmaptype)     
                            {
                            
                                if ($currentmaptype->gettileurl != "")
                                {

                                    $scripttext .= ' var customMapType'.$currentmaptype->id.' = new google.maps.ImageMapType({' ."\n";
                                    $scripttext .= '  getTileUrl: '.$currentmaptype->gettileurl.',' ."\n";
                                    $scripttext .= '  tileSize: new google.maps.Size('.$currentmaptype->tilewidth.', '.$currentmaptype->tileheight.'),' ."\n";
                                    if ((int)$currentmaptype->ispng == 1)
                                    {
                                        $scripttext .= '  isPng: true,' ."\n";
                                    }
                                    else
                                    {
                                        $scripttext .= '  isPng: false,' ."\n";
                                    }
                                    if ((int)$currentmaptype->minzoom != 0)
                                    {
                                        $scripttext .= '  minZoom: '.(int)$currentmaptype->minzoom.',' ."\n";
                                    }
                                    if ((int)$currentmaptype->maxzoom != 0)
                                    {
                                        $scripttext .= '  maxZoom: '.(int)$currentmaptype->maxzoom.',' ."\n";
                                    }
                                    if ($currentmaptype->opacity != "")
                                    {
                                        $scripttext .= '  opacity: '.$currentmaptype->opacity.','."\n";
                                    }
                                    $scripttext .= '  name: "'.str_replace('"','', $currentmaptype->title).'",' ."\n";
                                    $scripttext .= '  alt: "'.str_replace('"','', $currentmaptype->description).'",' ."\n";
                                    $scripttext .= '}); ' ."\n";
                                    
                                    // Add projection
                                    if ($currentmaptype->fromlatlngtopoint != "" && $currentmaptype->frompointtolatlng != "")
                                    {
                                        $scripttext .= $currentmaptype->projectionglobal."\n";
                                        
                                        $scripttext .= ' function customMapTypeProjection'.$currentmaptype->id.'() {'."\n";
                                        $scripttext .= $currentmaptype->projectiondefinition."\n";
                                        $scripttext .= ' }'."\n";
                                        
                                        $scripttext .= ' customMapTypeProjection'.$currentmaptype->id.'.prototype.fromLatLngToPoint  = ';
                                        $scripttext .= $currentmaptype->fromlatlngtopoint."\n";
                                        $scripttext .= ';'."\n";

                                        $scripttext .= ' customMapTypeProjection'.$currentmaptype->id.'.prototype.fromPointToLatLng = ';
                                        $scripttext .= $currentmaptype->frompointtolatlng."\n";
                                        $scripttext .= ';'."\n";
                                        
                                        $scripttext .= ' customMapType'.$currentmaptype->id.'.projection  = new customMapTypeProjection'.$currentmaptype->id.'();' ."\n";
                                    }

                                }
                            
                            }
                            // Custom Map Type - part 1 (define) - end
                        }
                        
                        $scripttext .= '  map = new google.maps.Map(document.getElementById("GMapsID"), myOptions);' ."\n";

                        if ((int)$mapMapTypeOSM != 0)
                        {
                            $scripttext .= ' map.mapTypes.set(\'osm\', openStreetType);' ."\n";
                            if (((int)$mapMapTypeOSM == 2)
                             || ((int)$mapMapTypeGoogle == 0
                                && (int)$mapMapTypeCustom == 0))
                            {
                                $scripttext .= '    map.setMapTypeId(\'osm\');' ."\n";
                            }
                        }

                        if ((int)$mapMapTypeCustom != 0)
                        {
                            // Custom Map Type - part 2 (bind) - begin
                            foreach ($maptypes as $key => $currentmaptype)     
                            {
                                if ($currentmaptype->gettileurl != "")
                                {
                                    $scripttext .= ' map.mapTypes.set(\'customMapType'.$currentmaptype->id.'\', customMapType'.$currentmaptype->id.');' ."\n";    
                                    if (((int)$mapMapTypeCustom == 2)
                                        || ((int)$mapMapTypeGoogle == 0
                                            && (int)$mapMapTypeOSM == 0))
                                    {
                                        $scripttext .= '    map.setMapTypeId(\'customMapType'.$currentmaptype->id.'\', customMapType'.$currentmaptype->id.');' ."\n";
                                    }
                                }
                            }
                            // Custom Map Type - part 2 (bind) - end
                        }
                        
                        $scripttext .= 'var selectedShape; ' ."\n";
                        
                        $scripttext .= '  function clearSelection() {' ."\n";
                        $scripttext .= '    if (selectedShape) {' ."\n";
                        $scripttext .= '      selectedShape.setEditable(false);' ."\n";
                        $scripttext .= '      selectedShape = null;' ."\n";
                        $scripttext .= '    }' ."\n";
                        $scripttext .= '  }' ."\n";

                        $scripttext .= '  function setSelection(shape) {' ."\n";
                        $scripttext .= '    clearSelection();' ."\n";
                        $scripttext .= '    selectedShape = shape;' ."\n";
                        $scripttext .= '    shape.setEditable(true);' ."\n";
                        $scripttext .= '  }' ."\n";

                        $scripttext .= ' function deleteSelectedShape() {' ."\n";
                        $scripttext .= '    if (selectedShape) {' ."\n";
                        $scripttext .= '      selectedShape.setMap(null);' ."\n";
                        $scripttext .= '    }' ."\n";
                        $scripttext .= '  }' ."\n";

                        $scripttext .= '  function getSelectionPathChanged() {' ."\n";
                        $scripttext .= '    if (typeof(plPath'. $this->item->id.') != "undefined")'."\n";
                        $scripttext .= '    {' ."\n";
                        $scripttext .= '      getSelectionPath(plPath'. $this->item->id.');' ."\n";
                        $scripttext .= '    }' ."\n";
                         
                        $scripttext .= '    if (selectedShape) {' ."\n";
                        $scripttext .= '      getSelectionPath(selectedShape);' ."\n";
                        $scripttext .= '      clearSelection();' ."\n";
                        $scripttext .= '    }' ."\n";
                        $scripttext .= '  }' ."\n";

                        $scripttext .= '  function getSelectionPath(shape) {' ."\n";
                        $scripttext .= '    var coords = [];' ."\n";
                        $scripttext .= '    if (shape) {' ."\n";

                        $scripttext .= '        shape.getPath().forEach(function(position){ ' ."\n";
                        $scripttext .= '          coords.push(position.toUrlValue());' ."\n";
                        $scripttext .= '        });' ."\n";

                        //$scripttext .= '        alert("shape: " + coords.join(" | "));' ."\n";
                        // $scripttext .= '      document.forms.adminForm.jform_helpitem.value = coords.join(";");' ."\n";
                        $scripttext .= '      document.forms.adminForm.jform_path.value = coords.join(";");' ."\n";
                        $scripttext .= '      ' ."\n";
                        $scripttext .= '    }' ."\n";
                        $scripttext .= '  }' ."\n";
                        
                        if ((int)$this->item->id == 0
                         || $this->item->path == "")
                        {
                            $scripttext .= 'var drawingManager = new google.maps.drawing.DrawingManager({' ."\n";
                            $scripttext .= '          drawingMode: google.maps.drawing.OverlayType.POLYLINE,' ."\n";
                            $scripttext .= '          drawingControl: true,' ."\n";
                            $scripttext .= '          drawingControlOptions: {' ."\n";
                            $scripttext .= '            position: google.maps.ControlPosition.TOP_CENTER,' ."\n";
                            $scripttext .= '            drawingModes: [' ."\n";
                            $scripttext .= '              google.maps.drawing.OverlayType.POLYLINE' ."\n";
                            $scripttext .= '              ],' ."\n";
                            $scripttext .= '            polylineOptions: {' ."\n";
                            $scripttext .= '               editable: true ' ."\n";
                            $scripttext .= '               }' ."\n";
                            $scripttext .= '          }' ."\n";
                            $scripttext .= '        });' ."\n";
                            $scripttext .= '        drawingManager.setMap(map);' ."\n";

                            $scripttext .= '      google.maps.event.addListener(drawingManager, \'overlaycomplete\', function(e) {' ."\n";
                            $scripttext .= '          if (e.type != google.maps.drawing.OverlayType.MARKER) {' ."\n";
                            // Switch back to non-drawing mode after drawing a shape.
                            $scripttext .= '          drawingManager.setDrawingMode(null);' ."\n";

                            // Add an event listener that selects the newly-drawn shape when the user
                            // mouses down on it.
                            $scripttext .= '          var newShape = e.overlay;' ."\n";
                            $scripttext .= '          newShape.type = e.type;' ."\n";
                            $scripttext .= '          google.maps.event.addListener(newShape, \'click\', function() {' ."\n";
                            $scripttext .= '            setSelection(newShape);' ."\n";
                            $scripttext .= '          });' ."\n";
                            $scripttext .= '          google.maps.event.addListener(newShape, \'dblclick\', function() {' ."\n";
                            $scripttext .= '            getSelectionPath(newShape);' ."\n";
                            $scripttext .= '          });' ."\n";
                            
                            $scripttext .= '          setSelection(newShape);' ."\n";
                            $scripttext .= '          getSelectionPath(newShape);' ."\n";
                            
                            $scripttext .= '        }' ."\n";
                            $scripttext .= '      });' ."\n";
                        }

                        // Clear the current selection when the drawing mode is changed, or when the
                        // map is clicked.
                        $scripttext .= '      google.maps.event.addListener(map, \'click\', getSelectionPathChanged);' ."\n";
                        
                        $scripttext .= 'initialLocation = spblocation;' ."\n";
                            $scripttext .= '    map.setCenter(initialLocation);' ."\n";
                            
                            // New version without marker
                            $scripttext .= '  marker = new google.maps.Marker({' ."\n";
                            $scripttext .= '      position: initialLocation, ' ."\n";
                            $scripttext .= '      draggable:true, ' ."\n";
                            $scripttext .= '      map: map, ' ."\n";
                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                            // Replace to new, because all charters are shown
                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";        
                            $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
                            $scripttext .= '});'."\n";

                            $scripttext .= '    google.maps.event.addListener(marker, \'drag\', function(event) {' ."\n";
                            $scripttext .= '    document.forms.adminForm.jform_helpitem.value = event.latLng.lat() +","+event.latLng.lng();' ."\n";
                            $scripttext .= '    });' ."\n";
                            
                            $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                            $scripttext .= '    marker.setPosition(event.latLng);' ."\n";
                            $scripttext .= '    document.forms.adminForm.jform_helpitem.value = event.latLng.lat() +","+event.latLng.lng();' ."\n";
                            $scripttext .= '    });' ."\n";

                            
                        if ((int)$this->item->id == 0)        
                        {
                                    $scripttext .= '      // Try W3C Geolocation method (Preferred)' ."\n";
                                    $scripttext .= '      if(navigator.geolocation) {' ."\n";
                                    $scripttext .= '        browserSupportFlag = true;' ."\n";
                                    $scripttext .= '        navigator.geolocation.getCurrentPosition(function(position) {' ."\n";
                                    $scripttext .= '          initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);' ."\n";
                                $scripttext .= '    map.setCenter(initialLocation);' ."\n";
                            $scripttext .= '    marker.setPosition(initialLocation);' ."\n";
                                    //$scripttext .= '          contentString = "Location found using W3C standard";' ."\n";
                                    //$scripttext .= '          infowindow.setContent(contentString);' ."\n";
                                    //$scripttext .= '          infowindow.setPosition(initialLocation);' ."\n";
                                    //$scripttext .= '          infowindow.open(map);' ."\n";
                                    $scripttext .= '        }, function() {' ."\n";
                                    $scripttext .= '          handleNoGeolocation(browserSupportFlag);' ."\n";
                                    $scripttext .= '        });' ."\n";
                                    $scripttext .= '      } else if (google.gears) {' ."\n";
                                    $scripttext .= '        // Try Google Gears Geolocation' ."\n";
                                    $scripttext .= '        browserSupportFlag = true;' ."\n";
                                    $scripttext .= '        var geo = google.gears.factory.create(\'beta.geolocation\');' ."\n";
                                    $scripttext .= '        geo.getCurrentPosition(function(position) {' ."\n";
                                    $scripttext .= '          initialLocation = new google.maps.LatLng(position.latitude,position.longitude);' ."\n";
                                $scripttext .= '    map.setCenter(initialLocation);' ."\n";
                            $scripttext .= '    marker.setPosition(initialLocation);' ."\n";
                                    //$scripttext .= '          contentString = "Location found using Google Gears";' ."\n";
                                    //$scripttext .= '          infowindow.setContent(contentString);' ."\n";
                                    //$scripttext .= '          infowindow.setPosition(initialLocation);' ."\n";
                                    //$scripttext .= '          infowindow.open(map);' ."\n";
                                    $scripttext .= '        }, function() {' ."\n";
                                    $scripttext .= '          handleNoGeolocation(browserSupportFlag);' ."\n";
                                    $scripttext .= '        });' ."\n";
                                    $scripttext .= '      } else {' ."\n";
                                    $scripttext .= '        // Browser doesn\'t support Geolocation' ."\n";
                                    $scripttext .= '        browserSupportFlag = false;' ."\n";
                                    $scripttext .= '        handleNoGeolocation(browserSupportFlag);' ."\n";
                                    $scripttext .= '      }' ."\n";
                        }
                        else
                        {
                            if ($this->item->path != "")
                            {
                                $current_path_path = str_replace(array("\r", "\r\n", "\n"), '', $this->item->path);
                                if ((int)$this->item->objecttype == 0
                                 || (int)$this->item->objecttype == 1) 
                                {
                                    
                                    $scripttext .= ' var allCoordinates = [ '."\n";
                                    $scripttext .=' new google.maps.LatLng('.str_replace(";","), new google.maps.LatLng(", $current_path_path).') '."\n";
                                    $scripttext .= ' ]; '."\n";

                                    
                                    if ((int)$this->item->objecttype == 0) 
                                    {
                                        $scripttext .= ' var plPath'. $this->item->id.' = new google.maps.Polyline({'."\n";
                                    }
                                    else
                                    {
                                        $scripttext .= ' var plPath'. $this->item->id.' = new google.maps.Polygon({'."\n";
                                    }

                                    $scripttext .= ' path: allCoordinates,'."\n";

                                    if (isset($this->item->geodesic) && (int)$this->item->geodesic == 1) 
                                    {
                                        $scripttext .= ' geodesic: true, '."\n";
                                    }
                                    else
                                    {
                                        $scripttext .= ' geodesic: false, '."\n";
                                    }

                                    $scripttext .= ' editable: true, '."\n";
                                    
                                    $scripttext .= ' strokeColor: "'.$this->item->color.'"'."\n";
                                    $scripttext .= ',strokeOpacity: '.$this->item->opacity."\n";
                                    $scripttext .= ',strokeWeight: '.$this->item->weight."\n";
                                    if ((int)$this->item->objecttype == 1) 
                                    {
                                        if ($this->item->fillcolor != "")
                                        {
                                            $scripttext .= ',fillColor: "'.$this->item->fillcolor.'"'."\n";
                                        }
                                        if ($this->item->fillopacity != "")
                                        {
                                            $scripttext .= ',fillOpacity: '.$this->item->fillopacity."\n";
                                        }
                                    }

                                    $scripttext .= ' });'."\n";

                                    
                                    $scripttext .= 'plPath'. $this->item->id.'.setMap(map);'."\n";

                                }
                                else if ((int)$this->item->objecttype == 2)
                                {
                                    if ($this->item->radius != "")
                                    {
                                        $arrayPathCoords = explode(';', $current_path_path);
                                        $arrayPathIndex = 0;
                                        foreach ($arrayPathCoords as $currentpathcoordinates) 
                                        {
                                            $arrayPathIndex += 1;
                                            $scripttext .= ' var plPath'.$arrayPathIndex.'_'. $this->item->id.' = new google.maps.Circle({'."\n";
                                            $scripttext .= ' center: new google.maps.LatLng('.$currentpathcoordinates.')'."\n";
                                            $scripttext .= ',radius: '.$this->item->radius."\n";
                                            $scripttext .= ',strokeColor: "'.$this->item->color.'"'."\n";
                                            $scripttext .= ',strokeOpacity: '.$this->item->opacity."\n";
                                            $scripttext .= ',strokeWeight: '.$this->item->weight."\n";
                                            if ($this->item->fillcolor != "")
                                            {
                                                $scripttext .= ',fillColor: "'.$this->item->fillcolor.'"'."\n";
                                            }
                                            if ($this->item->fillopacity != "")
                                            {
                                                $scripttext .= ',fillOpacity: '.$this->item->fillopacity."\n";
                                            }
                                            $scripttext .= '  });' ."\n";
                                            $scripttext .= 'plPath'.$arrayPathIndex.'_'. $this->item->id.'.setMap(map);'."\n";
                                        }

                                    }
                                }
                                else
                                {
                                }
                                
                                $arrayPC = explode(';', $current_path_path);
                                $coordsPCxy = 0;
                                foreach ($arrayPC as $currentpathcoordinates) 
                                {    
                                    $coordsPC = explode(',', $currentpathcoordinates);
                                    if ($coordsPCxy == 0)
                                    {
                                        $coordsPCxMin = $coordsPC[0];
                                        $coordsPCxMax = $coordsPC[0];
                                        $coordsPCyMin = $coordsPC[1];
                                        $coordsPCyMax = $coordsPC[1];
                                        $coordsPCxy = 1;
                                    }
                                    else
                                    {
                                        if (isset($coordsPC[0]) && isset($coordsPC[1]))
                                        {
                                            if ($coordsPC[0] < $coordsPCxMin)
                                            {
                                                $coordsPCxMin = $coordsPC[0];
                                            }
                                            if ($coordsPC[1] < $coordsPCyMin)
                                            {
                                                $coordsPCyMin = $coordsPC[1];
                                            }
                                            if ($coordsPC[0] > $coordsPCxMax)
                                            {
                                                $coordsPCxMax = $coordsPC[0];
                                            }
                                            if ($coordsPC[1] > $coordsPCyMax)
                                            {
                                                $coordsPCyMax = $coordsPC[1];
                                            }
                                        }
                                    }
                                    
                                }

                                if ($coordsPCxy == 1)
                                {
                                    $scripttext .= 'var mapBounds = new google.maps.LatLngBounds(' ."\n";
                                    $scripttext .= '  new google.maps.LatLng('.$coordsPCxMin.', '.$coordsPCyMin.'),' ."\n";
                                    $scripttext .= '  new google.maps.LatLng('.$coordsPCxMax.', '.$coordsPCyMax.'));' ."\n";
                                    //$scripttext .=' var mapCenter = new google.maps.LatLng('.(($coordsPCxMin + $coordsPCxMax)/2).','.(($coordsPCyMin + $coordsPCyMax)/2).');'."\n";
                                    $scripttext .=' map.fitBounds(mapBounds);'."\n";
                                    //$scripttext .=' map.setCenter(mapCenter);'."\n";
                                }
                                
                            }
                        }

                    // end initializeMap    
                    $scripttext .= '};' ."\n";

                    $scripttext .= 'function handleNoGeolocation(errorFlag) {' ."\n";
                    $scripttext .= '  if (errorFlag == true) {' ."\n";
                    $scripttext .= '    initialLocation = spblocation;' ."\n";
                    //$scripttext .= '    contentString = "Error: The Geolocation service failed.";' ."\n";
                    $scripttext .= '  } else {' ."\n";
                    $scripttext .= '    initialLocation = spblocation;' ."\n";
                    //$scripttext .= '    contentString = "Error: Your browser doesn\'t support geolocation.";' ."\n";
                    $scripttext .= '  }' ."\n";
                    $scripttext .= '  map.setCenter(initialLocation);' ."\n";
                    $scripttext .= '  marker.setPosition(initialLocation);' ."\n";
                    //$scripttext .= '  infowindow.setContent(contentString);' ."\n";
                    //$scripttext .= '  infowindow.setPosition(initialLocation);' ."\n";
                    //$scripttext .= '  infowindow.open(map);' ."\n";
                    $scripttext .= '};' ."\n";

                    $scripttext .= ' function addLoadEvent(func) {' ."\n";
                    $scripttext .= '  var oldonload = window.onload;' ."\n";
                    $scripttext .= '  if (typeof window.onload != \'function\') {' ."\n";
                    $scripttext .= '    window.onload = func;' ."\n";
                    $scripttext .= '  } else {' ."\n";
                    $scripttext .= '    window.onload = function() {' ."\n";
                    $scripttext .= '      if (oldonload) {' ."\n";
                    $scripttext .= '        oldonload();' ."\n";
                    $scripttext .= '      }' ."\n";
                    $scripttext .= '      func();' ."\n";
                    $scripttext .= '    }' ."\n";
                    $scripttext .= '  }' ."\n";
                    $scripttext .= '}    ' ."\n";    

                    if ($flg_show_map)
                    {
                        $scripttext .= 'document.getElementById("mapDivWrapper").style.display = "block";' ."\n";
                        $scripttext .= 'addLoadEvent(initializeMap);' ."\n";
                    }
                    else
                    {
                        $scripttext .= 'document.getElementById("mapDivWrapper").style.display = "none";' ."\n";
                    }
                        
                    $scripttext .= '//]]></script>' ."\n";
                    // Script end

                    echo $scripttext;



                    ?>
                    </div>


                    <?php
                        $credits ='<div>'."\n";
                        if ((int)$mapMapTypeOSM != 0)
                        {
                            $credits .= 'OSM '.Text::_('COM_ZHGOOGLEMAP_MAP_POWEREDBY').': ';
                            $credits .= '<a href="'.$urlProtocol.'://www.openstreetmap.org/" target="_blank">OpenStreetMap</a>'."\n";
                        }
                        $credits .='</div>'."\n";
                    echo $credits;
                    ?>

                </div>

            </div>
        </div>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAPPATH_DETAIL')); ?>
    <div class="row" id="tab1">
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('details') as $field): ?>
                <div class="control-group">
                    
                    <div class="control-label">
                    <?php 
                        echo $field->label;
                    ?>
                    </div>
                    <div class="controls">
                    <?php 
                        echo $field->input;
                    ?>
                    </div>
 
                </div>
                <?php endforeach; ?>

            
        </fieldset>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'pathadvanced', Text::_('COM_ZHGOOGLEMAP_MAPPATH_PATHADVANCED')); ?>
    <div class="row" id="tab2">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('pathadvanced') as $field): ?>
                <div class="control-group">
                    <?php 
                        ?>
                        <div class="control-label">
                        <?php 
                            echo $field->label;
                        ?>
                        </div>
                        <div class="controls">
                        <?php 
                            echo $field->input;
                        ?>
                        </div>
                        <?php 
                    ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'kmloptions', Text::_('COM_ZHGOOGLEMAP_MAPPATH_DETAIL_KMLLAYER_TITLE')); ?>
    <div class="row" id="tab3">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('kmloptions') as $field): ?>
                <div class="control-group">
                    <?php 
                        ?>
                        <div class="control-label">
                        <?php 
                            echo $field->label;
                        ?>
                        </div>
                        <div class="controls">
                        <?php 
                            echo $field->input;
                        ?>
                        </div>
                        <?php 
                    ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAPPATH_DETAIL_ELEVATION_LABEL')); ?>
    <div class="row" id="tab4">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('pathelevation') as $field): ?>
                <div class="control-group">
                    <?php 
                        if ($field->id == 'jform_elevationicontype')
                        {
                            ?>
                            <div class="control-label">
                            <?php 
                                echo $field->label;
                            ?>
                            </div>
                            <div class="controls">
                            <?php 
                                $iconTypeJS = " onchange=\"javascript:
                                if (document.forms.adminForm.jform_elevationicontype.options[selectedIndex].value!='') 
                                {document.image.src='".$imgpath."' + document.forms.adminForm.jform_elevationicontype.options[selectedIndex].value.replace(/#/g,'%23') + '.png'}
                                else 
                                {document.image.src=''}\"";


                                $scriptPosition = ' name=';

                                echo str_replace($scriptPosition, $iconTypeJS.$scriptPosition, $field->input);
                                echo '<img name="image" src="'.$imgpath .str_replace("#", "%23", $this->item->elevationicontype).'.png" alt="" />';

                                echo '<div class="clr"></div>';
                                echo '<a class="btn btn-primary" href="'.$urlProtocol.'://wiki.zhuk.cc/index.php?title=Zh_GoogleMap_Credits_Icons" target="_blank">'.Text::_( 'COM_ZHGOOGLEMAP_MAP_TERMSOFUSE_ICONS' ).' <img src="'.$utilspath.'info.png" alt="'.Text::_( 'COM_ZHGOOGLEMAP_MAP_TERMSOFUSE_ICONS' ).'" style="margin: 0;" /></a>';
                                echo '<div class="clr"></div>';
                                echo '<br />';
                            ?>
                            </div>
                            <?php
                        }
                        else
                        {
                            ?>
                            <div class="control-label">
                            <?php 
                                echo $field->label;
                            ?>
                            </div>
                            <div class="controls">
                            <?php 
                                echo $field->input;
                            ?>
                            </div>
                            <?php 
                        }
                        ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'groundimage', Text::_('COM_ZHGOOGLEMAP_MAPPATH_DETAIL_IMGGROUND_TITLE')); ?>
    <div class="row" id="tab5">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('groundimage') as $field): ?>
                <div class="control-group">
                    <?php 
                        ?>
                        <div class="control-label">
                        <?php 
                            echo $field->label;
                        ?>
                        </div>
                        <div class="controls">
                        <?php 
                            echo $field->input;
                        ?>
                        </div>
                        <?php 
                    ?>
                </div>
                <?php endforeach; ?>
            
        </fieldset>
    </div>
     <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

</div>

<div class="row-fluid">
    <input type="hidden" name="task" value="mappath.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</div>


</form>
