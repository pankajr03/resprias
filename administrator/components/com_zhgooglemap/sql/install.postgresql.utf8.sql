DROP TABLE IF EXISTS "#__zhgooglemaps_maps";

CREATE TABLE "#__zhgooglemaps_maps" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(255) default '' NOT NULL,
  "width" integer default 0 NOT NULL,
  "height" integer default 0 NOT NULL,
  "latitude" varchar(20) default '' NOT NULL,
  "longitude" varchar(20) default '' NOT NULL,
  "zoom" smallint default 0 NOT NULL,
  "minzoom" smallint default 0 NOT NULL,
  "maxzoom" smallint default 0 NOT NULL,
  "draggable" smallint default 1 NOT NULL,
  "doubleclickzoom" smallint default 0 NOT NULL,
  "scrollwheelzoom" smallint default 1 NOT NULL,
  "zoomcontrol" smallint default 0 NOT NULL,
  "scalecontrol" smallint default 0 NOT NULL,
  "maptype" smallint default 0 NOT NULL,
  "maptypecontrol" smallint default 0 NOT NULL,
  "overviewmapcontrol" smallint default 0 NOT NULL,
  "rotatecontrol" smallint default 0 NOT NULL,
  "pancontrol" smallint default 0 NOT NULL,
  "trafficcontrol" smallint default 0 NOT NULL,
  "transitcontrol" smallint default 0 NOT NULL,
  "streetviewcontrol" smallint default 0 NOT NULL,
  "streetview" smallint default 0 NOT NULL,
  "streetviewstyleid" integer default 0 NOT NULL,
  "bikecontrol" smallint default 0 NOT NULL,
  "balloon" smallint default 0 NOT NULL,
  "openballoon" smallint default 0 NOT NULL,
  "pospan" smallint default 0 NOT NULL,
  "posmaptype" smallint default 0 NOT NULL,
  "poszoom" smallint default 0 NOT NULL,
  "posscale" smallint default 0 NOT NULL,
  "posstreet" smallint default 0 NOT NULL,
  "description" text NOT NULL,
  "markermanager" smallint default 0 NOT NULL,
  "published" smallint default 0 NOT NULL,
  "markercluster" smallint default 0 NOT NULL,
  "markerclustergroup" smallint default 0 NOT NULL,
  "clusterzoom" smallint default 0 NOT NULL,
  "kmllayer" text NOT NULL,
  "markergroupcontrol" smallint default 0 NOT NULL,
  "markergrouptype" smallint default 0 NOT NULL,
  "markergroupwidth" integer default 20 NOT NULL,
  "markergroupshowicon" smallint default 0 NOT NULL,
  "markergroupshowiconall" smallint default 100 NOT NULL,
  "markergroupcss" integer default 0 NOT NULL,
  "markergroupdesc1" text NOT NULL,
  "markergroupdesc2" text NOT NULL,
  "markergrouptitle" varchar(255) default '' NOT NULL,
  "markergroupsep1" smallint default 0 NOT NULL,
  "markergroupsep2" smallint default 0 NOT NULL,
  "markergrouporder" smallint default 0 NOT NULL,
  "markergroupsearch" smallint default 0 NOT NULL,
  "markerlist" smallint default 0 NOT NULL,
  "markerlistpos" smallint default 0 NOT NULL,
  "markerlistwidth" integer default 0 NOT NULL,
  "markerlistheight" integer default 0 NOT NULL,
  "markerlistbgcolor" text NOT NULL,
  "markerlistaction" smallint default 0 NOT NULL,
  "markerlistcontent" smallint default 0 NOT NULL,
  "markerlistbuttonpos" smallint default 3 NOT NULL,
  "markerlistbuttontype" smallint default 0 NOT NULL,
  "markerlistsearch" smallint default 0 NOT NULL,
  "markerlistsync" smallint default 0 NOT NULL,
  "headerhtml" text NOT NULL,
  "footerhtml" text NOT NULL,
  "headersep" smallint default 0 NOT NULL,
  "footersep" smallint default 0 NOT NULL,
  "openstreet" smallint default 0 NOT NULL,
  "opentopomap" smallint default 0 NOT NULL,
  "nztopomaps" smallint default 0 NOT NULL,
  "placesenable" smallint default 0 NOT NULL,
  "placesautocomplete" smallint default 0 NOT NULL,
  "placesacwidth" integer default 70 NOT NULL,
  "placestypeac" text NOT NULL,
  "placestype" text NOT NULL,
  "placesradius" integer default 0 NOT NULL,
  "placesdirection" smallint default 0 NOT NULL,
  "findcontrol" smallint default 0 NOT NULL,
  "findwidth" integer default 0 NOT NULL,
  "findpos" smallint default 2 NOT NULL,
  "findroute" smallint default 0 NOT NULL,
  "elevation" smallint default 0 NOT NULL,
  "usercontact" smallint default 0 NOT NULL,
  "useruser" smallint default 0 NOT NULL,
  "usermarkers" smallint default 0 NOT NULL,
  "usermarkersfilter" smallint default 0 NOT NULL,
  "usermarkerspublished" smallint default 0 NOT NULL,
  "usermarkersicon" smallint default 1 NOT NULL,
  "usercontactpublished" smallint default 0 NOT NULL,
  "usermarkersinsert" smallint default 1 NOT NULL,
  "usermarkersupdate" smallint default 1 NOT NULL,
  "usermarkersdelete" smallint default 1 NOT NULL,
  "routedraggable" smallint default 0 NOT NULL,
  "routeshowpanel" smallint default 0 NOT NULL,
  "routeaddress" text NOT NULL,
  "autoposition" smallint default 0 NOT NULL,
  "geolocationcontrol" smallint default 0 NOT NULL,
  "geolocationpos" smallint default 2 NOT NULL,
  "geolocationbutton" smallint default 1 NOT NULL,
  "lang" varchar(20) default '' NOT NULL,
  "custommaptype" smallint default 0 NOT NULL,
  "custommaptypelist" text NOT NULL,
  "usercontactattributes" text NOT NULL,
  "mapstyles" text NOT NULL,
  "css2load" text NOT NULL,
  "js2load" text NOT NULL,
  "cssclassname" text NOT NULL,
  "mapbounds" varchar(100) default '' NOT NULL,
  "weathertypeid" integer default 0 NOT NULL,
  "routedriving" smallint default 1 NOT NULL,
  "routewalking" smallint default 1 NOT NULL,
  "routebicycling" smallint default 1 NOT NULL,
  "routetransit" smallint default 0 NOT NULL,
  "routeavoidhighways" smallint default 0 NOT NULL,
  "routeavoidtolls" smallint default 0 NOT NULL,
  "routeunitsystem" smallint default 0 NOT NULL,
  "useajax" smallint default 0 NOT NULL,
  "useajaxobject" smallint default 0 NOT NULL,
  "zoombyfind" smallint default 100 NOT NULL,
  "markergroupctlmarker" smallint default 1 NOT NULL,
  "markergroupctlpath" smallint default 0 NOT NULL,
  "placemark_rating" smallint default 0 NOT NULL,
  "hovermarker" smallint default 0 NOT NULL,
  "hoverinfobubble" integer default 0 NOT NULL,
  "defaultmaptypes" smallint default 1 NOT NULL,
  "disableautopan" smallint default 0 NOT NULL,
  "ajaxbufferplacemark" integer default 0 NOT NULL,
  "ajaxbufferpath" integer default 0 NOT NULL,
  "ajaxbufferroute" integer default 0 NOT NULL,
  "ajaxgetplacemark" smallint default 0 NOT NULL,
  "region" varchar(20) default '' NOT NULL,
  "country" varchar(20) default '' NOT NULL,
  "trafficcontrolpos" smallint default 3 NOT NULL,
  "transitcontrolpos" smallint default 3 NOT NULL,
  "bikecontrolpos" smallint default 3 NOT NULL,
  "mapcentercontrol" smallint default 0 NOT NULL,
  "mapcentercontrolpos" smallint default 2 NOT NULL,
  "markerorder" smallint default 0 NOT NULL,
  "markerspinner" smallint default 0 NOT NULL,
  "showcreateinfo" smallint default 0 NOT NULL,
  "override_id" integer default 0 NOT NULL,
  "panelinfowin" smallint default 0 NOT NULL,
  "panelwidth" smallint default 300 NOT NULL,
  "panelstate" smallint default 0 NOT NULL,
  "overlayopacitycontrol" smallint default 0 NOT NULL,
  "overlayopacitycontrolpos" smallint default 2 NOT NULL,  
  "gogoogle" smallint default 0 NOT NULL,
  "gogoogle_map" smallint default 0 NOT NULL,
  "auto_center_zoom" smallint default 0 NOT NULL,
  "closepopuponclick" smallint default 0 NOT NULL,
  "circle_border" smallint default 0 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_markers";

