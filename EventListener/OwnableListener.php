<?php

namespace Librinfo\UserBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Librinfo\BaseEntitiesBundle\EventListener\Traits\ClassChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class OwnableListener implements LoggerAwareInterface, EventSubscriber
{
    use ClassChecker;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $userClass;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * define Ownable mapping at runtime
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        $reflectionClass = $metadata->getReflectionClass();

        if (!$reflectionClass || !$this->hasTrait($reflectionClass, 'Librinfo\UserBundle\Entity\Traits\Ownable'))
            return; // return if current entity doesn't use Ownable trait

        $this->logger->debug("[OwnableListener] Entering OwnableListener for « loadClassMetadata » event");

        // setting default mapping configuration for Ownable

        // owner mapping
        $metadata->mapManyToOne([
            'targetEntity' => $this->userClass,
            'fieldName'    => 'owner',
            'joinColumn'   => [
                'name'                 => 'owner_id',
                'referencedColumnName' => 'id',
                'onDelete'             => 'SET NULL',
                'nullable'             => true
            ]
        ]);

        $this->logger->debug("[OwnableListener] Added Ownable mapping metadata to Entity", ['class' => $metadata->getName()]);
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param string $userClass
     */
    public function setUserClass($userClass)
    {
        $this->userClass = $userClass;
    }
}