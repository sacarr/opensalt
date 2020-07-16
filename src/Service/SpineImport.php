<?php

namespace App\Service;

use App\Entity\Framework\AdditionalField;
use App\Entity\Framework\ImportLog;
use App\Entity\Framework\LsAssociation;
use App\Entity\Framework\LsDefAssociationGrouping;
use App\Entity\Framework\LsDefItemType;
use App\Entity\Framework\LsDefLicence;
use App\Entity\Framework\LsDoc;
use App\Entity\Framework\LsItem;
use App\Util\EducationLevelSet;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Null_;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls\Worksheet as XlsWorksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet as XlsxWorksheet;
use Ramsey\Uuid\Uuid;
use RuntimeException;

final class SpineImport
{
    private static $itemCustomFields;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    private $hierarchyItemIdentifiers    = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        
        if (null === self::$itemCustomFields) {
            $customFieldsArray = $this->getEntityManager()->getRepository(AdditionalField::class)
                ->findBy(['appliesTo' => LsItem::class]);
            self::$itemCustomFields = array_map(static function (AdditionalField $cf) {
                return $cf->getName();
            }, $customFieldsArray);
        }

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

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    private $hierarchyLevels = array();

    private function setHierarchyLevel(int $row, int $column, string $skillCode, ?LsItem $item) {
        $rowOffset=5;
        $skillRow = 5;
        $realRow  = (($row*$rowOffset)+$column);
        $priorRow = ($realRow-$rowOffset);
        $rowValue = [];
        $action = "N";
        $msg = "SpineImport::setHierarchyLevel()";

        if (null === $skillCode) {
            throw new \RuntimeException(sprintf("%s Missing skill code", $msg));
        }
        // Initialize items in the first row to level 1 setting null items to level -1
        if (0 === $row) {
            if (null === $item) {
                $action = "0";
                $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => -1);
                $this->hierarchyLevels[$realRow] = $rowValue;
                $msg = sprintf("%s [%s] row[%d], array(code => %s, column => %d, level => %d, item => null)\n",
                $msg, $action, $realRow, $this->hierarchyLevels[$realRow]['code'], $this->hierarchyLevels[$realRow]['column'],
                $this->hierarchyLevels[$realRow]['level']);
            } else {
                $action = "N";
                $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => 1);
                $this->hierarchyLevels[$realRow] = $rowValue;
                $msg = sprintf("%s [%s] row[%d], array(code => %s, column => %d, level => %d, item => %s)\n",
                $msg, $action, $realRow, $this->hierarchyLevels[$realRow]['code'], $this->hierarchyLevels[$realRow]['column'],
                $this->hierarchyLevels[$realRow]['level'], $this->hierarchyLevels[$realRow]['item']->getAbbreviatedStatement());
            }
            $this->var_error_log($msg);
            return;
        }
        // If items are null, set the level to -1
        if (null === $item) {
            $action = "0";
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => -1);
            $this->hierarchyLevels[$realRow] = $rowValue;
            $msg = sprintf("%s [%s] row[%d] array(code => %s, column => %d, level => %d, item => null)\n",
            $msg, $action, $realRow, $this->hierarchyLevels[$realRow]['code'], $this->hierarchyLevels[$realRow]['column'],
            $this->hierarchyLevels[$realRow]['level']);
            return;
        }
        // Since this is not he first row and not a null item, it must be either a new item or a repeat of a prior item
        // Scan through items in this column to see if it is a repeat
        $lastItem = $this->hierarchyLevels[$priorRow]['item'];
        $lastLevel = $this->hierarchyLevels[$priorRow]['level'];
        while ($priorRow >= 0 && null !== $lastItem && $lastItem->getAbbreviatedStatement() !== $item->getAbbreviatedStatement()) {
            $priorRow=$priorRow-$rowOffset;
            $lastItem = $this->hierarchyLevels[$priorRow]['item'];
            $lastLevel = $this->hierarchyLevels[$priorRow]['level'];
        }
        // If this item did not match a prior one, it is new.  Column 0 is the simpler case
        if ( null === $lastItem ) {
            if (0 === $column) {
                $action = "N";
                // If it is in column 0, its level is 1 + the highest level in column 0.
                $priorRow =$realRow-$rowOffset;
                $highestLevel = 1;
                while ($priorRow >= 0 ) {
                    $level = $this->hierarchyLevels[$priorRow]['level'];
                    if ($level > $highestLevel) {
                        $highestLevel = $level;
                    }
                    $priorRow = $priorRow - $rowOffset;
                }
                $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => ($highestLevel + 1));
                $this->hierarchyLevels[$realRow] = $rowValue;
                $msg = sprintf("%s [%s] row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
                    $msg, $action, $realRow, $this->hierarchyLevels[$realRow]['code'], $this->hierarchyLevels[$realRow]['column'],
                        $this->hierarchyLevels[$realRow]['level'], $this->hierarchyLevels[$realRow]['item']->getAbbreviatedStatement());
                    $this->var_error_log($msg);
            } else {
                // A new item in column 1 - 6
                // If not in column 0,
                // find the highest level for an item in this column with the same parent
                $action = "N";
                $parentStatement = $this->hierarchyLevels[$realRow-1]['item']->getAbbreviatedStatement();
                $priorRow =$realRow-$rowOffset;
                $highestLevel = 1;
                while ($priorRow >= 0 ) {
                    $level = $this->hierarchyLevels[$priorRow]['level'];
                    if ($level > $highestLevel ) {
                        $statement = $this->hierarchyLevels[$priorRow-1]['item']->getAbbreviatedStatement();
                        if ($statement === $parentStatement) {
                            $highestLevel = $level;
                        }
                    }
                    $priorRow = $priorRow - $rowOffset;
                }
                // Now, check the parent items.  If this is not the first occurrence of the parent level (e.g. column -1) === this item's grandparent level (e.g. row - 5, column -1),
                // Then this item has a predecessor at the same parent level.  Therefore this item's level is one plus the highest
                // level for this column where the item's grandparent's statement matches it's parent's statement
                $parentLevel = $this->hierarchyLevels[$realRow-1]['level'];
                $grandParentLevel = $this->hierarchyLevels[$realRow-(1+$rowOffset)]['level'];
                if ($parentLevel === $grandParentLevel) {
                    $lastLevel = $this->hierarchyLevels[$realRow-$rowOffset]['level'];
                    $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => ($highestLevel + 1));
                    $this->hierarchyLevels[$realRow] = $rowValue;
                } else {
                    // If parent and grandparent are not at the same level this is actually the first item for the parent level
                    $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => 1);
                    $this->hierarchyLevels[$realRow] = $rowValue;
                }
            }
            $msg = sprintf("%s [%s] item row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
            $msg, $action, $realRow, $this->hierarchyLevels[$realRow]['code'], $this->hierarchyLevels[$realRow]['column'],
            $this->hierarchyLevels[$realRow]['level'], $this->hierarchyLevels[$realRow]['item']->getAbbreviatedStatement());
        } else {
            // This is a repeated item
            $action = "R";
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => $lastLevel);
            $this->hierarchyLevels[$realRow] = $rowValue;
            $msg = sprintf("%s [%s] row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
            $msg, $action, $realRow, $this->hierarchyLevels[$realRow]['code'], $this->hierarchyLevels[$realRow]['column'],
            $this->hierarchyLevels[$realRow]['level'], $this->hierarchyLevels[$realRow]['item']->getAbbreviatedStatement());
        }
        // The only thing left is items out of sequence, may not happen as these would still come thorugh as nulls
        $this->var_error_log($msg);
        return;
    }

    private function getSmartLevel(int $row, int $column, string $skillCode, string $itemIdentifier): ?string {

        $smartLevel = "";
        $levels = $this->hierarchyLevels[$row];
        foreach ($levels as $level) {
            if (null !== $level) {
                $smartLevel = sprintf("%s.%d", $smartLevel, $level);
            }
        }
        if (empty($smartLevel)) {
            return null;
        }
        return $smartLevel;
    }

    public function importSpine(string $path): LsDoc
    {
        set_time_limit(180); // increase time limit for large files

        $phpExcelObject = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        if (null === $phpExcelObject) {
            throw new \RuntimeException('Cannot load learning spine from file'.$path);
        }
        /** @var LsItem[] $items */
        $items = [];
        $itemSmartLevels = [];
        $children = [];
        $hierarchyLevels[] = array(1,1,1,1,1);

        /** @var LsItem[] $smartLevels */
        $smartLevels = [];
        $sheet = $phpExcelObject->getSheetByName('ELASpine');
        if (null === $sheet) {
            throw new \RuntimeException('This workbook does not container a Learinng Spine.');
        }
        $doc = $this->saveDocument($sheet);
        $children[$doc->getIdentifier()] = $doc->getIdentifier();

        $lastRow = $sheet->getHighestRow();

        // Create hierarchy items
        for ($row =  7; $row <= $lastRow; ++$row) {
            $rowLevel = $row - 7;
            $hierarchyLevel = 0;
            $skillCode = $this->getCellValueOrNull($sheet, 9, $row);
            $smartLevel = "";
            for ( $column = 1; $column <= 5; $column++ ) {
                $item = null;
                $item = $this->saveHierarchyItem($sheet, $doc, $row, $column);
                $this->setHierarchyLevel($rowLevel, $hierarchyLevel++, $skillCode, $item);
                if (null === $item) {
                    continue;
                }
                $statement = $item->getFullStatement();
                $itemIdentifier = $item->getIdentifier();
                if (null === $this->hierarchyItemIdentifiers[$statement]) {
                    // Item is already in the catalog
                    $this->hierarchyItemIdentifiers[$statement] = $itemIdentifier;
                } 
                $smartLevel = $this->getSmartLevel($rowLevel, $hierarchyLevel, $skillCode, $itemIdentifier);
                $items[$item->getIdentifier()] = $item;
                $smartLevels[$smartLevel] = $item;
                $itemSmartLevels[$itemIdentifier] = $smartLevel;
//                $msg = sprintf("SpineImport::importSpine() smartLevel %s [%s][%d]: %s", $smartLevels[$smartLevel]->getFullStatement() , $skillCode, $column, $smartLevel /*$itemSmartLevels[$itemIdentifier]*/);
//                $this->var_error_log($msg);
//                $hierarchyLevel++;
/*
                if (empty($smartLevel)) {
                    $smartLevel = sprintf("%d", $hierarchyLevels[$skillCode][$column-1]);
                } else {
                    $smartLevel = sprintf("%s.%d", $smartLevel, $hierarchyLevels[$skillCode][$column-1]);
                }
            if (null === $item) {
                        for ($q = $column-1; $q <= 5; $q++) {
                        $hiearchyLevels[$row-7][$q] = null;
                    }
                    continue;
                }
                if (null === $item->getFullstatement()) {
                    throw new \RuntimeException('Saved a hierarchy item with a null statement');
                }
*/


            }

            $item = $this->saveSkill($sheet, $doc, $row);
            if (null === $item) {
                continue;
            } else {
                $statement = $item->getAbbreviatedStatement();
                $itemIdentifier = $item->getIdentifier();
                $this->hierarchyItemIdentifiers[$statement] = $itemIdentifier;
                $items[$item->getIdentifier()] = $item;
                $smartLevels[$smartLevel] = $item;
//                $msg = sprintf("SpineImport::importSpine() created skill [%d]: %s", $smartLevel, $statement);
                $itemSmartLevels[$item->getIdentifier()] = $smartLevel++;
//                $this->var_error_log($msg);
//                $this->var_error_log("SpineImport::importSpine() created item ", $item->getAbbreviatedStatement());
//                $items[$item->getIdentifier()] = $item;
//                $smartLevels[$smartLevel] = $item;
//                $itemSmartLevels[$item->getIdentifier()] = $smartLevel++;
            }
        }
            
/*
        $associationsIdentifiers = [];
        foreach ($items as $item) {
            $smartLevel = $itemSmartLevels[$item->getIdentifier()];
            $levels = explode('.', $smartLevel);
            $seq = array_pop($levels);
            $parentLevel = implode('.', $levels);

            if (!is_numeric($seq)) {
                $seq = null;
            }

            $children[$item->getIdentifier()] = $doc->getIdentifier();

            if (in_array($parentLevel, $itemSmartLevels, true)) {
                $assoc = $this->getEntityManager()->getRepository(LsAssociation::class)->findOneBy([
                    'originNodeIdentifier' => $item->getIdentifier(),
                    'type' => LsAssociation::CHILD_OF,
                    'destinationNodeIdentifier' => $smartLevels[$parentLevel]->getIdentifier(),
                ]);

                if (null === $assoc) {
                    $assoc = $smartLevels[$parentLevel]->addChild($item, null, $seq);
                } else {
                    $assoc->setSequenceNumber($seq);
                }
            } else {
                $assoc = $this->getEntityManager()->getRepository(LsAssociation::class)->findOneBy([
                    'originNodeIdentifier' => $item->getIdentifier(),
                    'type' => LsAssociation::CHILD_OF,
                    'destinationNodeIdentifier' => $item->getLsDoc()->getIdentifier(),
                ]);

                if (null === $assoc) {
                    $assoc = $doc->createChildItem($item, null, $seq);
                } else {
                    $assoc->setSequenceNumber($seq);
                }
            }

            $associationsIdentifiers[$assoc->getIdentifier()] = null;
        }

        $items[$doc->getIdentifier()] = $doc;

        $sheet = $phpExcelObject->getSheetByName('CF Association');
        if (null === $sheet) {
            throw new \RuntimeException('CF Association sheet does not exist in the workbook');
        }
        $lastRow = $sheet->getHighestRow();

        for ($i = 2; $i <= $lastRow; ++$i) {
            $assoc = $this->saveAssociation($sheet, $doc, $i, $items, $children);
            if (null !== $assoc) {
                $associationsIdentifiers[$assoc->getIdentifier()] = null;
            }
        }
        $this->checkRemovedItems($doc, $items);
        $this->checkRemovedAssociations($doc, $associationsIdentifiers);
*/

        return $doc;
    }

    private function saveDocument(Worksheet $sheet): LsDoc
    {
        $id = $this->getCellValueOrNull($sheet, 2, 3);
        $docRepo = $this->getEntityManager()->getRepository(LsDoc::class);
        $doc = $docRepo->findOneByIdentifier($id);

        if (null === $doc) {
            $doc = new LsDoc();
            $doc->setIdentifier($id);
        }
        $doc->setCreator("Houghton Mifflin Harcourt Learning Sciences");
        $doc->setPublisher("Houghton Mifflin Harcourt, LLC");
        $doc->setLanguage("EN");
        $doc->setAdoptionStatus("Private Draft");
        $doc->setTitle($this->getCellValueOrNull($sheet, 2, 1));
        $doc->setDescription($this->getCellValueOrNull($sheet, 2, 1));
        $doc->setSubject($this->getCellValueOrNull($sheet, 2, 2));
        $doc->setVersion($this->getCellValueOrNull($sheet, 2, 4));
        $officialURI = "https://www.hmhco.com/learningspines/".$id;
        $doc->setOfficialUri($officialURI);
        $statusStart = new \DateTime();
        $statusEnd = new \DateTime("12/31/2020");
        $doc->setStatusStart($statusStart);
        $doc->setStatusEnd($statusEnd);
        $doc->setNote("Imported from Chiropractor");

        $this->getEntityManager()->persist($doc);

        return $doc;
    }

    private function humanCodingSchemeFromStatement(?string $statement): ?string
    {
        $pattern = "(^[Aa]nd$|^[Bb]ut$|^[Oo][f|n|r]$|^[Ii][s|f|n|t]$|^I$|^I'm$|[Yy]ou.*$|^[Oo]ur$|^[Ww]here$|^[Ww]hat$|^[Ww]hen$|^[Ww]hy$|^[Hh]ow$)";
        if (null === $statement){
            return null;
        }
        $words = explode(" ", $statement);
        if ( empty($words) ) {
            $msg = sprintf("Unrecognized statement format for statement %s", $statement);
            throw new \RuntimeException($msg);
        }
        if (1 === count($words) ) {
            return strtoupper(substr($words[0], 0, 2));
        }
        $humanCodingScheme = "";
        foreach ($words as $word) {
            if (0 === preg_match($pattern, $word) ) {
                $humanCodingScheme = sprintf("%s%s", $humanCodingScheme, strtoupper(substr($word, 0, 1)));
            }
        }
        return $humanCodingScheme;
    }
    
    private function humanCodingSchemeFromsSkillCode(?string $code, $column): ?string
    {
        if (null === $code) {
            return null;
        }
        $words = explode(".", $code);
        if (empty($words) ) {
            $msg = sprintf("Unrecognized skill code format for code %s", $code);
            throw new \RuntimeException($msg);
        }
        $cnt = count($words);
        if ($cnt < $column) {
            return null;
        }
        return $words[$column];
    }

