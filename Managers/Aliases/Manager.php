<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\Aliases;

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
