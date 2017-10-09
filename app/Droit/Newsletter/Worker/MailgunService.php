<?php namespace App\Droit\Newsletter\Worker;

use App\Droit\Newsletter\Worker\MailgunInterface;

use Illuminate\Http\Request;
use \SendGrid;


/**
 * Created by PhpStorm.
 * User: cindyleschaud
 * Date: 05.10.17
 * Time: 09:56
 */
use Mailgun\Mailgun;

class MailgunService implements MailgunInterface
{
    protected $mailgun;
    protected $sender = '';
    protected $list   = null;
    protected $html   = null;
    protected $recipients   = [];

    public $isTest = false;

    public function __construct(Mailgun $mailgun)
    {
        $this->mailgun = $mailgun;
    }

    public function setSenderEmail($email)
    {
        $this->sender = $email;
    }

    public function setList($list)
    {
        $this->list = $list;
    }

    public function getList()
    {
        return $this->list;
    }

    public function setRecipients($emails)
    {
        $this->recipients = $emails;
    }

    public function getRecipients()
    {
        return $this->recipients;
    }

    public function getAllLists()
    {
        $response = $this->mailgun->get("lists/pages", array('limit' => 10));

        if($response->http_response_code == 200){
            $data = $response->http_response_body->items;

            return collect((array) $data)->map(function ($item, $key) {
                return [
                    'ID' => $item->address,
                    'Name' => $item->name,
                    'SubscriberCount' => $item->members_count,
                ];
            });
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);
    }

    public function addList($address, $name, $description){

        $response = $this->mailgun->post("lists", [
            'address'      => $address,
            'name'         => $name,
            'description'  => $description,
            'access_level' => 'readonly'
        ]);

        if($response->http_response_code == 200){
            return $response->http_response_body->list;
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);
    }

    public function getSubscribers()
    {

    }

    public function getAllSubscribers()
    {

    }

    public function addContact($emails)
    {
        if(is_array($emails)){
            $request_body = collect($emails)->map(function ($email, $key) {
                return ['address' => $email, 'name' => '', 'description' => '', 'subscribed' => true, 'vars'  => ''];
            })->toArray();
        }
        else{
            $request_body = [
                ['address' => $emails, 'name' => '', 'description' => '', 'subscribed' => true, 'vars'  => '']
            ];
        }

        $response = $this->mailgun->post('lists/'.$this->list.'/members.json', array(
            'members' => json_encode($request_body),
            'upsert'  => true
        ));

        if($response->http_response_code == 200){
            return $response->http_response_body->list;
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);
    }

    public function getContactByEmail($contactEmail)
    {
        /*$response = $this->sendgrid->get(Resources::$Contact, ['ID'  => $contactEmail]);

        if($response->success()){
            $contact = $response->getData();
            return $contact[0]['ID']; // returns ID directly
        }

        return false;*/

        return base64_encode($contactEmail);
    }

    public function addContactToList($emails)
    {
        $this->hasList();

        if(is_array($emails)){
            $request_body = collect($emails)->map(function ($email, $key) {
                return ['address' => $email, 'name' => '', 'description' => '', 'subscribed' => true, 'vars'  => ''];
            })->toArray();
        }
        else{
            $request_body = [
                ['address' => $emails, 'name' => '', 'description' => '', 'subscribed' => true, 'vars'  => '']
            ];
        }

        $response = $this->mailgun->post('lists/'.$this->list.'/members.json', array(
            'members' => json_encode($request_body),
            'upsert'  => true
        ));

        if($response->http_response_code == 200){
            return $response->http_response_body->list;
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);
    }

    public function subscribeEmailToList($emails)
    {
        $this->hasList();

        if(is_array($emails)){
            $request_body = collect($emails)->map(function ($email, $key) {
                return ['address' => $email, 'name' => '', 'description' => '', 'subscribed' => true, 'vars'  => ''];
            })->toArray();
        }
        else{
            $request_body = [
                ['address' => $emails, 'name' => '', 'description' => '', 'subscribed' => true, 'vars'  => '']
            ];
        }

        $response = $this->mailgun->post('lists/'.$this->list.'/members.json', array(
            'members' => json_encode($request_body),
            'upsert'  => true
        ));

        if($response->http_response_code == 200){
            return $response->http_response_body->list;
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);

    }

