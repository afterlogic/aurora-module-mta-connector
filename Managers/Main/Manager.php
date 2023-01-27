<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\Main;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 */
class Manager extends \Aurora\System\Managers\AbstractManagerWithStorage
{
    /**
     * @param \Aurora\System\Module\AbstractModule $oModule
     */
    public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
    {
        parent::__construct($oModule, new \Aurora\Modules\MtaConnector\Managers\Main\Storages\db\Storage($this));
    }

    public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId)
    {
        $sEmail = \MailSo\Base\Utils::idn()->encode($sEmail);

        return $this->oStorage->createAccount($sEmail, self::EncodePassword($sPassword), $iUserId, $iDomainId);
    }

    public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
    {
        $sEmail = \MailSo\Base\Utils::idn()->encode($sEmail);

        return $this->oStorage->updateAccountPassword($sEmail, $sPassword, self::EncodePassword($sNewPassword));
    }

    public function updateAccountPasswordByEmail($sEmail, $sNewPassword)
    {
        $sEmail = \MailSo\Base\Utils::idn()->encode($sEmail);

        return $this->oStorage->updateAccountPasswordByEmail($sEmail, self::EncodePassword($sNewPassword));
    }

    public function deleteAccount($sEmail)
    {
        $sEmail = \MailSo\Base\Utils::idn()->encode($sEmail);

        return $this->oStorage->deleteAccount($sEmail);
    }

    /**
     * Creates tables required for module work by executing create.sql file.
     *
     * @return boolean
     */
    public function createTablesFromFile()
    {
        $sFilePath = dirname(__FILE__) . '/Storages/db/Sql/create.sql';
        $bResult = \Aurora\System\Managers\Db::getInstance()->executeSqlFile($sFilePath);

        return $bResult;
    }

    /**
     * Update user mail quota
     *
     * @param int $UserId
     * @param int $iQuotaKb
     * @return bool
     */
    public function updateUserMailQuota($UserId, $iQuotaKb)
    {
        return $this->oStorage->updateUserMailQuota($UserId, $iQuotaKb);
    }

    /**
     * Return user mail quota in KB
     *
     * @param $UserId
     * @return int
     */
    public function getUserMailQuota($UserId)
    {
        return $this->oStorage->getUserMailQuota($UserId);
    }

    /**
     * Return user mail quota usage in Bytes
     *
     * @param $UserId
     * @return int
     */
    public function getUserMailQuotaUsage($UserId)
    {
        return $this->oStorage->getUserMailQuotaUsage($UserId);
    }

    public static function EncodePassword($sPassword)
    {
        if (empty($sPassword)) {
            return '';
        }
        $salt = substr(sha1(rand()), 0, 16);
        $sResult = hash('sha256', $sPassword . hex2bin($salt)) . $salt;

        return $sResult;
    }

    /**
     * Return Account with specified email
     *
     * @param $sAccountEmail
     * @return array|bool
     */
    public function getAccountByEmail($sAccountEmail)
    {
        $sAccountEmail = \MailSo\Base\Utils::idn()->encode(trim($sAccountEmail));

        return $this->oStorage->getAccountByEmail($sAccountEmail);
    }
}
