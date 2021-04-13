<?php

namespace Pancake;

/**
 * Fetch invoices.
 *
 * @package  Pancake
 * @author   Pancake Dev Team <support@pancakeapp.com>
 * @license  https://www.pancakeapp.com/license Pancake End User License Agreement
 * @link     https://www.pancakeapp.com
 *
 */
class Invoices
{

    public static function get(Server $server)
    {
        return $server->get("invoices/fetch");
    }

    public static function getByClientId(Server $server, $client_id)
    {
        return $server->get("invoices/fetch", [
            "client_id" => $client_id,
        ]);
    }

    public static function getByUniqueId(Server $server, $unique_id)
    {
        $result = $server->get("invoices/fetch", [
            "unique_id" => $unique_id,
            "include_totals" => true,
            "include_partials" => true,
        ]);

        $invoice = new Invoice($server);
        foreach ()
        $invoice->

        return reset($result);
    }

    public static function send(Server $server, $unique_id)
    {
        $result = $server->post("invoices/send", array("unique_id" => $unique_id));
        return $result['status'];
    }

    public static function delete(Server $server, $unique_id)
    {
        $result = $server->post("invoices/delete", array("unique_id" => $unique_id));
        return $result['status'];
    }
}
