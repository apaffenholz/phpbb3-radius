<?php

/**
 * @package phpBB Extension - Radius
 * @copyright (c) 2020 Andreas Paffenholz
 * @license GNU General Public License v3
 */

namespace apaffenholz\radius;

class ext extends \phpbb\extension\base {

    function disable_step($old_state) {
		$config = $this->container->get('config');
			
		if ($config['auth_method'] == 'radius') {
			$config->set('auth_method', 'db');
		}
		
		return false;
	}
}
