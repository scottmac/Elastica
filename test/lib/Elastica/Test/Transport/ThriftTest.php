<?php

class Elastica_Thrift_Test extends Elastica_Test
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('Elasticsearch_RestClient')) {
            self::markTestSkipped('munkie/elasticsearch-thrift-php package should be installed to run thrift transport tests');
        }
    }

    public function testConstruct()
    {
        $host = 'localhost';
        $port = 9500;
        $client = new Elastica_Client(array('host' => $host, 'port' => $port, 'transport' => 'Thrift'));

        $this->assertEquals($host, $client->getConnection()->getHost());
        $this->assertEquals($port, $client->getConnection()->getPort());
    }

    /**
     * @dataProvider configProvider
     */
    public function testSearchRequest($config)
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Elastica_Client($config);

        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Elastica_Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $doc1->setVersion(0);
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Elastica_Document(2,
            array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Elastica_Document(3,
            array('username' => 'rolf', 'test' => array('2', '3', '7'))
        );
        $type->addDocuments($docs);

        // Refresh index
        $index->refresh();
        $resultSet = $type->search('rolf');

        $this->assertEquals(1, $resultSet->getTotalHits());
    }

    /**
     * @expectedException Elastica_Exception_Client
     */
    public function testInvalidHostRequest()
    {
        $client = new Elastica_Client(array('host' => 'unknown', 'port' => 9555, 'transport' => 'Thrift'));
        $client->getStatus();
    }

    /**
     * @expectedException Elastica_Exception_Response
     */
    public function testInvalidElasticRequest()
    {
        $connection = new Elastica_Connection();
        $connection->setHost('localhost');
        $connection->setPort(9500);
        $connection->setTransport('Thrift');

        $client = new Elastica_Client();
        $client->addConnection($connection);

        $index = new Elastica_Index($client, 'missing_index');
        $index->getStatus();
    }

    public function configProvider()
    {
        return array(
            array(
                array(
                    'host' => 'localhost',
                    'port' => 9500,
                    'transport' => 'Thrift'
                )
            ),
            array(
                array(
                    'host' => 'localhost',
                    'port' => 9500,
                    'transport' => 'Thrift',
                    'config' => array(
                        'framedTransport' => false,
                        'sendTimeout' => 10000,
                        'recvTimeout' => 20000,
                    )
                )
            )
        );
    }
}
