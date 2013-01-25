<?php

/**
 * Response exception
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Exception_Response extends Elastica_Exception_Abstract
{
    /**
     * Request
     *
     * @var Elastica_Request Request object
     */
    protected $_request = null;

    /**
     * Response
     *
     * @var Elastica_Response Response object
     */
    protected $_response = null;

    /**
     * Construct Exception
     *
     * @param Elastica_Request $request
     * @param Elastica_Response $response
     */
    public function __construct(Elastica_Request $request, Elastica_Response $response)
    {
        $this->_request = $request;
        $this->_response = $response;
        parent::__construct($response->getError());
    }

    /**
     * Returns request object
     *
     * @return Elastica_Request Request object
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Returns response object
     *
     * @return Elastica_Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
