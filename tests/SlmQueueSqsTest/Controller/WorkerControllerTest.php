<?php

namespace SlmQueueSqsTest\Options;

use PHPUnit_Framework_TestCase as TestCase;
use Aws\Sqs\SqsClient;
use SlmQueueSqs\Service\SqsService;
use SlmQueueSqsTest\Util\ServiceManagerFactory;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;

class WorkerControllerTest extends TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SqsClient
     */
    protected $sqsClient;


    public function setUp()
    {
        parent::setUp();
        $this->serviceManager = ServiceManagerFactory::getServiceManager();

        $this->sqsClient = $this->getMock('Aws\Sqs\SqsClient', array('receiveMessage', 'createQueue', 'deleteMessage'), array(), '', false);
        $sqsService      = new SqsService($this->sqsClient);
        $this->serviceManager->setAllowOverride(true);

        $this->serviceManager->setFactory('SlmQueueSqs\Service\SqsService', function() use ($sqsService) {
            return $sqsService;
        });
    }

    public function testThrowExceptionIfQueueIsUnknown()
    {
        $controller = $this->serviceManager->get('ControllerLoader')->get('SlmQueueSqs\Controller\Worker');
        $routeMatch = new RouteMatch(array('queueName' => 'unknownQueue'));
        $controller->getEvent()->setRouteMatch($routeMatch);

        $result = $controller->processAction();

        $this->assertContains('An error occurred', $result);
    }

    public function testCorrectlyCountJobs()
    {
        $controller = $this->serviceManager->get('ControllerLoader')->get('SlmQueueSqs\Controller\Worker');
        $routeMatch = new RouteMatch(array('queueName' => 'newsletter'));
        $controller->getEvent()->setRouteMatch($routeMatch);

        $message = array(
            'Body' => '{"class":"SlmQueueSqsTest\\\Asset\\\SimpleJob","content":"Foo"}',
            'MessageId'     => 4,
            'ReceiptHandle' => 5,
            'MD5OfBody'     => md5('foo')
        );

        $result['Messages']['items'] = array($message);

        $this->sqsClient->expects($this->once())
            ->method('receiveMessage')
            ->will($this->returnValue($result));

        $result = $controller->processAction();

        $this->assertContains('Work for queue newsletter is done, 1 jobs were processed', $result);
    }
}
