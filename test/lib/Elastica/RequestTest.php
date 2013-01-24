<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_RequestTest extends Elastica_Test
{

    public function testConstructor()
    {
        $path = 'test';
        $method = Elastica_Request::POST;
        $query = array('no' => 'params');
        $data = array('key' => 'value');

        $request = new Elastica_Request($path, $method, $data, $query);

        $this->assertEquals($path, $request->getPath());
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($query, $request->getQuery());
        $this->assertEquals($data, $request->getData());
    }

    /**
     * @expectedException Elastica_Exception_Invalid
     */
    public function testInvalidConnection()
    {
        $request = new Elastica_Request('', Elastica_Request::GET);
        $request->send();
    }

    public function testSend()
    {
        $connection = new Elastica_Connection();
        $connection->setHost('localhost');
        $connection->setPort('9200');

        $request = new Request('_status', Elastica_Request::GET, array(), array(), $connection);

        $response = $request->send();

        $this->assertInstanceOf('Elastica_Response', $response);
    }

    public function testToString()
    {
        $path = 'test';
        $method = Elastica_Request::POST;
        $query = array('no' => 'params');
        $data = array('key' => 'value');

        $connection = new Elastica_Connection();
        $connection->setHost('localhost');
        $connection->setPort('9200');

        $request = new Elastica_Request($path, $method, $data, $query, $connection);

        $data = $request->toArray();

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('path', $data);
        $this->assertArrayHasKey('query', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('connection', $data);
        $this->assertEquals($request->getMethod(), $data['method']);
        $this->assertEquals($request->getPath(), $data['path']);
        $this->assertEquals($request->getQuery(), $data['query']);
        $this->assertEquals($request->getData(), $data['data']);
        $this->assertInternalType('array', $data['connection']);
        $this->assertArrayHasKey('host', $data['connection']);
        $this->assertArrayHasKey('port', $data['connection']);
        $this->assertEquals($request->getConnection()->getHost(), $data['connection']['host']);
        $this->assertEquals($request->getConnection()->getPort(), $data['connection']['port']);

        $string = $request->toString();

        $this->assertInternalType('string', $string);

        $string = (string) $request;
        $this->assertInternalType('string', $string);
    }
}
