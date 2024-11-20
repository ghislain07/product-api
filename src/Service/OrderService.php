<?php
namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createOrder($data)
    {
        // Logique pour créer et enregistrer la commande dans la base de données
        $order = new Order();
        $order->setCustomerId($data['customerId']);
        $order->setItems($data['items']);
        $order->setTotalPrice($this->calculateTotalPrice($data['items']));
        $order->setCreatedAt(new \DateTime());

        $this->em->persist($order);
        $this->em->flush();

        return $order;
    }

    private function calculateTotalPrice($items)
    {
        $totalPrice = 0;
        foreach ($items as $item) {
            $totalPrice += $item['quantity'] * $this->getProductPrice($item['productId']);
        }
        return $totalPrice;
    }

    private function getProductPrice($productId)
    {
        // Logique pour obtenir le prix du produit à partir de la base de données
        return 40.5; // Exemple
    }
}
