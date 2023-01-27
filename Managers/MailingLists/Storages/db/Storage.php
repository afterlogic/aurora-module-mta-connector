<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\MailingLists\Storages\db;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 */
class Storage extends \Aurora\Modules\MtaConnector\Managers\MailingLists\Storages\DefaultStorage
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
     * Creates mailing list.
     * @param int $iDomainId Domain identifier.
     * @param string $sEmail Email of mailing list.
     * @return boolean
     */
    public function createMailingList($iDomainId, $sEmail)
    {
        $mResult = $this->oConnection->Execute($this->oCommandCreator->createMailingList($iDomainId, $sEmail));

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Obtains mailing lists with specified parameters.
     * @param int $iTenantId Tenant identifier.
     * @param int $iDomainId Domain identifier.
     * @param string $sSearch Search.
     * @param int $iOffset Offset.
     * @param int $iLimit Limit.
     * @return array|boolean
     */
    public function getMailingLists($iTenantId = 0, $iDomainId = 0, $sSearch = '', $iOffset = 0, $iLimit = 0)
    {
        $mResult = false;
        if ($this->oConnection->Execute($this->oCommandCreator->getMailingLists($iTenantId, $iDomainId, $sSearch, $iOffset, $iLimit))) {
            $mResult = [];
            while (false !== ($oRow = $this->oConnection->GetNextRecord())) {
                if ($oRow) {
                    $mResult[] = [
                        'Id' => $oRow->id_acct,
                        'Name' => $oRow->email,
                        'Email' => $oRow->email
                    ];
                }
            }
        }

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Obtains count of mailing lists with specified parameters.
     * @param int $iTenantId Tenant identifier.
     * @param int $iDomainId Domain identifier.
     * @param string $sSearch Search.
     * @return int
     */
    public function getMailingListsCount($iTenantId = 0, $iDomainId = 0, $sSearch = '')
    {
        $iResult = 0;
        if ($this->oConnection->Execute($this->oCommandCreator->getMailingListsCount($iTenantId, $iDomainId, $sSearch))) {
            $oRow = $this->oConnection->GetNextRecord();
            if ($oRow) {
                $iResult = (int) $oRow->count;
            }

            $this->oConnection->FreeResult();
        }

        $this->throwDbExceptionIfExist();
        return $iResult;
    }

    /**
     * Obtains mailing list with specified identifier.
     * @param int $iListId List identifier.
     * @return string|boolean
     */
    public function getMailingListEmail($iListId)
    {
        $mResult = false;
        if ($this->oConnection->Execute($this->oCommandCreator->getMailingListEmail($iListId))) {
            $oRow = $this->oConnection->GetNextRecord();
            if ($oRow) {
                $mResult = $oRow->email;
            }
        }

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Deletes mailing list members.
     * @param int $iListId Mailing list identifier.
     * @return boolean
     */
    public function deleteMailingListMembers($iListId)
    {
        $mResult = $this->oConnection->Execute($this->oCommandCreator->deleteMailingListMembers($iListId));

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Deletes mailing list.
     * @param int $iListId Mailing list identifier.
     * @return boolean
     */
    public function deleteMailingList($iListId)
    {
        $mResult = $this->oConnection->Execute($this->oCommandCreator->deleteMailingList($iListId));

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Creates SQL-query to obtain all mailing list members.
     * @param int $iListId Mailing list identifier.
     * @return string
     */
    public function getMailingListMembers($iListId)
    {
        $mResult = false;
        if ($this->oConnection->Execute($this->oCommandCreator->getMailingListMembers($iListId))) {
            $mResult = [];
            while (false !== ($oRow = $this->oConnection->GetNextRecord())) {
                if ($oRow) {
                    $mResult[] = $oRow->list_to;
                }
            }
        }

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Adds new member to mailing list.
     * @param int $iListId Mailing list identifier.
     * @param string $sListName Email of mailing list.
     * @param string $sListTo Email of the mailbox where messages should be sent.
     * @return boolean
     */
    public function addMember($iListId, $sListName, $sListTo)
    {
        $mResult = $this->oConnection->Execute($this->oCommandCreator->addMember($iListId, $sListName, $sListTo));

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Deletes member from mailing list.
     * @param int $iListId Mailing list identifier.
     * @param string $sListName Email of the mailbox where messages should be sent.
     * @return boolean
     */
    public function deleteMember($iListId, $sListName)
    {
        $mResult = $this->oConnection->Execute($this->oCommandCreator->deleteMember($iListId, $sListName));

        $this->throwDbExceptionIfExist();

        return $mResult;
    }

    /**
     * Obtains mailing list ID with specified email
     * @param string $sEmail email.
     * @return int|boolean
     */
    public function getMailingListIdByEmail($sEmail)
    {
        $mResult = false;
        if ($this->oConnection->Execute($this->oCommandCreator->getMailingListIdByEmail($sEmail))) {
            $oRow = $this->oConnection->GetNextRecord();
            if ($oRow) {
                $mResult = (int) $oRow->id_acct;
            }
        }

        $this->throwDbExceptionIfExist();

        return $mResult;
    }
}
