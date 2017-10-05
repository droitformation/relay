<?php

class MailjetWorkerTest extends BrowserKitTest
{
    protected $mailjet;
    protected $resources;
    protected $campagne;

    public function setUp()
    {
        parent::setUp();

        $this->mailjet = Mockery::mock('\Mailjet\Client');
        $this->app->instance('\Mailjet\Client', $this->mailjet);

        $this->resources = Mockery::mock('\Mailjet\Resources');
        $this->app->instance('\Mailjet\Resources', $this->resources);
    }

    public function tearDown()
    {
        Mockery::close();
    }
    
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testSetList()
    {
        $worker = \App::make('App\Droit\Newsletter\Worker\MailjetServiceInterface');

        $worker->setList(1);

        $this->assertEquals(1, 1);
    }

    /**
     * @expectedException App\Exceptions\ListNotSetException
     */
    public function testNoListException()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $result = $worker->getSubscribers();
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testGetResponses()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $normalReponse = [
            'getAllLists',
            'getSubscribers',
            'getAllSubscribers',
        ];

        foreach($normalReponse as $call)
        {
            $this->responseOk();

            $worker->$call();
        }
    }

    public function testGetByEmail()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $return = [0 => ['ID' => 1234]];

        $this->responseOk($return);

        $worker->getContactByEmail('cindy.leschaud@gmail.com');
    }

    public function testAddEmail()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk([], 'post');

        $worker->addContactToList(1234);
    }

    public function testAddContactToList()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk([], 'post');

        $worker->addContactToList(1234);
    }

    public function testRemoveContact()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $return = [0 => ['ID' => 1234]];

        $response = Mockery::mock('\Mailjet\Response');

        $this->mailjet->shouldReceive('get')->twice()->andReturn($response);// called in getContactByEmail,getListRecipient
        $this->mailjet->shouldReceive('delete')->once()->andReturn($response); // called in removeContact
        $response->shouldReceive('success')->times(3)->andReturn(true); // called in getContactByEmail,getListRecipient,removeContact
        $response->shouldReceive('getData')->times(2)->andReturn($return);// called in getContactByEmail,getListRecipient

        $worker->removeContact('cindy.leschaud@gmail.com');
    }

    public function testGetCampagne()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk();

        $worker->getCampagne(1);
    }

    public function testUpdateCampagne()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk([], 'put');

        $worker->updateCampagne(1,0);
    }

    public function testCreateCampagne()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $return = [0 => ['ID' => 1234]];

        $this->responseOk($return, 'post');

        $worker->createCampagne($this->campagne);
    }

    public function testSetHtml()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk([], 'put');

        $worker->setHtml('',1);
    }

    public function testGetHtml()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $return = [0 => ['Html-part' => 'yeah']];

        $this->responseOk($return);

        $result = $worker->getHtml('',1);

        $this->assertEquals($result,'yeah');
    }

    public function testSendTest()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);

        $worker->setList(1);

        $this->responseOk([], 'post');

        $worker->sendTest(1,'cindy.leschaud@gmail.com','title');
    }

    public function testSendCampagne()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);
        $this->responseOk([], 'post');

        $worker->sendCampagne(1);
    }

    public function testSendCampagneFailed()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $response = Mockery::mock('\Mailjet\Response');

        $this->mailjet->shouldReceive('post')->once()->andReturn($response);
        $response->shouldReceive('success')->once()->andReturn(false);
        $response->shouldReceive('getData')->once()->andReturn([]);

        $result = $worker->sendCampagne(1);

        $this->assertFalse($result['success']);
    }

    public function testStatsCampagne()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $return = [0 => 123];

        $this->responseOk($return);

        $worker->statsCampagne(1);
    }

    public function testClickStatistics()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk([]);

        $worker->clickStatistics(1);
    }

    public function testUploadCSVContactslistData()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $return = ['ID' => 123];

        $this->responseOk($return, 'post');

        $worker->uploadCSVContactslistData("Email");
    }

    public function testImportCSVContactslistData()
    {
        $worker = new \App\Droit\Newsletter\Worker\MailjetService($this->mailjet,$this->resources);
        $worker->setList(1);

        $this->responseOk([], 'post');

        $worker->importCSVContactslistData(1);
    }

    public function responseOk($return = [], $type = 'get')
    {
        $response = Mockery::mock('\Mailjet\Response');

        $this->mailjet->shouldReceive($type)->once()->andReturn($response);
        $response->shouldReceive('success')->once()->andReturn(['success' => true]);
        $response->shouldReceive('getData')->once()->andReturn($return);
    }
}
