<?php
/**
 * Layers handler
 * User: moyo
 * Date: 06/08/2017
 * Time: 6:33 PM
 */

namespace Carno\Chain;

use Carno\Chain\Chips\Extensions;
use Carno\Coroutine\Context;
use Carno\Promise\Promise;
use Carno\Promise\Promised;
use Closure;
use Throwable;

final class Layers
{
    use Extensions;

    /**
     * processed layer idx
     */
    private const XID = 'chain-lax-id';

    /**
     * @var Layered[]
     */
    private $layers = [];

    /**
     * Layers constructor.
     * @param Layered ...$layers
     */
    public function __construct(Layered ...$layers)
    {
        $this->layers = $layers;
    }

    /**
     * @param Context $ctx
     * @param mixed $initial
     * @return Promised
     */
    private function processing(Context $ctx, $initial) : Promised
    {
        $seed = $chain = Promise::deferred();

        foreach ($this->layers as $idx => $layer) {
            $chain = $chain->then(static function ($data) use ($ctx, $layer, $idx) {
                return $layer->inbound($data, $ctx->set(self::XID, $idx));
            }, static function (Throwable $e) use ($ctx, $layer, $idx) {
                if (($ctx->get(self::XID) ?? 999) >= $idx) {
                    $layer->exception($e, $ctx);
                } else {
                    throw $e;
                }
            });
        }

        $size = count($this->layers);

        /**
         * @var Layered[] $replies
         */

        $replies = array_reverse($this->layers);

        foreach ($replies as $idx => $layer) {
            $chain = $chain->then(static function ($data) use ($ctx, $layer) {
                return $layer->outbound($data, $ctx);
            }, static function (Throwable $e) use ($ctx, $layer, $idx, $size) {
                if (($ctx->get(self::XID) ?? 999) >= ($size - $idx - 1)) {
                    $layer->exception($e, $ctx);
                } else {
                    throw $e;
                }
            });
        }

        $seed->resolve($initial, $ctx);

        return $chain;
    }

    /**
     * @return Closure
     */
    public function handler() : Closure
    {
        return function ($initial, Context $ctx = null) {
            try {
                return $this->processing($ctx ?? new Context, $initial);
            } catch (Throwable $e) {
                return Promise::rejected($e);
            }
        };
    }
}
