<?php
// src/Controller/SoapController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SoapController extends AbstractController
{
    public function sendSoapRequest(Request $request)
    {
        // Données SOAP à envoyer
        $soapData = [
            'customerId' => '12345',
            'items' => [
                ['id' => '1', 'price' => 19.99],
                ['id' => '2', 'price' => 29.99],
            ],
        ];

        // Créer la requête SOAP en XML
        $soapXml = $this->generateSoapXml($soapData);

        $soapUrl = 'http://example.com/soap-service';

        // Utilisation de HttpClient pour envoyer la requête SOAP
        $client = HttpClient::create();
        $response = $client->request('POST', $soapUrl, [
            'headers' => [
                'Content-Type' => 'text/xml;charset=UTF-8',
            ],
            'body' => $soapXml,
        ]);

        // Vérifiez la réponse du service SOAP
        $responseContent = $response->getContent();

        // Vous pouvez retourner la réponse sous forme de JSON ou effectuer des actions en fonction du résultat
        return new JsonResponse([
            'status' => 'success',
            'message' => 'SOAP request sent successfully',
            'response' => $responseContent,
        ]);
    }

    // Générer le XML SOAP à partir des données
    private function generateSoapXml($data)
    {
        // Construire le XML pour la requête SOAP
        $xml = new \SimpleXMLElement('<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://www.example.com/webservice"/>');

        $body = $xml->addChild('soapenv:Body');
        $createOrderRequest = $body->addChild('web:CreateOrderRequest');

        // Ajouter les éléments de la commande
        $createOrderRequest->addChild('customerId', $data['customerId']);
        $items = $createOrderRequest->addChild('items');

        foreach ($data['items'] as $item) {
            $itemElement = $items->addChild('item');
            $itemElement->addChild('id', $item['id']);
            $itemElement->addChild('price', $item['price']);
        }

        // Retourner le XML en tant que chaîne
        return $xml->asXML();
    }

}
