<?php
/**
 * Handler base
 * User: moyo
 * Date: 18/03/2018
 * Time: 7:57 PM
 */

namespace Carno\Chain\Tests\Handlers;

use Carno\Coroutine\Context;
use Throwable;

abstract class Handler
{
    protected $flag = 'H';

    public function inbound($message, Context $ctx)
    {
        $ctx->set('flows-in', array_merge($ctx->get('flows-in') ?? [], [$this->flag]));
    }

    public function outbound($message, Context $ctx)
    {
        $ctx->set('flows-out', array_merge($ctx->get('flows-out') ?? [], [$this->flag]));
    }

    public function exception(Throwable $e, Context $ctx)
    {
        throw $e;
    }
}
