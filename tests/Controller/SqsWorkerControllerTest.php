<?php

namespace SlmQueueSqsTest\Controller;

use PHPUnit\Framework\TestCase;
use SlmQueueSqs\Controller\SqsWorkerController;
use Laminas\Router\RouteMatch;

class SqsWorkerControllerTest extends TestCase
{
    public function testCorrectlyCountJobs()
    {
        $queue         = $this->getMockBuilder('SlmQueue\Queue\QueueInterface')->getMock();
        $worker        = $this->getMockBuilder('SlmQueue\Worker\WorkerInterface')->getMock();
        $pluginManager = $this->getMockBuilder('SlmQueue\Queue\QueuePluginManager')->disableOriginalConstructor()->getMock();

        $pluginManager->expects($this->once())
            ->method('get')
            ->with('newsletter')
            ->will($this->returnValue($queue));

        $controller    = new SqsWorkerController($worker, $pluginManager);

        $routeMatch = new RouteMatch(array('queue' => 'newsletter'));
        $controller->getEvent()->setRouteMatch($routeMatch);

        $worker->expects($this->once())
            ->method('processQueue')
            ->with($queue)
            ->will($this->returnValue(array('One state')));

        $result = $controller->processAction();

        $this->assertStringEndsWith("One state\n", $result);
    }
}
