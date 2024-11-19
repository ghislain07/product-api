<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController extends AbstractController
{

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;

    }

    #[Route('/api/get-price', methods: ['GET'])]
    #[Route('/api/orders', name: 'get_orders', methods: ['GET'])]

    public function getPrice(Request $request): JsonResponse
    {
        $factory = $request->query->get('factory');
        $collection = $request->query->get('collection');
        $article = $request->query->get('article');

        if ($factory !== 'cobsa' || $collection !== 'manual' || $article !== 'manu7530whbm-manualwhite7-5x30') {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
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

            // Rechercher la balise contenant le prix
            $priceNode = $xpath->query('//span[contains(@class, "js-price-tag")]');

            if ($priceNode->length === 0) {
                return new JsonResponse(['error' => 'Price not found on the page'], 404);
            }

            // Extraire le prix
            $priceRaw = $priceNode->item(0)->getAttribute('data-price-raw'); // Prix brut
            $priceDisplay = trim($priceNode->item(0)->textContent); // Prix affiché

            return new JsonResponse([
                'price_raw' => floatval($priceRaw), // Prix en valeur numérique
                'price_display' => $priceDisplay, // Prix formaté tel qu'affiché sur le site
                'factory' => $factory,
                'collection' => $collection,
                'article' => $article,
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

//     public function getOrders(Request $request): JsonResponse
// {
//     $page = $request->query->getInt('page', 1);
//     $limit = $request->query->getInt('limit', 10);

//     // $repository = $this->getDoctrine()->getRepository(Order::class);
//     // Récupérer le repository de l'entité Order
//     $repository = $entityManager->getRepository(Order::class);
//     $orders = $repository->findBy([], null, $limit, ($page - 1) * $limit);

//     return new JsonResponse($orders);
// }

}
