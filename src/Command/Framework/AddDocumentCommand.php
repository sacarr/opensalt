<?php

namespace App\Command\Framework;

use App\Command\BaseCommand;
use App\Entity\Framework\LsDoc;
use Symfony\Component\Validator\Constraints as Assert;

class AddDocumentCommand extends BaseCommand
{
    /**
     * @var LsDoc
     *
     * @Assert\Type(LsDoc::class)
     * @Assert\NotNull()
     */
    private $doc;

    /**
     * Constructor.
     */
    public function __construct(LsDoc $doc)
    {
        $this->doc = $doc;
    }

    public function getDoc(): LsDoc
    {
        return $this->doc;
    }
}
