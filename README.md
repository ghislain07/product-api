# product-api

* Get the price of an item (/api/get-price)
    - Retrieves the price of a specific item from d'un site externe.
    - Takes factory, collection and article parameters in URL.
    - Returns the gross and formatted price of the item.

* Get grouped orders (/api/orders/grouped)
    - Retrieves grouped orders from an external site.
    - Supports pagination via page and perPage parameters.
    - Returns a paginated list of prices and related information.

* Send data to a SOAP service (/api/soap/send)
    - Sends data to the SOAP service to create a command.
    - Takes data in JSON format from the request body.
    
* Create an order in the database (via OrderService)
    - Creates an order in the database using Doctrine ORM.
    - The OrderService handles the order creation logic, including total price calculation.

* Installing dependencies with Composer
    - composer install

* Modify the following variables as required:
    - APP_PORT=8080
    - MYSQL_ROOT_PASSWORD=root
    - MYSQL_DATABASE=symfony
    - MYSQL_USER=symfony
    - MYSQL_PASSWORD=password

* Start Docker containers (optional) If you use Docker for the database :
    - docker-compose up -d

* Creating the database If you are using Doctrine ORM, you need to create the :
    - php bin/console doctrine:database:create
    - php bin/console doctrine:schema:update --force

* /api/get-price (example)
    - {
        "price_raw": 40.5,
        "price_display": "40.50 €",
        "factory": "exampleFactory",
        "collection": "exampleCollection",
        "article": "exampleArticle"
    }

* /api/orders/grouped (example)
    - {
        "page": 1,
        "perPage": 10,
        "totalPages": 5,
        "totalItems": 50,
        "data": [
            {
            "priceRaw": 40.5,
            "priceFormatted": "40.50 €",
            "currency": "€/m²"
            }
        ]
    }

* /api/soap/send (example)
    - {
        "status": "success",
        "response": "Response from SOAP service"
    }

* Example of use in the controller :
    - $orderService = new OrderService($entityManager);
        $order = $orderService->createOrder([
            'customerId' => 12345,
            'items' => [
                ['id' => '1', 'price' => 19.99],
                ['id' => '2', 'price' => 29.99],
            ],
        ]);

* Docker configuration
    - Démarrer les services :
        - docker-compose up -d