<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

if (gethostname() == "Rion.local") {
    define("PANCAKE_URL", "http://pancakepayments.test");
    define("PANCAKE_API_KEY", "y2x45e07cavdpiy18c95r35u40eo49xg4lyv7hjw");
} else {
    define("PANCAKE_URL", "https://demo.pancakeapp.com");
    define("PANCAKE_API_KEY", "y2x45e07cavdpiy18c95r35u40eo49xg4lyv7hjw");
}