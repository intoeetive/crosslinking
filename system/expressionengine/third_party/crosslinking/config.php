<?php

if ( ! defined('CROSSLINKING_ADDON_NAME'))
{
	define('CROSSLINKING_ADDON_NAME',         'Crosslinking');
	define('CROSSLINKING_ADDON_VERSION',      '1.3.5');
}

$config['name']=CROSSLINKING_ADDON_NAME;
$config['version']=CROSSLINKING_ADDON_VERSION;

$config['nsm_addon_updater']['versions_xml']='http://www.intoeetive.com/index.php/update.rss/34';