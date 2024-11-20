<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{

    #[Route('/api/get-price', methods: ['GET'])]
    public function getPrice(Request $request): JsonResponse
    {
        $factory = $request->query->get('factory');
        $collection = $request->query->get('collection');
        $article = $request->query->get('article');

        if (!$factory || !$collection || !$article) {
            return new JsonResponse(['error' => 'Missing parameters: factory, collection, and article are required'], 400);
        }

        $url = "https://tile.expert/fr/tile/$factory/$collection/a/$article";

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($url);
            $html = $response->getBody()->getContents();

            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML($html);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);

            $priceNode = $xpath->query('//span[contains(@class, "js-price-tag")]');

            if ($priceNode->length === 0) {
                return new JsonResponse(['error' => 'Price not found on the page'], 404);
            }

            $priceRaw = $priceNode->item(0)->getAttribute('data-price-raw');
            $priceDisplay = trim($priceNode->item(0)->textContent);

            return new JsonResponse([
                'price_raw' => floatval($priceRaw),
                'price_display' => $priceDisplay,
                'factory' => $factory,
                'collection' => $collection,
                'article' => $article,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/orders/grouped', name: 'get_grouped_orders', methods: ['GET'])]
    public function getGroupedOrders(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, (int) $request->query->get('perPage', 10));

        $client = \Symfony\Component\Panther\Client::createChromeClient();

        try {

            $crawler = $client->request('GET', 'https://tile.expert/fr/tile/cobsa/manual');

            $priceBlocks = $crawler->filter('.js-full-price-block li')->each(function ($node) {
                return [
                    'priceRaw' => $node->filter('.js-price-tag')->attr('data-price-raw'),
                    'priceFormatted' => $node->filter('.js-price-tag')->text(),
                    'currency' => '€/m²',
                ];
            });

            $totalItems = count($priceBlocks);
            $totalPages = ceil($totalItems / $perPage);
            $data = array_slice($priceBlocks, ($page - 1) * $perPage, $perPage);

            return new JsonResponse([
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalItems,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/soap/send', name: 'send_data_to_soap', methods: ['POST'])]
    public function sendDataToSoap(Request $request): JsonResponse
    {
        $data = [
            'pricePerSquareMeter' => 40.5,
            'currency' => 'EUR',
            'date' => (new \DateTime())->format('Y-m-d H:i:s'),
        ];

        $wsdl = "https://tile.expert/fr/tile/cobsa/manual";

        try {
            $client = new \SoapClient($wsdl, [
                'trace' => true,
                'exceptions' => true,
            ]);

            $response = $client->__soapCall('CreateOrder', [
                'parameters' => $data,
            ]);

            return new JsonResponse([
                'status' => 'success',
                'response' => $response,
            ]);
        } catch (\SoapFault $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
