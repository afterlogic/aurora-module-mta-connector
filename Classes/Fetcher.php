<?php

/* -AFTERLOGIC LICENSE HEADER- */

/**
 * @property int $IdUser
 * @property int $IdAccount
 * 
 * @property bool $IsEnabled
 * @property string $IncomingServer
 * @property int $IncomingPort
 * @property int $IncomingMailSecurity
 * @property string $IncomingLogin
 * @property string $IncomingPassword
 * @property bool $LeaveMessagesOnServer
 * @property string $Folder
 * 
 * @property bool $IsOutgoingEnabled
 * @property string $Name
 * @property string $Email
 * @property string $OutgoingServer
 * @property int $OutgoingPort
 * @property int $OutgoingMailSecurity
 * @property bool $OutgoingUseAuth
 * 
 * @property bool $UseSignature
 * @property string $Signature
 * 
 * @property bool $IsLocked
 * @property int $CheckInterval
 * @property int $CheckLastTime
 *
 * @package Users
 * @subpackage Classes
 */

namespace Aurora\Modules\MailSuite\Classes;

class Fetcher extends \Aurora\System\EAV\Entity
{
	protected $aStaticMap = array(
		'IdUser'				=> array('int', 0, true),
		'IdAccount'				=> array('int', 0, true),

		'IsEnabled'				=> array('bool', true),
		'IncomingServer'		=> array('string', ''),
		'IncomingPort'			=> array('int', 110),
		'IncomingMailSecurity'	=> array('int', \MailSo\Net\Enumerations\ConnectionSecurityType::NONE),
		'IncomingLogin'			=> array('string', ''),
		'IncomingPassword'		=> array('encrypted', ''),
		'LeaveMessagesOnServer' => array('bool', true),
		'Folder'				=> array('string', ''),
		
		'IsOutgoingEnabled'		=> array('bool', false),
		'Name'					=> array('string', ''),
		'Email'					=> array('string', ''),
		'OutgoingServer'		=> array('string', ''),
		'OutgoingPort'			=> array('int', 25),
		'OutgoingMailSecurity'	=> array('int', \MailSo\Net\Enumerations\ConnectionSecurityType::NONE),
		'OutgoingUseAuth'		=> array('bool', true),
		
		'UseSignature'			=> array('bool', false),
		'Signature'				=> array('string', ''),

		'IsLocked'				=> array('bool', false),
		'CheckInterval'			=> array('int', 0),
		'CheckLastTime'			=> array('int', 0),
	);
	
	public function toResponseArray()
	{
		$aResponse = parent::toResponseArray();
		$aResponse['IncomingUseSsl'] = $aResponse['IncomingMailSecurity'] === \MailSo\Net\Enumerations\ConnectionSecurityType::SSL;
		unset($aResponse['IncomingMailSecurity']);
		$aResponse['OutgoingUseSsl'] = $aResponse['OutgoingMailSecurity'] === \MailSo\Net\Enumerations\ConnectionSecurityType::SSL;
		unset($aResponse['OutgoingMailSecurity']);
		unset($aResponse['IncomingPassword']);
		return $aResponse;
	}
}
