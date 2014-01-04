<?php

namespace SlmQueueSqsTest\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use SlmQueueSqs\Controller\SqsWorkerController;
use Zend\Mvc\Router\RouteMatch;

class SqsWorkerControllerTest extends TestCase
{
    public function testCorrectlyCountJobs()
    {
        $worker     = $this->getMock('SlmQueue\Worker\WorkerInterface');
        $controller = new SqsWorkerController($worker);

        $routeMatch = new RouteMatch(array('queue' => 'newsletter'));
        $controller->getEvent()->setRouteMatch($routeMatch);

        $worker->expects($this->once())
               ->method('processQueue')
               ->with('newsletter')
               ->will($this->returnValue(1));

        $result = $controller->processAction();

        $this->assertEquals("Finished worker for queue 'newsletter' with 1 jobs\n", $result);
    }
}
