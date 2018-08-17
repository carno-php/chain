<?php
/**
 * Handler B
 * User: moyo
 * Date: 18/03/2018
 * Time: 7:55 PM
 */

namespace Carno\Chain\Tests\Handlers;

use Carno\Chain\Layered;

class B extends Handler implements Layered
{
    protected $flag = 'B';
}
