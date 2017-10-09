<?php
namespace App\Droit\Newsletter\Worker;

interface MailgunInterface
{
    public function setSenderEmail($email);
    public function setList($list);

    public function getList();
    public function addList($address, $name, $description);
    public function getAllLists();
    public function getSubscribers();
    public function getAllSubscribers();

    public function addContact($email);
    public function getContactByEmail($contactEmail);
    public function addContactToList($contactID);
    public function subscribeEmailToList($email);

    public function removeContact($email);

    /**
     * Lists
     */
    public function getListRecipient($email);

    /**
     * Campagnes
     */
    public function getAllCampagne();
    public function getCampagne($CampaignID);
    public function updateCampagne($CampaignID, $status);
    public function createCampagne($campagne);
    public function deleteCampagne($id);

    public function setHtml($html);
    public function getHtml();

    public function sendTransactional($email,$type);
    public function sendCampagne($id, $date = null);
    public function send();

    /**
     * Statistiques
     */
    public function statsCampagne($id);
    public function clickStatistics($id, $offset = 0);

    /**
     * import listes
     */
    public function uploadCSVContactslistData($CSVContent);
    public function importCSVContactslistData($dataID);

    /*
     * Send transactional
     * */
    public function sendBulk($campagne,$timestamp);

    /*
     * Misc test
     * */
    public function hasList();
}