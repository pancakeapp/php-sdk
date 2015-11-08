<?php

namespace Pancake;

class Invoices {

    public static function get(Server $server) {
        return $server->get("invoices");
    }

    public static function send(Server $server, $unique_id) {
        $result = $server->post("invoices/send", array("unique_id" => $unique_id));
        return $result['status'];
    }

}