    public function removeContact($email)
    {
        $response = $this->mailgun->delete('lists/'.$this->list.'/members/'.$email);

        if($response->http_response_code == 200){
            return true;
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);
    }

    /**
     * Lists
     */
    public function getListRecipient($email)
    {
        $this->hasList();

        $response = $this->mailgun->get('lists/'.$this->list.'/members/'.$email);

        echo '<pre>';
        print_r($response);
        echo '</pre>';exit();

    }

    /**
     * Campagnes
     */
    public function getAllCampagne(){


    }

    public function getCampagne($CampaignID)
    {

    }

    public function updateCampagne($CampaignID, $status)
    {
        $request_body = ['status' => $status];

    }

    public function createCampagne($campagne, $categories = ['droit'])
    {
        $this->hasList();


    }

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function sendTransactional($email,$type)
    {

    }

    public function sendCampagne($campagne, $date = null)
    {
        $this->hasHtml();
        $this->hasRecipients();

        $recipients = collect($this->recipients)->mapWithKeys(function ($email, $key) {
            return [$email => ['id' => $key + 1]];
        })->toArray();

        $sujet = $this->isTest ? 'TEST | '.$campagne->sujet : $campagne->sujet;

        $data = [
            'from'                => $campagne->newsletter->from_name.' <'.$campagne->newsletter->from_email.'>',
            "subject"             => $sujet,
            'to'                  => $this->recipients,
            "html"                => $this->html, // Inlined CSS HTML from Blade
            "text"                => strip_tags($this->html,'<a>'),
            "recipient-variables" => json_encode($recipients), // Required for batch sending, matches to recipient details
            "v:messageId"         => 'campagne_'.$campagne->id, // Custom variable used for webhooks
            'o:deliverytime'      => $date,
            'o:tag'               => ['campagne_'.$campagne->id]
        ];

        $response = $this->mailgun->sendMessage(config('mailgun.domain'), $data);

        if($response->http_response_code == 200){
            return $response->http_response_body->id;
        }

        throw new \App\Exceptions\NewsletterImplementationException($response->http_response_body, $response->http_response_code);

    }

    public function deleteCampagne($id)
    {

    }

    /**
     * Statistiques
     */
    public function statsCampagne($id)
    {

    }

    public function clickStatistics($id, $offset = 0)
    {

    }

    /**
     * import listes
     */
    public function uploadCSVContactslistData($CSVContent)
    {
        $this->hasList();

    }

    public function importCSVContactslistData($dataID)
    {
        $this->hasList();

    }

    /*
     * Send transactional
     * Mouais
     * */
    public function sendBulk($campagne ,$timestamp)
    {
        $batchMsg = $this->mailgun->BatchMessage('mg.droitne.ch');

        # Define the from address.
        $batchMsg->setFromAddress("info@droit.ch", ['name' => 'DroitNe']);
        # Define the subject.
        $batchMsg->setSubject($campagne->sujet);
        # Define the body of the message.
        $batchMsg->setHtmlBody($this->html);
        $batchMsg->setTextBody(strip_tags($this->html,'<a>'));
        $batchMsg->setDeliveryTime($timestamp,'Europe/Zurich');
        $batchMsg->setClickTracking(true);
        $batchMsg->addCustomHeader("campagne_id", $campagne->id);

        foreach ($this->recipients as $key => $recipient){
            $batchMsg->addToRecipient($recipient, ["id" => $key + 1]);
        }

        $batchMsg->finalize();
    }

    /*
     * Misc test
     * */
    public function hasList()
    {
        if(!$this->list){
            throw new \App\Exceptions\ListNotSetException('Attention aucune liste indiqué');
        }
    }

    public function hasHtml()
    {
        if(!$this->html){
            throw new \App\Exceptions\ListNotSetException('Attention aucun contenu indiqué');
        }
    }

    public function hasRecipients()
    {
        if(empty($this->recipients)){
            throw new \App\Exceptions\ListNotSetException('Attention aucun recipient indiqué');
        }
    }
}