<?php

namespace Pancake;

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
