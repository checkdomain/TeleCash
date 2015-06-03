<?php

namespace Checkdomain\TeleCash\IPG\API\Request;

use Checkdomain\TeleCash\IPG\API\AbstractRequest;
use Checkdomain\TeleCash\IPG\API\Service\OrderService;

/**
 * Class ActionRequest
 *
 * @package Checkdomain\TeleCash\IPG\API\Service
 */
class ActionRequest extends AbstractRequest
{
    protected $service;

    /**
     * @param OrderService $service
     */
    public function __construct(OrderService $service)
    {
        $this->service = $service;
        $this->document = new \DOMDocument('1.0', 'UTF-8');

        $this->element = $this->document->createElement('ns3:IPGApiActionRequest');
    }

}
