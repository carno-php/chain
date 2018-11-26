<?php
/**
 * Extensions test
 * User: moyo
 * Date: 18/03/2018
 * Time: 7:54 PM
 */

namespace Carno\Chain\Tests;

use Carno\Chain\Layered;
use Carno\Chain\Layers;
use Carno\Chain\Tests\Handlers\A;
use Carno\Chain\Tests\Handlers\B;
use Carno\Chain\Tests\Handlers\C;
use Carno\Chain\Tests\Handlers\E;
use function Carno\Coroutine\async;
use Carno\Coroutine\Context;
use Carno\Coroutine\Stats as COStats;
use Carno\Promise\Promise;
use Carno\Promise\Stats as POStats;
use PHPUnit\Framework\TestCase;
use Closure;
use Throwable;

class ExtensionsTest extends TestCase
{
    public function testOrder()
    {
        $a = new A;
        $b = new B;
        $c = new C;

        $this->flowTesting(function (Layers $ext) use ($a, $c) {
            $ext->prepend(null, $a);
            $ext->append(null, $c);
        }, 'ABC', 'CBA', $b);

        $this->flowTesting(function (Layers $ext) use ($a, $c) {
            $ext->prepend(null, $c);
            $ext->append(null, $a);
        }, 'CBA', 'ABC', $b);

        $this->flowTesting(function (Layers $ext) use ($a, $b, $c) {
            $ext->append(null, $b);
            $ext->append(B::class, $c);
            $ext->prepend(C::class, $a);
        }, 'BAC', 'CAB');

        $this->flowTesting(function (Layers $ext) use ($a, $b, $c) {
            $ext->prepend(null, $b);
            $ext->prepend(B::class, $a);
            $ext->append(A::class, $c);
        }, 'ACB', 'BCA');

        $this->flowTesting(function (Layers $ext) use ($a, $b, $c) {
            $ext->prepend(null, $b);
            $this->assertFalse($ext->append(A::class, $c));
            $ext->prepend(B::class, $a);
        }, 'AB', 'BA');

        $this->flowTesting(function (Layers $ext) use ($a, $b, $c) {
            $ext->prepend(B::class, $a);
            $ext->append(A::class, $c);
        }, '', '');
    }

    public function testManager()
    {
        $a = new A;
        $b = new B;
        $c = new C;

        $this->flowTesting(function (Layers $ext) {
            $this->assertTrue($ext->has(B::class));
            $this->assertTrue($ext->remove(B::class));
            $this->assertFalse($ext->has(B::class));
            $this->assertTrue($ext->has(A::class));
            $this->assertTrue($ext->has(C::class));
        }, 'AC', 'CA', $a, $b, $c);
    }

    public function testManager2()
    {
        $a = new A;
        $b = new B;
        $c = new C;

        $this->flowTesting(function (Layers $ext) use ($a, $b, $c) {
            $ext->remove(B::class);
            $ext->prepend(C::class, $b);
            $ext->remove(A::class);
            $ext->prepend(B::class, $a);
            $ext->remove(C::class);
            $ext->append(B::class, $c);
        }, 'ABC', 'CBA', $a, $b, $c);
    }

    private function flowTesting(Closure $opr, string $in, string $out, Layered ...$init)
    {
        $ext = new Layers(...$init);

        $opr($ext);

        $ext->handler()(null, $ctx = new Context);

        $this->assertEquals($in, implode('', $ctx->get('flows-in') ?? []));
        $this->assertEquals($out, implode('', $ctx->get('flows-out') ?? []));
    }

    public function testCoException()
    {
        $start = Promise::deferred();

        $ext = new Layers(new E);

        $em = '';

        async(function () use ($start, $ext) {
            yield $start;
            yield $ext->handler()('test');
        })->catch(function (Throwable $e) use (&$em) {
            $em = $e->getMessage();
        });

        $start->resolve();

        $this->assertEquals('test', $em);

        $start = null;

        $this->assertTrue(gc_collect_cycles() > 0);

        $this->assertEquals(0, COStats::running());
        $this->assertEquals(0, POStats::pending());
    }
}
