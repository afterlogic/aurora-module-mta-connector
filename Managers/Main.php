<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers;

use Aurora\Modules\MtaConnector\Models\Account;
use Aurora\Modules\MtaConnector\Models\AccountQuotas;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @property Module $oModule
 */
class Main extends \Aurora\System\Managers\AbstractManager
{
    public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId)
    {
        if (!empty($sEmail) && !empty($sPassword)) {
            $sEmail = \MailSo\Base\Utils::idn()->encodeEmail($sEmail);
            return !!Account::create([
                'email' => $sEmail,
                'password' => self::EncodePassword($sPassword),
                'id_user' => $iUserId,
                'id_domain' => $iDomainId
            ]);
        }

        return false;
    }

    public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
    {
        if (!empty($sEmail) && !empty($sPassword) && !empty($sNewPassword)) {
            $sEmail = \MailSo\Base\Utils::idn()->encodeEmail($sEmail);
            $sNewPassword = self::EncodePassword($sNewPassword);
            return !!Account::where('email', $sEmail)
                ->whereRaw('CONCAT(SHA2(CONCAT(?, UNHEX(SUBSTR(password, -16))), 256), SUBSTR(password, -16)) = password', [$sPassword])
                ->update([
                    'password' => $sNewPassword
                ]);
        }

        return false;
    }

    public function updateAccountPasswordWithoutCheck($sEmail, $sNewPassword)
    {
        if (!empty($sEmail) && !empty($sNewPassword)) {
            $sEmail = \MailSo\Base\Utils::idn()->encodeEmail($sEmail);
            $sNewPassword = self::EncodePassword($sNewPassword);

            return !!Account::where('email', $sEmail)->update([
                'password' => $sNewPassword
            ]);
        }

        return false;
    }

    public function deleteAccount($sEmail)
    {
        return !!Account::where('email', $sEmail)->delete() && AccountQuotas::where('name', $sEmail)->delete();
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
        return !!Account::where('id_user', (int) $UserId)->update(['mail_quota_kb' => (int) $iQuotaKb]);
    }

    /**
     * Return user mail quota in KB
     *
     * @param $UserId
     * @return int
     */
    public function getUserMailQuota($UserId)
    {
        /** @var \Aurora\Modules\MtaConnector\Models\Account $account */
        $account = Account::where('id_user', (int) $UserId)->get('mail_quota_kb');

        if ($account) {
            return $account->mail_quota_kb;
        }

        return 0;
    }

    /**
     * Return user mail quota usage in Bytes
     *
     * @param $UserId
     * @return int
     */
    public function getUserMailQuotaUsage($UserId)
    {
        if ($UserId) {
            $quota = AccountQuotas::leftJoin('awm_accounts', function ($join) {
                $join->on('awm_accounts.email', '=', 'awm_account_quotas.name');
            })->where('id_user', $UserId)->get('mail_quota_usage_bytes')->first();

            if ($quota) {
                return $quota->mail_quota_usage_bytes;
            }
        }

        return 0;
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
        $sAccountEmail = \MailSo\Base\Utils::idn()->encodeEmail(trim($sAccountEmail));

        if (!empty($sAccountEmail)) {
            $account = Account::firstWhere('email', $sAccountEmail);
            if ($account) {
                return [
                    'AccountId' => $account->id_acct,
                    'UserId' => $account->id_user,
                    'DomainId' => $account->id_domain,
                    'Email' => $account->email,
                    'IsMailingList' => $account->mailing_list
                ];
            }
        }

        return false;
    }

    /**
     * Set status of Account with specified email
     *
     * @param int $UserId
     * @param bool $bIsDisabled
     * @return array|bool
     */
    public function updateDisableStatus($UserId, $bIsDisabled)
    {
        return !!Account::where('id_user', (int) $UserId)->update(['deleted' => (bool) $bIsDisabled]);
    }
}
