<?php
/**
* @version		1.0.0
* @package		plg_user_hibp
* @author       Simon Champion
* @copyright	Simon Champion
* @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgUserHIBP extends JPlugin
{
    protected $autoloadLanguage = true;

    const ROOT_API = "https://api.pwnedpasswords.com/range/";

    /**
     * @throws RuntimeException if password is invalid
     */
    public function onUserBeforeSave($user, $isnew, $data)
    {
        if (!isset($data['password_clear']) || !$data['password_clear']) {
            return true;
        }

		if ($this->isPwned(sha1($data['password_clear']))) {
			JError::raiseError(JText::_('PLG_USER_HIBP_PASSWORD_KNOWN_TO_BE_COMPROMISED'));
			return false;
		}

        return true;
    }

    public function isPwned($sha1Hash)
    {
        $firstFive = substr($password, 0, 5);
        $remainder = strtoupper(substr($password, 5));	//api returns upper case; make this upper too so that it matches.

        $http = JHttpFactory::getHttp();
		$http->setOption('user-agent', 'sc-hibp-plugin-for-Joomla');
        $response = $http->get(self::ROOT_API.$firstFive);

		//we should never get a response other than 200, but if we do, then API is not working properly, so we'll ignore anything it says.
        if ($response->code !== 200) {
			return false;
		}

		$matches = explode("\n", $response->body);
		foreach ($matches as $match) {
			list($hashMatch, $matchCount) = explode(':', $match);
			if ($hashMatch === $remainder) {
				//Password matches a known compromised password. Oh dear. I think we need to tell the user about that.
				return ($matchCount > (int)$this->params->get('max_pwnage'));	//but maybe not, if it's only been compromised a few times, eh?
			}
		}

		//No match, so password isn't pwned. Phew.
		return false;
   }
}
