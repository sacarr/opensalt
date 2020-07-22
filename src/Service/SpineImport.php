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
use Psr\Log;


final class SpineImport
{
    use LoggerTrait;

    private static $itemCustomFields;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $levels = [];
    private int $rowOffset;
    private $levelIndex = [];
    private int $rootLevel;

    private $hierarchyItemIdentifiers    = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->rowOffset = 6;
        $this->rootLevel = 0;
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

    private function initializeIndex(?LsItem $item, int $row, int $column): int {
        $msg = "SpineImport::initializeLevel()";
        $realRow  = (($row*$this->rowOffset)+$column);
        $smartLevel = "1";
        $key = "";
        $match = [];
        $action = "N";
        // Items at level 1
        if (0 !== $row){
            throw new RuntimeException(sprintf("%s, cannot initialize the index for an item at position [%d, %d]", $msg, $row, $column));
        }
        if (0 === $column) {
            $this->rootLevel++;
        }
        // null Item in the first row of the spreadsheet
        // TODO: Fix this to handle multiple null items in the first row
        if (null === $item) {
            $action = "0";
            if (null === $this->levelIndex['null']) {
                $newRow = array('row' => $realRow, 'level' => -1, 'smartLevel' => "-1", 'last' => 0);
            } else {
                $newRow = array('row' => $realRow, 'level' => -1, 'smartLevel' => "-1", 'last' => $this->levelIndex['null']['row']);
            }
            $this->levelIndex['null'] = $newRow;
            $this->var_error_log(sprintf("%s\t\t[%s] row[%d] index[%s][%d, level => %d, smartLevel => %s, last => %d]\n",
                $msg, $action, $this->levelIndex['null']['row'], "null", $this->levelIndex['null']['row'],
                $this->levelIndex['null']['level'], $this->levelIndex['null']['smartLevel'], $this->levelIndex['null']['last']));
            return -1;
        }
        for ($i = 1; $i <= $column; $i++) {
            $smartLevel = sprintf("%s.1", $smartLevel);
        }
        $newRow = array('row' => $realRow, 'level' => $this->rootLevel, 'smartLevel' => $smartLevel, 'last' => 0);
        $key = $item->getAbbreviatedStatement();
        $this->levelIndex[$key] = $newRow;
        $this->var_error_log(sprintf("%s\t\t[%s] row[%d]\tindex[%s][%d, level => %d, smartLevel => %s, last => %d]\n",
            $msg, $action, $this->levelIndex[$key]['row'], $key, $this->levelIndex[$key]['row'],
            $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel'], $this->levelIndex[$key]['last']));
        return 1;
    }

    private function updateIndex(?LsItem $item, int $row, int $column): ?int {
        $msg = "SpineImport::updateIndex()";
        $realRow  = (($row * $this->rowOffset)+$column);
        $action = "L";
        if (null === $item) {
            $key = "null";
        } else {
            $key = $item->getAbbreviatedStatement();
        }
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] lookup item %s in the INDEX", $msg, $action, $realRow, $key));
        $match = $this->levelIndex[$key];
        if (false === boolVal($match)) {
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] no match for item %s", $msg, $action, $realRow, $key));
            return null;
        }
        if (array_key_exists('level', $this->levelIndex[$key]) && array_key_exists('smartLevel', $this->levelIndex[$key])) {
            $action = "M";
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] matched item %s: at level %d, smartLevel %s", $msg, $action, $realRow, $key,
                $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel']));
            $this->levelIndex[$key]['row'] = $realRow;
            $action = "U";
            $msg = 
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] updated last occurrence of item [%s][row => %s, level => %d, smartLevel => %s]\n", $msg, $action, $realRow, $key,
            $this->levelIndex[$key]['row'], $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel']));
            return $this->levelIndex[$key]['level'];
        }
    }

    private function getParent(LsItem $item, int $row, int $column): ?LsItem {
        $msg = "SpineImport::getParent()";
        $realRow  = (($row * $this->rowOffset)+$column);
        $priorRow = $realRow - 1;
        $key = "";
        $action = "L";
        $parent = null;
        if (null === $item) {
            throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d,%d] null item", $msg, $action, $realRow, $column));
        }
        if (0 === $column ) {
            throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d, %d] no parent for item %s", $msg, $action, $realRow, $column, $key));
        }
        $key = $item->getAbbreviatedStatement();
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] find parent for %s", $msg, $action, $realRow, $key));
        while ($priorRow >= $realRow-$column) {
            $parent =  $this->levels[$priorRow]['item'];
            if (null === $parent) {
                $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] ineligible null predecessor at row[%d] for item %s", $msg, $action, $realRow, $priorRow--, $key));
                continue;
            }
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] parent found for item %s at row[%d, %d]", $msg, $action, $realRow, $key, $priorRow, $column));
            return $parent;
        }
        return null;
    }

    private function getPredecessor(LsItem $item, int $row, int $column): ?LsItem {
        $msg = "SpineImport::getPredecessor()";
        $realRow  = (($row * $this->rowOffset)+$column);
        $priorRow = $realRow - $this->rowOffset;
        $key = "";
        $action = "L";
        if (null === $item) {
            throw new RuntimeException(sprintf("%s\t\t[%s] row[%d, %d] null item", $msg, $action,  $realRow, $column));
        }
        $key = $item->getAbbreviatedStatement();
        $this->var_error_log(sprintf("%s\t\t[%s] row[%d] find predecessor for %s", $msg, $action, $realRow, $key));
        while ($priorRow >= 0) {
            $predecessor = $this->levels[$priorRow]['item'];
            if (null === $predecessor) {
                $this->var_error_log(sprintf("%s\t\t[%s] row(%d] ineligible null predecessor for item %s", $msg, $action, $priorRow, $key));
                $priorRow = $priorRow - $this->rowOffset;
                continue;
            }
            return $predecessor;
        }
        return null;
    }

    private function addLevel(?LsItem $item, int $row, int $column): ?int {
        $msg = "SpineImport::addLevel()";
        $realRow  = (($row * $this->rowOffset)+$column);
        $smartLevel = "-1";
        $key = "";
        $action = "N";
        $level = 0;
        // First row
        if (0 === $row ) {
            throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d, %d] cannot add items to row 0", $msg, $action, $realRow, $column));
        }
        if (null === $item) {
            $action = "0";
            $key = "null";
            $this->levelIndex[$key] = array('row' =>$realRow, 'level' => -1, $smartLevel => "-1");
            return -1;
        }
        $key = $item->getAbbreviatedStatement();
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] adding to INDEX item %s", $msg, $action, $realRow, $key));
        $predecessor = $this->getPredecessor($item, $row, $column);
        if (null === $predecessor) {
            throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d,%d] no predecessor for item %s", $msg, $action, $realRow, $column, $key));
        }
        $predecessorKey = $predecessor->getAbbreviatedStatement();
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] found predecessor %s for item %s", $msg, $action, $realRow, $predecessorKey, $key));
        $match = $this->levelIndex[$predecessorKey];
        if (true === boolVal($match)  && array_key_exists('level', $this->levelIndex[$predecessorKey])) {
            $level = 1 + $this->levelIndex[$predecessorKey]['level'];
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] set level %d for item %s", $msg, $action, $realRow, $level,  $key));
        }
        if (0 === $column ) {
            $smartLevel = sprintf("%d", $level);
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] setting smartLevel to %d for item %s", $msg, $action, $realRow, $smartLevel,  $key));
        } else {
            // Get the Parent and its smartLevel
            $parent = $this->getParent($item, $row, $column);
            if (null === $parent) {
                throw new RuntimeException(sprintf("%s\t\t\t]%s] row[%d,%d] no parent for item %s", $msg, $action, $realRow, $column, $key));
            }
            $parentKey = $parent->getAbbreviatedStatement();
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] found parent %s for item %s", $msg, $action, $realRow, $parentKey, $key));
            $match = $this->levelIndex[$parentKey];
            if (true === boolVal($match) && array_key_exists('smartLevel', $this->levelIndex[$predecessorKey])) {
                    $smartLevel = $this->levelIndex[$parentKey]['smartLevel'];
                    $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] found smartLevel %s for item %s", $msg, $action, $realRow, $smartLevel,  $parentKey));
                    $smartLevel = sprintf("%s.%d", $smartLevel, $level);
                    $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] setting smartLevel to %s for item %s", $msg, $action, $realRow, $smartLevel,  $key));
                } else {
                throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d, %d] corrupted Index entry for parent %s of item %s", $msg, $action, $realRow, $column, $parentKey, $key));
            }
        }
        $this->levelIndex[$key] = array('row' => $realRow, 'level' => $level, 'smartLevel' => $smartLevel);
        $msg = sprintf("%s\t\t\t[%s] row[%d] index[%s][row => %s, level => %d, smartLevel => %s]\n", $msg, $action, $realRow, $key,
            $this->levelIndex[$key]['row'],$this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel']);
        $this->var_error_log($msg);
        return $level;
    }

    private function setHierarchyLevel(int $row, int $column, string $skillCode, ?LsItem $item) {
        $realRow  = (($row*$this->rowOffset)+$column);
        $rowValue = [];
        $action = "N";
        $msg = "SpineImport::setHierarchyLevel()";
        $statement = "";

        if (null === $skillCode) {
            throw new \RuntimeException(sprintf("%s Missing skill code", $msg));
        }
        // Initialize items in the first row to level 1, null items to level -1
        if (0 === $row) {
            $level = $this->InitializeIndex($item, $row, $column);
            if (null === $item) {
                $action = "0";
                $level = -1;
                $statement = "null";
            } else {
                $action = "N";
                $statement = $item->getAbbreviatedStatement();
            }
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => $level);
            $this->levels[$realRow] = $rowValue;
            $msg = sprintf("%s\t[%s] row[%d], array(code => %s, column => %d, level => %d, item => %s)\n",
                $msg, $action, $realRow, $this->levels[$realRow]['code'], $this->levels[$realRow]['column'],
                $this->levels[$realRow]['level'], $statement);
            $this->var_error_log($msg);
            return;
        }
        // Remaining itesm are not in the first row.  They must be either repeated items or new ones.
        // Try to update the level index for item.  If the update returns null, this is a new item.
        // Add it to the index and the table
        $repeated = $this->updateIndex($item, $row, $column);
        if (null === $repeated) {
            $action = "N";
            $level = $this->addLevel($item, $row, $column);
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => $level);
            $this->levels[$realRow] = $rowValue;
            $this->var_error_log(sprintf("%s\t[%s] row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
                $msg, $action, $realRow, $this->levels[$realRow]['code'], $this->levels[$realRow]['column'],
                $this->levels[$realRow]['level'], $this->levels[$realRow]['item']->getAbbreviatedStatement()));
            return;
        } else {
            // Remaining items are repeats.  
            $action = "R";
            if (null === $item) {
                $statement = "null";
            } else {
                $statement = $item->getAbbreviatedStatement();
            }
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => $repeated);
            $this->levels[$realRow] = $rowValue;
            $msg = sprintf("%s\t[%s] row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
                $msg, $action, $realRow, $this->levels[$realRow]['code'], $this->levels[$realRow]['column'],
                $this->levels[$realRow]['level'], $statement);
            $this->var_error_log($msg);
            return;
        }
    }

    public function importSpine(string $path): LsDoc
    {
        set_time_limit(180); // increase time limit for large files

        $phpExcelObject = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        if (null === $phpExcelObject) {
            throw new \RuntimeException('Cannot load spine from file'.$path);
        }
        /** @var LsItem[] $items */
        $items = [];
        $itemSmartLevels = [];
        $children = [];
        $levels[] = array(1,1,1,1,1);

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
                $key = $item->getAbbreviatedStatement();
                $itemIdentifier = $item->getIdentifier();
                if (null === $this->hierarchyItemIdentifiers[$key]) {
                    // Item is already in the catalog
                    $this->hierarchyItemIdentifiers[$key] = $itemIdentifier;
                }
                if (array_key_exists('smartLevel', $this->levelIndex[$key])) {
                    $smartLevel = $this->levelIndex[$key]['smartLevel'];
                    $items[$itemIdentifier] = $item;
                    $smartLevels[$smartLevel] = $item;
                    $itemSmartLevels[$itemIdentifier] = $smartLevel;
                }
            }
            $item = $this->saveSkill($sheet, $doc, $row);
            $this->setHierarchyLevel($rowLevel, $hierarchyLevel++, $skillCode, $item);
            if (null === $item) {
                continue;
            }
            $key = $item->getAbbreviatedStatement();
            $itemIdentifier = $item->getIdentifier();
            if (null === $this->hierarchyItemIdentifiers[$key]) {
                // Item is already in the catalog
                $this->hierarchyItemIdentifiers[$key] = $itemIdentifier;
            }
            if (array_key_exists('smartLevel', $this->levelIndex[$key])) {
                $smartLevel = $this->levelIndex[$key]['smartLevel'];
                $items[$itemIdentifier] = $item;
                $smartLevels[$smartLevel] = $item;
                $itemSmartLevels[$itemIdentifier] = $smartLevel;
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