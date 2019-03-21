<?php
/**
 * This code is licensed under AfterLogic Software License.
 * For full statements of the license see LICENSE file.
 */

namespace Aurora\Modules\MtaConnector\Managers\Aliases;

/**
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @package MtaConnector
 * @subpackage Managers
 */
class Manager extends \Aurora\System\Managers\AbstractManagerWithStorage
{
	/**
	 * @param \Aurora\System\Module\AbstractModule $oModule
	 */
	public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
	{
		parent::__construct($oModule, new \Aurora\Modules\MtaConnector\Managers\Aliases\Storages\db\Storage($this));
	}

	/**
	 * Obtains all aliases for specified mail account.
	 * @param int $iAccountId Account identifier.
	 * @return array|boolean
	 */
	public function getAliases($iAccountId)
	{
		return $this->oStorage->getAliases($iAccountId);
	}
	
	/**
	 * Creates new alias with specified name and domain.
	 * @param int $iAccountId Account identifier.
	 * @param string $sName Alias name.
	 * @param string $sDomain Alias domain.
	 * @param string $sToEmail Email of the mailbox where messages should be sent.
	 * @return boolean
	 */
	public function addAlias($iAccountId, $sName, $sDomain, $sToEmail)
	{
		return $this->oStorage->addAlias($iAccountId, $sName, $sDomain, $sToEmail);
	}
	
	/**
	 * Deletes alias with specified name and domain.
	 * @param int $iAccountId Account identifier
	 * @param string $sName Alias name.
	 * @param string $sDomain Alias domain.
	 * @return string
	 */
	public function deleteAlias($iAccountId, $sName, $sDomain)
	{
		return $this->oStorage->deleteAlias($iAccountId, $sName, $sDomain);
	}
	
	/**
	 * Deletes all aliases for specified account.
	 * @param int $iAccountId Account identifier.
	 * @return boolean
	 */
	public function deleteAliases($iAccountId)
	{
		return $this->oStorage->deleteAliases($iAccountId);
	}
}
