<?php

/**
 * Elastica Thrift Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Mikhail Shamin <munk13@gmail.com>
 */
class Elastica_Transport_Thrift extends Elastica_Transport_Abstract
{
    /**
     * @var RestClient[]
     */
    protected $_clients = array();

    /**
     * Construct transport
     *
     * @param Elastica_Connection $connection Connection object
     * @throws Elastica_Exception_Runtime
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        if (!class_exists('Elasticsearch_RestClient')) {
            throw new Elastica_Exception_Runtime('Elasticsearch_RestClient class not found. Check that suggested package munkie/elasticsearch-thrift-php is required in composer.json');
        }
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $sendTimeout msec
     * @param int $recvTimeout msec
     * @param bool $framedTransport
     * @return Elasticsearch_RestClient
     */
    protected function _createClient($host, $port, $sendTimeout = null, $recvTimeout = null, $framedTransport = false)
    {
        $socket = new TSocket($host, $port, true);

        if (null !== $sendTimeout) {
            $socket->setSendTimeout($sendTimeout);
        }

        if (null !== $recvTimeout) {
            $socket->setRecvTimeout($recvTimeout);
        }

        if ($framedTransport) {
            $transport = new TFramedTransport($socket);
        } else {
            $transport = new TBufferedTransport($socket);
        }
        $protocol = new TBinaryProtocolAccelerated($transport);

        $client = new Elasticsearch_RestClient($protocol);

        $transport->open();

        return $client;
    }

    /**
     * @param string $host
     * @param int $port
     * @param int $sendTimeout
     * @param int $recvTimeout
     * @param bool $framedTransport
     * @return Elasticsearch_RestClient
     */
    protected function _getClient($host, $port, $sendTimeout = null, $recvTimeout = null, $framedTransport = false)
    {
        $key = $host . ':' . $port;
        if (!isset($this->_clients[$key])) {
            $this->_clients[$key] = $this->_createClient($host, $port, $sendTimeout, $recvTimeout, $framedTransport);
        }
        return $this->_clients[$key];
    }

    /**
     * Makes calls to the elasticsearch server
     *
     * @param Elastica_Request $request
     * @param  array             $params Host, Port, ...
     * @throws Elastica_Exception_Thrift
     * @throws Elastica_Exception_Response
     * @return Elastica_Response Response object
     */
    public function exec(Request $request, array $params)
    {
        $connection = $this->getConnection();

        $sendTimeout = $connection->hasConfig('sendTimeout') ? $connection->getConfig('sendTimeout') : null;
        $recvTimeout = $connection->hasConfig('recvTimeout') ? $connection->getConfig('recvTimeout') : null;
        $framedTransport = $connection->hasConfig('framedTransport') ? (bool) $connection->getConfig('framedTransport') : false;

        try {
            $client = $this->_getClient(
                $connection->getHost(),
                $connection->getPort(),
                $sendTimeout,
                $recvTimeout,
                $framedTransport
            );

            $restRequest = new Elasticsearch_RestRequest();
            $restRequest->method = array_search($request->getMethod(), Method::$__names);
            $restRequest->uri = $request->getPath();

            $query = $request->getQuery();
            if (!empty($query)) {
                $restRequest->parameters = $query;
            }

            $data = $request->getData();
            if (!empty($data)) {
                if (is_array($data)) {
                    $content = json_encode($data);
                } else {
                    $content = $data;
                }
                $restRequest->body = $content;
            }

            /* @var $result RestResponse */
            $start = microtime(true);

            $result = $client->execute($restRequest);
            $response = new Elastica_Response($result->body);

            $end = microtime(true);
        } catch (TException $e) {
            $response = new Elastica_Response('');
            throw new Elastica_Exception_Thrift($e, $request, $response);
        }

        if (defined('DEBUG') && DEBUG) {
            $response->setQueryTime($end - $start);
        }

        if ($response->hasError()) {
            throw new Elastica_Exception_Response($response);
        }

        return $response;
    }
}
