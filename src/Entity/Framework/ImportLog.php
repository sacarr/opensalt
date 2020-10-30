<?php

namespace App\Entity\Framework;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ImportLog
 *
 * @ORM\Entity(repositoryClass="App\Repository\Framework\ImportLogRepository")
 * @ORM\Table(name="import_logs")
 * @UniqueEntity("id")
 */
class ImportLog
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     * @var LsDoc
     *
     * @ORM\ManyToOne(targetEntity="LsDoc", inversedBy="importLogs")
     * @ORM\JoinColumn(name="ls_doc_id", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotBlank()
     */
    protected $lsDoc;

    /**
     * @var string
     *
     * @ORM\Column(name="message_text", type="string", length=250)
     *
     * @Assert\NotBlank
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(name="message_type", type="string", length=30, nullable=false)
     */
    protected $messageType;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_read", type="boolean", nullable=false, options={"default": 0})
     */
    protected $read = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messageType = 'warning';
    }

    /**
     * Set LsDoc
     *
     * @param LsDoc $lsDoc
     */
    public function setLsDoc($lsDoc): ImportLog
    {
        $this->lsDoc = $lsDoc;

        return $this;
    }

    /**
     * Set message
     *
     * @param string $message
     */
    public function setMessage($message): ImportLog
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get read
     */
    public function isRead(): bool
    {
        return $this->read;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Set read as true
     */
    public function markAsRead(): ImportLog
    {
        $this->read = true;

        return $this;
    }

    /**
     * Get messageType
     *
     * @return string
     */
    public function getMessageType(): ?string
    {
        return $this->messageType;
    }

    /**
     * Set messageType
     *
     * @param string $messageType
     */
    public function setMessageType($messageType): ImportLog
    {
        $this->messageType = $messageType;

        return $this;
    }
}
