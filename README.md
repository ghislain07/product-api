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