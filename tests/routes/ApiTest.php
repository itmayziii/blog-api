<?php

class ApiTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function test_contacts_show_requires_authentication()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/vnd.api+json';
        $_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
        $response = $this->call('GET', 'v1/contacts', [], [], [], $_SERVER);
        $this->assertThat($response->getStatusCode(), $this->equalTo(401));
    }
}