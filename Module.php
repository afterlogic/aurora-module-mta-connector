<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\MailSuite;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	public $oApiMainManager = null;

	/* 
	 * @var $oApiFetchersManager Managers\Fetchers
	 */
	public $oApiFetchersManager = null;
			
	public function init()
	{
		$this->subscribeEvent('AdminPanelWebclient::CreateUser::after', array($this, 'onAfterCreateUser'));

		$this->oApiMainManager = new Managers\Main\Manager($this);
		$this->oApiFetchersManager = new Managers\Fetchers\Manager($this);
	}

	public function onAfterCreateUser(&$aData, &$mResult)
	{
		$sEmail = isset($aData['PublicId']) ? $aData['PublicId'] : '';
		$sPassword = isset($aData['Password']) ? $aData['Password'] : '';
		$sQuota = isset($aData['Quota']) ? $aData['Quota'] : null;
		$oUser = \Aurora\System\Api::getUserById($mResult);
		if ($sEmail && $sPassword && $oUser instanceof \Aurora\Modules\Core\Classes\User)
		{
			$this->oApiMainManager->createAccount($sEmail, $sPassword, $sQuota);
			\Aurora\System\Api::GetModuleDecorator('Mail')->CreateAccount($oUser->EntityId, '', $sEmail, $sEmail, $sPassword);
		}
	}
	
	public function GetFetchers($UserId)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		return $this->oApiFetchersManager->getFetchers($UserId);
	}
	
	public function CreateFetcher($UserId, $AccountID, $Folder, $IncomingLogin, $IncomingPassword, $IncomingServer, $IncomingPort, $IncomingUseSsl, $LeaveMessagesOnServer)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		$oFetcher = new \Aurora\Modules\MailSuite\Classes\Fetcher($this->GetName());
		$oFetcher->IdUser = $UserId;
		$oFetcher->IdAccount = $AccountID;
		
		$oFetcher->IncomingServer = $IncomingServer;
		$oFetcher->IncomingPort = $IncomingPort;
		$oFetcher->IncomingLogin = $IncomingLogin;
		$oFetcher->IncomingPassword = $IncomingPassword;
		$oFetcher->IncomingMailSecurity = $IncomingUseSsl ? \MailSo\Net\Enumerations\ConnectionSecurityType::SSL : \MailSo\Net\Enumerations\ConnectionSecurityType::NONE;
		$oFetcher->LeaveMessagesOnServer = $LeaveMessagesOnServer;
		$oFetcher->Folder = $Folder;
		
		return $this->oApiFetchersManager->createFetcher($oFetcher);
	}
	
	public function UpdateFetcher($FetcherID, $IsEnabled, $Folder, $IncomingServer, $IncomingPort, $IncomingUseSsl, $LeaveMessagesOnServer, $IncomingPassword = null)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		$oFetcher = $this->oApiFetchersManager->getFetcher($FetcherID);
		if ($oFetcher)
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
		
		return false;
	}
	
	public function UpdateFetcherSmtpSettings($FetcherID, $Email, $Name, $IsOutgoingEnabled, $OutgoingServer, $OutgoingPort, $OutgoingUseSsl, $OutgoingUseAuth)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		$oFetcher = $this->oApiFetchersManager->getFetcher($FetcherID);
		if ($oFetcher)
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
		
		return false;
	}
	
	/**
	 * @api {post} ?/Api/ DeleteFetcher
	 * @apiName DeleteFetcher
	 * @apiGroup Mail
	 * @apiDescription Deletes fetcher.
	 * 
	 * @apiHeader {string} Authorization "Bearer " + Authentication token which was received as the result of Core.Login method.
	 * @apiHeaderExample {json} Header-Example:
	 *	{
	 *		"Authorization": "Bearer 32b2ecd4a4016fedc4abee880425b6b8"
	 *	}
	 * 
	 * @apiParam {string=Mail} Module Module name
	 * @apiParam {string=DeleteIdentity} Method Method name
	 * @apiParam {string} Parameters JSON.stringified object<br>
	 * {<br>
	 * &emsp; **EntityId** *int* Fetcher identifier.<br>
	 * }
	 * 
	 * @apiParamExample {json} Request-Example:
	 * {
	 *	Module: 'Mail',
	 *	Method: 'DeleteFetcher',
	 *	Parameters: '{ "EntityId": 14 }'
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
	 *	Module: 'Mail',
	 *	Method: 'DeleteFetcher',
	 *	Result: true
	 * }
	 * 
	 * @apiSuccessExample {json} Error response example:
	 * {
	 *	Module: 'Mail',
	 *	Method: 'DeleteFetcher',
	 *	Result: false,
	 *	ErrorCode: 102
	 * }
	 */
	/**
	 * Deletes fetcher.
	 * @param int $EntityId Fetcher identifier.
	 * @return boolean
	 */
	public function DeleteFetcher($EntityId)
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::NormalUser);
		
		return $this->oApiFetchersManager->deleteFetcher($EntityId);
	}
}
