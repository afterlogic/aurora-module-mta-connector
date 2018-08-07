<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MailSuite\Managers\Main\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @internal
 * 
 * @package Helpdesk
 * @subpackage Storages
 */
class CommandCreator extends \Aurora\System\Db\AbstractCommandCreator
{
	/**
	 * TODO remove CHelpdeskUser::getStaticMap call
	 * @param string $sWhere
	 *
	 * @return string
	 */
	protected function _getUserByWhere($sWhere)
	{
		$aMap = \Aurora\System\AbstractContainer::DbReadKeys(CHelpdeskUser::getStaticMap());
		$aMap = array_map(array($this, 'escapeColumn'), $aMap);

		$sSql = 'SELECT %s FROM %sahd_users WHERE %s';

		return sprintf($sSql, implode(', ', $aMap), $this->prefix(), $sWhere);
	}

	/**
	 * @param int $iIdTenant
	 * @param int $iHelpdeskUserId
	 *
	 * @return string
	 */
	public function getUserById($iIdTenant, $iHelpdeskUserId)
	{
		return $this->_getUserByWhere(sprintf('%s = %d AND %s = %d',
			$this->escapeColumn('id_tenant'), $iIdTenant,
			$this->escapeColumn('id_helpdesk_user'), $iHelpdeskUserId));
	}

	/**
	
	/**
	 * @param int $iIdTenant
	 *
	 * @return string
	 */
	public function updateHelpdeskFetcherTimer($iIdTenant)
	{
		return sprintf('UPDATE %sawm_tenants SET hd_fetcher_timer = %d WHERE id_tenant = %d', $this->prefix(), time(), $iIdTenant);
	}

	
	/**
	 * TODO remove this method
	 * @param \Aurora\Modules\Core\Classes\User $oUser
	 *
	 * @return string
	 */
	public function createAccount($sEmail, $sPassword, $iDomainId, $iQuota)
	{
		if (!empty($sEmail) && !empty($sPassword))
		{
			$sSql = 'INSERT INTO awm_accounts ( %s, %s, %s, %s ) VALUES ( %s, %s, %d, %d )';
			return sprintf($sSql,
				$this->escapeColumn('mail_inc_login'),
				$this->escapeColumn('mail_inc_pass'),
				$this->escapeColumn('quota'),
				$this->escapeColumn('id_domain'),
				$this->escapeString($sEmail),
				$this->escapeString($sPassword),
				(int) $iQuota,
				(int) $iDomainId
			);
		}

		return '';
	}

	/**
	 * TODO remove
	 * @param \Aurora\Modules\Core\Classes\User $oUser
	 *
	 * @return string
	 */
	public function updateUser(\Aurora\Modules\Core\Classes\User $oUser)
	{
		$aResult = \Aurora\System\AbstractContainer::DbUpdateArray($oUser, $this->oHelper);

		$sSql = 'UPDATE %sahd_users SET %s WHERE %s = %d AND %s = %d';
		return sprintf($sSql, $this->prefix(), implode(', ', $aResult),
			$this->escapeColumn('id_tenant'), $oUser->IdTenant,
			$this->escapeColumn('id_helpdesk_user'), $oUser->EntityId
		);
	}
}

/**
 * @package Helpdesk
 * @subpackage Storages
 */
class CommandCreatorMySQL extends CommandCreator
{
}
