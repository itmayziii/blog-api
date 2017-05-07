<?php

class ApiTest extends TestCase
{
    public function test_contacts_show_authentication()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/vnd.api+json';
        $_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
        $response = $this->call('GET', 'v1/contacts', [], [], [], $_SERVER);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_contacts_show_authorization()
    {
        $this->actAsStandardUser();
        $_SERVER['CONTENT_TYPE'] = 'application/vnd.api+json';
        $_SERVER['HTTP_ACCEPT'] = 'application/vnd.api+json';
        $response = $this->call('GET', 'v1/contacts', [], [], [], $_SERVER);
        $this->assertEquals(403, $response->getStatusCode());
    }
}