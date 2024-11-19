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

    #[Route('/api/orders/grouped', name: 'get_grouped_orders', methods: ['GET'])]

    public function getGroupedOrders(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, (int) $request->query->get('perPage', 10));

        // Simulez un appel pour obtenir des données depuis l'URL
        $url = 'https://tile.expert/fr/tile/cobsa/manual';
        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get($url);
            $htmlContent = $response->getBody()->getContents();

            // Utilisez une bibliothèque comme DOMDocument ou Symfony DomCrawler pour analyser l'HTML
            $crawler = new \Symfony\Component\DomCrawler\Crawler($htmlContent);

            // Analysez les prix et regroupez-les
            $priceBlocks = $crawler->filter('.wrap-price-block .js-full-price-block li')->each(function ($node) {
                return [
                    'pricePerSquareMeter' => $node->filter('.price-per-measure-container .js-price-tag')->attr('data-price-raw'),
                    'pricePerBox' => $node->filter('.price-per-box-container span')->text(),
                    'currency' => '€',
                ];
            });

            // Paginer les résultats
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
            return new JsonResponse(['error' => 'Failed to fetch or process data'], 500);
        }
    }

}
