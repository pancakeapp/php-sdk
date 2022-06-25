<?php declare(strict_types=1);

namespace Pancake;

use PHPUnit\Framework\TestCase;

/**
 * A PHPUnit test case for \Pancake\Invoices.
 */
final class InvoicesTest extends TestCase
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

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        $this->server = new Server(PANCAKE_URL, PANCAKE_API_KEY);
    }

    /**
     * Looks for a specific invoice in the API given a unique ID.
     *
     * @param string $unique_id
     * @return boolean
     */
    protected function findInvoice(string $unique_id): bool
    {
        return Invoices::getByUniqueId($this->server, $unique_id) !== null;
    }

    public function testCreate()
    {
        $invoice = new Invoice($this->server);
        $invoice->client_id = $this->client_id;
        $invoice->addStandardLineItem("Test", 2, 20, ["10%"]);
        $invoice->addPaymentPart(true, 100);
        $invoice->save();

        self::assertNotEmpty($invoice->unique_id);
        self::assertTrue($this->findInvoice($invoice->unique_id));
    }

    /**
     * @depends testCreate
     */
    public function testCannotChangeUniqueId()
    {
        $this->expectException(\Pancake\DomainException::class);

        $invoice = new Invoice($this->server);
        $invoice->client_id = 1;
        $invoice->addStandardLineItem("Test", 2, 20);
        $invoice->save();

        $invoice->unique_id = "test";

        self::assertTrue($this->findInvoice($invoice->unique_id));
        Invoices::delete($this->server, $invoice->unique_id);
        self::assertFalse($this->findInvoice($invoice->unique_id));
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {
        $invoice = new Invoice($this->server);
        $invoice->client_id = 1;
        $invoice->addStandardLineItem("Test", 2, 20);
        $invoice->save();

        self::assertTrue($this->findInvoice($invoice->unique_id));
        Invoices::delete($this->server, $invoice->unique_id);
        self::assertFalse($this->findInvoice($invoice->unique_id));
    }

    /**
     * @depends testCreate
     * @depends testDelete
     */
    public function testMarkAsPaid()
    {
        $invoice = new Invoice($this->server);
        $invoice->client_id = 1;
        $invoice->addStandardLineItem("Test", 2, 20);
        $invoice->save();

        self::assertFalse($invoice->is_paid);
        $invoice->setPaymentDetails(1, "cash_m", null, "Completed", null, null, false);
        self::assertTrue($invoice->is_paid);

        Invoices::delete($this->server, $invoice->unique_id);
    }

    /**
     * @depends testCreate
     * @depends testDelete
     */
    public function testAddPayment()
    {
        $invoice = new Invoice($this->server);
        $invoice->client_id = 1;
        $invoice->addStandardLineItem("Test", 2, 20);
        $invoice->save();

        self::assertEquals(0, $invoice->paid_amount);
        $invoice->addPayment(20, "cash_m", null, "Completed", null, null, false);
        self::assertEquals(20, $invoice->paid_amount);

        Invoices::delete($this->server, $invoice->unique_id);
    }
}
