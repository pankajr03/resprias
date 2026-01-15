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

$wa->registerAndUseScript('zhgooglemaps.mapmap.script', $mainScript);
   
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

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'mapmain', 'recall' => true, 'breakpoint' => 768]); ?>


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapmain', Text::_('COM_ZHGOOGLEMAP_MAP_MAP')); ?>
       <div class="row" id="tab0">
            <div>
                <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapmain') as $field): ?>
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

                        $loadmodules    ='';

                        $wa->registerAndUseStyle('zhgooglemaps.mapmap.style', URI::root() .'administrator/components/com_zhgooglemap/assets/css/admin.css');
                        

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
                            $scripttext .= '      scaleControl: false,' ."\n";
                            $scripttext .= '      streetViewControl: false,' ."\n";
                            $scripttext .= '      rotateControl: false,' ."\n";
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
                            
                            $scripttext .= '    map = new google.maps.Map(document.getElementById("GMapsID"), myOptions);' ."\n";

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
                            
                            
                            if (isset($this->item->mapbounds) && $this->item->mapbounds != "")
                            {
                                $mapSearchBoundsArray = explode(";", str_replace(',',';',$this->item->mapbounds));
                                if (count($mapSearchBoundsArray) != 4)
                                {
                                    //
                                }
                                else
                                {
                                    $scripttext .= ' var rectangleBounds = '."\n";
                                    $scripttext .=' new google.maps.LatLngBounds(new google.maps.LatLng('.str_replace(";","), new google.maps.LatLng(", $this->item->mapbounds).')) '."\n";
                                    $scripttext .= '; '."\n";
                                    $scripttext .= ' var plPath = new google.maps.Rectangle({'."\n";
                                    $scripttext .= ' bounds: rectangleBounds'."\n";
                                    $scripttext .= ',clickable: false'."\n";
                                    //$scripttext .= ',strokeColor: "'.$currentpath->color.'"'."\n";
                                    //$scripttext .= ',strokeOpacity: '.$currentpath->opacity."\n";
                                    //$scripttext .= ',strokeWeight: '.$currentpath->weight."\n";
                                    $scripttext .= ' });'."\n";

                                    $scripttext .= 'plPath.setMap(map);'."\n";

                                }
                            }                                

                            
                            if (isset($this->item->latitude) && isset($this->item->longitude) )
                            {
                                $scripttext .= 'initialLocation = new google.maps.LatLng('.$this->item->latitude.', ' .$this->item->longitude.');' ."\n";
                                    $scripttext .= '  map.setCenter(initialLocation);' ."\n";

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
                                $scripttext .= '    document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                                $scripttext .= '    document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                                $scripttext .= '    });' ."\n";
 
 
                                $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                                $scripttext .= '    marker.setPosition(event.latLng);' ."\n";
                                $scripttext .= '    document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                                $scripttext .= '    document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                                $scripttext .= '    });' ."\n";


                            }
                            else
                            {
                                $scripttext .= 'initialLocation = spblocation;' ."\n";
                                    $scripttext .= '  map.setCenter(initialLocation);' ."\n";

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
                                $scripttext .= '    document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                                $scripttext .= '    document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                                $scripttext .= '    });' ."\n";
                                
                                $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                                $scripttext .= '    marker.setPosition(event.latLng);' ."\n";
                                $scripttext .= '    document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                                $scripttext .= '    document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                                $scripttext .= '    });' ."\n";


                                $scripttext .= '    map.setCenter(initialLocation);' ."\n";
                            $scripttext .= '    marker.setPosition(initialLocation);' ."\n";

                           
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
                            
                        // end initializeMap    
                        $scripttext .= '}' ."\n";


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
                        $scripttext .= '}' ."\n";


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
                                $credits .= '<a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> '.Text::_('COM_ZHGOOGLEMAP_MAP_CONTRIBUTORS')."\n";
                            }
                            $credits .='</div>'."\n";
                        echo $credits;
                        
                        ?>
                       
                    </div>

                </div>

            </div>
        </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL')); ?>
        <div class="row" id="tab1">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('details') as $field): ?>
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
                        </div>

                    <?php endforeach; ?>
                
            </fieldset>
        </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapdecorheader', Text::_('COM_ZHGOOGLEMAP_MAP_DECOR_HEADER')); ?>
        <div class="row" id="tab2">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapdecorheader') as $field): ?>
                        <div class="control-group">
                        <?php 
                        if ($field->id == 'jform_headerhtml')
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapdecorfooter', Text::_('COM_ZHGOOGLEMAP_MAP_DECOR_FOOTER')); ?>
        <div class="row" id="tab3">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapdecorfooter') as $field): ?>
                        <div class="control-group">
                        <?php 
                        if ($field->id == 'jform_footerhtml')
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapdecorstyle', Text::_('COM_ZHGOOGLEMAP_MAP_DECOR_STYLE')); ?>
       <div class="row" id="tab4">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapdecorstyle') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapdecors', Text::_('COM_ZHGOOGLEMAP_MAP_MAPDECOR')); ?>
        <div class="row" id="tab5">
           
            <fieldset class="options-form">
            <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAP_MAPDECOR'); ?></legend>   
                    <?php foreach($this->form->getFieldset('mapdecor') as $field): ?>
                        
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
            
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAP_GEOFINDCONTROL'); ?></legend>
                <?php foreach($this->form->getFieldset('mapcontrolgeofind') as $field): ?>
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

            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAP_MAPPOSITION'); ?></legend>
                <?php foreach($this->form->getFieldset('positions') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapmarker', Text::_('COM_ZHGOOGLEMAP_MAP_MAPMARKER')); ?>
        <div class="row" id="tab6">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapmarker') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapmarkerlist', Text::_('COM_ZHGOOGLEMAP_MAP_DETAIL_PLACEMARKLIST')); ?>
        <div class="row" id="tab7">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapmarkerlist') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapmarkergroup', Text::_('COM_ZHGOOGLEMAP_MAP_MAPMARKERGROUP')); ?>
        <div class="row" id="tab8">
            
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapmarkergroup') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'maproute', Text::_('COM_ZHGOOGLEMAP_MAP_MAPROUTE')); ?>
        <div class="row" id="tab9">
            
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('maproute') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapplaces', Text::_('COM_ZHGOOGLEMAP_MAP_PLACES')); ?>
        <div class="row" id="tab10">
            
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapplaces') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapadvanced', Text::_('COM_ZHGOOGLEMAP_MAP_MAPADVANCED')); ?>
        <div class="row" id="tab12">
            
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapadvanced') as $field): ?>
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
                        </div>
                    <?php endforeach; ?>
                
            </fieldset>
        </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'mapgeolocation', Text::_('COM_ZHGOOGLEMAP_MAP_GEOLOCATION')); ?>
        <div class="row" id="tab13">
            
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('mapgeolocation') as $field): ?>
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'integration', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_INTEGRATION')); ?>
        <div class="row" id="tab14">
            <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('integration') as $field): ?>
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
    <input type="hidden" name="task" value="mapmap.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</div>


</form>

