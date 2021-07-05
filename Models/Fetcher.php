<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Models;

class Fetcher extends \Aurora\System\Classes\Model
{
	protected $fillable = [
		'IdUser',
		'IdAccount',

		'IsEnabled',
		'IncomingServer',
		'IncomingPort',
		'IncomingMailSecurity',
		'IncomingLogin',
		'IncomingPassword',
		'LeaveMessagesOnServer',
		'Folder',
		
		'IsOutgoingEnabled',
		'Name',
		'Email',
		'OutgoingServer',
		'OutgoingPort',
		'OutgoingMailSecurity',
		'OutgoingUseAuth',
		
		'UseSignature',
		'Signature',

		'IsLocked',
		'CheckInterval',
		'CheckLastTime'
	];
	
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