<?php

namespace Pancake;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Reflection\DocBlock\Tag\PropertyTag;

/**
 * An invoice record.
 *
 * @package  Pancake
 * @author   Pancake Dev Team <support@pancakeapp.com>
 * @license  https://www.pancakeapp.com/license Pancake End User License Agreement
 * @link     https://www.pancakeapp.com
 *
 * @property integer $client_id
 * @property integer $project_id
 * @property integer $owner_id
 * @property integer $currency_id
 * @property double $exchange_rate
 * @property string $invoice_number
 * @property string $notes
 * @property string $description
 * @property string $type
 * @property integer $date_entered
 * @property string $is_recurring
 * @property string $frequency
 * @property boolean $auto_send
 * @property integer $send_x_days_before
 * @property boolean $is_viewable
 * @property boolean $is_archived
 * @property-read integer $date_to_automatically_notify
 * @property-read string $unique_id
 * @property-read integer $id
 * @property-read double $amount
 * @property-read integer $due_date
 * @property-read boolean $is_paid
 * @property-read integer $recur_id
 * @property-read integer $next_recur_date
 * @property-read integer $last_sent
 * @property-read boolean $has_sent_notification
 * @property-read integer $last_viewed
 * @property-read integer $proposal_id
 * @property-read integer $last_status_change
 * @property-read string $status
 * @property-read string $currency_symbol
 * @property-read string $currency_code
 * @property-read string $url
 * @property-read integer $total_comments
 * @property-read boolean $has_files
 * @property-read double $tax_total
 * @property-read double $billable_amount
 * @property-read double $unpaid_amount
 * @property-read double $paid_amount
 * @property-read integer $days_overdue
 * @property-read string $list_invoice_belongs_to
 * @property-read integer $next_part_to_pay
 * @property-read double $total_transaction_fees
 * @property-read double $tax_collected
 * @property-read double $sub_total
 * @property-read double $sub_total_after_discounts
 * @property-read double $total
 * @property-read boolean $has_discount
 * @property-read array $taxes
 * @property-read array $discounts
 * @property-read array $collected_taxes
 */
class Invoice
{

    protected $internal_fields = [
        "unique_id" => null,
        "parts" => [],
        "items" => [],
        "files" => [],
    ];

    protected $read_only_fields = [
        "parts",
        "items",
        "files",
        'date_to_automatically_notify',
        'unique_id',
        'id',
        'amount',
        'due_date',
        'is_paid',
        'recur_id',
        'next_recur_date',
        'last_sent',
        'has_sent_notification',
        'last_viewed',
        'proposal_id',
        'last_status_change',
        'status',
        'currency_symbol',
        'currency_code',
        'url',
        'total_comments',
        'has_files',
        'tax_total',
        'billable_amount',
        'unpaid_amount',
        'paid_amount',
        'days_overdue',
        'list_invoice_belongs_to',
        'next_part_to_pay',
        'total_transaction_fees',
        'tax_collected',
        'sub_total',
        'sub_total_after_discounts',
        'total',
        'has_discount',
        'taxes',
        'discounts',
        'collected_taxes',
    ];

    protected $dirty_fields = [];

    protected $server;

    protected $auto_cast = [];

    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->internal_fields['type'] = "DETAILED";

