<?php
namespace Icicle\Tests\Loop\Events;

use Icicle\Loop\Events\EventFactory;
use Icicle\Tests\TestCase;

class EventFactoryTest extends TestCase
{
    protected $factory;
    
    public function setUp()
    {
        $this->factory = new EventFactory();
    }
    
    protected function createLoop()
    {
        $loop = $this->getMockBuilder('Icicle\Loop\LoopInterface')
                     ->getMock();
        
        return $loop;
    }
    
    public function createSockets()
    {
        return stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
    }
    
    public function testCreatePoll()
    {
        list($socket) = $this->createSockets();
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->identicalTo($socket), $this->identicalTo(false));
        
        $poll = $this->factory->createPoll($this->createLoop(), $socket, $callback);
        
        $this->assertInstanceOf('Icicle\Loop\Events\PollInterface', $poll);
        
        $this->assertSame($socket, $poll->getResource());
        
        $callback = $poll->getCallback();
        $callback($socket, false);
    }
    
    public function testCreateAwait()
    {
        list($socket) = $this->createSockets();
        
        $callback = $this->createCallback(1);
        $callback->method('__invoke')
                 ->with($this->identicalTo($socket), $this->identicalTo(false));
        
        $await = $this->factory->createAwait($this->createLoop(), $socket, $callback);
        
        $this->assertInstanceOf('Icicle\Loop\Events\AwaitInterface', $await);
        
        $this->assertSame($socket, $await->getResource());
        
        $callback = $await->getCallback();
        $callback($socket, false);
    }
    
    public function testCreateTimer()
    {
        $timeout = 0.1;
        $periodic = true;
        
        $timer = $this->factory->createTimer($this->createLoop(), $this->createCallback(1), $timeout, $periodic);
        
        $this->assertInstanceOf('Icicle\Loop\Events\TimerInterface', $timer);
        
        $this->assertSame($timeout, $timer->getInterval());
        $this->assertSame($periodic, $timer->isPeriodic());
        
        $callback = $timer->getCallback();
        $callback();
    }
    
    public function testCreateImmediate()
    {
        $immediate = $this->factory->createImmediate($this->createLoop(), $this->createCallback(1));
        
        $this->assertInstanceOf('Icicle\Loop\Events\ImmediateInterface', $immediate);
        
        $callback = $immediate->getCallback();
        $callback();
    }
}
