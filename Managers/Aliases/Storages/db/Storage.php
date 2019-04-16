<?php
/**
 * This code is licensed under Afterlogic Software License.
 * For full statements of the license see LICENSE file.
 */
namespace Aurora\Modules\MtaConnector\Managers\Aliases\Storages\db;
/**
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2019, Afterlogic Corp.
 *
 * @internal
 */
class Storage extends \Aurora\Modules\MtaConnector\Managers\Aliases\Storages\DefaultStorage
{
	protected $oConnection;
	protected $oCommandCreator;

	/**
	 * 
	 * @param \Aurora\System\Managers\AbstractManager $oManager
	 */
	public function __construct(\Aurora\System\Managers\AbstractManager &$oManager)
	{
		parent::__construct($oManager);

		$this->oConnection =& $oManager->GetConnection();
		$this->oCommandCreator = new CommandCreator();
	}

	/**
	 * Obtains all aliases for specified mail account.
	 * @param int $iAccountId Account identifier.
	 * @return array|boolean
	 */
	public function getAliases($iAccountId)
	{
		$mResult = false;
		if ($this->oConnection->Execute($this->oCommandCreator->getAliases($iAccountId)))
		{
			$mResult = [];
			while (false !== ($oRow = $this->oConnection->GetNextRecord()))
			{
				if ($oRow)
				{
					$mResult[] = $oRow->alias_name . '@' . $oRow->alias_domain;
				}
			}
		}
		
		$this->throwDbExceptionIfExist();
		
		return $mResult;
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
		$mResult = $this->oConnection->Execute($this->oCommandCreator->addAlias($iAccountId, $sName, $sDomain, $sToEmail));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Deletes alias with specified name and domain.
	 * @param int $iAccountId Account identifier
	 * @param string $sName Alias name.
	 * @param string $sDomain Alias domain.
	 * @return boolean
	 */
	public function deleteAlias($iAccountId, $sName, $sDomain)
	{
		$mResult = $this->oConnection->Execute($this->oCommandCreator->deleteAlias($iAccountId, $sName, $sDomain));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
	
	/**
	 * Deletes all aliases for specified account.
	 * @param int $iAccountId Account identifier.
	 * @return boolean
	 */
	public function deleteAliases($iAccountId)
	{
		$mResult = $this->oConnection->Execute($this->oCommandCreator->deleteAliases($iAccountId));

		$this->throwDbExceptionIfExist();
		
		return $mResult;
	}
}
