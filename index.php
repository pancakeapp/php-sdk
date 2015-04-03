<?php

namespace Pancake;

include 'vendor/autoload.php';

$server = new Server("http://localhost/pancakepayments", "vvRVmZQvysA1ndKpmZgJoQNwW0wb8nVzwZ4axTvz");
$server->setDebug(true);

$invoice = new Invoice($server);

$invoice->invoice_number = "A150";
$invoice->client_id = 2;

$amount = 50;
$due_date = strtotime("2015-04-24"); # All dates are to be provided as timestamps.
$notes = "some notes";
$invoice->addPercentagePaymentPart($amount, $due_date, $notes);
$invoice->addFixedPaymentPart($amount, $due_date, $notes);

$name = "item name";
$description = "item description";
$qty = 1;
$rate = 100;
$taxes = ["VAT", "10%", 3]; # You can specify a tax name (will search the DB), a percentage (will be created if none exists), or the tax's ID.
$discount = 0;
$invoice->addStandardLineItem($name, $qty, $rate, $taxes, $description, $discount);
$invoice->addFlatRateLineItem($name, $rate, $taxes, $description, $discount);
$invoice->addFixedDiscountLineItem($name, 5);
$invoice->addPercentageDiscountLineItem($name, 10);

$invoice->addFile("http://i.28hours.org/files/APIEndpoints.pdf"); # Pass a URL...
$invoice->addFile("LICENSE.txt"); # Or a filename.
$invoice->addFileFromContents("this is my test file", "filename.txt"); # You can provide your file's contents here and give it a filename.

# Will always create a new record, for now.
$result = $invoice->save();

echo "Created Invoice #{$result['unique_id']}.";
