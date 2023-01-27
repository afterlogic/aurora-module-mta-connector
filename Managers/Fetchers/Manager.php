<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\Fetchers;

use Aurora\Modules\MtaConnector\Models\Fetcher;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
    /**
     * @param \Aurora\System\Module\AbstractModule $oModule
     */
    public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
    {
        parent::__construct($oModule);
    }

    /**
     * Tries to connect to POP3 server and login with fetcher's credentials.
     * @param \Aurora\Modules\MtaConnector\Classes\Fetcher $oFetcher
     */
    private function testPop3Connect($oFetcher)
    {
        if ($oFetcher) {
            $oPop3Client = \MailSo\Pop3\Pop3Client::NewInstance();
            $oPop3Client->SetLogger(\Aurora\System\Api::SystemLogger());
            $oPop3Client->Connect($oFetcher->IncomingServer, $oFetcher->IncomingPort, $oFetcher->IncomingMailSecurity);
            $oPop3Client->Login($oFetcher->IncomingLogin, $oFetcher->IncomingPassword);
        }
    }

    /**
     * Creates fetcher in database.
     * @param \Aurora\Modules\MtaConnector\Classes\Fetcher $oFetcher Fetcher object to create in database.
     * @return bool|int
     */
    public function createFetcher($oFetcher)
    {
        try {
            $this->testPop3Connect($oFetcher);
            $oFetcher->save();
            return $oFetcher->Id;
        } catch (\Aurora\System\Exceptions\BaseException $oException) {
            throw $oException;
        } catch (\MailSo\Net\Exceptions\ConnectionException $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotConnectToMailServer, $oException, $oException->getMessage());
        } catch (\MailSo\Pop3\Exceptions\Exception $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
        } catch (\MailSo\Pop3\Exceptions\LoginBadCredentialsException $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
        } catch (\Exception $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
        }
        return false;
    }

    /**
     * Obtains all user's fetchers.
     * @param int $iUserId User identifier.
     * @return array|bool
     */
    public function getFetchers($iUserId = null)
    {
        $query = Fetcher::query();
        if ($iUserId !== null) {
            $query->where('IdUser', $iUserId);
        }

        return $query->get()->all();
    }

    /**
     * Obtains specified fetcher.
     * @param int $iEntityId Identifier of fetcher to obtain.
     * @return Fetcher
     */
    public function getFetcher($iEntityId)
    {
        try {
            return Fetcher::find($iEntityId);
        } catch (\Aurora\System\Exceptions\BaseException $oException) {
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

        try {
            $oFetcher = $this->getFetcher($iEntityId);
            if ($oFetcher) {
                $bResult = $oFetcher->delete();
            }
        } catch (\Aurora\System\Exceptions\BaseException $oException) {
            $this->setLastException($oException);
        }

        return $bResult;
    }

    /**
     * Updates fetcher in database.
     * @param \Aurora\Modules\MtaConnector\Classes\Fetcher $oFetcher Fetcher object to update in database.
     * @param bool $bTestPop3 Indicates if it is necessary to test POP3 connect before update.
     * @return bool
     */
    public function updateFetcher($oFetcher, $bTestPop3)
    {
        try {
            if ($bTestPop3) {
                $this->testPop3Connect($oFetcher);
            }
            return $oFetcher->save();
        } catch (\Aurora\System\Exceptions\BaseException $oException) {
            throw $oException;
        } catch (\MailSo\Net\Exceptions\ConnectionException $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotConnectToMailServer, $oException, $oException->getMessage());
        } catch (\MailSo\Pop3\Exceptions\Exception $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
        } catch (\MailSo\Pop3\Exceptions\LoginBadCredentialsException $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
        } catch (\Exception $oException) {
            throw new \Aurora\Modules\Mail\Exceptions\Exception(\Aurora\Modules\Mail\Enums\ErrorCodes::CannotLoginCredentialsIncorrect, $oException, $oException->getMessage());
        }
        return false;
    }
}
