<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SoapController extends AbstractController
{
    public function sendSoapRequest(Request $request)
    {
        $soapData = [
            'customerId' => '12345',
            'items' => [
                ['id' => '1', 'price' => 19.99],
                ['id' => '2', 'price' => 29.99],
            ],
        ];

        $soapXml = $this->generateSoapXml($soapData);

        $soapUrl = 'http://example.com/soap-service';

        $client = HttpClient::create();
        $response = $client->request('POST', $soapUrl, [
            'headers' => [
                'Content-Type' => 'text/xml;charset=UTF-8',
            ],
            'body' => $soapXml,
        ]);

        $responseContent = $response->getContent();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'SOAP request sent successfully',
            'response' => $responseContent,
        ]);
    }

    private function generateSoapXml($data)
    {
        $xml = new \SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://www.example.com/webservice"/>');

        $body = $xml->addChild('soapenv:Body');
        $createOrderRequest = $body->addChild('web:CreateOrderRequest');

        $createOrderRequest->addChild('customerId', $data['customerId']);
        $items = $createOrderRequest->addChild('items');

        foreach ($data['items'] as $item) {
            $itemElement = $items->addChild('item');
            $itemElement->addChild('id', $item['id']);
            $itemElement->addChild('price', $item['price']);
        }

        return $xml->asXML();
    }

}
