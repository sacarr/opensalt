<?php

namespace App\Handler\Import;

use App\Event\NotificationEvent;
use App\Handler\AbstractDoctrineHandler;
use App\Command\Import\ImportSpineCommand;
use App\Event\CommandEvent;
use App\Service\SpineImport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportSpineHandler extends AbstractDoctrineHandler
{
    /**
     * @var SpineImport
     */
    protected $importService;

    public function __construct(ValidatorInterface $validator, EntityManagerInterface $entityManager, SpineImport $spineImport)
    {
        parent::__construct($validator, $entityManager);
        $this->importService = $spineImport;
    }

    public function handle(CommandEvent $event, string $eventName, EventDispatcherInterface $dispatcher): void
    {
        /** @var ImportSpineCommand $command */
        $command = $event->getCommand();
        $this->validate($command, $command);


        $path = $command->getSpinePath();
        $creator = $command->getCreator();
        $organization = $command->getOrganization();
        $target = $command->getImportTarget();

        switch ($target) {
            case "spine": {
                $doc = $this->importService->importSpine($path);
            break;
            }
            case "skills": {
                $doc = $this->importService->importSkills($path);
            break;
            }
            case "standards": {
                $doc = $this->importService->importAssociations($path);
            break;
            }
        }
        if ($creator) {
            $doc->setCreator($creator);
        }
        if ($organization) {
            $doc->setOrg($organization);
        }

        $notification = new NotificationEvent(
            'LS00',
            sprintf('Learning spine "%s" imported', $doc->getTitle()),
            $doc,
            [
                'doc-a' => [
                    $doc,
                ],
            ]
        );
        $command->setNotificationEvent($notification);
    }

}
