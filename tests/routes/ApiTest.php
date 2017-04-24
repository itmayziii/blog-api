<?php

class ApiTest extends TestCase
{
    public function test_contacts_show_authentication()
    {
        $response = $this->call('GET', 'v1/contacts/1');
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_contacts_show_authorization()
    {
        $response = $this->call('GET', 'v1/contacts');
        $this->assertEquals(401, $response->getStatusCode());
    }
}