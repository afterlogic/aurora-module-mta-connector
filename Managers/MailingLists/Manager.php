<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\MtaConnector\Managers\MailingLists;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 */
class Manager extends \Aurora\System\Managers\AbstractManagerWithStorage
{
    /**
     * @var \Aurora\Modules\MtaConnector\Managers\MailingLists\Storages\db\Storage
     */
    public $oStorage;

    /**
     * @param \Aurora\System\Module\AbstractModule $oModule
     */
    public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
    {
        parent::__construct($oModule, new \Aurora\Modules\MtaConnector\Managers\MailingLists\Storages\db\Storage($this));
    }

    /**
     * Creates mailing list.
     * @param int $iDomainId Domain identifier.
     * @param string $sEmail Email of mailing list.
     * @return boolean
     */
    public function createMailingList($iDomainId, $sEmail)
    {
        return $this->oStorage->createMailingList($iDomainId, $sEmail);
    }

    /**
     * Obtains mailing list with specified identifier.
     * @param int $iListId List identifier.
     * @return string|boolean
     */
    public function getMailingListEmail($iListId)
    {
        return $this->oStorage->getMailingListEmail($iListId);
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
        return $this->oStorage->getMailingLists($iTenantId, $iDomainId, $sSearch, $iOffset, $iLimit);
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
        return $this->oStorage->getMailingListsCount($iTenantId, $iDomainId, $sSearch);
    }

    /**
     * Deletes mailing list.
     * @param int $iListId Mailing list identifier.
     * @return boolean
     */
    public function deleteMailingList($iListId)
    {
        if ($this->oStorage->deleteMailingListMembers($iListId)) {
            return $this->oStorage->deleteMailingList($iListId);
        }
        return false;
    }

    /**
     * Obtains all mailing list members.
     * @param int $iListId Mailing list identifier.
     * @return array|boolean
     */
    public function getMailingListMembers($iListId)
    {
        return $this->oStorage->getMailingListMembers($iListId);
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
        return $this->oStorage->addMember($iListId, $sListName, $sListTo);
    }

    /**
     * Deletes member from mailing list.
     * @param int $iListId Mailing list identifier.
     * @param string $sListName Email of the mailbox where messages should be sent.
     * @return boolean
     */
    public function deleteMember($iListId, $sListName)
    {
        return $this->oStorage->deleteMember($iListId, $sListName);
    }

    /**
     * Obtains mailing list ID with specified email
     * @param string $sEmail email.
     * @return string|boolean
     */
    public function getMailingListIdByEmail($sEmail)
    {
        return $this->oStorage->getMailingListIdByEmail($sEmail);
    }
}
