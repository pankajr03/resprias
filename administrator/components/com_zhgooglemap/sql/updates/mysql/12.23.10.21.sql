update #__menu
set link = replace(link, 'com_zhgooglemap&view=zhgooglemap&', 'com_zhgooglemap&view=map&')
where link like '%com_zhgooglemap%';