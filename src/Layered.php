<?php
/**
 * Layered API
 * User: moyo
 * Date: 06/08/2017
 * Time: 8:14 PM
 */

namespace Carno\Chain;

use Carno\Coroutine\Context;
use Carno\Promise\Promised;
use Throwable;

interface Layered
{
    /**
     * @param mixed $message
     * @param Context $ctx
     * @return Promised|mixed|null
     */
    public function inbound($message, Context $ctx);

    /**
     * @param mixed $message
     * @param Context $ctx
     * @return Promised|mixed|null
     */
    public function outbound($message, Context $ctx);

    /**
     * @param Throwable $e
     * @param Context $ctx
     * @return Promised|mixed|null
     */
    public function exception(Throwable $e, Context $ctx);
}
