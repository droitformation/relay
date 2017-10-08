<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

Route::get('tracking', function () {

    $data = [
        [
            "sg_message_id" =>"sendgrid_internal_message_id",
            "email" => "john.doe@sendgrid.com",
            "timestamp" => 1337197600,
            "smtp-id" => "<4FB4041F.6080505@sendgrid.com>",
            "event" => "processed"
        ],
        [
            "sg_message_id" => "sendgrid_internal_message_id",
            "email" =>  "john.doe@sendgrid.com",
            "timestamp" =>  1337966815,
            "category" =>  "newuser",
            "event" =>  "click",
            "url" =>  "https => //sendgrid.com"
        ],
        [
            "sg_message_id" => "sendgrid_internal_message_id",
            "email" =>  "john.doe@sendgrid.com",
            "timestamp" =>  1337969592,
            "smtp-id" =>  "<20120525181309.C1A9B40405B3@Example-Mac.local>",
            "event" =>  "group_unsubscribe",
            "asm_group_id" =>  42
        ]
    ];

    $html = '<html><head><title>Hey new newsletter</title></head><body><h3>Nice!</h3><p>Fourth Campagne</p>
                <p>OhYeah!</p>
                <p><a href="https://google.ch">Un lien</a></p>
            </body></html>';



    $request_body = [
        'from' => [
            'email' => 'info@droitne.ch',
            'name' => 'Droitne'
        ],
        'subject' => 'Un tests',
        'content' => [
            [
                'type' => 'text/html',
                'value' => $html
            ]
        ],
        'personalizations' => [
            [
                'to' => [
                   ['email' => 'cindy.leschaud@gmail.com']
                ]
            ]
        ]
    ];


/*    echo '<pre>';
    print_r(json_encode($request_body));
    echo '</pre>';exit;*/

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $response = $sg->client->mail()->send()->post($request_body);

    echo '<pre>';
    echo $response->statusCode();
    echo $response->body();
    print_r($response->headers());
    echo '</pre>';

});

Route::get('/test', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $emails = ['cindy.leschaud@gmail.com','cindy.leschaud@unine.ch','info@designpond.ch'];

    $emails = collect($emails)->map(function ($email, $key) {
        return ['email' => $email];
    })->toArray();

    echo '<pre>';
    print_r($emails);
    echo '</pre>';exit;

   /* $from    = new SendGrid\Email("Example User", "test@example.com");
    $subject = "Sending with SendGrid is Fun";

    $to      = new SendGrid\Email("Example User", "test@example.com");
    $content = new SendGrid\Content("text/plain", "and easy to do anywhere, even with PHP");

    $mail    = new SendGrid\Mail($from, $subject, $to, $content);
    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $response = $sg->client->mail()->send()->post($mail);

    echo $response->statusCode();
    print_r($response->headers());
    echo $response->body();*/

});

Route::get('/getlist', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $response = $sg->client->contactdb()->lists()->get();

    echo '<pre>';
    echo $response->statusCode();
    echo $response->body();
    print_r($response->headers());
    echo '</pre>';

});

Route::get('/addtolist', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $request_body = json_decode('[
      "recipient_id1", 
      "recipient_id2"
    ]');

    $list_id = "test_list";

    $response = $sg->client->contactdb()->lists()->_($list_id)->recipients()->post($request_body);

    echo '<pre>';
    echo $response->statusCode();
    echo $response->body();
    print_r($response->headers());
    echo '</pre>';

});

Route::get('/addrecipient', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $request_body = json_decode('[
      {
        "email": "cindy.leschaud@gmail.com", 
        "first_name": "Cindy", 
        "last_name": "Leschaud"
      }
    ]');

    $response = $sg->client->contactdb()->recipients()->post($request_body);

    echo '<pre>';
    echo $response->statusCode();
    echo $response->body();
    print_r($response->headers());
    echo '</pre>';

});

Route::get('/getrecipient', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $query_params = json_decode('{"page": 1, "page_size": 1}');
    $response = $sg->client->contactdb()->recipients()->get(null, $query_params);

    echo '<pre>';
    echo $response->statusCode();
    echo $response->body();
    print_r($response->headers());
    echo '</pre>';

});


Route::get('/stats', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $query_params = [
        'aggregated_by' => 'day',
        'end_date' => '2017-10-08',
        'start_date' => '2017-10-08',
        'limit' => 1,
        'offset' => 1 ,
        'categories' => 'droit'
    ];

    $response = $sg->client->categories()->stats()->get(null, $query_params);

    echo '<pre>';
    echo $response->statusCode();
    print_r(json_decode($response->body()));
    print_r($response->headers());
    echo '</pre>';

});

Route::get('/implementation', function () {

    $sendgrid = App::make('App\Droit\Newsletter\Worker\SendgridInterface');

    $sendgrid->setList(2043174);

    $campagne = new stdClass();

    $campagne->id         = 2345;
    $campagne->titre      = 'My Fourth Campagne';
    $campagne->sujet      = 'This is the foruth draft';
    $campagne->from_email = 'info@droitne.ch';
    $campagne->from_name  = 'DroitNe';

    //$result = $sendgrid->getCampagne(1736467);
    $html = '<html><head><title>Hey new newsletter</title></head><body><h3>Nice!</h3><p>Fourth Campagne</p>
                <p>OhYeah!</p>
                <p><a href="https://google.ch">Un lien</a></p>
                <a href="[unsubscribe]">Click Here to Unsubscribe</a>
            </body></html>';

    $emails = ['cindy.leschaud@gmail.com','cindy.leschaud@unine.ch','info@designpond.ch'];
   // $result = $sendgrid->addContactToList($emails);
    //$result = $sendgrid->subscribeEmailToList('cindy@designpond.ch');

    //$result = $sendgrid->addContactToList(base64_encode('cindy.leschaud@gmail.com'));

    //$result = $sendgrid->createCampagne($campagne, $categories = ['campagne_'.$campagne->id]);
    $result = $sendgrid->sendBulk($campagne,$html,$emails);

    //$result = $sendgrid->setHtml($html, 1742529);
    //$result = $sendgrid->getHtml(1742529);
    $toSend = \Carbon\Carbon::now()->addMinutes(5)->timestamp;

    //$result = $sendgrid->sendCampagne(1742529,$toSend);
   // $result = $sendgrid->deleteCampagne(1742529);

    echo '<pre>';
    print_r($result);
    echo '</pre>';exit;

});