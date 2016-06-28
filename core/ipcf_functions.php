<?php
/**
*
* @package phpBB Extension - IPCF 1.0.0 -(IP Country Flag)
* @copyright (c) 2005 - 2008 - 2016 3Di (Marco T.)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace threedi\ipcf\core;

class ipcf_functions
{
	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\extension\manager "Extension Manager" */
	protected $ext_manager;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver		$db					Database object
	 * @param \phpbb\user					$user				User object
	 * @param \phpbb\extension\manager		$ext_manager		Extension manager object
	 * @param \phpbb\path_helper			$path_helper		Path helper object
	 */
	public function __construct(
			\phpbb\db\driver\driver_interface $db,
			\phpbb\user $user,
			\phpbb\extension\manager $ext_manager,
			\phpbb\path_helper $path_helper)
	{
		$this->db = $db;
		$this->user = $user;
		$this->ext_manager	 = $ext_manager;
		$this->path_helper	 = $path_helper;

		$this->ext_path = $this->ext_manager->get_extension_path('threedi/ipcf', true);
		$this->ext_path_web = $this->path_helper->update_web_root_path($this->ext_path);
	}

	/* Self-explanatory, isn't? */
	/**
	 * Obtain session IP
	 *
	 * @return string user_session_ip
	 */
	public function obtain_session_ip($user_id)
	{
		$sql = 'SELECT session_ip
			FROM ' . SESSIONS_TABLE . '
				WHERE session_user_id = ' . $user_id . '';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$user_session_ip = $row['session_ip'];

		return ($user_session_ip);
	}

	/**
	 * Obtain Country Flag string
	 *
	 * @return string country_flag
	 */
	public function obtain_country_flag_string($user_session_ip)
	{
		/**
			* 16386 = countryCode,status fields, using magic numbers to save bandwidth
		*/
		$json_response = @json_decode(file_get_contents('http://ip-api.com/json/' . $user_session_ip . '?fields=16386'));

		if (($json_response->status) == 'success')
		{
			$iso_country_code =  strtolower($json_response->countryCode);

			$country_flag = '<img class="flag_image_small" src="' . $this->ext_path_web . 'images/flags/small/' .  $iso_country_code . '.png" alt="' . $this->user->lang['country'][strtoupper($iso_country_code)] . '" title="' . $this->user->lang['country'][strtoupper($iso_country_code)] . '" />';
		}
		else if (($json_response->status) == 'fail')
		{
			/** WO represents my flag of World, aka Unknown IP */
			$failure = 'WO';
			$iso_country_code =  strtolower($failure);

			$country_flag = '<img class="flag_image_small" src="' . $this->ext_path_web . 'images/flags/small/' .  $iso_country_code . '.png" alt="' . $this->user->lang['country'][strtoupper($iso_country_code)] . '" title="' . $this->user->lang['country'][strtoupper($iso_country_code)] . '" />';
		}
		else
		{
			/** WO represents my flag of World, aka Unknown IP */
			$failure = 'WO';
			$iso_country_code =  strtolower($failure);

			$country_flag = '<img class="flag_image_small" src="' . $this->ext_path_web . 'images/flags/small/' .  $iso_country_code . '.png" alt="' . $this->user->lang['country'][strtoupper($iso_country_code)] . '" title="' . $this->user->lang['country'][strtoupper($iso_country_code)] . '" />';
		}

		return ($country_flag);
	}
}
