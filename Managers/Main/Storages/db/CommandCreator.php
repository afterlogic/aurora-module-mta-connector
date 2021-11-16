<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\Main\Storages\db;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
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
				$this->escapeColumn('email'),
				$this->escapeColumn('password'),
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
			$sSql = 'UPDATE awm_accounts set password = %s where email = %s and
					CONCAT(SHA2(CONCAT(%s, UNHEX(SUBSTR(password, -16))), 256), SUBSTR(password, -16)) = password';
			//SUBSTR(password, -16) = salt
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

	public function updateAccountPasswordByEmail($sEmail, $sNewPassword)
	{
		if (!empty($sEmail) && !empty($sNewPassword))
		{
			$sSql = 'UPDATE awm_accounts set password = %s where email = %s';

			return sprintf($sSql,
				$this->escapeString($sNewPassword),
				$this->escapeString($sEmail)
			);
		}

		return '';
	}

	/**
	 *
	 * @param string $sEmail
	 * @return string
	 */
	public function deleteAccountByEmail($sEmail)
	{
		if (!empty($sEmail))
		{
			$sSql = 'DELETE FROM awm_accounts WHERE %s = %s';
			return sprintf($sSql,
				$this->escapeColumn('email'),
				$this->escapeString($sEmail)
			);
		}

		return '';
	}

	/**
	 *
	 * @param string $sEmail
	 * @return string
	 */
	public function deleteAccountQuotaUsageByEmail($sEmail)
	{
		if (!empty($sEmail))
		{
			$sSql = 'DELETE FROM awm_account_quotas WHERE %s = %s';
			return sprintf($sSql,
				$this->escapeColumn('name'),
				$this->escapeString($sEmail)
			);
		}

		return '';
	}

	/**
	 *
	 * @param int $UserId
	 * @param int $iQuotaKb
	 * @return string
	 */
	public function updateUserMailQuota($UserId, $iQuotaKb)
	{
		$sSql = 'UPDATE awm_accounts SET %s=%d WHERE %s = %d';
		return sprintf($sSql,
			$this->escapeColumn('mail_quota_kb'),
			(int) $iQuotaKb,
			$this->escapeColumn('id_user'),
			(int) $UserId
		);
	}

	/**
	 *
	 * @param int $UserId
	 * @return string
	 */
	public function getUserMailQuota($UserId)
	{
		if ($UserId)
		{
			$sSql = 'SELECT %s FROM awm_accounts WHERE %s = %s';
			return sprintf($sSql,
				$this->escapeColumn('mail_quota_kb'),
				$this->escapeColumn('id_user'),
				(int) $UserId
			);
		}

		return '';
	}

	/**
	 *
	 * @param int $UserId
	 * @return string
	 */
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
				$this->escapeColumn('email'),
				$this->escapeColumn('name'),
				$this->escapeColumn('id_user'),
				(int) $UserId
			);
		}

		return '';
	}

	/**
	 *
	 * @param string $sAccountEmail
	 * @return string
	 */
	public function getAccountByEmail($sAccountEmail)
	{
		if (!empty($sAccountEmail))
		{
			$sSql = 'SELECT
					awm_accounts.%s,
					awm_accounts.%s,
					awm_accounts.%s,
					awm_accounts.%s,
					awm_accounts.%s
				FROM awm_accounts
				WHERE awm_accounts.%s = %s';
			return sprintf($sSql,
				$this->escapeColumn('id_acct'),
				$this->escapeColumn('id_user'),
				$this->escapeColumn('id_domain'),
				$this->escapeColumn('email'),
				$this->escapeColumn('mailing_list'),
				$this->escapeColumn('email'),
				$this->escapeString($sAccountEmail)
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