CREATE TABLE "#__zhgooglemaps_markers" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "latitude" varchar(20) default '' NOT NULL,
  "longitude" varchar(20) default '' NOT NULL,
  "addresstext" text,
  "mapid" integer default 0 NOT NULL,
  "openbaloon" smallint default 0 NOT NULL,
  "actionbyclick" smallint default 1 NOT NULL,
  "zoombyclick" smallint default 100 NOT NULL,
  "baloon" smallint default 0 NOT NULL,
  "icontype" varchar(250) default '' NOT NULL,
  "iconofsetx" smallint default 0 NOT NULL,
  "iconofsety" smallint default 0 NOT NULL,
  "description" text,
  "descriptionhtml" text,
  "descriptionfullhtml" text,
  "hoverhtml" text,
  "published" smallint default 0 NOT NULL,
  "hrefsite" text,
  "hrefimage" text,
  "hrefimagecss" text,
  "hrefimagethumbnail" text,
  "hrefsitename" text,
  "markergroup" integer default 0 NOT NULL,
  "markercontent" smallint default 0 NOT NULL,
  "contactid" integer default 0 NOT NULL,
  "createdbyuser" integer default 0 NOT NULL,
  "showcontact" smallint default 0 NOT NULL,
  "showuser" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "createddate" timestamp without time zone,
  "userprotection" smallint default 0 NOT NULL,
  "streetviewstyleid" integer default 0 NOT NULL,
  "streetviewinfowin" smallint default 0 NOT NULL,
  "streetviewinfowinw" smallint default 400 NOT NULL,
  "streetviewinfowinh" smallint default 250 NOT NULL,
  "params" text,
  "attribute1" text,
  "attribute2" text,
  "attribute3" text,
  "attribute4" text,
  "attribute5" text,
  "attribute6" text,
  "attribute7" text,
  "attribute8" text,
  "attribute9" text,
  "tabid" integer default 0 NOT NULL,
  "tab1" text,
  "tab2" text,
  "tab3" text,
  "tab4" text,
  "tab5" text,
  "tab6" text,
  "tab7" text,
  "tab8" text,
  "tab9" text,
  "tab10" text,
  "tab11" text,
  "tab12" text,
  "tab13" text,
  "tab14" text,
  "tab15" text,
  "tab16" text,
  "tab17" text,
  "tab18" text,
  "tab19" text,
  "tab1title" varchar(250),
  "tab2title" varchar(250),
  "tab3title" varchar(250),
  "tab4title" varchar(250),
  "tab5title" varchar(250),
  "tab6title" varchar(250),
  "tab7title" varchar(250),
  "tab8title" varchar(250),
  "tab9title" varchar(250),
  "tab10title" varchar(250),
  "tab11title" varchar(250),
  "tab12title" varchar(250),
  "tab13title" varchar(250),
  "tab14title" varchar(250),
  "tab15title" varchar(250),
  "tab16title" varchar(250),
  "tab17title" varchar(250),
  "tab18title" varchar(250),
  "tab19title" varchar(250),
  "tab1image" text,
  "tab2image" text,
  "tab3image" text,
  "tab4image" text,
  "tab5image" text,
  "tab6image" text,
  "tab7image" text,
  "tab8image" text,
  "tab9image" text,
  "tab10image" text,
  "tab11image" text,
  "tab12image" text,
  "tab13image" text,
  "tab14image" text,
  "tab15image" text,
  "tab16image" text,
  "tab17image" text,
  "tab18image" text,
  "tab19image" text,
  "tab_info" smallint default 0 NOT NULL,
  "ordering" integer default 0 NOT NULL,
  "rating_value" REAL DEFAULT 0 NOT NULL,
  "rating_count" integer default 0 NOT NULL,
  "labelinbackground" smallint default 0 NOT NULL,
  "labelanchorx" integer default 0 NOT NULL,
  "labelanchory" integer default 0 NOT NULL,
  "labelclass" varchar(250),
  "labelcontent" text,
  "includeinlist" smallint default 1 NOT NULL,
  "access" integer default 1 NOT NULL,
  "alias" varchar(255),
  "hrefcontact" text,
  "hrefarticle" text,
  "hrefdetail" text,
  "toolbarcontact" smallint default 0 NOT NULL,
  "toolbararticle" smallint default 0 NOT NULL,
  "toolbardetail" smallint default 0 NOT NULL,
  "articleid" integer default 0 NOT NULL,
  "attributesdetail" text,
  "userorder" integer default 0 NOT NULL,
  "showgps" smallint default 0 NOT NULL,
  "iframearticleclass" varchar(250),
  "gogoogle" smallint default 0 NOT NULL,
  "preparecontent" smallint default 0 NOT NULL,
  "tag_show" smallint default 0 NOT NULL,
  "tag_style" smallint default 0 NOT NULL,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_routers";

