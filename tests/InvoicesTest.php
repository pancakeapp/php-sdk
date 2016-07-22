<?php

namespace Pancake;

/**
 * A PHPUnit test case for \Pancake\Invoices.
 */
class InvoicesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The server object which allows everything to use the API.
     *
     * @var Server
     */
    public $server;

    /**
     * The default client ID to use for everything.
     *
     * @var string
     */
    public $client_id = 1;

    public function setUp()
    {
        $this->server = new Server("https://demo.pancakeapp.com", "y2x45e07cavdpiy18c95r35u40eo49xg4lyv7hjw");
    }

    /**
     * Looks for a specific invoice in the API given a unique ID.
     *
     * @param string $unique_id
     * @return boolean
     */
    protected function findInvoice($unique_id)
    {
        $client_invoices = Invoices::getByUniqueId($this->server, $this->client_id);
        $found_invoice = false;
        foreach ($client_invoices as $invoice) {
            if ($invoice["unique_id"] == $unique_id) {
                $found_invoice = true;
            }
        }
        return $found_invoice;
    }

    public function testCreate()
    {
        $invoice = new Invoice($this->server);
        $invoice->client_id = $this->client_id;
        $invoice->addStandardLineItem("Test", 2, 20);
        $result = $invoice->save();

        self::assertArrayHasKey("unique_id", $result);
        self::assertNotEmpty($result["unique_id"]);
        self::assertTrue($this->findInvoice($result["unique_id"]));
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        $invoice = new Invoice($this->server);
        $invoice->client_id = 1;
        $invoice->addStandardLineItem("Test", 2, 20);
        $result = $invoice->save();

        self::assertTrue($this->findInvoice($result["unique_id"]));
        Invoices::delete($this->server, $result["unique_id"]);
        self::assertFalse($this->findInvoice($result["unique_id"]));
    }
}
