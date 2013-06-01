<?php

use Amp\ReactorFactory, 
    Artax\AsyncClient,
    Artax\Client,
    Artax\Request;

class ClientHttpBinIntegrationTest extends PHPUnit_Framework_TestCase {
    
    private $client;
    
    function setUp() {
        $this->client = new Client;
    }
    
    function tearDown() {
        $this->client = NULL;
    }
    
    function testUserAgentResponse() {
        $uri = 'http://httpbin.org/user-agent';
        $response = $this->client->request($uri);
        $body = $response->getBody();
        
        $result = json_decode($body);
        
        $this->assertEquals(Client::USER_AGENT, $result->{'user-agent'});
    }
    
    function testPostStringBody() {
        $uri = 'http://httpbin.org/post';
        
        $body = 'zanzibar';
        $request = (new Request)->setUri($uri)->setMethod('POST')->setBody($body);
        $response = $this->client->request($request);
        $rcvdBody = $response->getBody();
        
        $result = json_decode($rcvdBody);
        
        $this->assertEquals($body, $result->data);
    }
    
    function testPostResourceBody() {
        $uri = 'http://httpbin.org/post';
        
        $body = 'zanzibar';
        $bodyStream = fopen('php://memory', 'r+');
        fwrite($bodyStream, $body);
        rewind($bodyStream);
        
        $request = (new Request)->setUri($uri)->setMethod('POST')->setBody($bodyStream);
        $response = $this->client->request($request);
        $rcvdBody = $response->getBody();
        
        $result = json_decode($rcvdBody);
        
        $this->assertEquals($body, $result->data);
    }
    
    function testPutStringBody() {
        $uri = 'http://httpbin.org/put';
        
        $body = 'zanzibar';
        $request = (new Request)->setUri($uri)->setMethod('PUT')->setBody($body);
        $response = $this->client->request($request);
        $rcvdBody = $response->getBody();
        
        $result = json_decode($rcvdBody);
        
        $this->assertEquals($body, $result->data);
    }
    
    /**
     * @dataProvider provideStatusCodes
     */
    function testStatusCodeResponses($statusCode) {
        $uri = "http://httpbin.org/status/{$statusCode}";
        $response = $this->client->request($uri);
        $this->assertEquals($statusCode, $response->getStatus());
    }
    
    function provideStatusCodes() {
        return array(
            array(200),
            array(400),
            array(404),
            array(500)
        );
    }
    
    function testReason() {
        $uri = "http://httpbin.org/status/418";
        $response = $this->client->request($uri);
        $this->assertEquals("I'M A TEAPOT", $response->getReason());
    }
    
    function testRedirect() {
        $statusCode = 299;
        $redirectTo = "/status/{$statusCode}";
        $uri = "http://httpbin.org/redirect-to?url={$redirectTo}";
        $response = $this->client->request($uri);
        $this->assertEquals($statusCode, $response->getStatus());
    }
    
    function testVerboseSend() {
        $expectedOutput = '' .
            "GET / HTTP/1.1\r\n" .
            "Host: httpbin.org:80\r\n" . 
            "User-Agent: " . Client::USER_AGENT . "\r\n\r\n";
        
        $this->expectOutputString($expectedOutput);
        
        $uri = "http://httpbin.org/";
        $this->client->setOption('verboseSend', TRUE);
        $response = $this->client->request($uri);
    }
    
}