CREATE TABLE "#__zhgooglemaps_routers" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "providealt" smallint default 0 NOT NULL,
  "optimizewaypoints" smallint default 0 NOT NULL,
  "avoidhighways" smallint default 0 NOT NULL,
  "avoidtolls" smallint default 0 NOT NULL,
  "travelmode" smallint default 0 NOT NULL,
  "unitsystem" smallint default 0 NOT NULL,
  "route" text NOT NULL,
  "routebymarker" text NOT NULL,
  "csv_file" text NOT NULL,
  "csv_sep" varchar(1) default '' NOT NULL,
  "route_data" smallint default 0 NOT NULL,
  "mapid" integer default 0 NOT NULL,
  "description" text NOT NULL,
  "descriptionhtml" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "showtype" smallint default 0 NOT NULL,
  "draggable" smallint default 0 NOT NULL,
  "showpanel" smallint default 0 NOT NULL,
  "showpaneltotal" smallint default 1 NOT NULL,
  "showdescription" smallint default 0 NOT NULL,  
  "suppressmarkers" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "weight" smallint default 5 NOT NULL,
  "color" varchar(250) default '#4FA4FF' NOT NULL,
  "opacity" varchar(20) default '0.7' NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_paths";

CREATE TABLE "#__zhgooglemaps_paths" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "weight" smallint default 0 NOT NULL,
  "color" varchar(250) default '' NOT NULL,
  "hover_color" varchar(250) default '' NOT NULL,
  "opacity" varchar(20) default '' NOT NULL,
  "path" text NOT NULL,
  "kmllayer" text NOT NULL,
  "mapid" integer default 0 NOT NULL,
  "description" text NOT NULL,
  "descriptionhtml" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "elevation" smallint default 0 NOT NULL,
  "showtype" smallint default 0 NOT NULL,
  "suppressinfowindows" smallint default 0 NOT NULL,
  "geodesic" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "infowincontent" smallint default 0 NOT NULL,
  "actionbyclick" smallint default 0 NOT NULL,
  "objecttype" smallint default 0 NOT NULL,
  "fillcolor" varchar(250) default '' NOT NULL,
  "fillopacity" varchar(20) default '' NOT NULL,
  "hover_fillcolor" varchar(250) default '' NOT NULL,
  "radius" varchar(250) default '' NOT NULL,
  "elevationwidth" integer default 400 NOT NULL,
  "elevationheight" integer default 200 NOT NULL,
  "elevationcount" integer default 256 NOT NULL,
  "elevationcountkml" integer default 0 NOT NULL,
  "elevationicontype" varchar(250) default '' NOT NULL,
  "elevationbaseline" integer default 0 NOT NULL,
  "v_min_value" varchar(20) default '' NOT NULL,
  "v_max_value" varchar(20) default '' NOT NULL,
  "v_baseline_color" varchar(250) default '' NOT NULL,
  "v_gridline_color" varchar(250) default '' NOT NULL,
  "v_gridline_count" smallint default 5 NOT NULL,
  "v_minor_gridline_color" varchar(250) default '' NOT NULL,
  "v_minor_gridline_count" smallint default 0 NOT NULL,
  "background_color_stroke" varchar(250) default '' NOT NULL,
  "background_color_width" smallint default 0 NOT NULL,
  "background_color_fill" varchar(250) default '' NOT NULL,
  "markergroup" integer default 0 NOT NULL,
  "hoverhtml" text NOT NULL,
  "hrefsite" text NOT NULL,
  "hrefsitename" text NOT NULL, 
  "imgurl" text NOT NULL,
  "imgclickable" smallint default 0 NOT NULL,  
  "imgbounds" varchar(100) default '' NOT NULL,
  "imgopacity" varchar(20) default '' NOT NULL,
  "imgopacitymanage" smallint default 1 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_markergroups";

