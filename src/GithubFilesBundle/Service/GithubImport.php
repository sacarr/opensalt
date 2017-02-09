<?php

namespace GithubFilesBundle\Service;

use CftfBundle\Entity\LsDoc;
use CftfBundle\Entity\LsItem;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class GithubImport.
 *
 * @DI\Service("cftf_import.github")
 */
class GithubImport
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     *
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Parse an Github document into a LsDoc/LsItem hierarchy
     *
     * @param array $lsDocKeys
     * @param array $lsItemKeys
     * @param string $fileContent
     */
    public function parseCSVGithubDocument($lsDocKeys, $lsItemKeys, $fileContent)
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
                $tempContent[$col] = $row[$h];
            }

            $content[] = $tempContent;
        }

        $this->saveCSVGithubDocument($lsDocKeys, $lsItemKeys, $content);
    }

    /**
     * Save an Github document into a LsDoc/LsItem hierarchy
     *
     * @param array $lsDocKeys
     * @param array $lsItemKeys
     * @param array $content
     */
    public function saveCSVGithubDocument($lsDocKeys, $lsItemKeys, $content)
    {
        $em = $this->getEntityManager();
        $lsDoc = new LsDoc();

        if (empty($this->getValue($lsDocKeys['creator'], $content[0]))) {
            $lsDoc->setCreator('Imported from GitHub');
        } else {
            $lsDoc->setCreator($this->getValue($lsDocKeys['creator'], $content[0]));
        }

        $lsDoc->setTitle($this->getValue($lsDocKeys['title'], $content[0]));
        $lsDoc->setOfficialUri($this->getValue($lsDocKeys['officialSourceURL'], $content[0]));
        $lsDoc->setPublisher($this->getValue($lsDocKeys['publisher'], $content[0]));
        $lsDoc->setDescription($this->getValue($lsDocKeys['description'], $content[0]));
        $lsDoc->setVersion($this->getValue($lsDocKeys['version'], $content[0]));
        $lsDoc->setSubject($this->getValue($lsDocKeys['subject'], $content[0]));
        $lsDoc->setLanguage($this->getValue($lsDocKeys['language'], $content[0]));
        $lsDoc->setNote($this->getValue($lsDocKeys['notes'], $content[0]));

        $lastGroupBy = "";
        $groupBy = "";
        $listGroupByInstances = [];
        $listGroupByTitles = [];

        for ($i = 1, $iMax = count($content); $i < $iMax; ++$i) {
            $row = $content[$i];
            $lsItem = $this->parseCSVGithubStandard($lsDoc, $lsItemKeys, $row, false);
            $groupBy = $this->getValue($lsItemKeys['groupBy'], $row);
            if( $groupBy === $lastGroupBy && strlen($groupBy) > 0){
                $listGroupByInstances[$groupBy]->addChild($lsItem);
            }else{
                if( in_array($groupBy, $listGroupByTitles)){
                    $listGroupByInstances[$groupBy]->addChild($lsItem);
                }else{
                    array_push($listGroupByTitles, $groupBy);
                    $newTopItem = $this->parseCSVGithubStandard($lsDoc, $lsItemKeys, $row, true);
                    $listGroupByInstances[$groupBy] = $newTopItem;
                    $lsDoc->addTopLsItem($newTopItem);
                }
            }
            $lastGroupBy = $groupBy;
        }

        $em->persist($lsDoc);
        $em->flush();
    }

    /**
     * @param LsDoc $lsDoc
     * @param array $lsItemKeys
     * @param string $data
     *
     * @return LsItem
     */
    public function parseCSVGithubStandard(LsDoc $lsDoc, $lsItemKeys, $data, $isTop)
    {
        $lsItem = new LsItem();
        $em = $this->getEntityManager();

        $fullStatement = $this->getValue($lsItemKeys['fullStatement'], $data);
        $humanCodingScheme = $this->getValue($lsItemKeys['humanCodingScheme'], $data);

        $lsItem->setLsDoc($lsDoc);
        if( $isTop ){
            $fullStatement = $this->getValue($lsItemKeys['groupBy'], $data);
            $humanCodingScheme = "";
        }
        $lsItem->setFullStatement($fullStatement);
        $lsItem->setHumanCodingScheme($humanCodingScheme);
        $lsItem->setAbbreviatedStatement($this->getValue($lsItemKeys['abbreviatedStatement'], $data));
        $lsItem->setListEnumInSource($this->getValue($lsItemKeys['listEnumeration'], $data));
        $lsItem->setConceptKeywords($this->getValue($lsItemKeys['conceptKeywords'], $data));
        $lsItem->setConceptKeywordsUri($this->getValue($lsItemKeys['conceptKeywordsUri'], $data));
        $lsItem->setLanguage($this->getValue($lsItemKeys['language'], $data));
        $lsItem->setLicenceUri($this->getValue($lsItemKeys['license'], $data));
        $lsItem->setNotes($this->getValue($lsItemKeys['notes'], $data));

        $em->persist($lsItem);

        return $lsItem;
    }

    /**
     * @param string $key
     * @param string $row
     *
     * @return string
     */
    private function getValue($key, $row)
    {
        if (empty($key)) {
            return '';
        } else {
            $res = strtok($key, ',');;

            if (strlen($res) !== strlen($key)) {
                return $res;
            }
        }

        return $row[$key];
    }
}