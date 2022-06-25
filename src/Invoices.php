<?php

declare(strict_types=1);

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

    /**
     * Get all invoices.
     *
     * @param \Pancake\Server $server
     * @return \Pancake\Invoice[]
     */
    public static function get(Server $server): array
    {
        $invoices = $server->get("invoices/fetch");

        return array_map(function ($record) use ($server) {
            return Invoice::createFromArray($server, $record);
        }, $invoices);
    }

    /**
     * Get all invoices belonging to a given client.
     *
     * @param \Pancake\Server $server
     * @param int $client_id
     * @return \Pancake\Invoice[]
     */
    public static function getByClientId(Server $server, int $client_id): array
    {
        $invoices = $server->get("invoices/fetch", [
            "client_id" => $client_id,
        ]);

        return array_map(function ($record) use ($server) {
            return Invoice::createFromArray($server, $record);
        }, $invoices);
    }

    /**
     * Get a specific invoice.
     *
     * @param \Pancake\Server $server
     * @param string $unique_id
     * @return \Pancake\Invoice|null
     */
    public static function getByUniqueId(Server $server, string $unique_id): ?Invoice
    {
        $result = $server->get("invoices/fetch", [
            "unique_id" => $unique_id,
            "include_totals" => true,
            "include_partials" => true,
        ]);

        $result = reset($result);

        if (!isset($result["unique_id"]) || $result["unique_id"] !== $unique_id) {
            return null;
        }

        return Invoice::createFromArray($server, $result);
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