CREATE TABLE "#__zhgooglemaps_markergroups" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "icontype" varchar(250) default '' NOT NULL,
  "iconofsetx" smallint default 0 NOT NULL,
  "iconofsety" smallint default 0 NOT NULL,
  "overridegroupicon" smallint default 0 NOT NULL,
  "overridemarkericon" smallint default 0 NOT NULL,
  "markermanagerminzoom" smallint default 0 NOT NULL,
  "markermanagermaxzoom" smallint default 18 NOT NULL,
  "activeincluster" smallint default 0 NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "ordering" integer default 0 NOT NULL,
  "userorder" integer default 0 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_maptypes";

CREATE TABLE "#__zhgooglemaps_maptypes" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "gettileurl" text NOT NULL,
  "tilewidth" integer default 256 NOT NULL,
  "tileheight" integer default 256 NOT NULL,
  "ispng" smallint default 1 NOT NULL,
  "minzoom" smallint default 0 NOT NULL,
  "maxzoom" smallint default 18 NOT NULL,
  "opacity" varchar(20) default '' NOT NULL,
  "opacitymanage" smallint default 1 NOT NULL,
  "layertype" smallint default 1 NOT NULL,
  "projectionglobal" text NOT NULL,
  "projectiondefinition" text NOT NULL,
  "fromlatlngtopoint" text NOT NULL,
  "frompointtolatlng" text NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_infobubbles";

