<?php

namespace BM_TEST;

require 'vendor/autoload.php';

use Carno\Chain\Layered;
use Carno\Chain\Layers;
use Carno\Coroutine\Context;
use Throwable;

$layers = new Layers(
    $c = new class implements Layered {
        public function inbound($message, Context $ctx)
        {
            return $message;
        }
        public function outbound($message, Context $ctx)
        {
            return $message;
        }
        public function exception(Throwable $e, Context $ctx)
        {
            throw $e;
        }
    },
    clone $c
);

$begin = microtime(true);

for ($r = 0; $r < 102400; $r ++) {
    $layers->handler()($r);
}

$cost = round((microtime(true) - $begin) * 1000);

echo 'cost ', $cost, ' ms | op ', round($cost * 1000 / $r, 3), ' μs', PHP_EOL;
