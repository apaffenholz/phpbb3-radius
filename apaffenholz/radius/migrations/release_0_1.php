<?php

/**
 * @package phpBB Extension - Radius
 * @copyright (c) 2020 Andreas Paffenholz
 * @license GNU General Public License v3
 */

namespace apaffenholz\radius\migrations;

class release_0_1 extends \phpbb\db\migration\migration {
	public function effectively_insetalled() {
		return (isset($this->config['radiusserver']) && isset($this->config['radiussecret'])) ? true : false ;
	}

	public function update_data() {
		return array(
			array('config.add',
				array('radiusserver', '')),
			array('config.add',
				array('radiussecret', '')),
		);
	}
}
