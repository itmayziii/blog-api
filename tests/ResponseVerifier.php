<?php

namespace itmayziii\tests;

use Illuminate\Http\Response;

class ResponseVerifier extends TestCase
{
    public function testUnauthorized(Response $response)
    {
        $expectedResponse = [
            'status' => 403,
            'title'  => 'Forbidden',
            'detail' => 'Authorization checks failed.'
        ];

        $this->assertThat($response->getStatusCode(), $this->equalTo(403));
        $this->assertThat($response->getOriginalContent(), $this->identicalTo($expectedResponse));
    }
}