<?php
/**
* @version   1.0.0
* @package   plg_user_hibp
* @author    Simon Champion
* @copyright Simon Champion
* @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;

/**
 * Plugin class for the HIBP PLugin
 *
 * @since  1.0.0
 */
class plgUserHibp extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * The API URL for the HIBP API Call
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	const ROOT_API = 'https://api.pwnedpasswords.com/range/';

	/**
	 * Method is called before user data is stored in the database.
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isNew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return  boolean  True in case the password passed the checks exception in case the password is compromised
	 *
	 * @since   1.0.0
	 * @throws  RuntimeException If password is invalid
	 */
	public function onUserBeforeSave($user, $isnew, $data)
	{
		if (!isset($data['password_clear']) || !$data['password_clear'])
		{
			return true;
		}

		if ($this->isPwned(sha1($data['password_clear'])))
		{
			throw new RuntimeException(Text::_('PLG_USER_HIBP_PASSWORD_KNOWN_TO_BE_COMPROMISED'));
		}

		return true;
	}

	/**
	 * Method checks the first five characters against the HIBP API
	 *
	 * @param   string  $sha1Hash   Holds the password hash we should check whether it is pwned or not.
	 *
	 * @return  boolean  True in case the password passed the checks false in case the password is compromised
	 *
	 * @since   1.0.0
	 * @throws  RuntimeException if password is invalid
	 */
	public function isPwned($sha1Hash)
	{
		$firstFive = substr($sha1Hash, 0, 5);

		// The HIBP api returns upper case; make this upper too so that it matches.
		$remainder = strtoupper(substr($sha1Hash, 5));

		// Build the HttpFactory Object
		$http = HttpFactory::getHttp();
		$http->setOption('user-agent', 'sc-hibp-plugin-for-Joomla');
		$response = $http->get(self::ROOT_API . $firstFive);

		// We should never get a response other than 200, but if we do, then API is not working properly, so we'll ignore anything it says.
		if ($response->code !== 200)
		{
			return false;
		}

		$matches = explode("\n", $response->body);

		foreach ($matches as $match)
		{
			list($hashMatch, $matchCount) = explode(':', $match);

			if ($hashMatch === $remainder)
			{
				// Password matches a known compromised password. Oh dear. I think we need to tell the user about that.
				// But maybe not, if it's only been compromised a few times, eh?
				return ($matchCount > (int) $this->params->get('max_pwnage'));
			}
		}

		// No match, so password isn't pwned. Phew.
		return false;
	}
}
