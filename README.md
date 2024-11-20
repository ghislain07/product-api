# product-api

* Obtenir le prix d'un article (/api/get-price)
    - Permet de récupérer le prix d'un article spécifique à partir d'un site externe.
    - Prend en paramètres factory, collection, et article dans l'URL.
    - Renvoie le prix brut et formaté de l'article.

* Obtenir des commandes groupées (/api/orders/grouped)
    - Permet de récupérer des commandes groupées depuis un site externe.
    - Supporte la pagination via les paramètres page et perPage.
    - Renvoie une liste paginée de prix et d'informations associées.

* Envoyer des données à un service SOAP (/api/soap/send)
    - Envoie des données au service SOAP pour créer une commande.
    - Prend des données au format JSON dans le corps de la requête.
    
* Créer une commande dans la base de données (via OrderService)
    - Crée une commande dans la base de données en utilisant Doctrine ORM.
    - Le service OrderService s'occupe de la logique de création de commande, y compris le calcul du prix total.