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

    public function test_blogs_store_authentication()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/vnd.api+json';
        $_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
        $response = $this->call('POST', 'v1/blogs', [], [], [], $_SERVER);
        $this->assertThat($response->getStatusCode(), $this->equalTo(401));
    }
}