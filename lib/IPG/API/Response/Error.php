<?php

namespace Checkdomain\TeleCash\IPG\API\Response;

use Checkdomain\TeleCash\IPG\API\AbstractResponse;
use Checkdomain\TeleCash\IPG\API\Service\OrderService;

/**
 * Class Error
 */
class Error extends AbstractResponse
{

    const SOAP_ERROR_SERVER = 'SOAP-ENV:Server';
    const SOAP_ERROR_CLIENT = 'SOAP-ENV:Client';

    const SOAP_CLIENT_ERROR_MERCHANT   = 'MerchantException';
    const SOAP_CLIENT_ERROR_PROCESSING = 'ProcessingException';

    const ERROR_TYPE_SERVER         = 'Server-Error';
    const ERROR_TYPE_CLIENT         = 'Client-Error';
    const ERROR_TYPE_NOT_SUCCESSFUL = 'Not successful';

    /** @var string $errorType */
    private $errorType;
    /** @var string $errorMessage */
    private $errorMessage;
    /** @var string $clientErrorType */
    private $clientErrorType;
    /** @var string $clientErrorDetail */
    private $clientErrorDetail;

    /**
     * @return string
     */
    public function getErrorType()
    {
        return $this->errorType;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getClientErrorType()
    {
        return $this->clientErrorType;
    }

    /**
     * @return string
     */
    public function getClientErrorDetail()
    {
        return $this->clientErrorDetail;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $exceptionText = "SOAP Error: " . $this->errorType . " ";

        if ($this->errorType === self::ERROR_TYPE_SERVER) {
            $exceptionText .= '(' . $this->errorMessage . ')';
        } else {
            $exceptionText .= '(' . $this->clientErrorType . ': ' . $this->clientErrorDetail . ')';
        }

        return $exceptionText;
    }

    /**
     * @param \DOMDocument $document
     *
     * @return Error|null
     * @throws \Exception
     */
    public static function createFromSoapFault(\DOMDocument $document)
    {
        $response = null;
        $errorElement = $document->getElementsByTagNameNS(OrderService::NAMESPACE_SOAP, 'Fault');

        if ($errorElement->length > 0) {
            $response = new Error();

            $faultCode              = $document->getElementsByTagName('faultcode');
            $response->errorMessage = $document->getElementsByTagName('faultstring')->item(0)->nodeValue;

            switch ($faultCode->item(0)->nodeValue) {
                case self::SOAP_ERROR_SERVER:
                    $response->errorType = self::ERROR_TYPE_SERVER;

                    break;

                case self::SOAP_ERROR_CLIENT:
                    $response->errorType   = self::ERROR_TYPE_CLIENT;
                    $errorDetail = $document->getElementsByTagName('detail');

                    if (strpos($response->errorMessage, ':') !== false) {
                        $response->clientErrorType = substr($response->errorMessage, 0, strpos($response->errorMessage, ':'));
                    } else {
                        $response->clientErrorType = $response->errorMessage;
                    }

                    switch ($response->clientErrorType) {
                        case self::SOAP_CLIENT_ERROR_MERCHANT:
                            $response->clientErrorDetail = $errorDetail->item(0)->nodeValue;
                            break;

                        case self::SOAP_CLIENT_ERROR_PROCESSING:
                            $response->clientErrorDetail = $response->firstElementByTagNSString($document, OrderService::NAMESPACE_N3, 'ErrorMessage');;
                            break;

                        default:
                            throw new \Exception("Undefined SOAP Client Exception: " . $response->clientErrorType . ' (Complete SOAP Fault: ' . $document->saveXML() . ')');
                    }
                    break;

                default:
                    throw new \Exception("Undefined SOAP Error: (" . $document->saveXML() . ")");
            }
        }

        return $response;
    }
}
