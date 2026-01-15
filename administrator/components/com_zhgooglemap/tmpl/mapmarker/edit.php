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
        $mainScriptLibrary .= '&libraries=places';
    }
    else
    {
        $mainScriptLibrary .= ',places';
    }
}

$mainScriptBegin .= $mainScriptLibrary;

$mainScript = $mainScriptBegin . $mainScriptEnd;

$wa->registerAndUseScript('zhgooglemaps.mapmarker.script', $mainScript);
      
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

    <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'markermain', 'recall' => true, 'breakpoint' => 768]); ?>


    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'markermain', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_MARKER')); ?>
    <div class="row" id="tab0">
            <div>
                <fieldset class="adminform">
                
                    <?php foreach($this->form->getFieldset('markermain') as $field): 
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

                    <div id="placesDivAC" style="margin:0;padding:0;width:100%;height:50px">
                            <input id="searchTextField" type="text" size="100">
                            <?php  echo '  <button id="findAddressButton" onclick="Do_Find(); return false;">'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_DOFINDBUTTON').'</button>'; ?>
                    </div>
                    <div id="GMapsID" style="margin:0;padding:0;width:100%;height:<?php echo $map_height ?>">
                        
                    <?php 

                    $wa->registerAndUseStyle('zhgooglemaps.mapmarker.style', URI::root() .'administrator/components/com_zhgooglemap/assets/css/admin.css');
                        
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
                        $scripttext .= 'var geocoder;' ."\n";
                        $scripttext .= 'var inputPlacesAC;' ."\n";
                        

                        $scripttext .= 'function initializeMap() {' ."\n";

                        $scripttext .= 'infowindow = new google.maps.InfoWindow();' ."\n";
                        
                        $scripttext .= 'geocoder = new google.maps.Geocoder();'."\n";

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
                        
                        if (isset($this->item->latitude) && isset($this->item->longitude)
                        && ($this->item->latitude != "") && ($this->item->longitude !="")
                         )
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
                            $scripttext .= '  });'."\n";

                            $scripttext .= '    google.maps.event.addListener(marker, \'drag\', function(event) {' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";
                            
                            $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                            $scripttext .= '      marker.setPosition(event.latLng);' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";
                        }
                        else
                        {
                          if (isset($this->item->addresstext) and ($this->item->addresstext!= ""))
                          {
                            $scripttext .= '  geocoder.geocode( { \'address\': "'.$this->item->addresstext.'"}, function(results, status) {'."\n";
                            $scripttext .= '  if (status == google.maps.GeocoderStatus.OK) {'."\n";
                            $scripttext .= '    initialLocation = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());' ."\n";
                            //$scripttext .= '    alert("Geocode was successful");'."\n";
                            //$scripttext .= '    alert("latlng="+latlng'. $currentmarker->id.');'."\n";
                            $scripttext .= '  map.setCenter(initialLocation);' ."\n";

                            $scripttext .= '  marker = new google.maps.Marker({' ."\n";
                            $scripttext .= '      position: initialLocation, ' ."\n";
                            $scripttext .= '      draggable:true, ' ."\n";
                            $scripttext .= '      map: map, ' ."\n";
                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                            // Replace to new, because all charters are shown
                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";        
                            $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
                            $scripttext .= '  });'."\n";

                            $scripttext .= '    google.maps.event.addListener(marker, \'drag\', function(event) {' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";
                            
                            $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                            $scripttext .= '      marker.setPosition(event.latLng);' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";

                            $scripttext .= '  }'."\n";
                            $scripttext .= '  else'."\n";
                            $scripttext .= '  {'."\n";
                            $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': '.$this->item->addresstext.'");'."\n";
                            $scripttext .= '    initialLocation = spblocation;' ."\n";

                            $scripttext .= '  map.setCenter(initialLocation);' ."\n";

                            $scripttext .= '  marker = new google.maps.Marker({' ."\n";
                            $scripttext .= '      position: initialLocation, ' ."\n";
                            $scripttext .= '      draggable:true, ' ."\n";
                            $scripttext .= '      map: map, ' ."\n";
                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                            // Replace to new, because all charters are shown
                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";        
                            $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
                            $scripttext .= '  });'."\n";

                            $scripttext .= '    google.maps.event.addListener(marker, \'drag\', function(event) {' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";
                            
                            $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                            $scripttext .= '      marker.setPosition(event.latLng);' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";

                            $scripttext .= '  }'."\n";
                            $scripttext .= '});'."\n";
                          
                          }
                          else
                          {
                            $scripttext .= 'initialLocation = spblocation;' ."\n";
                            $scripttext .= '    map.setCenter(initialLocation);' ."\n";
                            $scripttext .= '    marker = new google.maps.Marker({' ."\n";
                            $scripttext .= '      position: initialLocation, ' ."\n";
                            $scripttext .= '      draggable:true, ' ."\n";
                            $scripttext .= '      map: map, ' ."\n";
                            $scripttext .= '      animation: google.maps.Animation.DROP,' ."\n";
                            // Replace to new, because all charters are shown
                            //$scripttext .= '      title:"'.htmlspecialchars(str_replace('\\', '/', $this->item->title) , ENT_QUOTES, 'UTF-8').'"' ."\n";        
                            $scripttext .= '      title:"'.str_replace('\\', '/', str_replace('"', '\'\'', $this->item->title)).'"' ."\n";
                            $scripttext .= '    });'."\n";

                            $scripttext .= '    google.maps.event.addListener(marker, \'drag\', function(event) {' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";
                            
                            $scripttext .= '    google.maps.event.addListener(map, \'click\', function(event) {' ."\n";
                            $scripttext .= '      marker.setPosition(event.latLng);' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_longitude.value = event.latLng.lng();' ."\n";
                            $scripttext .= '      document.forms.adminForm.jform_latitude.value = event.latLng.lat();' ."\n";
                            $scripttext .= '    });' ."\n";

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
                        }
                        
                        
                        // Places Begin
                        $scripttext .= '  inputPlacesAC = document.getElementById(\'searchTextField\');' ."\n";
                        $scripttext .= '  var autocompletePlaces = new google.maps.places.Autocomplete(inputPlacesAC);' ."\n";

                        $scripttext .= '  autocompletePlaces.bindTo(\'bounds\', map);' ."\n";

                        $scripttext .= '  google.maps.event.addListener(autocompletePlaces, \'place_changed\', function() {' ."\n";
                        $scripttext .= '  var place = autocompletePlaces.getPlace();' ."\n";
                        
                        $scripttext .= '  var markerPlacesACText = place.name;' ."\n";            

                        $scripttext .= '  if (place.geometry.viewport) ' ."\n";
                        $scripttext .= '  {' ."\n";
                        $scripttext .= '    map.fitBounds(place.geometry.viewport);' ."\n";
                        $scripttext .= '  } ' ."\n";
                        $scripttext .= '  else ' ."\n";
                        $scripttext .= '  {' ."\n";
                        $scripttext .= '    map.setCenter(place.geometry.location);' ."\n";
                        $scripttext .= '    map.setZoom(17);' ."\n";
                        $scripttext .= '  }' ."\n";

                        $scripttext .= '  marker.setPosition(place.geometry.location);' ."\n";
                        $scripttext .= '  marker.setTitle(markerPlacesACText);' ."\n";
                        // For normal render marker after move
                        $scripttext .= '  marker.setMap(map);' ."\n";
                        $scripttext .= '  document.forms.adminForm.jform_longitude.value = place.geometry.location.lng();' ."\n";
                        $scripttext .= '  document.forms.adminForm.jform_latitude.value = place.geometry.location.lat();' ."\n";
                        
                        $scripttext .= '  });' ."\n";
                        // Places End
                        


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

                        // Find button
                        $scripttext .= 'function Do_Find() {';
                        //$scripttext .= 'var findAddressButton = document.getElementById(\'findAddressButton\');' ."\n";
                        //$scripttext .= 'google.maps.event.addDomListener(findAddressButton, \'click\', function() {' ."\n";
                        $scripttext .= '  geocoder.geocode( { \'address\': inputPlacesAC.value}, function(results, status) {'."\n";
                        $scripttext .= '  if (status == google.maps.GeocoderStatus.OK) {'."\n";
                        $scripttext .= '    var latlngFind = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());' ."\n";

                        $scripttext .= '    map.setCenter(latlngFind);' ."\n";
                        $scripttext .= '    map.setZoom(17);' ."\n";

                        $scripttext .= '  marker.setPosition(latlngFind);' ."\n";
                        $scripttext .= '  marker.setTitle(inputPlacesAC.value);' ."\n";
                        // For normal render marker after move
                        $scripttext .= '  marker.setMap(map);' ."\n";
                        $scripttext .= '  document.forms.adminForm.jform_longitude.value = latlngFind.lng();' ."\n";
                        $scripttext .= '  document.forms.adminForm.jform_latitude.value = latlngFind.lat();' ."\n";
                        
                        $scripttext .= '  }'."\n";
                        $scripttext .= '  else'."\n";
                        $scripttext .= '  {'."\n";
                        $scripttext .= '    alert("'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_REASON').': " + status + "\n" + "'.Text::_('COM_ZHGOOGLEMAP_MAPMARKER_GEOCODING_ERROR_ADDRESS').': "+inputPlacesAC.value);'."\n";
                        $scripttext .= '  }'."\n";
                        $scripttext .= '});'."\n";
                        //$scripttext .= '});' ."\n";
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

    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL')); ?>
    <div class="row" id="tab1">
        <fieldset class="adminform">
                <?php foreach($this->form->getFieldset('details') as $field): ?>
                <div class="control-group">
                    <?php 
                        if ($field->id == 'jform_rating_value')
                        {
                        
                            ?>
                            <div class="control-label">
                            <?php 
                                echo $field->label;
                            ?>
                            </div>
                            <div class="controls">
                            <?php 

                                
                                echo "<div>";
                                $val_cnt_max = 5;
                                
                                $val_main = $this->item->rating_value;
                                $val_int = floor($val_main);
                                
                                if ($val_main > $val_cnt_max)
                                {
                                    $val_main = $val_cnt_max;
                                    $val_int = $val_cnt_max;
                                }
                                
                                $val_cnt = 0;
                                if ($val_main == 0)
                                {
                                    echo '<img name="image'.$val_cnt.'" src="'.$utilspath .'star5_00.png" alt="" />';
                                    $val_cnt++;
                                }
                                else if ($val_int == 0 && $val_main > 0)
                                {
                                    echo '<img name="image'.$val_cnt.'" src="'.$utilspath .'star5_05.png" alt="" />';
                                    $val_cnt++;
                                }
                                else
                                {
                                    for ($i=0; $i<$val_int; $i++)
                                    {
                                        echo '<img name="image'.$val_cnt.'" src="'.$utilspath .'star5_10.png" alt="" />';
                                        $val_cnt++;
                                    }
                                    if (ceil(($val_main-$val_int)*10)>4)
                                    {
                                        echo '<img name="image'.$val_cnt.'" src="'.$utilspath .'star5_05.png" alt="" />';
                                        $val_cnt++;
                                    }
                                }
                                for ($i=$val_cnt; $i < $val_cnt_max; $i++)
                                {
                                    echo '<img name="image'.$val_cnt.'" src="'.$utilspath .'star5_00.png" alt="" />';
                                }
                                
                                
                                echo "</div>";
                                
                                echo $field->input;

                            ?>
                            </div>
                            <?php 
                                
                        }
                        else if ($field->id == 'jform_ordering')
                        {
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
    
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'markeradvanced', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_APPEARANCE')); ?>
    <div class="row" id="tab2">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('markeradvanced') as $field): ?>
                <div class="control-group">
                    <?php 
                        if ($field->id == 'jform_icontype')
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
                                if (document.forms.adminForm.jform_icontype.options[selectedIndex].value!='') 
                                {document.image.src='".$imgpath."' + document.forms.adminForm.jform_icontype.options[selectedIndex].value.replace(/#/g,'%23') + '.png'}
                                else 
                                {document.image.src=''}\"";


                                $scriptPosition = ' name=';

                                echo str_replace($scriptPosition, $iconTypeJS.$scriptPosition, $field->input);
                                echo '<img name="image" src="'.$imgpath .str_replace("#", "%23", $this->item->icontype).'.png" alt="" />';

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
    
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'markerwithlabel', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_WITHLABEL')); ?>
    <div class="row" id="tab6">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('markerwithlabel') as $field): ?>
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
    
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'infowintabs', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_TABS')); ?>
    <div class="row" id="tab3">

            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_TABS'); ?></legend>
                    <?php foreach($this->form->getFieldset('infowintabs') as $field): ?>
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

            <?php 
            for ($i = 1; $i <= 19; $i++) {
            ?>
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_TAB'.$i); ?></legend>
                    <?php foreach($this->form->getFieldset('infowintab'.$i) as $field): ?>
                    <div class="control-group">
                        <?php 
                            if ($field->id == 'jform_tab'.$i)
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
            <?php }; ?>

    </div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'integration', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_INTEGRATION')); ?>
    <div class="row" id="tab4">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('integration') as $field): ?>
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
            
            
                <?php foreach($this->form->getFieldset('integration_article') as $field): ?>
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
    
    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extraattributes', Text::_('COM_ZHGOOGLEMAP_MAPMARKER_DETAIL_ATTRIBUTES')); ?>
    <div class="row" id="tab5">
        
        <fieldset class="adminform">
            
                <?php foreach($this->form->getFieldset('extraattributes') as $field): ?>
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
    <input type="hidden" name="task" value="mapmarker.edit" />
    <?php echo HTMLHelper::_('form.token'); ?>
</div>


</form>
