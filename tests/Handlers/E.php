<?php
/**
 * Handler E
 * User: moyo
 * Date: 2018/3/30
 * Time: 3:56 PM
 */

namespace Carno\Chain\Tests\Handlers;

use Carno\Chain\Layered;
use Carno\Coroutine\Context;
use Throwable;

class E implements Layered
{
    public function inbound($message, Context $ctx)
    {
        throw new \Exception($message);
    }

    public function outbound($message, Context $ctx)
    {
        throw new \Exception($message);
    }

    public function exception(Throwable $e, Context $ctx)
    {
        throw $e;
    }
}