CREATE TABLE "#__zhgooglemaps_infobubbles" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "shadowstyle" smallint default 0 NOT NULL,
  "padding" varchar(50) default '' NOT NULL,
  "borderradius" varchar(50) default '' NOT NULL,
  "borderwidth" varchar(50) default '' NOT NULL,
  "bordercolor" varchar(50) default '' NOT NULL,
  "backgroundcolor" varchar(50) default '' NOT NULL,
  "minwidth" varchar(50) default '' NOT NULL,
  "maxwidth" varchar(50) default '' NOT NULL,
  "minheight" varchar(50) default '' NOT NULL,
  "maxheight" varchar(50) default '' NOT NULL,
  "arrowsize" varchar(50) default '' NOT NULL,
  "arrowposition" varchar(50) default '' NOT NULL,
  "arrowstyle" smallint default 0 NOT NULL,
  "disableautopan" smallint default 0 NOT NULL,
  "disableanimation" smallint default 0 NOT NULL,
  "hideclosebutton" smallint default 0 NOT NULL,
  "backgroundclassname" varchar(250) default '' NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_streetviews";

CREATE TABLE "#__zhgooglemaps_streetviews" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "heading" varchar(10) default '0' NOT NULL,
  "pitch" varchar(10) default '0' NOT NULL,
  "zoom" smallint default 1 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_weathertypes";

