<?php

namespace App\Form\DataTransformer;

use App\Entity\Framework\LsDefItemType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Data transformer which can create new ItemTypes
 *
 * Class ItemTypeTransformer
 */
class ItemTypeTransformer implements DataTransformerInterface
{
    /** @var EntityManagerInterface */
    protected $em;
    /** @var string */
    protected $className;
    /** @var string */
    protected $textProperty;
    /** @var string */
    protected $primaryKey;

    protected $accessor;

    /**
     * @param string        $class
     * @param string|null   $textProperty
     * @param string        $primaryKey
     */
    public function __construct(EntityManagerInterface $em, $class, $textProperty = null, $primaryKey = 'id')
    {
        $this->em = $em;
        $this->className = $class;
        $this->textProperty = $textProperty;
        $this->primaryKey = $primaryKey;
        $this->accessor = PropertyAccess::createPropertyAccessor();

        if (LsDefItemType::class !== $this->className) {
            throw new \InvalidArgumentException("Class {$class} not supported in ItemTypeTransformer");
        }
    }

    /**
     * Transform entity to array
     *
     * @param mixed $entity
     */
    public function transform($entity): array
    {
        $data = array();
        if (empty($entity)) {
            return $data;
        }

        $text = (null === $this->textProperty)
            ? (string) $entity
            : $this->accessor->getValue($entity, $this->textProperty);

        $data[$this->accessor->getValue($entity, $this->primaryKey)] = $text;

        return $data;
    }

    /**
     * Transform single id value to an entity
     *
     * @param string $value
     *
     * @return mixed|object|null
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }

        // Add a potential new tag entry
        $cleanValue = substr($value, 2);
        $valuePrefix = substr($value, 0, 2);
        if ('__' === $valuePrefix) {
            // In that case, we have a new entry
            $entity = new LsDefItemType();
            $entity->setCode($cleanValue);
            $entity->setTitle($cleanValue);
            $entity->setHierarchyCode($cleanValue);
            $this->em->persist($entity);
        } else {
            // We do not search for a new entry, as it does not exist yet, by definition
            try {
                $entity = $this->em->createQueryBuilder()
                    ->select('entity')
                    ->from($this->className, 'entity')
                    ->where('entity.'.$this->primaryKey.' = :id')
                    ->setParameter('id', $value)
                    ->getQuery()
                    ->getSingleResult();
            } catch (\Exception $ex) {
                // this will happen if the form submits invalid data
                throw new TransformationFailedException(sprintf('The choice "%s" does not exist or is not unique', $value));
            }
        }

        if (!$entity) {
            return null;
        }

        return $entity;
    }
}
