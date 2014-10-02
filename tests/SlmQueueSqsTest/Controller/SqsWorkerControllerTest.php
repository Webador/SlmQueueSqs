<?php

namespace SlmQueueSqsTest\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use SlmQueueSqs\Controller\SqsWorkerController;
use Zend\Mvc\Router\RouteMatch;

class SqsWorkerControllerTest extends TestCase
{
    public function testCorrectlyCountJobs()
    {
        $queue         = $this->getMock('SlmQueue\Queue\QueueInterface');
        $worker        = $this->getMock('SlmQueue\Worker\WorkerInterface');
        $pluginManager = $this->getMock('SlmQueue\Queue\QueuePluginManager', array(), array(), '', false);

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
               ->will($this->returnValue(1));

        $result = $controller->processAction();

        $this->assertEquals("Finished worker for queue 'newsletter' with 1 jobs\n", $result);
    }
}
