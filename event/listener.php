<?php
/**
*
* @package phpBB Extension - IPCF 1.0.0 -(IP Country Flag)
* @copyright (c) 2005 - 2008 - 2016 3Di (Marco T.)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace threedi\ipcf\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\template\template */
	protected $template;

	/* @var \threedi\ipcf\core\ipcf_functions */
	protected $ipcf_functions;

	/**
		* Constructor
		*
		* @param \phpbb\auth\auth					$auth				Authentication object
		* @param \phpbb\cache\service				$cache
		* @param \phpbb\config\config				$config				Config Object
		* @param \phpbb\user						$user				User Object
		* @param \phpbb\db\driver\driver			$db					Database object
		* @param \phpbb\template\template			$template			Template object
		* @param \threedi\ipcf\core\ipcf_functions	$ipcf_functions		Methods to be used by Class
		* @access public
	*/
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\service $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user,
		\phpbb\template\template $template,
		\threedi\ipcf\core\ipcf_functions $ipcf_functions)
	{
		$this->auth				=	$auth;
		$this->cache			=	$cache;
		$this->config			=	$config;
		$this->db				=	$db;
		$this->user				=	$user;
		$this->template			=	$template;
		$this->ipcf_functions	=	$ipcf_functions;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'					=>	'load_language_on_setup',
			'core.viewtopic_modify_post_row'	=>	'viewtopic_flags',
			'core.permissions'					=>	'permissions',
		);
	}

	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'threedi/ipcf',
			'lang_set' => 'ipcf',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/* Permission's language file is automatically loaded */
	public function permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions += array(
			'u_allow_ipcf' => array(
				'lang'	=> 'ACL_U_ALLOW_IPCF',
				'cat'	=> 'misc'
			),
		);
		$event['permissions'] = $permissions;
	}

	public function viewtopic_flags($event)
	{
		$user_id = $event['post_row']['POSTER_ID'];

		/* Config time for cache, hinerits from View online time span */
		$config_time_cache = ( (int) ($this->config['load_online_time'] * 60) );

		/* Check cached data */
		if ( ($row = $this->cache->get('_ipcf_viewtopic') ) === false)
		{
			/* Self-explanatory, isn't? */
			$user_session_ip = ( (string) $this->ipcf_functions->obtain_session_ip($user_id) );

			/* Caching this data improves performance */
			$this->cache->put('_ipcf_viewtopic', $row, (int) $config_time_cache);
		}

		/* The Flag Image itself lies here */
		$country_flag = ( $this->ipcf_functions->obtain_country_flag_string($user_session_ip) );

		$template_output = array('COUNTRY_FLAG'	=>	$country_flag);

		$event['post_row'] = array_merge($event['post_row'], $template_output);

		// template stuffs, as usual
		$this->template->assign_vars(array(
			'S_IPCF'	=>	($this->auth->acl_get('u_allow_ipcf')) ? true : false,
		));
	}
}
