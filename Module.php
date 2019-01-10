<?php
/**
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	const QUOTA_KILO_MULTIPLIER = 1024;

	public $oApiMainManager = null;

	/* 
	 * @var $oApiFetchersManager Managers\Fetchers
	 */
	public $oApiFetchersManager = null;
			
	/* 
	 * @var $oApiAliasesManager Managers\Aliases
	 */
	public $oApiAliasesManager = null;
			
	/* 
	 * @var $oApiMailingListsManager Managers\MailingLists
	 */
	public $oApiMailingListsManager = null;
			
	/* 
	 * @var $oApiDomainsManager Managers\MailingLists
	 */
	public $oApiDomainsManager = null;
			
	public function init()
	{
		$this->aErrors = [
			Enums\ErrorCodes::DomainExists	=> $this->i18N('ERROR_CONNECT_TO_MAIL_SERVER')
		];

		$this->subscribeEvent('AdminPanelWebclient::CreateUser::after', array($this, 'onAfterCreateUser'));
		$this->subscribeEvent('Core::CreateTables::after', array($this, 'onAfterCreateTables'));
		$this->subscribeEvent('Mail::SaveMessage::before', array($this, 'onBeforeSendOrSaveMessage'));
		$this->subscribeEvent('Mail::SendMessage::before', array($this, 'onBeforeSendOrSaveMessage'));
		$this->subscribeEvent('AdminPanelWebclient::GetEntityList::before', array($this, 'onBeforeGetEntityList'));
		$this->subscribeEvent('Mail::ServerToResponseArray', array($this, 'onServerToResponseArray'));
		$this->subscribeEvent('Core::AfterDeleteUser', array($this, 'onAfterDeleteUser'));
		$this->subscribeEvent('Core::GetEntityList::after', array($this, 'onAfterGetEntityList'));
		$this->subscribeEvent('Core::DeleteTenant::after', array($this, 'onAfterDeleteTenant'));
		$this->subscribeEvent('AdminPanelWebclient::UpdateEntity::after', array($this, 'onAfterUpdateEntity'));
		$this->subscribeEvent('Files::GetQuota::after', array($this, 'onAfterGetQuotaFiles'), 110);
		$this->subscribeEvent('Mail::GetQuota::before', array($this, 'onBeforeGetQuotaMail'), 110);
		$this->subscribeEvent('Mail::ChangePassword::before', array($this, 'onBeforeChangePassword'));
		$this->subscribeEvent('MailSignup::Signup::before', array($this, 'onAfterSignup'));

		$this->oApiMainManager = new Managers\Main\Manager($this);
		$this->oApiFetchersManager = new Managers\Fetchers\Manager($this);
		$this->oApiAliasesManager = new Managers\Aliases\Manager($this);
		$this->oApiMailingListsManager = new Managers\MailingLists\Manager($this);
		$this->oApiDomainsManager = new Managers\Domains\Manager($this);

		\Aurora\Modules\Mail\Classes\Server::extend(
			self::GetName(),
			[
				'Native' => array('bool', false),
			]
		);		
		\Aurora\Modules\Core\Classes\User::extend(
			self::GetName(),
			[
				'DomainId' => array('int', 0),
				'TotalQuotaBytes' => array('bigint', 1),//bytes
			]
		);		
	}

	/***** public functions might be called with web API *****/
	/**
	 * @apiDefine MtaConnector MtaConnector Module
	 * MtaConnector module. It provides PHP and Web APIs for managing fetchers and other MtaConnector features.
	 */
	
	/**
	 * @api {post} ?/Api/ GetSettings
	 * @apiName GetSettings
	 * @apiGroup MtaConnector
	 * @apiDescription Obtains list of module settings for authenticated user.
	 * 
	 * @apiHeader {string} [Authorization] "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=GetSettings} Method Method name
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'GetSettings'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {mixed} Result.Result List of module settings in case of success, otherwise **false**.
	 * 
	 * @apiSuccess {boolean} Result.Result.AllowFetchers=false Indicates if fetchers are allowed.
	 * 
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'GetSettings',
	 *	Result: { AllowFetchers: false }
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'GetSettings',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Obtains list of module settings for authenticated user.
	 * @return array
	 */
	public function GetSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);
		
		return array(
			'AllowFetchers' => $this->getConfig('AllowFetchers', false),
			'UserDefaultQuotaMB' => $this->getConfig('UserDefaultQuotaMB', false)
		);
	}
	
	/**
	 * @api {post} ?/Api/ GetFetchers
	 * @apiName GetFetchers
	 * @apiGroup MtaConnector
	 * @apiDescription Obtains all fetchers of specified user.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=GetServers} Method Method name
	 * @apiParam {string} [Parameters] JSON.stringified object<br>
	 * {<br>
	 * &emsp; **UserId** *int* (optional) User identifier.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'GetFetchers'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {mixed} Result.Result List fetchers in case of success, otherwise **false**.
	 * @apiSuccess {int} Result.Result.EntityId Fetcher identifier.
	 * @apiSuccess {string} Result.Result.UUID Fetcher UUID.
	 * @apiSuccess {int} Result.Result.IdUser User identifier.
	 * @apiSuccess {int} Result.Result.IdAccount Identifier of account owns fetcher.
	 * @apiSuccess {boolean} Result.Result.IsEnabled Indicates if fetcher is enabled.
	 * @apiSuccess {string} Result.Result.IncomingServer POP3 server.
	 * @apiSuccess {int} Result.Result.IncomingPort Port of POP3 server.
	 * @apiSuccess {string} Result.Result.IncomingLogin Fetcher account login.
	 * @apiSuccess {boolean} Result.Result.LeaveMessagesOnServer Indicates if messages shouldn't be removed from POP3 server during fetching.
	 * @apiSuccess {string} Result.Result.Folder Where to store emails fetched from POP3 server.
	 * @apiSuccess {boolean} Result.Result.IsOutgoingEnabled Indicates if send message is allowed from this fetcher.
	 * @apiSuccess {string} Result.Result.Name Value of fetcher friendly name.
	 * @apiSuccess {string} Result.Result.Email Value of fetcher email.
	 * @apiSuccess {string} Result.Result.OutgoingServer SMTP server.
	 * @apiSuccess {int} Result.Result.OutgoingPort Port of SMTP server.
	 * @apiSuccess {boolean} Result.Result.OutgoingUseAuth Indicates if SMTP connect should be authenticated.
	 * @apiSuccess {boolean} Result.Result.UseSignature Indicates if signature should be used in outgoing mails.
	 * @apiSuccess {string} Result.Result.Signature Fetcher signature.
	 * @apiSuccess {boolean} Result.Result.IsLocked
	 * @apiSuccess {int} Result.Result.CheckInterval
	 * @apiSuccess {int} Result.Result.CheckLastTime
	 * @apiSuccess {boolean} Result.Result.IncomingUseSsl Indicates if SSL should be used on POP3 server.
	 * @apiSuccess {boolean} Result.Result.OutgoingUseSsl Indicates if SSL should be used on SMTP server.
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'GetFetchers',
	 *	Result: [ { "EntityId": 14, "UUID": "uuid_value", "IdUser": 3, "IdAccount": 12,
	 *				"IsEnabled": true, "IncomingServer": "pop.server.com", "IncomingPort": 995,
	 *				"IncomingLogin": "login_value", "LeaveMessagesOnServer": true, "Folder": "fetch_folder_value",
	 *				"IsOutgoingEnabled": true, "Name": "", "Email": "email_value@server.com",
	 *				"OutgoingServer": "smtp.server.com", "OutgoingPort": 465, "OutgoingUseAuth": true,
	 *				"UseSignature": false, "Signature": "", "IsLocked": false, "CheckInterval": 0,
	 *				"CheckLastTime": 0, "IncomingUseSsl": true, "OutgoingUseSsl": true },
	 *			  ... ]
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'GetFetchers',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Obtains all fetchers of specified user.
	 * @param int $UserId User identifier.
	 * @return array|false
	 */
	public function GetFetchers($UserId)
	{
		$mResult = false;
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		//Only owner or SuperAdmin can get fetchers
		if ($oUser && $oUser instanceof \Aurora\Modules\Core\Classes\User
			&& $this->getConfig('AllowFetchers', false)
			&& (
				($oUser->Role === \Aurora\System\Enums\UserRole::NormalUser
					&& $oUser->EntityId === $UserId)
				|| $oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin
			)
		)
		{
			$mResult = $this->oApiFetchersManager->getFetchers($UserId);
		}

		return $mResult;
	}
	
	/**
	 * @api {post} ?/Api/ CreateFetcher
	 * @apiName CreateFetcher
	 * @apiGroup MtaConnector
	 * @apiDescription Creates fetcher.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=CreateFetcher} Method Method name
	 * @apiParam {string} Parameters JSON.stringified object<br>
	 * {<br>
	 * &emsp; **AccountId** *int* Account identifier.<br>
	 * &emsp; **Folder** *string* Where to store emails fetched from POP3 server.<br>
	 * &emsp; **IncomingLogin** *string* Fetcher account login.<br>
	 * &emsp; **IncomingPassword** *string* Fetcher account password.<br>
	 * &emsp; **IncomingServer** *string* POP3 server.<br>
	 * &emsp; **IncomingPort** *int* Port of POP3 server.<br>
	 * &emsp; **IncomingUseSsl** *boolean* Indicates if SSL should be used.<br>
	 * &emsp; **LeaveMessagesOnServer** *boolean* Indicates if messages shouldn't be removed from POP3 server during fetching.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'CreateFetcher',
	 *	Parameters: '{	"AccountId": 12, "Folder": "fetch_folder_value", "IncomingLogin": "login_value",
	 *					"IncomingPassword": "pass_value", "IncomingServer": "pop.server.com",
	 *					"IncomingPort": 110, "IncomingUseSsl": false, "LeaveMessagesOnServer": true }'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {mixed} Result.Result Identifier of created fetcher in case of success, otherwise **false**.
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'CreateFetcher',
	 *	Result: 14
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'CreateFetcher',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Creates fetcher.
	 * @param int $UserId User identifier.
	 * @param int $AccountId Account identifier.
	 * @param string $Folder Where to store emails fetched from POP3 server.
	 * @param string $IncomingLogin Fetcher account login.
	 * @param string $IncomingPassword Fetcher account password.
	 * @param string $IncomingServer POP3 server.
	 * @param int $IncomingPort Port of POP3 server.
	 * @param boolean $IncomingUseSsl Indicates if SSL should be used.
	 * @param boolean $LeaveMessagesOnServer Indicates if messages shouldn't be removed from POP3 server during fetching.
	 * @return int|boolean
	 */
	public function CreateFetcher($UserId, $AccountId, $Folder, $IncomingLogin, $IncomingPassword,
			$IncomingServer, $IncomingPort, $IncomingUseSsl, $LeaveMessagesOnServer)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		if ($this->getConfig('AllowFetchers', false))
		{
			$oFetcher = new \Aurora\Modules\MtaConnector\Classes\Fetcher(self::GetName());
			$oFetcher->IdUser = $UserId;
			$oFetcher->IdAccount = $AccountId;

			$oFetcher->IncomingServer = $IncomingServer;
			$oFetcher->IncomingPort = $IncomingPort;
			$oFetcher->IncomingLogin = $IncomingLogin;
			$oFetcher->IncomingPassword = $IncomingPassword;
			$oFetcher->IncomingMailSecurity = $IncomingUseSsl ? \MailSo\Net\Enumerations\ConnectionSecurityType::SSL : \MailSo\Net\Enumerations\ConnectionSecurityType::NONE;
			$oFetcher->LeaveMessagesOnServer = $LeaveMessagesOnServer;
			$oFetcher->Folder = $Folder;
			
			$oFetcher->CheckInterval = $this->getConfig('FetchersIntervalMinutes', 20);

			return $this->oApiFetchersManager->createFetcher($oFetcher);
		}
		
		return false;
	}
	
	/**
	 * @api {post} ?/Api/ UpdateFetcher
	 * @apiName UpdateFetcher
	 * @apiGroup MtaConnector
	 * @apiDescription Updates fetcher.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=UpdateFetcher} Method Method name
	 * @apiParam {string} Parameters JSON.stringified object<br>
	 * {<br>
	 * &emsp; **FetcherId** *int* Fetcher identifier.<br>
	 * &emsp; **IsEnabled** *boolean* Indicates if fetcher is enabled.<br>
	 * &emsp; **Folder** *string* Where to store emails fetched from POP3 server.<br>
	 * &emsp; **IncomingServer** *string* POP3 server.<br>
	 * &emsp; **IncomingPort** *int* Port of POP3 server.<br>
	 * &emsp; **IncomingUseSsl** *boolean* Indicates if SSL should be used.<br>
	 * &emsp; **LeaveMessagesOnServer** *boolean* Indicates if messages shouldn't be removed from POP3 server during fetching.<br>
	 * &emsp; **IncomingPassword** *string* Fetcher account password.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateFetcher',
	 *	Parameters: '{ "FetcherId": 14, "IsEnabled": true, "Folder": "fetch_folder_name",
	 *		"IncomingServer": "pop.server.com", "IncomingPort": 110, "IncomingUseSsl": false,
	 *		"LeaveMessagesOnServer": true, "IncomingPassword": "pass_value" }'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {boolean} Result.Result Indicates if fetcher was updated successfully.
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateFetcher',
	 *	Result: true
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateFetcher',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Updates fetcher.
	 * @param int $UserId User identifier.
	 * @param int $FetcherId Fetcher identifier.
	 * @param boolean $IsEnabled Indicates if fetcher is enabled.
	 * @param string $Folder Where to store emails fetched from POP3 server.
	 * @param string $IncomingServer POP3 server.
	 * @param int $IncomingPort Port of POP3 server.
	 * @param boolean $IncomingUseSsl Indicates if SSL should be used.
	 * @param boolean $LeaveMessagesOnServer Indicates if messages shouldn't be removed from POP3 server during fetching.
	 * @param string $IncomingPassword Fetcher account password.
	 * @return boolean
	 */
	public function UpdateFetcher($UserId, $FetcherId, $IsEnabled, $Folder, $IncomingServer, $IncomingPort,
			$IncomingUseSsl, $LeaveMessagesOnServer, $IncomingPassword = null)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		if ($this->getConfig('AllowFetchers', false))
		{
			$oFetcher = $this->oApiFetchersManager->getFetcher($FetcherId);
			if ($oFetcher && $oFetcher->IdUser === $UserId)
			{
				$oFetcher->IsEnabled = $IsEnabled;
				$oFetcher->IncomingServer = $IncomingServer;
				$oFetcher->IncomingPort = $IncomingPort;
				if (isset($IncomingPassword))
				{
					$oFetcher->IncomingPassword = $IncomingPassword;
				}
				$oFetcher->IncomingMailSecurity = $IncomingUseSsl ? \MailSo\Net\Enumerations\ConnectionSecurityType::SSL : \MailSo\Net\Enumerations\ConnectionSecurityType::NONE;
				$oFetcher->LeaveMessagesOnServer = $LeaveMessagesOnServer;
				$oFetcher->Folder = $Folder;

				return $this->oApiFetchersManager->updateFetcher($oFetcher, true);
			}
		}
		
		return false;
	}
	
	/**
	 * @api {post} ?/Api/ UpdateFetcherSmtpSettings
	 * @apiName UpdateFetcherSmtpSettings
	 * @apiGroup MtaConnector
	 * @apiDescription Updates fetcher SMTP settings.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=UpdateFetcherSmtpSettings} Method Method name
	 * @apiParam {string} Parameters JSON.stringified object<br>
	 * {<br>
	 * &emsp; **FetcherId** *int* Fetcher identifier.<br>
	 * &emsp; **Email** *string* New value of fetcher email.<br>
	 * &emsp; **Name** *string* New value of fetcher friendly name.<br>
	 * &emsp; **IsOutgoingEnabled** *boolean* Indicates if send message is allowed from this fetcher.<br>
	 * &emsp; **OutgoingServer** *string* SMTP server.<br>
	 * &emsp; **OutgoingPort** *int* Port of SMTP server.<br>
	 * &emsp; **OutgoingUseSsl** *boolean* Indicates if SSL should be used.<br>
	 * &emsp; **OutgoingUseAuth** *boolean* Indicates if SMTP connect should be authenticated.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateFetcherSmtpSettings',
	 *	Parameters: '{ "FetcherId": 14, "Email": "test@email", "Name": "New my name",
	 *		"IsOutgoingEnabled": true, "OutgoingServer": "smtp.server.com", "OutgoingPort": 25,
	 *		"OutgoingUseSsl": false, "OutgoingUseAuth": false }'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {boolean} Result.Result Indicates if fetcher was updated successfully.
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateFetcherSmtpSettings',
	 *	Result: true
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateFetcherSmtpSettings',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Updates fetcher.
	 * @param int $UserId User identifier.
	 * @param int $FetcherId Fetcher identifier.
	 * @param string $Email New value of fetcher email.
	 * @param string $Name New value of fetcher friendly name.
	 * @param boolean $IsOutgoingEnabled Indicates if send message is allowed from this fetcher.
	 * @param string $OutgoingServer SMTP server.
	 * @param int $OutgoingPort Port of SMTP server.
	 * @param boolean $OutgoingUseSsl Indicates if SSL should be used.
	 * @param boolean $OutgoingUseAuth Indicates if SMTP connect should be authenticated.
	 * @return boolean
	 */
	public function UpdateFetcherSmtpSettings($UserId, $FetcherId, $Email, $Name, $IsOutgoingEnabled,
			$OutgoingServer, $OutgoingPort, $OutgoingUseSsl, $OutgoingUseAuth)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		if ($this->getConfig('AllowFetchers', false))
		{
			$oFetcher = $this->oApiFetchersManager->getFetcher($FetcherId);
			if ($oFetcher && $oFetcher->IdUser === $UserId)
			{
				$oFetcher->IsOutgoingEnabled = $IsOutgoingEnabled;
				$oFetcher->Name = $Name;
				$oFetcher->Email = $Email;
				$oFetcher->OutgoingServer = $OutgoingServer;
				$oFetcher->OutgoingPort = $OutgoingPort;
				$oFetcher->OutgoingMailSecurity = $OutgoingUseSsl ? \MailSo\Net\Enumerations\ConnectionSecurityType::SSL : \MailSo\Net\Enumerations\ConnectionSecurityType::NONE;
				$oFetcher->OutgoingUseAuth = $OutgoingUseAuth;

				return $this->oApiFetchersManager->updateFetcher($oFetcher, false);
			}
		}
		
		return false;
	}
	
	/**
	 * @api {post} ?/Api/ UpdateSignature
	 * @apiName UpdateSignature
	 * @apiGroup MtaConnector
	 * @apiDescription Updates fetcher signature.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=UpdateSignature} Method Method name
	 * @apiParam {string} Parameters JSON.stringified object<br>
	 * {<br>
	 * &emsp; **FetcherId** *int* Fetcher identifier.<br>
	 * &emsp; **UseSignature** *boolean* Indicates if signature should be used in outgoing mails.<br>
	 * &emsp; **Signature** *string* Fetcher signature.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateSignature',
	 *	Parameters: '{ "FetcherId": 14, "UseSignature": true, "Signature": "signature_value" }'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {boolean} Result.Result Indicates if signature was updated successfully.
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateSignature',
	 *	Result: true
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'UpdateSignature',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Updates fetcher signature.
	 * @param int $UserId User identifier.
	 * @param int $FetcherId Fetcher identifier.
	 * @param boolean $UseSignature Indicates if signature should be used in outgoing mails.
	 * @param string $Signature Fetcher signature.
	 * @return boolean
	 * @throws \Aurora\System\Exceptions\ApiException
	 */
	public function UpdateSignature($UserId, $FetcherId = null, $UseSignature = null, $Signature = null)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		if ($this->getConfig('AllowFetchers', false))
		{
			$oFetcher = $this->oApiFetchersManager->getFetcher($FetcherId);
			if ($oFetcher && $oFetcher->IdUser === $UserId)
			{
				$oFetcher->UseSignature = $UseSignature;
				$oFetcher->Signature = $Signature;
				return $this->oApiFetchersManager->updateFetcher($oFetcher, false);
			}
		}

		return false;
	}
	
	/**
	 * @api {post} ?/Api/ DeleteFetcher
	 * @apiName DeleteFetcher
	 * @apiGroup MtaConnector
	 * @apiDescription Deletes fetcher.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=MtaConnector} Module Module name
	 * @apiParam {string=DeleteFetcher} Method Method name
	 * @apiParam {string} Parameters JSON.stringified object<br>
	 * {<br>
	 * &emsp; **FetcherId** *int* Fetcher identifier.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'DeleteFetcher',
	 *	Parameters: '{ "FetcherId": 14 }'
	 * }
	 * 
	 * @apiSuccess {object[]} Result Array of response objects.
	 * @apiSuccess {string} Result.Module Module name.
	 * @apiSuccess {string} Result.Method Method name.
	 * @apiSuccess {boolean} Result.Result Indicates if fetcher was deleted successfully.
	 * @apiSuccess {int} [Result.ErrorCode] Error code
	 * 
	 * @apiSuccessExample {json} Success response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'DeleteFetcher',
	 *	Result: true
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'MtaConnector',
	 *	Method: 'DeleteFetcher',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Deletes fetcher.
	 * @param int $FetcherId Fetcher identifier.
	 * @return boolean
	 */
	public function DeleteFetcher($UserId, $FetcherId)
	{
		$mResult = false;
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		//Only owner or SuperAdmin can delete fetcher
		if ($oUser instanceof \Aurora\Modules\Core\Classes\User &&
			$this->getConfig('AllowFetchers', false) &&
			(
				$oUser->Role === \Aurora\System\Enums\UserRole::NormalUser &&
				$oUser->EntityId === $UserId
			) ||
			$oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin
		)
		{
			$oFetcher = $this->oApiFetchersManager->getFetcher($FetcherId);
			if ($oFetcher && $oFetcher->IdUser === $UserId)
			{
				$mResult = $this->oApiFetchersManager->deleteFetcher($FetcherId);
			}
		}

		return $mResult;
	}
	
	/**
	 * Obtains all aliases for specified user.
	 * @param int $UserId User identifier.
	 * @return array|boolean
	 */
	public function GetAliases($UserId)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$oUser = \Aurora\System\Api::GetModuleDecorator('Core')->GetUser($UserId);
		$oAccount = \Aurora\System\Api::GetModuleDecorator('Mail')->GetAccountByEmail($oUser->PublicId);
		if ($oAccount)
		{
			$sDomain = preg_match('/.+@(.+)$/',  $oAccount->Email, $aMatches) && $aMatches[1] ? $aMatches[1] : '';
			$aAliases = $this->oApiAliasesManager->getAliases($oAccount->EntityId);
			return [
				'Domain' => $sDomain,
				'Aliases' => $aAliases
			];
		}
		
		return false;
	}
	
	/**
	 * Creates new alias with specified name and domain.
	 * @param int $UserId User identifier.
	 * @param string $AliasName Alias name.
	 * @param string $AliasDomain Alias domain.
	 * @return boolean
	 */
	public function AddNewAlias($UserId, $AliasName, $AliasDomain)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$CoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
		$oUser = $CoreDecorator ? $CoreDecorator->GetUser($UserId) : null;
		$oMailDecorator = \Aurora\System\Api::GetModuleDecorator('Mail');
		$oAccount = $oUser && $oMailDecorator ? $oMailDecorator->GetAccountByEmail($oUser->PublicId) : null;
		if ($oAccount)
		{
			return $this->oApiAliasesManager->addAlias($oAccount->EntityId, $AliasName, $AliasDomain, $oAccount->Email);
		}
		
		return false;
	}
	
	/**
	 * Deletes aliases with specified emails.
	 * @param int $UserId User identifier
	 * @param array $Aliases Aliases emails.
	 * @return boolean
	 */
	public function DeleteAlias($UserId, $Aliases)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$mResult = false;
		$CoreDecorator = \Aurora\System\Api::GetModuleDecorator('Core');
		$oUser = $CoreDecorator ? $CoreDecorator->GetUser($UserId) : null;
		$oMailDecorator = \Aurora\System\Api::GetModuleDecorator('Mail');
		$oAccount = $oUser && $oMailDecorator ? $oMailDecorator->GetAccountByEmail($oUser->PublicId) : null;
		if ($oAccount)
		{
			foreach ($Aliases as $sAlias)
			{
				preg_match('/(.+)@(.+)$/',  $sAlias, $aMatches);
				$AliasName = isset($aMatches[1]) ? $aMatches[1] : '';
				$AliasDomain = isset($aMatches[2]) ? $aMatches[2] : '';
				if ($this->oApiAliasesManager->deleteAlias($oAccount->EntityId, $AliasName, $AliasDomain))
				{
					$mResult = true;
				}
			}
		}
		
		return $mResult;
	}
	
	/**
	 * Creates mailing list.
	 * @param int $DomainId Domain identifier.
	 * @param string $Email Email of mailing list.
	 * @return boolean
	 */
	public function CreateMailingList($DomainId = 0, $Email = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		if ($TenantId === 0)
		{
			$TenantId = $this->getSingleDefaultTenantId();
		}
		return $this->oApiMailingListsManager->createMailingList($DomainId, $Email);
	}
	
	/**
	 * Obtains all mailing lists for specified tenant.
	 * @param int $TenantId Tenant identifier.
	 * @param int $DomainId Domain identifier.
	 * @param string $Search Search.
	 * @param int $Offset Offset.
	 * @param int $Limit Limit.
	 * @return array|boolean
	 */
	public function GetMailingLists($TenantId = 0, $DomainId = 0, $Search = '', $Offset = 0, $Limit = 0)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		if ($TenantId === 0)
		{
			if ($DomainId !== 0)
			{
				$oDomain = $this->Decorator()->GetDomain($DomainId);
				if ($oDomain)
				{
					$TenantId = $oDomain->TenantId;
				}
			}
			if ($TenantId === 0)
			{
				$TenantId = $this->getSingleDefaultTenantId();
			}
		}
		$aMailingLists = $this->oApiMailingListsManager->getMailingLists($TenantId, $DomainId, $Search, $Offset, $Limit);
		if (is_array($aMailingLists))
		{
			return [
				'Count' => $this->oApiMailingListsManager->getMailingListsCount($TenantId, $DomainId, $Search),
				'Items' => $aMailingLists
			];
		}
		
		return false;
	}
	
	/**
	 * Deletes mailing lists.
	 * @param int $IdList List of mailing list identifiers.
	 * @return boolean
	 */
	public function DeleteMailingLists($IdList)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		$mResult = false;
		foreach ($IdList as $iListId)
		{
			$mResult = $this->oApiMailingListsManager->deleteMailingList($iListId);
		}
		return $mResult;
	}
	
	/**
	 * Obtains all mailing list members.
	 * @param int $Id Mailing list identifier.
	 * @return array|boolean
	 */
	public function GetMailingListMembers($Id)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		return $this->oApiMailingListsManager->getMailingListMembers($Id);
	}
	
	/**
	 * Adds new member to mailing list.
	 * @param int $ListId Mailing list identifier.
	 * @param string $ListTo Email of mailing list.
	 * @return boolean
	 */
	public function AddMailingListMember($ListId, $ListTo)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$sListName = $this->oApiMailingListsManager->getMailingListEmail($ListId);
		
		return $this->oApiMailingListsManager->addMember($ListId, $sListName, $ListTo);
	}
	
	/**
	 * Deletes member from mailing list.
	 * @param int $ListId Mailing list identifier.
	 * @param string $Members Emails of members.
	 * @return boolean
	 */
	public function DeleteMailingListMembers($ListId, $Members)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		$bResult = false;
		foreach ($Members as $sListName)
		{
			$bResult = $this->oApiMailingListsManager->deleteMember($ListId, $sListName);
		}
		return $bResult;
	}
	
	/**
	 * Creates domain.
	 * @param int $TenantId Tenant identifier.
	 * @param int $DomainName Domain name.
	 * @return boolean
	 */
	public function CreateDomain($TenantId = 0, $DomainName = '')
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		if ($DomainName === '')
		{
			throw new \Aurora\System\Exceptions\ApiException(\Aurora\System\Notifications::InvalidInputParameter);
		}
		
		if ($TenantId === 0)
		{
			$TenantId = $this->getSingleDefaultTenantId();
		}
		$mResult = $this->oApiDomainsManager->createDomain($TenantId, $DomainName);
		if ($mResult)
		{
			$oServer = \Aurora\System\Api::GetModule('Mail')->oApiServersManager->getServerByFilter([self::GetName() . '::Native' => true]);
			if ($oServer instanceof \Aurora\Modules\Mail\Classes\Server)
			{
				if ($oServer->Domains === '*')
				{
					$oServer->Domains = trim($DomainName);
				}
				else
				{
					$oServer->Domains .= ($oServer->Domains ? "\r\n" : '') . trim($DomainName);
				}
				\Aurora\System\Api::GetModule('Mail')->oApiServersManager->updateServer($oServer);
			}
			else
			{
				$mResult = false;
			}
		}

		return $mResult;
	}
	
	/**
	 * Obtains all domains for specified tenant.
	 * @param int $TenantId Tenant identifier.
	 * @return array|boolean
	 */
	public function GetDomains($TenantId = 0)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		if ($TenantId === 0)
		{
			$TenantId = $this->getSingleDefaultTenantId();
		}
		$aDomains = $this->oApiDomainsManager->getDomains($TenantId);
		if (is_array($aDomains))
		{
			return [
				'Count' => count($aDomains),
				'Items' => $aDomains
			];
		}
		
		return false;
	}
	
	/**
	 * Obtains domain.
	 * @param int $Id Domain identifier.
	 * @return array|boolean
	 */
	public function GetDomain($Id)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		
		return $this->oApiDomainsManager->getDomain($Id);
	}
	
	/**
	 * Deletes domains.
	 * @param int $IdList List of domain identifiers.
	 * @return boolean
	 */
	public function DeleteDomains($TenantId, $IdList)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::SuperAdmin);
		$mResult = false;
		foreach ($IdList as $iDomainId)
		{
			// deleteDomainMembers
			$aDomainMemberEmails = $this->oApiDomainsManager->getDomainMembers($iDomainId);
			$oDomain = $this->oApiDomainsManager->getDomain($iDomainId);
			foreach ($aDomainMemberEmails as $aMember)
			{
				if ($aMember['UserId'] > 0)
				{
					\Aurora\Modules\Core\Module::Decorator()->DeleteUser((int) $aMember['UserId']);
				}
			}
			$mResult = $this->oApiDomainsManager->deleteDomain($iDomainId);
			if ($mResult)
			{
				//remove domain from server domains list
				$oServer = \Aurora\System\Api::GetModule('Mail')->oApiServersManager->getServerByDomain($oDomain['Name']);
				if ($oServer instanceof \Aurora\Modules\Mail\Classes\Server)
				{
					$aDomainsNames = explode("\r\n", $oServer->Domains);
					$iIndex = array_search($oDomain['Name'], $aDomainsNames);
					if ($iIndex !== false)
					{
						array_splice($aDomainsNames, $iIndex, 1);
						$oServer->Domains = count($aDomainsNames) === 0 ? '*' : join("\r\n", $aDomainsNames);
						\Aurora\System\Api::GetModule('Mail')->oApiServersManager->updateServer($oServer);
					}
				}
				
				// remove mailing lists of removed domain
				$aMailingLists = $this->Decorator()->GetMailingLists($TenantId, $iDomainId);
				$aMailingListIds = [];
				if (isset($aMailingLists['Items']) && is_array($aMailingLists['Items']))
				{
					foreach ($aMailingLists['Items'] as $oMailingList)
					{
						$aMailingListIds[] = $oMailingList['Id'];
					}
				}
				if (count($aMailingListIds))
				{
					$this->Decorator()->DeleteMailingLists($aMailingListIds);
				}
			}
		}
		return $mResult;
	}
	
	/**
	 * 
	 * @param string $Email
	 * @param string $Password
	 * @param string $NewPassword
	 * @return bool
	 */
	public function UpdateAccountPassword($Email, $Password, $NewPassword)
	{
		$bResult = false;

		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		if ($oUser instanceof \Aurora\Modules\Core\Classes\User &&
			(($oUser->Role === \Aurora\System\Enums\UserRole::NormalUser && $oUser->PublicId === $Email) ||
			$oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin)
		)
		{
			$bResult = $this->oApiMainManager->updateAccountPassword($Email, $Password, $NewPassword);
		}

		return $bResult;

	}

	public function GetUserQuota($UserId)
	{
		$iResult = 0;
		$oUser = \Aurora\System\Api::getAuthenticatedUser();
		//Only owner or superadmin can get quota
		if ($oUser instanceof \Aurora\Modules\Core\Classes\User)
		{
			if ($oUser->Role === \Aurora\System\Enums\UserRole::NormalUser && $oUser->EntityId === $UserId)
			{
				$iResult = $oUser->{self::GetName() . '::TotalQuotaBytes'};
			}
			else if ($oUser->Role === \Aurora\System\Enums\UserRole::SuperAdmin)
			{
				$oUser = \Aurora\System\Api::getUserById($UserId);
				if ($oUser instanceof \Aurora\Modules\Core\Classes\User)
				{
					$iResult = $oUser->{self::GetName() . '::TotalQuotaBytes'};
				}
			}
		}

		return $iResult;
	}

	public function onServerToResponseArray($aArgs, &$mResult)
	{
		if (is_array($mResult))
		{
			$mResult['AllowToDelete'] = !$mResult[self::GetName().'::Native'];
			$mResult['AllowEditDomains'] = !$mResult[self::GetName().'::Native'];
		}
	}
	
	public function onAfterCreateTables(&$aData, &$mResult)
	{
		$this->oApiMainManager->createTablesFromFile();

		$oMailDecorator = \Aurora\Modules\Mail\Module::Decorator();
		$aServers = $oMailDecorator->GetServers();
		if (is_array($aServers) && count($aServers) === 0)
		{
			$mServerId = $oMailDecorator->CreateServer('localhost', 'localhost', 143, false, 'localhost', 25, false, 
				\Aurora\Modules\Mail\Enums\SmtpAuthType::NoAuthentication, '*', true, false, 4190);
			if (is_int($mServerId))
			{
				$oMailModule = \Aurora\System\Api::GetModule('Mail');
				$oApiServersManager = $oMailModule->oApiServersManager;
				$mServer = $oApiServersManager->getServer($mServerId);
				if ($mServer)
				{
					$mServer->{self::GetName().'::Native'} = true;
					$oApiServersManager->updateServer($mServer);
				}
			}
		}
	}
	
	public function onAfterCreateUser(&$aData, &$mResult)
	{
		$sEmail = isset($aData['PublicId']) ? $aData['PublicId'] : '';
		$iDomainId = isset($aData['DomainId']) ? $aData['DomainId'] : 0;
		$sPassword = isset($aData['Password']) ? $aData['Password'] : '';
		$sQuotaBytes = isset($aData['QuotaBytes']) ? $aData['QuotaBytes'] : null;
		$oUser = \Aurora\System\Api::getUserById($mResult);
		if ($sEmail && $sPassword && $oUser instanceof \Aurora\Modules\Core\Classes\User)
		{
			$oUser->{self::GetName() . '::DomainId'} = $iDomainId;
			$oUser->{self::GetName() . '::TotalQuotaBytes'} = (int) $sQuotaBytes;
			\Aurora\Modules\Core\Module::Decorator()->UpdateUserObject($oUser);
			$mResult = $this->oApiMainManager->createAccount($sEmail, $sPassword, $oUser->EntityId, $iDomainId);
			if ($mResult)
			{
				$this->oApiMainManager->updateUserMailQuota($oUser->EntityId, (int) ($sQuotaBytes / self::QUOTA_KILO_MULTIPLIER));//bytes to Kbytes
				try
				{
					\Aurora\Modules\Mail\Module::Decorator()->CreateAccount($oUser->EntityId, '', $sEmail, $sEmail, $sPassword);
				}
				catch (\Exception $oException)
				{
					if ($oException instanceof \Aurora\Modules\Mail\Exceptions\Exception &&
						$oException->getCode() === \Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect)
					{
						\Aurora\Modules\Core\Module::Decorator()->DeleteUser($oUser->EntityId);
					}
					throw $oException;
				}
			}
		}
	}

	public function onBeforeSendOrSaveMessage(&$aArgs, &$mResult)
	{
		$oFetcher = $this->oApiFetchersManager->getFetcher($aArgs['FetcherID']);
		if ($oFetcher && $oFetcher->IdUser === $aArgs['UserId'])
		{
			$aArgs['Fetcher'] = $oFetcher;
		}
	}
	
	public function onBeforeGetEntityList(&$aArgs, &$mResult)
	{
		if ($aArgs['Type'] === 'User' && isset($aArgs['DomainId']) && $aArgs['DomainId'] !== 0)
		{
			if (isset($aArgs['Filters']) && is_array($aArgs['Filters']) && count($aArgs['Filters']) > 0)
			{
				$aArgs['Filters'][self::GetName() . '::DomainId'] = [$aArgs['DomainId'], '='];
				$aArgs['Filters'] = [
					'$AND' => $aArgs['Filters']
				];
			}
			else
			{
				$aArgs['Filters'] = [self::GetName() . '::DomainId' => [$aArgs['DomainId'], '=']];
			}
		}
	}

	public function onAfterDeleteUser($aArgs, &$mResult)
	{
		$sUserPublicId = isset($aArgs["User"]) ? $aArgs["User"]->PublicId : null;
		if ($sUserPublicId)
		{
			//remove from awm_accounts
			$this->oApiMainManager->deleteAccount($sUserPublicId);
			//remove mailbox
			$sScript = '/opt/afterlogic/scripts/webshell-maildirdel.sh';
			if (file_exists($sScript))
			{
				$sEmail = \Aurora\System\Utils::GetAccountNameFromEmail($sUserPublicId);
				$sDomain = \MailSo\Base\Utils::GetDomainFromEmail($sUserPublicId);
				$sCmd = $sScript . ' ' . $sDomain . ' ' . $sEmail;

				\Aurora\System\Api::Log('deleteMailDir / exec: '.$sCmd, \Aurora\System\Enums\LogLevel::Full);
				$sReturn = trim(shell_exec($sCmd));
				if (!empty($sReturn))
				{
					\Aurora\System\Api::Log('deleteMailDir / exec result: '.$sReturn, \Aurora\System\Enums\LogLevel::Full);
				}
			}
			else
			{
				\Aurora\System\Api::Log('deleteMailDir: '.$sScript.' does not exist', \Aurora\System\Enums\LogLevel::Full);
			}
			//remove fetchers
			if (isset($aArgs["User"]->EntityId))
			{
				$mFetchers = $this->GetFetchers($aArgs["User"]->EntityId);
				if ($mFetchers && is_array($mFetchers))
				{
					foreach ($mFetchers as $oFetcher)
					{
						$this->DeleteFetcher($aArgs["User"]->EntityId, $oFetcher->EntityId);
					}
				}
			}
		}
	}
	
	public function onAfterDeleteTenant($aArgs, &$mResult)
	{
		$TenantId = $aArgs['TenantId'];
		
		$aDomains = $this->Decorator()->GetDomains($TenantId);
		$aDomainIds = [];
		if (isset($aDomains['Items']) && is_array($aDomains['Items']))
		{
			foreach ($aDomains['Items'] as $oDomain)
			{
				if ($oDomain['TenantId'] === $TenantId)
				{
					$aDomainIds[] = $oDomain['Id'];
				}
			}
		}
		if (count($aDomainIds))
		{
			$this->Decorator()->DeleteDomains($TenantId, $aDomainIds);
		}
	}

	public function onAfterGetEntityList($aArgs, &$mResult)
	{
		if (isset($aArgs['Type']) && $aArgs['Type'] === 'User')
		{
			foreach ($mResult['Items'] as &$aUser)
			{
				$oUser = \Aurora\Modules\Core\Module::Decorator()->GetUser($aUser['Id']);
				$aUser['QuotaBytes'] = $oUser instanceof \Aurora\Modules\Core\Classes\User ? $oUser->{self::GetName() . '::TotalQuotaBytes'} : 0;
			}
		}
	}

	public function onAfterUpdateEntity($aArgs, &$mResult, &$mSubscriptionResult)
	{
		if (isset($aArgs['Type']) && $aArgs['Type'] === 'User' && isset($aArgs['Data']) && isset($aArgs['Data']['Id']))
		{
			$oUser = \Aurora\System\Api::getUserById($aArgs['Data']['Id']);
			if ($oUser instanceof \Aurora\Modules\Core\Classes\User)
			{
				//Update quota
				if (isset($aArgs['Data']['QuotaBytes']))
				{
					$oUser->{self::GetName() . '::TotalQuotaBytes'} = (int) $aArgs['Data']['QuotaBytes'];
					\Aurora\Modules\Core\Module::Decorator()->UpdateUserObject($oUser);
					//Update mail quota
					$iTotalQuotaBytes =  $oUser->{self::GetName() . '::TotalQuotaBytes'};
					$iFileUsageBytes = $oUser->{'PersonalFiles::UsedSpace'};
					$iMailQuotaKb = (int) (($iTotalQuotaBytes - $iFileUsageBytes) / self::QUOTA_KILO_MULTIPLIER);//bytes to Kbytes
					$this->oApiMainManager->updateUserMailQuota($aArgs['Data']['Id'], $iMailQuotaKb > 0 ? $iMailQuotaKb : 1);
				}
				//Update password
				if (isset($aArgs['Data']['Password']) && trim($aArgs['Data']['Password']) !== '')
				{
					$oAccount = \Aurora\Modules\Mail\Module::Decorator()->GetAccountByEmail($oUser->PublicId);
					if ($oAccount instanceof \Aurora\Modules\Mail\Classes\Account)
					{
						$mSubscriptionResult['IsPasswordChanged'] = (bool) \Aurora\Modules\Mail\Module::Decorator()->ChangePassword($oAccount->EntityId, $oAccount->getPassword(), \trim($aArgs['Data']['Password']));
					}
				}
				else if (isset($aArgs['Data']['Password']) && trim($aArgs['Data']['Password']) === '')
				{
					$mSubscriptionResult['IsPasswordChanged'] = false;
				}
			}
		}
		$mSubscriptionResult['Result'] = true;
	}

	public function onAfterGetQuotaFiles($aArgs, &$mResult)
	{
		//We get the used space of the file quota, take its value from the total quota and write result in the mail quota
		if (isset($aArgs['UserId']) && isset($mResult['Used']))
		{
			$oUser = \Aurora\System\Api::getUserById($aArgs['UserId']);
			if ($oUser instanceof \Aurora\Modules\Core\Classes\User)
			{
				$iTotalQuotaBytes =  $oUser->{self::GetName() . '::TotalQuotaBytes'};
				$iFileUsageBytes = $mResult['Used'];
				$iMailQuotaUsageBytes = $this->oApiMainManager->getUserMailQuotaUsage($aArgs['UserId']);
				$iMailQuotaKb = (int) (($iTotalQuotaBytes - $iFileUsageBytes) / self::QUOTA_KILO_MULTIPLIER);//bytes to Kbytes
				$this->oApiMainManager->updateUserMailQuota($aArgs['UserId'], $iMailQuotaKb > 0 ? $iMailQuotaKb : 1);
				$mResult['Limit'] = $iTotalQuotaBytes;
				$mResult['Used'] = $mResult['Used']  + $iMailQuotaUsageBytes;
			}
		}
	}

	public function onBeforeGetQuotaMail($aArgs, &$mResult)
	{
		if (isset($aArgs['UserId']) && isset($aArgs['AccountID']))
		{
			$oUser = \Aurora\System\Api::getUserById($aArgs['UserId']);
			$oAccount = \Aurora\Modules\Mail\Module::Decorator()->GetAccount($aArgs['AccountID']);
			if ($oUser instanceof \Aurora\Modules\Core\Classes\User &&
				$oAccount instanceof \Aurora\Modules\Mail\Classes\Account &&
				$oUser->PublicId === $oAccount->Email)
			{
				$mResult = [];
				$iFilesQuotaUsageBytes = $oUser->{'PersonalFiles::UsedSpace'};
				$iMailQuotaUsageBytes = $this->oApiMainManager->getUserMailQuotaUsage($aArgs['UserId']);
				$mResult[0] = (int) (($iFilesQuotaUsageBytes + $iMailQuotaUsageBytes) / self::QUOTA_KILO_MULTIPLIER);//bytes to Kbytes
				$mResult[1] = (int) ($oUser->{self::GetName() . '::TotalQuotaBytes'} / self::QUOTA_KILO_MULTIPLIER);//bytes to Kbytes

				return true;
			}
		}
	}

	public function onBeforeChangePassword($aArguments, &$mResult)
	{
		$mResult = true;

		$oAccount = \Aurora\System\Api::GetModule('Mail')->GetAccount($aArguments['AccountId']);

		if ($oAccount)
		{
			$mResult = $this->UpdateAccountPassword($oAccount->Email, $aArguments['CurrentPassword'], $aArguments['NewPassword']);
			return !$mResult; // break subscriptions
		}
	}

	/**
	 * Creates account with credentials specified in registration form
	 *
	 * @param array $aArgs New account credentials.
	 * @param type $mResult Is passed by reference.
	 */
	public function onAfterSignup($aArgs, &$mResult)
	{
		if (isset($aArgs['Login']) && isset($aArgs['Password'])
			&& !empty(trim($aArgs['Password'])) && !empty(trim($aArgs['Login'])))
		{
			$bResult = false;
			$sLogin = trim($aArgs['Login']);
			$sPassword = trim($aArgs['Password']);
			$sFriendlyName = isset($aArgs['Name']) ? trim($aArgs['Name']) : '';
			$bSignMe = isset($aArgs['SignMe']) ? (bool) $aArgs['SignMe'] : false;
			$bPrevState = \Aurora\System\Api::skipCheckUserRole(true);
			$iUserId = \Aurora\Modules\Core\Module::Decorator()->CreateUser(0, $sLogin);
			$oUser = \Aurora\System\Api::getUserById((int) $iUserId);
			if ($oUser instanceof \Aurora\Modules\Core\Classes\User)
			{
				$sDomain = \MailSo\Base\Utils::GetDomainFromEmail($oUser->PublicId);
				$aDomain = $this->oApiDomainsManager->getDomainByName($sDomain);
				if (is_array($aDomain) && isset($aDomain['DomainId']))
				{
					$oUser->{$this->GetName() . '::DomainId'} = $aDomain['DomainId'];
					$sQuotaBytes = (int) $this->getConfig('UserDefaultQuotaMB', 1) * self::QUOTA_KILO_MULTIPLIER * self::QUOTA_KILO_MULTIPLIER; //Mbytes to bytes
					$oUser->{$this->GetName() . '::TotalQuotaBytes'} = $sQuotaBytes;
					\Aurora\Modules\Core\Module::Decorator()->UpdateUserObject($oUser);
					$mResult = $this->oApiMainManager->createAccount($sLogin, $sPassword, $oUser->EntityId, $aDomain['DomainId']);
					if ($mResult)
					{
						$this->oApiMainManager->updateUserMailQuota($oUser->EntityId, (int) ($sQuotaBytes / self::QUOTA_KILO_MULTIPLIER));//bytes to Kbytes
						try
						{
							$oAccount = \Aurora\Modules\Mail\Module::Decorator()->CreateAccount($oUser->EntityId, $sFriendlyName, $sLogin, $sLogin, $sPassword);
							if ($oAccount instanceof \Aurora\Modules\Mail\Classes\Account)
							{
								$bResult = true;
								$iTime = $bSignMe ? 0 : time();
								$sAuthToken = \Aurora\System\Api::UserSession()->Set(
									[
										'token' => 'auth',
										'sign-me' => $bSignMe,
										'id' => $oAccount->IdUser,
										'account' => $oAccount->EntityId,
										'account_type' => $oAccount->getName()
									], $iTime);
								$mResult = ['AuthToken' => $sAuthToken];
							}
						}
						catch (\Exception $oException)
						{
							if ($oException instanceof \Aurora\Modules\Mail\Exceptions\Exception &&
								$oException->getCode() === \Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect)
							{
								\Aurora\Modules\Core\Module::Decorator()->DeleteUser($oUser->EntityId);
							}
							throw $oException;
						}
					}
				}
			}
			if (!$bResult)
			{//If Account wasn't created - delete user
				\Aurora\Modules\Core\Module::Decorator()->DeleteUser($oUser->EntityId);
			}
			\Aurora\System\Api::skipCheckUserRole($bPrevState);
		}

		return true; // break subscriptions to prevent account creation in other modules
	}

	/***** private functions *****/
	private function getSingleDefaultTenantId()
	{
		$iTenantId = 0;
		$oSettings =& \Aurora\System\Api::GetSettings();

		if (!$oSettings->GetConf('EnableMultiChannel') && !$oSettings->GetConf('EnableMultiTenant'))
		{
			$iTenantId = \Aurora\Modules\Core\Module::Decorator()->GetTenantIdByName('Default');
		}

		return $iTenantId;
	}
}
