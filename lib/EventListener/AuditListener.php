<?php

namespace JBJ\Workflow\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use JBJ\Workflow\Event\WorkflowEvent;

class AuditListener implements EventSubscriberInterface
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onWorkflowAudit(WorkflowEvent $event)
    {
        $this->logger->info('todo: expand event into useful information');
    }

    public static function getSubscribedEvents()
    {
        return [
            //todo
            'workflow' => ['onWorkflowAudit'],
        ];
    }
}
