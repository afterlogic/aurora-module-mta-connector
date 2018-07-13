<?php
/**
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MailSuite\Managers\Fetchers;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
	/**
	 * @var \Aurora\System\Managers\Eav
	 */
	public $oEavManager = null;
	
	/**
	 * @param \Aurora\System\Module\AbstractModule $oModule
	 */
	public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct($oModule);
		
		$this->oEavManager = new \Aurora\System\Managers\Eav();
	}

	/**
	 * Tries to connect to POP3 server and login with fetcher's credentials.
	 * @param \Aurora\Modules\MailSuite\Classes\Fetcher $oFetcher
	 */
	private function testPop3Connect($oFetcher)
	{
		if ($oFetcher)
		{
			$oPop3Client = \MailSo\Pop3\Pop3Client::NewInstance();
			$oPop3Client->SetLogger(\Aurora\System\Api::SystemLogger());
			$oPop3Client->Connect($oFetcher->IncomingServer, $oFetcher->IncomingPort, $oFetcher->IncomingMailSecurity);
			$oPop3Client->Login($oFetcher->IncomingLogin, $oFetcher->IncomingPassword);
		}
	}

	/**
	 * Creates fetcher in database.
	 * @param \Aurora\Modules\MailSuite\Classes\Fetcher $oFetcher Fetcher object to create in database.
	 * @return bool|int
	 */
	public function createFetcher($oFetcher)
	{
		try
		{
			$this->testPop3Connect($oFetcher);
			$this->oEavManager->saveEntity($oFetcher);
			return $oFetcher->EntityId;
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			throw $oException;
		}
		catch (\MailSo\Net\Exceptions\ConnectionException $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotConnectToMailServer, $oException, $oException->getMessage());
		}
		catch (\MailSo\Pop3\Exceptions\Exception $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
		}
		catch (\MailSo\Pop3\Exceptions\LoginBadCredentialsException $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
		}
		catch (Exception $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
		}
		return false;
	}

	/**
	 * Obtains all user's fetchers.
	 * @param int $iUserId User identifier.
	 * @return array|bool
	 */
	public function getFetchers($iUserId)
	{
		try
		{
			$iOffset = 0;
			$iLimit = 0;
			$aFilters = ['IdUser' => [$iUserId, '=']];

			return $this->oEavManager->getEntities(
				$this->getModule()->getNamespace() . '\Classes\Fetcher',
				array(),
				$iOffset,
				$iLimit,
				$aFilters
			);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		
		return false;
	}

	/**
	 * Obtains specified fetcher.
	 * @param int $iEntityId Identifier of fetcher to obtain.
	 * @return boolean
	 */
	public function getFetcher($iEntityId)
	{
		try
		{
			return $this->oEavManager->getEntity($iEntityId, $this->getModule()->getNamespace() . '\Classes\Fetcher');
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}
		
		return false;
	}
	
	/**
	 * Deletes specified fetcher in database.
	 * @param int $iEntityId Identifier of fetcher to delete in database
	 * @return bool
	 */
	public function deleteFetcher($iEntityId)
	{
		$bResult = false;
		
		try
		{
			$oFetcher = $this->getFetcher($iEntityId);
			if ($oFetcher)
			{
				$bResult = $this->oEavManager->deleteEntity($iEntityId);
			}
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			$this->setLastException($oException);
		}

		return $bResult;
	}

	/**
	 * Updates fetcher in database.
	 * @param \Aurora\Modules\MailSuite\Classes\Fetcher $oFetcher Fetcher object to update in database.
	 * @param bool $bTestPop3 Indicates if it is necessary to test POP3 connect before update.
	 * @return bool
	 */
	public function updateFetcher($oFetcher, $bTestPop3)
	{
		try
		{
			if ($bTestPop3)
			{
				$this->testPop3Connect($oFetcher);
			}
			return $this->oEavManager->saveEntity($oFetcher);
		}
		catch (\Aurora\System\Exceptions\BaseException $oException)
		{
			throw $oException;
		}
		catch (\MailSo\Net\Exceptions\ConnectionException $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotConnectToMailServer, $oException, $oException->getMessage());
		}
		catch (\MailSo\Pop3\Exceptions\Exception $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
		}
		catch (\MailSo\Pop3\Exceptions\LoginBadCredentialsException $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
		}
		catch (Exception $oException)
		{
			throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
		}
		return false;
	}
}
