<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MtaConnector\Managers\Main\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @internal
 * 
 * @package MtaConnector
 * @subpackage Storages
 */
class CommandCreator extends \Aurora\System\Db\AbstractCommandCreator
{
	/**
	 * 
	 * @return string
	 */
	public function createAccount($sEmail, $sPassword, $iUserId, $iDomainId)
	{
		if (!empty($sEmail) && !empty($sPassword))
		{
			$sSql = "INSERT INTO awm_accounts ( %s, %s, %s, %s ) VALUES ( %s, %s, %d, %d )";
			return sprintf($sSql,
				$this->escapeColumn('mail_inc_login'),
				$this->escapeColumn('mail_inc_pass'),
				$this->escapeColumn('id_user'),
				$this->escapeColumn('id_domain'),
				$this->escapeString($sEmail),
				$this->escapeString($sPassword),
				(int) $iUserId,
				(int) $iDomainId
			);
		}

		return '';
	}
	
	public function updateAccountPassword($sEmail, $sPassword, $sNewPassword)
	{
		if (!empty($sEmail) && !empty($sPassword) && !empty($sNewPassword))
		{
			$sSql = 'UPDATE awm_accounts set mail_inc_pass = %s where mail_inc_login = %s and
					CONCAT(SHA2(CONCAT(%s, UNHEX(SUBSTR(mail_inc_pass, -16))), 256), SUBSTR(mail_inc_pass, -16)) = mail_inc_pass';
			//SUBSTR(mail_inc_pass, -16) = salt
			//SHA2(CONCAT({plain-password}, UNHEX(salt)), 256) = salted hash
			//hash + salt = S(alted)SH256
			return sprintf($sSql,
				$this->escapeString($sNewPassword),
				$this->escapeString($sEmail),
				$this->escapeString($sPassword)
			);
		}

		return '';
	}

	/**
	 *
	 * @return string
	 */
	public function deleteAccountByEmail($sEmail)
	{
		if (!empty($sEmail))
		{
			$sSql = 'DELETE FROM awm_accounts WHERE %s = %s';
			return sprintf($sSql,
				$this->escapeColumn('mail_inc_login'),
				$this->escapeString($sEmail)
			);
		}

		return '';
	}

	public function updateUserMailQuota($UserId, $iQuotaKb)
	{
		$sSql = 'UPDATE awm_accounts SET %s=%d WHERE %s = %d';
		return sprintf($sSql,
			$this->escapeColumn('mail_quota'),
			(int) $iQuotaKb,
			$this->escapeColumn('id_user'),
			(int) $UserId
		);
	}

	public function getUserMailQuota($UserId)
	{
		if ($UserId)
		{
			$sSql = 'SELECT %s FROM awm_accounts WHERE %s = %s';
			return sprintf($sSql,
				$this->escapeColumn('mail_quota'),
				$this->escapeColumn('id_user'),
				(int) $UserId
			);
		}

		return '';
	}

	public function getUserMailQuotaUsage($UserId)
	{
		if ($UserId)
		{
			$sSql = 'SELECT
					awm_account_quotas.%s
				FROM awm_account_quotas
				LEFT JOIN awm_accounts ON awm_accounts.%s = awm_account_quotas.%s
				WHERE awm_accounts.%s = %d';
			return sprintf($sSql,
				$this->escapeColumn('mail_quota_usage_bytes'),
				$this->escapeColumn('mail_inc_login'),
				$this->escapeColumn('name'),
				$this->escapeColumn('id_user'),
				(int) $UserId
			);
		}

		return '';
	}
}

/**
 * @package MtaConnector
 * @subpackage Storages
 */
class CommandCreatorMySQL extends CommandCreator
{
}