        $reflection = new \ReflectionClass($this);
        $docblock = $reflection->getDocComment();
        $properties = $server->convertDocBlockToProperties($docblock);
        foreach ($properties as $name => $type) {
            $this->auto_cast[$name] = $type;
        }
    }

    public function __set($name, $value)
    {
        if (!in_array($name, $this->read_only_fields)) {
            $this->internal_fields[$name] = $value;
            $this->dirty_fields[] = $name;
        } else {
            throw new DomainException("$name is a read-only property.");
        }
    }

    public function __get($name)
    {
        return $this->internal_fields[$name];
    }

    public function __isset($name)
    {
        return isset($this->internal_fields[$name]);
    }

    public function __unset($name)
    {
        throw new DomainException("You can't unset invoice properties.");
    }

    public function addPaymentPart(bool $is_percentage, float|string $amount, ?Carbon $due_date = null, ?string $notes = null): static
    {
        $this->internal_fields['parts'][] = [
            "is_percentage" => $is_percentage ? 1 : 0,
            "amount" => $amount,
            "due_date" => $due_date?->timestamp,
            "notes" => $notes,
        ];

        return $this;
    }

    public function addPercentagePaymentPart($amount, $due_date, $notes)
    {
        $this->addPaymentPart(true, $amount, $due_date, $notes);
    }

    public function addFixedPaymentPart($amount, $due_date, $notes)
    {
        $this->addPaymentPart(false, $amount, $due_date, $notes);
    }

    public function setPaymentDetails(
        $payment_i,
        $gateway,
        $payment_datetime = null,
        $payment_status = "Completed",
        $transaction_id = null,
        $transaction_fee = null,
        $send_notification_email = true
    ) {
        $result = $this->server->post("invoices/set_payment_details", [
            "unique_id" => $this->unique_id,
            "payment_i" => $payment_i,
            "gateway" => $gateway,
            "payment_datetime" => $payment_datetime,
            "payment_status" => $payment_status,
            "transaction_id" => $transaction_id,
            "transaction_fee" => $transaction_fee,
            "send_notification_email" => $send_notification_email,
        ]);

        if ($result['status']) {
            $this->reload();
        }

        return $result['status'];
    }

    public function addPayment(
        $amount,
        $gateway,
        $payment_datetime = null,
        $payment_status = "Completed",
        $transaction_id = null,
        $transaction_fee = null,
        $send_notification_email = true
    ) {
        $result = $this->server->post("invoices/add_payment", [
            "unique_id" => $this->unique_id,
            "amount" => $amount,
            "gateway" => $gateway,
            "payment_datetime" => $payment_datetime,
            "payment_status" => $payment_status,
            "transaction_id" => $transaction_id,
            "transaction_fee" => $transaction_fee,
            "send_notification_email" => $send_notification_email,
        ]);

        if ($result['status']) {
            $this->reload();
        }

        return $result['status'];
    }

    public function send()
    {
        $result = $this->server->post("invoices/send", array("unique_id" => $this->unique_id));

        if ($result['status']) {
            $this->reload();
        }

        return $result['status'];
    }


    public function addLineItem($type, $name, $qty, $rate, $tax_ids, $description, $discount)
    {

        $total = $qty * $rate;

        if (stristr($discount, "%") !== false) {
            $discount_is_percentage = 1;
            $discount = str_ireplace("%", "", $discount);
        } else {
            $discount_is_percentage = 0;
        }

        # Remove item discount from item total.
        if ($discount_is_percentage) {
            $total = $total - ($discount * $total / 100);
        } else {
            $total = $total - $discount;
        }

        if (in_array($type, array("fixed_discount", "percentage_discount"))) {
            $total = 0;
        }

        $this->internal_fields['items'][] = [
            'name' => $name,
            'description' => $description,
            'qty' => $qty,
            'rate' => $rate,
            'tax_ids' => is_array($tax_ids) ? $tax_ids : [$tax_ids],
            'discount' => $discount,
            'discount_is_percentage' => $discount_is_percentage,
            'total' => $qty * $rate,
            'sort' => (count($this->internal_fields['items']) + 1),
            'type' => $type
        ];
    }

    public function addStandardLineItem($name, $qty, $rate, $taxes = [], $description = "", $discount = 0)
    {
        $this->addLineItem("standard", $name, $qty, $rate, $taxes, $description, $discount);
    }

    public function addFlatRateLineItem($name, $rate, $taxes, $description, $discount)
    {
        $this->addLineItem("flat_rate", $name, 1, $rate, $taxes, $description, $discount);
    }

    public function addFixedDiscountLineItem($name, $discount)
    {
        $this->addLineItem("fixed_discount", $name, 1, 0, [], "", $discount);
    }

    public function addPercentageDiscountLineItem($name, $discount)
    {
        $this->addLineItem("percentage_discount", $name, 1, 0, [], "", $discount);
    }

    public function addFile($url)
    {
        if (substr($url, 0, strlen("http")) == "http") {
            $http = new Client();
            $response = $http->request("GET", $url);
            $contents = $response->getBody()->getContents();
            $url = explode("?", $url);
            $url = explode("/", $url[0]);
            $filename = end($url);
        } else {
            $contents = file_get_contents($url);
            $filename = pathinfo($url, PATHINFO_BASENAME);
        }

        $this->internal_fields['files'][] = ["filename" => $filename, "contents" => base64_encode($contents)];
    }

    public function addFileFromContents($contents, $filename)
    {

        $this->internal_fields['files'][] = ["filename" => $filename, "contents" => base64_encode($contents)];
    }

    public static function createFromArray(Server $server, array $array): static
    {
        $invoice = new static($server);
        $invoice->reload($array);
        return $invoice;
    }

    public function save()
    {
        if ($this->unique_id) {
            throw new RuntimeException("Updating invoices has not yet been implemented.");
        } else {
            $result = $this->server->post("invoices/advanced_create", $this->internal_fields);
        }

        if ($result['status']) {
            $this->internal_fields['unique_id'] = $result['unique_id'];
            $this->reload();
        } else {
            throw new RequestException(isset($result['error_message']) ? $result['error_message'] : $result['message']);
        }

        return $result;
    }

    public function reload($record = null): static
    {
        if ($record === null) {
            # Fetch the record ourselves.
            $record = $this->server->get("invoices/fetch", [
                "unique_id" => $this->unique_id,
                "include_totals" => true,
                "include_partials" => true,
            ]);

            $record = reset($record);
        }

        $items = $record["items"];
        unset($record["items"]);

        $parts = $record["partial_payments"];
        unset($record["partial_payments"]);

        $this->internal_fields = $record;

        foreach ($this->auto_cast as $name => $type) {
            switch ($type) {
                case "integer":
                case "int":
                    $this->internal_fields[$name] = (int)$this->internal_fields[$name];
                    break;
                case "boolean":
                case "bool":
                    $this->internal_fields[$name] = (bool)$this->internal_fields[$name];
                    break;
                case "float":
                case "double":
                    $this->internal_fields[$name] = (double)$this->internal_fields[$name];
                    break;
                case "string":
                    $this->internal_fields[$name] = (string)$this->internal_fields[$name];
                    break;
                case "array":
                    if (!is_array($this->internal_fields[$name])) {
                        throw new RuntimeException("Expected $name to be an array, but it wasn't.");
                    }
                    break;
                default:
                    throw new RuntimeException("Auto-casting $name to $type is not implemented.");
                    break;
            }
        }

        $this->internal_fields["items"] = [];
        foreach ($items as $item) {
            unset($item['taxes_buffer']);
            unset($item['unique_id']);
            unset($item['item_type_id']);
            unset($item['item_type_table']);
            unset($item['currency_code']);
            unset($item['item_time_entries']);
            unset($item['item_time_entries']);
        }

        $this->internal_fields["parts"] = [];
        foreach ($parts as $part) {
            $part['billable_amount'] = $part['billableAmount'];
            unset($part['improved']);
            unset($part['due_date_input']);
            unset($part['over_due']);
            unset($part['unique_invoice_id']);
            unset($part['billableAmount']);
            $this->internal_fields["parts"][] = $part;
        }

        return $this;
    }
}
