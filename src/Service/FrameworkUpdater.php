<?php

namespace App\Service;

use App\Entity\Framework\LsDoc;
use App\Entity\Framework\LsItem;
use App\Entity\Framework\LsAssociation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @deprecated
 */
class FrameworkUpdater
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var GithubImport
     */
    private $githubImport;

    public function __construct(EntityManagerInterface $entityManager, GithubImport $githubImport)
    {
        $this->entityManager = $entityManager;
        $this->githubImport = $githubImport;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * Update framework from a CSV
     */
    public function update(LsDoc $lsDoc, string $fileContent, string $frameworkToAssociate, array $cfItemKeys): void
    {
        $em = $this->getEntityManager();
        $contentTransformed = $this->transformContent($fileContent);

        foreach (array_keys($contentTransformed) as $i) {
            $cfItem = $em->getRepository(LsItem::class)
                ->findOneByIdentifier($contentTransformed[$i]['Identifier']);

            if (!$cfItem) {
                continue;
            }
            $cfItem->setLsDoc($lsDoc);
            $cfItem->setFullStatement($contentTransformed[$i][$cfItemKeys['fullStatement']]);
            $cfItem->setHumanCodingScheme($contentTransformed[$i][$cfItemKeys['humanCodingScheme']]);
            $cfItem->setAbbreviatedStatement($contentTransformed[$i][$cfItemKeys['abbreviatedStatement']]);
            $cfItem->setConceptKeywordsString($contentTransformed[$i][$cfItemKeys['conceptKeywords']]);
            $cfItem->setLanguage($contentTransformed[$i][$cfItemKeys['language']]);
            //$cfItem->setLicenceUri($contentTransformed[$i][$cfItemKeys['license']]);
            $cfItem->setNotes($contentTransformed[$i][$cfItemKeys['notes']]);

            $this->updateAssociations($cfItem, $contentTransformed[$i], $cfItemKeys);
        }

        $em->flush();
    }

    /**
     * Add associations not existances on this CfItem
     */
    private function updateAssociations(LsItem $lsItem, array $rowContent, array $cfItemKeys): void
    {
        $associationTypes = LsAssociation::allTypesForImportFromCSV();
        $assocNotMatched = [];

        foreach ($associationTypes as $assoTypeKey => $assoTypeValue) {
            foreach (explode(',', $rowContent[$cfItemKeys[$assoTypeKey]]) as $associationSeparatedByComma) {
                if ($associationSeparatedByComma === '') {
                    continue;
                }

                if (!array_key_exists($associationSeparatedByComma, $assocNotMatched)) {
                    $assocNotMatched[$associationSeparatedByComma] = ['matched' => true, 'type' => $assoTypeValue];
                }

                foreach ($lsItem->getAssociations() as $association) {
                    $destination = $association->getDestination();

                    if (LsAssociation::INVERSE_EXACT_MATCH_OF === $association->getType()
                        || LsAssociation::CHILD_OF === $association->getType()) {
                        continue;
                    }

                    if (is_string($destination)) {
                        if ($this->validatePresenceOnAssociation($destination, $associationSeparatedByComma)) {
                            $assocNotMatched[$associationSeparatedByComma]['matched'] = false;
                        } elseif ('data:text/x-ref-unresolved;base64,' === substr($destination, 0, 34)) {
                            if ($this->validatePresenceOnAssociation($association->getHumanCodingSchemeFromDestinationNodeUri(), $associationSeparatedByComma)) {
                                $assocNotMatched[$associationSeparatedByComma]['matched'] = false;
                            }
                        }
                    } elseif ($destination instanceof LsItem) {
                        if ($this->validatePresenceOnAssociation($destination->getHumanCodingScheme(), $associationSeparatedByComma)) {
                            $assocNotMatched[$associationSeparatedByComma] = ['matched' => false, 'type' => $assoTypeValue];
                        }
                    }
                }
            }
        }

        if (count($assocNotMatched) > 0) {
            foreach ($assocNotMatched as $assocForMatch => $assocForMatchValues) {
                if (!$assocForMatchValues['matched'] === false) {
                    $this->githubImport->addItemRelated($lsItem->getLsDoc(), $lsItem, $assocForMatch, 'all', $assocForMatchValues['type']);
                }
            }
        }
    }

    /* getHumanCodingSchemeFromDestinationNodeUri */

    /**
     * Return true or false if item has a association
     */
    private function validatePresenceOnAssociation(string $associationValue, string $associationOnContent): bool
    {
        return $associationOnContent === $associationValue;
    }

    /**
     * Transform string content in arrays per line.
     *
     * @param string $fileContent
     */
    protected function transformContent($fileContent): array
    {
        $csvContent = str_getcsv($fileContent, "\n");
        $headers = [];
        $content = [];

        foreach ($csvContent as $i => $row) {
            $tempContent = [];
            $row = str_getcsv($row, ',');

            if ($i === 0) {
                $headers = $row;
                continue;
            }

            foreach ($headers as $h => $col) {
                if ($h < count($row)) {
                    $tempContent[$col] = $row[$h];
                }
            }

            $content[] = $tempContent;
        }

        return $content;
    }

}
