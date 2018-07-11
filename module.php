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

	public function init()
	{
		$this->subscribeEvent('AdminPanelWebclient::CreateUser::after', array($this, 'onAfterCreateUser'));

		$this->oApiMainManager = new Managers\Main\Manager($this);
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
}