CREATE TABLE "#__zhgooglemaps_weathertypes" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "clickable" smallint default 1 NOT NULL,
  "suppressinfowindows" smallint default 0 NOT NULL,
  "labelcolor" smallint default 0 NOT NULL,
  "temperatureunits" smallint default 1 NOT NULL,
  "windspeedunits" smallint default 2 NOT NULL,
  "weatherlayer" smallint default 1 NOT NULL,
  "cloudlayer" smallint default 1 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_marker_rates";

CREATE TABLE "#__zhgooglemaps_marker_rates" (
  "id" serial NOT NULL,
  "markerid" integer default 0 NOT NULL,
  "rating_value" REAL DEFAULT 0 NOT NULL,
  "rating_date" timestamp without time zone,
  "ip" text NOT NULL,
  "hostname" text NOT NULL,
  "createdbyuser" integer default 0 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_text_overrides";

CREATE TABLE "#__zhgooglemaps_text_overrides" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "ordering" integer default 0 NOT NULL,
  "placemark_list_title" text NOT NULL,
  "placemark_list_button_title" text NOT NULL,
  "placemark_list_button_hint" text NOT NULL,
  "panelcontrol_hint" text NOT NULL,
  "panel_detail_title" text NOT NULL,
  "panel_placemarklist_title" text NOT NULL,
  "panel_route_title" text NOT NULL,
  "panel_group_title" text NOT NULL,
  "group_list_title" text NOT NULL,
  "gogoogle_text" text NOT NULL,
  "placemark_list_search" smallint default 0 NOT NULL,
  "placemark_list_mapping_type" smallint default 0 NOT NULL,
  "placemark_list_accent" text NOT NULL,
  "placemark_list_mapping" text NOT NULL,
  "placemark_list_accent_side" smallint default 3 NOT NULL,
  "group_list_search" smallint default 0 NOT NULL,
  "group_list_mapping_type" smallint default 0 NOT NULL,
  "group_list_accent" text NOT NULL,
  "group_list_mapping" text NOT NULL,
  "group_list_accent_side" smallint default 3 NOT NULL,
  "placemark_date_fmt" text NOT NULL,
  "circle_radius" varchar(250) default '' NOT NULL,
  "circle_stroke_weight" smallint default 4 NOT NULL,
  "circle_stroke_color" varchar(250) default '' NOT NULL,
  "circle_stroke_opacity" varchar(20) default '' NOT NULL,
  "circle_fill_color" varchar(250) default '' NOT NULL,
  "circle_fill_opacity" varchar(20) default '' NOT NULL,
  "circle_draggable" smallint default 1 NOT NULL,
  "circle_editable" smallint default 1 NOT NULL,
  "circle_info" smallint default 1 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_marker_content";

CREATE TABLE "#__zhgooglemaps_marker_content" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "ordering" integer default 0 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_marker_buffer";

CREATE TABLE "#__zhgooglemaps_marker_buffer" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "latitude" varchar(20) default '' NOT NULL,
  "longitude" varchar(20) default '' NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "addresstext" text,
  "icontype" varchar(250) default '' NOT NULL,
  "iconofsetx" smallint default 0 NOT NULL,
  "iconofsety" smallint default 0 NOT NULL,
  "description" text,
  "descriptionhtml" text,
  "hrefimage" text,
  "markergroup" integer default 0 NOT NULL,
  "createdbyuser" integer default 0 NOT NULL,
  "showuser" smallint default 0 NOT NULL,
  "createddate" timestamp without time zone,
  "showgps" smallint default 0 NOT NULL,
  "preparecontent" smallint default 0 NOT NULL,
  "markercontent" smallint default 0 NOT NULL,
  "status" smallint default 0 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);

DROP TABLE IF EXISTS "#__zhgooglemaps_log";

CREATE TABLE "#__zhgooglemaps_log" (
  "id" serial NOT NULL,
  "catid" integer default 0 NOT NULL,
  "extension" varchar(250) default '' NOT NULL,
  "kind" varchar(250) default '' NOT NULL,
  "title" varchar(250) default '' NOT NULL,
  "description" text NOT NULL,
  "remarks" text NOT NULL,
  "id_target" integer default 0 NOT NULL,
  "id_source" integer default 0 NOT NULL,
  "id_find" integer default 0 NOT NULL,
  "published" smallint default 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "ordering" integer default 0 NOT NULL,
  "params" text,
  PRIMARY KEY  ("id")
);


