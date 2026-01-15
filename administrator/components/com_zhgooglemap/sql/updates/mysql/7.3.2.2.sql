INSERT INTO `#__content_types` (`type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`) 
VALUES (	
	'ZhGoogleMap Marker', 
	'com_zhgooglemap.mapmarker', 
	'{"special":
		{"dbtable":"#__zhgooglemaps_markers"
		,"key":"id"
		,"type":"MapMarker"
		,"prefix":"ZhGoogleMapTable"
		,"config":"array()"}
	 ,"common":
		{"dbtable":"#__ucm_content"
		,"key":"ucm_id"
		,"type":"Corecontent"
		,"prefix":"JTable"
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