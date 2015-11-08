<?php

namespace Pancake;

class Invoices {

    public static function get(Server $server) {
        return $server->get("invoices");
    }

    public static function send(Server $server, $unique_id) {
        return $server->post("invoices/send", array("unique_id" => $unique_id));
    }

}
