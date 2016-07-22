<?php

namespace Pancake;

class Invoices
{

    public static function get(Server $server)
    {
        return $server->get("invoices");
    }

    public static function getByClientId(Server $server, $client_id)
    {
        return $server->get("invoices", [
            "client_id" => $client_id,
        ]);
    }

    public static function getByUniqueId(Server $server, $unique_id)
    {
        return $server->get("invoices", [
            "unique_id" => $unique_id,
        ]);
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
