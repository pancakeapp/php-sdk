<?php

use \Pancake\Server;
use \Pancake\Invoice;
use \Pancake\ReusableInvoiceItems;
use \Pancake\Invoices;
use \Pancake\ApiException;

include '../vendor/autoload.php';

try {
    $server = new Server("http://localhost/pancakepayments", "vvRVmZQvysA1ndKpmZgJoQNwW0wb8nVzwZ4axTvz");
    $unique_id = "GtQrHgsE";
    $invoice = Invoices::getByUniqueId($server, $unique_id);
    var_dump($invoice);die;
} catch (ApiException $e) {
    # You can use getResponse() to get details on what went wrong.
    $response = $e->getResponse();

    # Response can be an array (which means Pancake's API replied with an error message),
    # or a blob of HTML (which indicates problems like a Pancake-side error).
    if (is_array($response)) {
        echo "<pre>";
        var_dump($response);
    } else {
        echo $response;
    }
}