/*
    function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
    
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
*/
    private function saveHierarchyItem(Worksheet $sheet, lsDoc $doc, int $row, int $column): ?LsItem
    {
        $msg = sprintf("SpineImport::saveHierarchyItem() ");
        /** @var LsItem[] $items */
        $item = null;
        $humanCodingScheme = null;
        $identifier = null;
        $itemTypeTitle = $this->getCellValueOrNull($sheet, $column, 6);
        $statement = $this->getCellValueOrNull($sheet, $column, $row);
        $identifier = $this->hierarchyItemIdentifiers[$statement];
        if (null === $statement) {
            return null;
        }
        if ( !empty($identifier) && Uuid::isValid($identifier) ) {
            $item = $this->getEntityManager()->getRepository(LsItem::class)
                ->findOneBy(['identifier' => $identifier, 'lsDocIdentifier' => $doc->getIdentifier()]);
            if ( $item !== null) {
                $itemStatement=$item->getFullStatement();
                if ($itemStatement !== $statement ) {
                    $msg = sprintf("%s %s !== %s", $msg, $statement, $itemStatement);
                    throw new \RuntimeException($msg);
                }
                return $item;
            }
        }
        $item = $doc->createItem();

        if (null !== $statement) {
            $item->setFullStatement($statement);
            $item->setAbbreviatedStatement($statement);
        }
        if (null !== $humanCodingScheme){
            $item->setHumanCodingScheme($humanCodingScheme);
        }
        $item->setLanguage("En");
        $itemType = $this->findItemType($itemTypeTitle);
        $itemTypeTitle = $this->getCellValueOrNull($sheet, $column, 6);
        if (null !== $itemTypeTitle) {
            $item->setItemType($itemType);
        }
//        $this->addAdditionalFields($row, $item, $sheet);
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
        return $item;
    }

    private function saveSkill(Worksheet $sheet, LsDoc $doc, int $row): ?LsItem
    {
        $item = null;
        $itemTypeTitle = "Skill";
        $skillTitle = $this->getCellValueOrNull($sheet, 6, $row);
        $skillDescription = $this->getCellValueOrNull($sheet, 7, $row);
        $skillGuid = $this->getCellValueOrNull($sheet, 8, $row);
        $skillCode = $this->getCellValueOrNull($sheet, 9, $row);
        $skillLowerGrade = $this->getCellValueOrNull($sheet, 10, $row);
        $skilUpperGrade = $this->getCellValueOrNull($sheet, 11, $row);
        $skillKnowledgeType = $this->getCellValueOrNull($sheet, 12, $row);
        $skillEmphasis = $this->getCellValueOrNull($sheet, 13, $row);
        if (empty($skillGuid)) {
            $skillGuid = null;
        } elseif (Uuid::isValid($skillGuid)) {
            $item = $this->getEntityManager()->getRepository(LsItem::class)
                ->findOneBy(['identifier' => $skillGuid, 'lsDocIdentifier' => $doc->getIdentifier()]);
        }

        if (null === $item && !empty($skillTitle)) {
            $item = $doc->createItem($skillGuid);
        }

        if (null === $item) {
            return null;
        }
        if (null !== $skillTitle) {
            $item->setAbbreviatedStatement($skillTitle);
        }
        if (null !== $skillDescription) {
            $item->setFullStatement($skillDescription);
        }
        if (null !== $skillCode) {
            $item->setHumanCodingScheme($skillCode);
        }
//        $item->setListEnumInSource($smartLevel);
        $item->setLanguage("EN");
        $this->setEducationalAlignment($item, sprintf("%d - %d",$skillLowerGrade, $skilUpperGrade));

        $itemType = $this->findItemType($itemTypeTitle);
        $item->setItemType($itemType);

        // col 12 - licence

        // col 13+ - additional fields
//        $this->addAdditionalFields($row, $item, $sheet);

        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();

        return $item;
    }

    private function getLicence(Worksheet $sheet): LsDefLicence
    {
        $title = $this->getCellValueOrNull($sheet, 14, 2);
        $licenceText = $this->getCellValueOrNull($sheet, 15, 2);

        // creates licence if it doesn't exists locally
        $licence = new LsDefLicence();

        $licence->setTitle($title);
        $licence->setLicenceText($licenceText);

        $this->getEntityManager()->persist($licence);

        return $licence;
    }


    private function saveAssociation(Worksheet $sheet, LsDoc $doc, int $row, array $items, array $children): ?LsAssociation
    {
        $fieldNames = [
            1 => 'identifier',
            2 => 'originNodeURI',
            3 => 'originNodeIdentifier',
            4 => 'originNodeHumanCodingScheme',
            5 => 'associationType',
            6 => 'destinationNodeURI',
            7 => 'destinationNodeIdentifier',
            8 => 'destinationNodeHumanCodingScheme',
            9 => 'associationGroupIdentifier',
            10 => 'associationGroupName',
        ];

        $itemRepo = $this->getEntityManager()->getRepository(LsItem::class);
        $association = null;
        $fields = [];

        foreach ($fieldNames as $col => $name) {
            $value = $this->getCellValueOrNull($sheet, $col, $row);
            if (null !== $value) {
                $value = (string) $value;
            }
            $fields[$name] = $value;
        }

        if (LsAssociation::CHILD_OF === $fields['associationType'] && array_key_exists($fields['originNodeIdentifier'], $children)) {
            return null;
        }

        if (empty($fields['identifier'])) {
            $fields['identifier'] = null;
        } elseif (Uuid::isValid($fields['identifier'])) {
            $association = $this->getEntityManager()->getRepository(LsAssociation::class)
                ->findOneBy(['identifier' => $fields['identifier'], 'lsDocIdentifier' => $doc->getIdentifier()]);
        }

        if (null === $association) {
            $association = $this->getEntityManager()->getRepository(LsAssociation::class)->findOneBy([
                'originNodeIdentifier' => $fields['originNodeIdentifier'],
                'type' => $fields['associationType'],
                'destinationNodeIdentifier' => $fields['destinationNodeIdentifier'],
            ]);

            if (null === $association) {
                $association = $doc->createAssociation($fields['identifier']);
            }
        }

        if (array_key_exists($fields['originNodeIdentifier'], $items)) {
            $association->setOrigin($items[$fields['originNodeIdentifier']]);
        } else {
            $ref = 'data:text/x-ref-unresolved,'.$fields['originNodeIdentifier'];
            $association->setOrigin($ref, $fields['originNodeIdentifier']);
        }

        if (array_key_exists($fields['destinationNodeIdentifier'], $items)) {
            $association->setDestination($items[$fields['destinationNodeIdentifier']]);
        } elseif ($item = $itemRepo->findOneByIdentifier($fields['destinationNodeIdentifier'])) {
            $items[$item->getIdentifier()] = $item;
            $association->setDestination($item);
        } else {
            $ref = 'data:text/x-ref-unresolved,'.$fields['destinationNodeIdentifier'];
            $association->setDestination($ref, $fields['destinationNodeIdentifier']);
        }

        $allTypes = [];
        foreach (LsAssociation::allTypes() as $type) {
            $allTypes[str_replace(' ', '', strtolower($type))] = $type;
        }

        $associationType = str_replace(' ', '', strtolower($fields['associationType']));

        if (array_key_exists($associationType, $allTypes)) {
            $association->setType($allTypes[$associationType]);
        } else {
            $log = new ImportLog();
            $log->setLsDoc($doc);
            $log->setMessageType('error');
            $log->setMessage("Invalid Association Type ({$fields['associationType']} on row {$row}.");

            $this->getEntityManager()->persist($log);

            return null;
        }

        if (!empty($fields['associationGroupIdentifier'])) {
            $associationGrouping = new LsDefAssociationGrouping();
            $associationGrouping->setLsDoc($doc);
            $associationGrouping->setTitle($fields['associationGroupName']);
            $association->setGroup($associationGrouping);
            $this->getEntityManager()->persist($associationGrouping);
        }

        $this->getEntityManager()->persist($association);

        return $association;
    }

    private function getCellValueOrNull(Worksheet $sheet, int $col, int $row)
    {
        $cell = $sheet->getCellByColumnAndRow($col, $row, false);

        if (null === $cell) {
            return null;
        }

        return $cell->getValue();
    }

    private function checkRemovedItems(LsDoc $doc, array $array): void
    {
        $docRepo = $this->getEntityManager()->getRepository(LsDoc::class);
        $repo = $this->getEntityManager()->getRepository(LsItem::class);

        $existingItems = $docRepo->findAllItems($doc);

        $existingItems = array_filter($existingItems, static function ($item) use ($array) {
            return !array_key_exists($item['identifier'], $array);
        });

        foreach ($existingItems as $existingItem) {
            $element = $repo->findOneByIdentifier($existingItem['identifier']);

            if (null !== $element) {
                $repo->removeItemAndChildren($element);
            }
        }
    }

    private function checkRemovedAssociations(LsDoc $doc, array $array): void
    {
        $docRepo = $this->getEntityManager()->getRepository(LsDoc::class);
        $repo = $this->getEntityManager()->getRepository(LsAssociation::class);

        $existingAssociations = $docRepo->findAllAssociations($doc);

        $existingAssociations = array_filter($existingAssociations, static function ($association) use ($array) {
            return !array_key_exists($association['identifier'], $array);
        });

        foreach ($existingAssociations as $existingAssociation) {
            $element = $repo->findOneByIdentifier($existingAssociation['identifier']);

            if (null !== $element) {
                $repo->removeAssociation($element);
            }
        }
    }

    private function findItemType(?string $itemTypeTitle): ?LsDefItemType
    {
        static $itemTypes = [];

        if (null === $itemTypeTitle || '' === trim($itemTypeTitle)) {
            return null;
        }

        if (in_array($itemTypeTitle, $itemTypes, true)) {
            return $itemTypes[$itemTypeTitle];
        }

        $itemType = $this->getEntityManager()->getRepository(LsDefItemType::class)
            ->findOneByTitle($itemTypeTitle);

        if (null === $itemType) {
            $itemType = new LsDefItemType();
            $itemType->setTitle($itemTypeTitle);
            $itemType->setCode($itemTypeTitle);
            $itemType->setHierarchyCode($itemTypeTitle);
            $this->getEntityManager()->persist($itemType);
        }

        $itemTypes[$itemTypeTitle] = $itemType;

        return $itemType;
    }

    private function addAdditionalFields(int $row, LsItem $item, Worksheet $sheet): void
    {
        $column = 13;

        while (null !== $this->getCellValueOrNull($sheet, $column, 1)) {
            $customField = $this->getCellValueOrNull($sheet, $column, 1);

            if (null !== $customField && in_array($customField, self::$itemCustomFields, true)) {
                $value = $this->getCellValueOrNull($sheet, $column, $row);
                $item->setAdditionalField($customField, $value);
            }
            ++$column;
        }
    }

    private function setEducationalAlignment(LsItem $item, ?string $passedGradeString): void
    {
        $item->setEducationalAlignment(EducationLevelSet::fromString($passedGradeString)->toString());
    }
}