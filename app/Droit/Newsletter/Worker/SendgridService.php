<?php namespace App\Droit\Newsletter\Worker;

use App\Droit\Newsletter\Worker\SendgridInterface;

use Illuminate\Http\Request;
use \SendGrid;
use \SendGrid\Mail;
use \SendGrid\Email;
use \SendGrid\Content;

/**
 * Created by PhpStorm.
 * User: cindyleschaud
 * Date: 05.10.17
 * Time: 09:56
 */
class SendgridService implements SendgridInterface
{
    protected $sendgrid;

    protected $sender = '';
    protected $list   = null;

    public function __construct(SendGrid $sendgrid)
    {
        $this->sendgrid = $sendgrid;
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

    public function getAllLists()
    {
        $response = $this->sendgrid->client->contactdb()->lists()->get();

        if($response->statusCode() == 200)
            return json_decode($response->body());
        else
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
    }

    public function getSubscribers()
    {
        $this->hasList();

        $query_params = ['page' => 1, 'page_size' => 1];
        $response     = $this->sendgrid->client->contactdb()->lists()->_($this->list)->recipients()->get(null, $query_params);

        if($response->statusCode() == 200)
            return json_decode($response->body());
        else
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
    }

    public function getAllSubscribers()
    {
        $response = $this->sendgrid->get(Resources::$Contact);

        if($response->success())
            return json_decode($response->body());
        else
            return false;
    }

    public function addContact($emails)
    {
        if(is_array($emails)){
            $request_body = collect($emails)->map(function ($email, $key) {
                return ['email' => $email];
            })->toArray();
        }
        else{
            $request_body = [
                ['email' => $emails]
            ];
        }

        $response = $this->sendgrid->client->contactdb()->recipients()->post($request_body);

        if($response->statusCode() == 201)
            return true;
        else
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());

            //return $this->getContactByEmail($email);
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
                return base64_encode($email);
            })->toArray();
        }
        else{
            $request_body = [base64_encode($emails)];
        }

        $response = $this->sendgrid->client->contactdb()->lists()->_($this->list)->recipients()->post($request_body);

        if($response->statusCode() == 201)
            return true;
        else
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
    }

    public function subscribeEmailToList($email)
    {
        $this->hasList();

        $this->addContact($email);

        $response = $this->sendgrid->client->contactdb()->lists()->_($this->list)->recipients()->_(base64_encode($email))->post();

        if($response->statusCode() == 201)
            return true;
        else
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
    }

    public function removeContact($email)
    {
        $response = $this->sendgrid->client->contactdb()->lists()->_($this->list)->recipients()->_(base64_encode($email))->delete(null);

        if($response->statusCode() == 204)
            return true;
        else
            return true;
            // throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
            // It's ok if it fails
    }

    /**
     * Lists
     */
    public function getListRecipient($email)
    {
        $this->hasList();

        $response = $this->sendgrid->client->contactdb()->recipients()->_(base64_encode($email))->lists()->get();

        if($response->statusCode() == 200)
            return json_decode($response->body());
        else
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
    }

    /**
     * Campagnes
     */
    public function getAllCampagne(){

        $response = $this->sendgrid->client->campaigns()->get(null);

        if($response->statusCode() == 200){

            $data = json_decode($response->body());

            return $data->result;
        }
        else{
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
        }

    }

    public function getCampagne($CampaignID)
    {
        $response = $this->sendgrid->client->campaigns()->_($CampaignID)->get();

        if($response->statusCode() == 200){
            return json_decode($response->body());
        }
        else{
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
        }
    }

    public function updateCampagne($CampaignID, $status)
    {
        $request_body = ['status' => $status];

        $response = $this->sendgrid->client->campaigns()->_($CampaignID)->patch($request_body);

        if($response->statusCode() == 200){
            return json_decode($response->body());
        }
        else {
            throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());
        }
    }

    public function createCampagne($campagne, $categories = ['droit'])
    {
        $this->hasList();

        # Parameters
        $request_body = [
            'title'          => $campagne->titre,
            'subject'        => $campagne->sujet,
            'list_ids'       => [$this->list],
            'categories'     => $categories,
            'segment_ids'    => [],
            'plain_content'  => 'FirstCampagne',
            'suppression_group_id' => 3927,
            'sender_id'      => 171948,
            'html_content'   => '<html><head><title></title></head><body><p>FirstCampagne</p></body></html>',
            'SenderEmail'    => $campagne->from_email,
            'Sender'         => $campagne->from_name
        ];

        # Call
        $response = $this->sendgrid->client->campaigns()->post($request_body);

        if($response->statusCode() == 201){
           // $data = json_decode($response->body());
            return json_decode($response->body());
        }

        throw new \App\Exceptions\SendgridImplementationException($message = $response->body(), $response->statusCode());

    }

    public function setHtml($html,$id, $updates = null)
    {
        $request_body = [
            'html_content' => $html,
            'plain_content' => strip_tags($html,'<a>')
        ];

        if(isset($updates) && !empty($updates)){
            $request_body = $request_body + $updates;
        }

        $response = $this->sendgrid->client->campaigns()->_($id)->patch($request_body);

        if($response->statusCode() == 200)
            return json_decode($response->body());
        else
            return false;
    }

    public function getHtml($id)
    {
        $response = $this->sendgrid->client->campaigns()->_($id)->get();

        if($response->statusCode() == 200){
            $html = json_decode($response->body());
            return !empty($html->html_content) ? $html->html_content : '';
        }

        return false;
    }

    public function sendTest($id,$email)
    {
        $request_body = ['to' => $email];

        $response = $this->sendgrid->client->campaigns()->_($id)->schedules()->test()->post($request_body);

        if($response->statusCode() == 204){
            return true;
        }

        return json_decode($response->body());
    }

    public function sendCampagne($id, $date = null)
    {
        $this->hasList();

        $request_body = $date ? ['send_at' => $date] : \Carbon\Carbon::now()->addMinutes(15)->timestamp;

        $response = $this->sendgrid->client->campaigns()->_($id)->schedules()->post($request_body);

        if($response->statusCode() == 200){
            return true;
        }

        return json_decode($response->body());
    }

    public function deleteCampagne($id)
    {
        $response = $this->sendgrid->client->campaigns()->_($id)->schedules()->delete();

        if($response->statusCode() == 204){
            return true;
        }

        return json_decode($response->body());
    }

    /**
     * Statistiques
     */
    public function statsCampagne($id)
    {
        $response = $this->sendgrid->get(Resources::$Campaignstatistics, ['ID' => 'mj.nl='.$id]);

        if($response->success()){
            $stats = $response->getData();
            return $stats[0]; // returns ID directly
        }

        return false;
    }

    public function clickStatistics($id, $offset = 0)
    {
        $response = $this->sendgrid->get(Resources::$Toplinkclicked, ['filters' => ['CampaignID' => $id, 'Limit' => 500, 'Offset' => $offset]]);

        if($response->success())
        {
            return $response->getData();
        }

        return [];
    }

    /**
     * import listes
     */
    public function uploadCSVContactslistData($CSVContent)
    {
        $this->hasList();

        $response = $this->sendgrid->post(Resources::$ContactslistCsvdata, ['body' => $CSVContent, 'id' => $this->list]);

        if($response->success()){
            $import = $response->getData();
            return $import['ID']; // returns ID directly
        }

        return false;
    }

    public function importCSVContactslistData($dataID)
    {
        $this->hasList();

        $body = [
            'ContactsListID' => $this->list,
            'DataID'         => $dataID,
            'Method'         => "addnoforce"
        ];

        $response = $this->sendgrid->post(Resources::$Csvimport, ['body' => $body]);

        if($response->success()){
            return $response->getData();
        }

        return false;
    }

    /*
     * Send transactional
     * */
    public function sendBulk($campagne,$html,$recipients)
    {
        $to = [];

        if(!empty($recipients)){
            $to = collect($recipients)->map(function ($email, $key) {
                return ['email' => $email];
            })->toArray();
        }

        $request_body = [
            'from' => [
                'email' => $campagne->from_email,
                'name' => $campagne->from_name,
            ],
            'subject' => $campagne->sujet,
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $html
                ]
            ],
            'personalizations' => [
                [
                    'to' => $to
                ]
            ]
        ];

        $response = $this->sendgrid->client->mail()->send()->post($request_body);

        if($response->statusCode() == 202){
            return true;
        }

        echo '<h3><br/>Merci de faire une copie d\'écran de ce message d\'erreur et de la transmettre à cindy.leschaud@gmail.com</h3>';
        var_dump(json_decode($response->body()));
        var_dump($response->statusCode());
        var_dump($response->headers());
        exit;

    }

    /*
     * Misc test
     * */
    public function hasList()
    {
        if(!$this->list){
            throw new \App\Exceptions\ListNotSetException('Attention aucune liste indiqueé');
        }
    }
}