CREATE INDEX "zhgm_idx_catid" ON "#__zhgooglemaps_maps" ("catid");
CREATE INDEX "zhgm_idx_override" ON "#__zhgooglemaps_maps" ("override_id");

CREATE INDEX "zhgm_idx_catid2" ON "#__zhgooglemaps_markers" ("catid");
CREATE INDEX "zhgm_idx_mapid2" ON "#__zhgooglemaps_markers" ("mapid");
CREATE INDEX "zhgm_idx_markergroup2" ON "#__zhgooglemaps_markers" ("markergroup");
CREATE INDEX "zhgm_idx_createdbyuser2" ON "#__zhgooglemaps_markers" ("createdbyuser");
CREATE INDEX "zhgm_idx_access" ON "#__zhgooglemaps_markers" ("access");
CREATE INDEX "zhgm_idx_alias" ON "#__zhgooglemaps_markers" ("alias");
CREATE INDEX "zhgm_idx_tabid" ON "#__zhgooglemaps_markers" ("tabid");
CREATE INDEX "zhgm_idx_articleid" ON "#__zhgooglemaps_markers" ("articleid");
CREATE INDEX "zhgm_idx_contactid" ON "#__zhgooglemaps_markers" ("contactid");
CREATE INDEX "zhgm_idx_userorder2" ON "#__zhgooglemaps_markers" ("userorder");

CREATE INDEX "zhgm_idx_catid3" ON "#__zhgooglemaps_routers" ("catid");
CREATE INDEX "zhgm_idx_mapid3" ON "#__zhgooglemaps_routers" ("mapid");

CREATE INDEX "zhgm_idx_catid4" ON "#__zhgooglemaps_paths" ("catid");
CREATE INDEX "zhgm_idx_mapid4" ON "#__zhgooglemaps_paths" ("mapid");
CREATE INDEX "zhgm_idx_markergroup4" ON "#__zhgooglemaps_paths" ("markergroup");

CREATE INDEX "zhgm_idx_catid5" ON "#__zhgooglemaps_markergroups" ("catid");
CREATE INDEX "zhgm_idx_userorder5" ON "#__zhgooglemaps_markergroups" ("userorder");

CREATE INDEX "zhgm_idx_catid6" ON "#__zhgooglemaps_maptypes" ("catid");

CREATE INDEX "zhgm_idx_catid7" ON "#__zhgooglemaps_infobubbles" ("catid");

CREATE INDEX "zhgm_idx_catid8" ON "#__zhgooglemaps_streetviews" ("catid");


CREATE INDEX "zhgm_idx_catid9" ON "#__zhgooglemaps_weathertypes" ("catid");

CREATE INDEX "zhgm_idx_markerid" ON "#__zhgooglemaps_marker_rates" ("markerid");

CREATE INDEX "zhgm_idx_catid10" ON "#__zhgooglemaps_text_overrides" ("catid");
CREATE INDEX "zhgm_idx_catid11" ON "#__zhgooglemaps_marker_content" ("catid");

CREATE INDEX "zhgm_idx_catid12" ON "#__zhgooglemaps_marker_buffer" ("catid");
CREATE INDEX "zhgm_idx_markergroup12" ON "#__zhgooglemaps_marker_buffer" ("markergroup");
CREATE INDEX "zhgm_idx_createdbyuser12" ON "#__zhgooglemaps_marker_buffer" ("createdbyuser");

CREATE INDEX "zhgm_idx_catid13" ON "#__zhgooglemaps_log" ("catid");
CREATE INDEX "zhgm_idx_ext" ON "#__zhgooglemaps_log" ("extension");



