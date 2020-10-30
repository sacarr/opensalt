<?php

namespace App\Command\Framework;

use App\Command\BaseCommand;
use App\Entity\Framework\LsDoc;
use Symfony\Component\Validator\Constraints as Assert;

class DeleteDocumentCommand extends BaseCommand
{
    /**
     * @var LsDoc
     *
     * @Assert\Type(LsDoc::class)
     * @Assert\NotNull()
     */
    private $doc;

    /**
     * @var \Closure|null
     */
    private $callback;

    /**
     * constructor.
     */
    public function __construct(LsDoc $doc, ?\Closure $progressCallback = null)
    {
        $this->doc = $doc;
        $this->callback = $progressCallback;
    }

    public function getDoc(): LsDoc
    {
        return $this->doc;
    }

    public function getProgressCallback(): ?\Closure
    {
        return $this->callback;
    }
}
