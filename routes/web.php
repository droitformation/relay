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

    $from = new SendGrid\Email("Example User", "test@example.com");
    $subject = "Sending with SendGrid is Fun";
    $to = new SendGrid\Email("Example User", "test@example.com");
    $content = new SendGrid\Content("text/plain", "and easy to do anywhere, even with PHP");
    $mail = new SendGrid\Mail($from, $subject, $to, $content);
    $apiKey = env('SENDGRID_API');
    $sg = new \SendGrid($apiKey);
    $response = $sg->client->mail()->send()->post($mail);

    echo $response->statusCode();
    print_r($response->headers());
    echo $response->body();

});