<?php
/**
* @version   1.0.2
* @package   plg_user_hibp
* @author    Simon Champion
* @copyright Simon Champion
* @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerScript;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  1.0.2
 */
class plgUserHibpInstallerScript extends InstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   1.0.2
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '3.8';
		$this->minimumPhp    = '5.6';
		
		$this->deleteFiles = array(
			'/administrator/language/en-GB/en-GB.plg_user_hibp.ini',
			'/administrator/language/en-GB/en-GB.plg_user_hibp.sys.ini',
		);
	}

	/**
	 * Function to perform changes during postflight
	 *
	 * @param   string            $type    The action being performed
	 * @param   ComponentAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   1.0.2
	 */
	public function postflight($type, $parent)
	{
		$this->removeFiles();
	}
}