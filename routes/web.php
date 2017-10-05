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


Route::get('/test', function () {

    $apiKey = env('SENDGRID_API');
    $sg     = new \SendGrid($apiKey);

    $response = $sg->client->senders()->get();
    echo $response->statusCode();
    echo $response->body();
    print_r($response->headers());

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
        'end_date' => date('Y-m-d'),
        'aggregated_by' => 'day',
        'limit' => 1,
        'offset' => 1 ,
        'start_date' => date('Y-m-d'),
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

    $campagne->titre      = 'My Second Campagne';
    $campagne->sujet      = 'This is the scond draft';
    $campagne->from_email = 'info@droitne.ch';
    $campagne->from_name  = 'DroitNe';

    //$result = $sendgrid->getCampagne(1736467);
    $html = '<html><head><title>Hey new newsletter updatef</title></head><body><h3>Nice!</h3><p>FirstCampagne</p><p>OhYeah!</p>
                <a href="[unsubscribe]">Click Here to Unsubscribe</a>
            </body></html>';

    //$result = $sendgrid->addContactToList(base64_encode('cindy.leschaud@gmail.com'));
    //$result = $sendgrid->createCampagne($campagne);

    //$result = $sendgrid->setHtml($html, 1737540);
    //$result = $sendgrid->getHtml(1737540);
    $toSend = \Carbon\Carbon::now()->addMinutes(2)->timestamp;

    $result = $sendgrid->sendCampagne(1737540,$toSend);
   // $result = $sendgrid->deleteCampagne(1737540);

    echo '<pre>';
    print_r($result);
    echo '</pre>';exit;

});