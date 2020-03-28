<?php
/**
 * @package phpBB Extension - Radius
 * @copyright (c) 2020 Andreas Paffenholz
 * @license GNU General Public License v3
 */

if (!defined('IN_PHPBB')) {
	exit;
}

if (empty($lang) || !is_array($lang)) {
	$lang = array();
}

$lang = array_merge($lang, array(
		'RADIUSSERVER'	            => 'Radius Server',
		'RADIUSSECRET'  	    => 'Radius secret',
	        'RADIUS'                  => 'Radius Config Form',
	        'RADIUS_TITLE'            => 'Radius Config',
        	'RADIUSSERVER_EXPLAIN'    => 'The name of the radius server',
	        'RADIUSSECRET_EXPLAIN'    => 'The radius secret',
	)
);

