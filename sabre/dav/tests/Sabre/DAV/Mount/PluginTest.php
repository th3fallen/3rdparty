<?php

namespace Sabre\DAV\Mount;

use Sabre\DAV;
use Sabre\HTTP;

require_once 'Sabre/DAV/AbstractServer.php';

class PluginTest extends DAV\AbstractServer {

    function setUp() {

        parent::setUp();
        $this->server->addPlugin(new Plugin());

    }

    function testPassThrough() {

        $serverVars = array(
            'REQUEST_URI'    => '/',
            'REQUEST_METHOD' => 'GET',
        );

        $request = new HTTP\Request($serverVars);
        $this->server->httpRequest = ($request);
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 501 Not Implemented',$this->response->status,'We expected GET to not be implemented for Directories. Response body: ' . $this->response->body);

    }

    function testMountResponse() {

        $serverVars = array(
            'REQUEST_URI'    => '/?mount',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING'   => 'mount',
            'HTTP_HOST'      => 'example.org',
        );

        $request = new HTTP\Request($serverVars);
        $this->server->httpRequest = ($request);
        $this->server->exec();

        $this->assertEquals('HTTP/1.1 200 OK',$this->response->status);

        $xml = simplexml_load_string($this->response->body);
        $this->assertTrue($xml==true,'Response was not a valid xml document');

        $xml->registerXPathNamespace('dm','http://purl.org/NET/webdav/mount');
        $url = $xml->xpath('//dm:url');
        $this->assertEquals('http://example.org/',(string)$url[0]);

    }

}
