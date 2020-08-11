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

        $doc = $this->importService->importSpine($path, $target);
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


    public function var_error_log($message, $object = null) :void
    {
        if (null == $object ) {
            error_log("\n\nDEBUG: ".__FILE__."(line ".__LINE__.")::".__FUNCTION__. " " .$message);
            return;
        }
        ob_start();
        var_dump($object);
        $contents = ob_get_contents();
        ob_end_clean();
        error_log("\n\nDEBUG: ". __FILE__ . "(line ". __LINE__ . ")::" . __FUNCTION__ . "\n\n" . $message . " " . $contents);
    }

}
