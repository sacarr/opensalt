<?php

namespace App\Command\Import;

use App\Command\BaseCommand;
use App\Entity\User\Organization;
use Symfony\Component\Validator\Constraints as Assert;

class ImportSpineCommand extends BaseCommand
{
    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $path;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @var string
     */
    private $target;

    /**
     * @var string
     */
    private $creator;

    public function __construct(string $path, ?string $target = "spine", ?string $creator = null, ?Organization $organization = null)
    {
        $this->path = $path;
        $this->target = $target;
        $this->organization = $organization;
        $this->creator = $creator;
    }

    public function getSpinePath(): string
    {
        return $this->path;
    }

    public function getImportTarget(): ?string 
    {
        return $this->target;
    }
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getCreator(): ?string
    {
        return $this->creator;
    }
}
