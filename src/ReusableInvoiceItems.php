<?php

namespace Pancake;

/**
 * Fetch reusable invoice items.
 *
 * @package  Pancake
 * @author   Pancake Dev Team <support@pancakeapp.com>
 * @license  https://www.pancakeapp.com/license Pancake End User License Agreement
 * @link     https://www.pancakeapp.com
 *
 */
class ReusableInvoiceItems
{

    public static function get(Server $server)
    {
        return $server->get("reusable_invoice_items");
    }

    public static function getById(Server $server, $id)
    {
        return $server->get("reusable_invoice_items/show", array("id" => $id));
    }
}
