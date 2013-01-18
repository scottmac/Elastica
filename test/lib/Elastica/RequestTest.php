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

        $string = $request->toString();

        $expected = 'curl -XPOST \'http://localhost:9200/test?no=params\' -d \'{"key":"value"}\'';
        $this->assertEquals($expected, $string);

        $string = (string) $request;
        $this->assertEquals($expected, $string);
    }
}
