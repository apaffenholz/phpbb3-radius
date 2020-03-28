<?php

namespace apaffenholz\radius\auth\provider;

class radius extends \phpbb\auth\provider\base {
	protected $db;
	protected $user;
	protected $config;

	// connect to the database of phpBB
	// and read config
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user) {
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	// login function
	// first try to autheticate via radius
	// if that fails check the local database
	public function login($username, $password) {

	$password = trim($password);

	// exit if no password or username are givren
	if (!$password) {
		return array(
			'status'    => LOGIN_ERROR_PASSWORD,
			'error_msg' => 'NO_PASSWORD_SUPPLIED',
			'user_row'  => array('user_id' => ANONYMOUS),
		);
	}
	if (!$username) {
		return array(
			'status'    => LOGIN_ERROR_USERNAME,
			'error_msg' => 'LOGIN_ERROR_USERNAME',
			'user_row'  => array('user_id' => ANONYMOUS),
		);
	}

	$username_clean = utf8_clean_string($username);

	// connect to the radius instance
	$radius = radius_auth_open();
	radius_add_server($radius, $this->config['radiusserver'], 0, $this->config['radiussecret'], 5, 3);
	radius_create_request($radius, RADIUS_ACCESS_REQUEST);
 	radius_put_attr($radius, RADIUS_USER_NAME, $username);
 	radius_put_attr($radius, RADIUS_USER_PASSWORD, $password);
	$result = radius_send_request($radius);

	// try to find user in database
	$user_row = $this->get_user($username);

	switch ($result) {
		case RADIUS_ACCESS_ACCEPT:
			// the user successfully authenticated via radius
			// if user does not yet exist 
			// create an entry for the user
			if ($user_row == array()) {
				$user_email = '';
				$user_row = array_merge($user_row, array(
					'username' => $username,
					'user_password'	=> phpbb_hash($this->generate_random_password(24)),
					'user_email' => $user_email,
					'group_id' => 2, // by default, the REGISTERED user group is id 2
					'user_type' => USER_NORMAL,
					'user_timezone' => 'Europe/Berlin',
					'user_lang' => 'en',
					'user_regdate' => time(),
				));
				$user_id = user_add($user_row);

				// seems to be necessary, otherwise the first login attempt fails
				$user_row = $this->get_user($username);
			}

			return array(
				'status'	=> LOGIN_SUCCESS,
				'error_msg'	=> false,
				'user_row'	=> $user_row,
			);
			break;
		case RADIUS_ACCESS_REJECT:
		case RADIUS_ACCESS_CHALLENGE:
			// the user was not found via radius,
			// so try the local database

			// if nothing is found in the database the user does not exist
			if ( $user_row == array()) {
				return array(
					'status'    => LOGIN_ERROR_USERNAME,
					'error_msg' => 'LOGIN_ERROR_USERNAME',
					'user_row'  => array('user_id' => ANONYMOUS),
				);
			} else {
				// check if the user provided the password for the account
				if (phpbb_check_hash($password, $user_row['user_password'])) {
					return array(
						'status'        => LOGIN_SUCCESS,
						'error_msg'     => false,
						'user_row'      => $user_row,
					);
				} else {
					return array(
						'status'    => LOGIN_ERROR_PASSWORD,
						'error_msg' => 'LOGIN_ERROR_PASSWORD',
						'user_row'  => array('user_id' => ANONYMOUS),
					);
				}
			}
			break;
		default:
			die('A RADIUS error has occurred: ' . radius_strerror($radius));
		}
	}

	private function generate_random_password($length) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$_+=.<>()&%#';
		return substr(str_shuffle($chars),0,$length);
	}

	private function get_user($username) {
		$user_row = array();
		$sql = "SELECT * FROM " . USERS_TABLE . " WHERE username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row) {
			if ( !($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) ) {
				$user_row = $row;
			}
		}

		return $user_row;
	}

	public function acp() {
		return array(
			'radiusserver', 'radiussecret',
		);
	}

	public function get_acp_template($new_config) {

		$this->user->add_lang_ext('apaffenholz/radius','radius_acp');
		return array(
			'TEMPLATE_FILE'	=> '@apaffenholz_radius/auth_provider_radius.html',
			'TEMPLATE_VARS'	=> array(
				'AUTH_RADIUSSERVER' => $new_config['radiusserver'],
				'AUTH_RADIUSSECRET' => $new_config['radiussecret'],
			),
		);
	}
}