INSERT INTO "#__content_types" ("type_title", "type_alias", "table", "rules", "field_mappings", "router", "content_history_options") 
VALUES (	
	'ZhGoogleMap Category', 
	'com_zhgooglemap.category', 
	'{"special":
		{"dbtable":"#__categories"
		,"key":"id"
		,"type":"CategoryTable"
		,"prefix":"Joomla\\\\Component\\\\Categories\\\\Administrator\\\\Table\\\\"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"Joomla\\\\CMS\\\\Table\\\\"
		,"config":"array()"}}'
	,''
	, '{"common": {
        "core_content_item_id": "id",
        "core_title": "title",
        "core_state": "published",
        "core_alias": "alias",
        "core_created_time": "created_time",
        "core_modified_time": "modified_time",
        "core_body": "description",
        "core_hits": "hits",
        "core_publish_up": "null",
        "core_publish_down": "null",
        "core_access": "access",
        "core_params": "params",
        "core_featured": "null",
        "core_metadata": "metadata",
        "core_language": "language",
        "core_images": "null",
        "core_urls": "null",
        "core_version": "version",
        "core_ordering": "null",
        "core_metakey": "metakey",
        "core_metadesc": "metadesc",
        "core_catid": "parent_id",
        "asset_id": "asset_id"
    },
    "special": {
        "parent_id": "parent_id",
        "lft": "lft",
        "rgt": "rgt",
        "level": "level",
        "path": "path",
        "extension": "extension",
        "note": "note"
    }}'
	,''
	, ''
	);
	

INSERT INTO "#__content_types" ("type_title", "type_alias", "table", "rules", "field_mappings", "router", "content_history_options") 
VALUES (	
	'ZhGoogleMap Marker', 
	'com_zhgooglemap.mapmarker', 
	'{"special":
		{"dbtable":"#__zhgooglemaps_markers"
		,"key":"id"
		,"type":"MapmarkerTable"
		,"prefix":"ZhukDL\\\\Component\\\\ZhGoogleMap\\\\Administrator\\\\Table\\\\"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"Joomla\\\\CMS\\\\Table\\\\"
		,"config":"array()"}}'
	,''
	, '{"common":
			{"core_content_item_id":"id"
			,"core_title":"title"
			,"core_state":"null"
			,"core_alias":"alias"
			,"core_created_time":"null"
			,"core_modified_time":"null"
			,"core_body":"null"
			, "core_hits":"null"
			,"core_publish_up":"publish_up"
			,"core_publish_down":"publish_down"
			,"core_access":"access"
			, "core_params":"null"
			, "core_featured":"null"
			, "core_metadata":"null"
			, "core_language":"null"
			, "core_images":"null"
			, "core_urls":"null"
			, "core_version":"null"
			, "core_ordering":"ordering"
			, "core_metakey":"null"
			, "core_metadesc":"null"
			, "core_catid":"catid"
			, "core_xreference":"null"
			, "asset_id":"null"}
			, "special":{"fulltext":"null"}}'
	,''
	, ''
	);
	

INSERT INTO "#__content_types" ("type_title", "type_alias", "table", "rules", "field_mappings", "router", "content_history_options") 
VALUES (	
	'ZhGoogleMap Path', 
	'com_zhgooglemap.mappath', 
	'{"special":
		{"dbtable":"#__zhgooglemaps_paths"
		,"key":"id"
		,"type":"MappathTable"
		,"prefix":"ZhukDL\\\\Component\\\\ZhGoogleMap\\\\Administrator\\\\Table\\\\"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"Joomla\\\\CMS\\\\Table\\\\"
		,"config":"array()"}}'
	,''
	, '{"common":
			{"core_content_item_id":"id"
			,"core_title":"title"
			,"core_state":"null"
			,"core_alias":"alias"
			,"core_created_time":"null"
			,"core_modified_time":"null"
			,"core_body":"null"
			, "core_hits":"null"
			,"core_publish_up":"publish_up"
			,"core_publish_down":"publish_down"
			,"core_access":"access"
			, "core_params":"null"
			, "core_featured":"null"
			, "core_metadata":"null"
			, "core_language":"null"
			, "core_images":"null"
			, "core_urls":"null"
			, "core_version":"null"
			, "core_ordering":"ordering"
			, "core_metakey":"null"
			, "core_metadesc":"null"
			, "core_catid":"catid"
			, "core_xreference":"null"
			, "asset_id":"null"}
			, "special":{"fulltext":"null"}}'
	,''
	, ''
	);
	