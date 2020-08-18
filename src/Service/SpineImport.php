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

    private int $rowOffset;
    private int $rootLevel;

    private $levels = []; // Array of all items [int (0-based index into rows and columns) ['code' => string($skillCode), 'item' => LsItem($item), 'column' => int($column), 'level' => int($level the depth of this item relative to other items at the sam eparent level)]]
    private $levelIndex = []; // Index into $this->levels [string (abbreviated statement) ['row' => int, 'level' => int, 'smartLevel' => string, 'last' => int]]

    private $hierarchyItemIdentifiers = []; // Array of the hierarchy items [string (abbreviated statement) [identifier, column] used to track whether an item already exists in the hierarchy
    
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
        $action = "N";
        // Items at level 1
        if (0 !== $row){
            throw new RuntimeException(sprintf("%s, cannot initialize the index for an item at position [%d, %d]", $msg, $row, $column));
        }
        if (0 === $column) {
            $this->rootLevel++;
            $key = $item->getIdentifier();
            $this->levelIndex[$key] = array('row' => 0, 'level' => 1, 'smartLevel' => "1", 'last' => 0);
            $this->var_error_log(sprintf("%s\t\t[%s] row[%d] index[%s][row => %d, level => %d, smartLevel => %s, last => %d]\n", $msg, $action, $this->levelIndex[$key]['row'], $key, $this->levelIndex[$key]['row'], $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel'], $this->levelIndex[$key]['last']));
            return $this->rootLevel;
        }
        // null Item in the first row of the spreadsheet
        if (null === $item) {
            $action = "0";
            if (null === $this->levelIndex['null']) {
                $this->levelIndex['null'] = array('row' => $realRow, 'level' => -1, 'smartLevel' => "-1", 'last' => $realRow);
            } else {
                $this->levelIndex['null']['last'] = $this->levelIndex['null']['row'];
                $this->levelIndex['null']['row'] = $realRow;
            }
            $this->var_error_log(sprintf("%s\t\t[%s] row[%d] index[%s][row => %d, level => %d, smartLevel => %s, last => %d]\n", $msg, $action, $this->levelIndex['null']['row'], "null", $this->levelIndex['null']['row'], $this->levelIndex['null']['level'], $this->levelIndex['null']['smartLevel'], $this->levelIndex['null']['last']));
            return -1;
        }
        $key = $item->getIdentifier();
        $parent = $this->getParent($item, $row, $column);
        $parentKey = $parent->getIdentifier();
        $this->var_error_log(sprintf("%s\t\t[%s] row[%d] found parent %s for item %s", $msg, $action, $realRow, $parentKey, $key));
        $this->levelIndex[$parentKey]['last'] = $realRow;
        $smartLevel = sprintf("%s.1", $this->levelIndex[$parentKey]['smartLevel']);
        $this->levelIndex[$key] = array('row' => $realRow, 'level' => $this->rootLevel, 'smartLevel' => $smartLevel, 'last' => $realRow);
        $this->var_error_log(sprintf("%s\t\t[P] row[%d] PARENT[%s][row => %d, level => %d, smartLevel => %s, last => %d]\n", $msg, $this->levelIndex[$key]['row'], $parentKey, $this->levelIndex[$parentKey]['row'], $this->levelIndex[$parentKey]['level'], $this->levelIndex[$parentKey]['smartLevel'], $this->levelIndex[$parentKey]['last']));
        $this->var_error_log(sprintf("%s\t\t[%s] row[%d] index[%s][row => %d, level => %d, smartLevel => %s, last => %d]\n", $msg, $action, $this->levelIndex[$key]['row'], $key, $this->levelIndex[$key]['row'], $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel'], $this->levelIndex[$key]['last']));
        return 1;
    }

    private function updateIndex(?LsItem $item, int $row, int $column): ?int {
        $msg = "SpineImport::updateIndex()";
        $realRow  = (($row * $this->rowOffset)+$column);
        $action = "L";
        if (null === $item) {
            $key = "null";
        } else {
            $key = $item->getIdentifier();
        }
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] lookup item %s in the INDEX", $msg, $action, $realRow, $key));
        $match = $this->levelIndex[$key];
        if (false === boolVal($match)) {
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] no match for item %s", $msg, $action, $realRow, $key));
            return null;
        }
        if (array_key_exists('level', $this->levelIndex[$key]) && array_key_exists('smartLevel', $this->levelIndex[$key]) && array_key_exists('last', $this->levelIndex[$key])) {
            $action = "M";
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] matched item %s: at level %d, smartLevel %s, last %d", $msg, $action, $realRow, $key,
                $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel'], $this->levelIndex[$key]['last']));
            $this->levelIndex[$key]['row'] = $realRow;
            $action = "U";
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] updated last occurrence of item [%s][row => %s, level => %d, smartLevel => %s, last => %d]\n", $msg, $action, $realRow, $key,
            $this->levelIndex[$key]['row'], $this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel'], $this->levelIndex[$key]['last']));
            return $this->levelIndex[$key]['level'];
        }
        return null;
    }

    private function getParent(?LsItem $item, int $row, int $column): ?LsItem {
        $msg = "SpineImport::getParent()";
        $realRow  = (($row * $this->rowOffset)+$column);
        $priorRow = $realRow - 1;
        $key = "";
        $action = "L";
        $parent = null;
        if (0 === $column ) {
            return null;
        }
        if (null === $item) {
            $key = "null";
        } else {
            $key = $item->getIdentifier();
        }
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] find parent for %s", $msg, $action, $realRow, $key));
        while ($priorRow >= $realRow-$column) {
            $parent =  $this->levels[$priorRow]['item'];
            if (null === $parent) {
                $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] ineligible null predecessor at row[%d] for item %s", $msg, $action, $realRow, $priorRow--, $key));
                continue;
            }
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] found parent %s for item %s at row[%d, %d]", $msg, $action, $realRow, $parent->getIdentifier(), $key, $priorRow, $column));
            return $parent;
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
            $key = "null";
        } else {
            $key = $item->getIdentifier();
        }
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] adding item %s to INDEX", $msg, $action, $realRow, $key));
        switch ($column) {
            case 0: {
            // In column 0, there is no true parent
            // Therefore no need to update the reference to the last child in the level index when processing these items
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] root level %d, while looking up item %s", $msg, $action, $realRow, $this->rootLevel, $key));
            $predecessor = $this->levels[($this->rootLevel-1)]['item'];
            if (null === $predecessor) {
                throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d, %d] no predecessor found for item %s", $msg, $action, $realRow, $column, $key));
            }
            $predecessorKey = $predecessor->getIdentifier();
            $parentKey = $predecessorKey;
            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] PARENT[%s][row => %d, level => %d, smartLevel => %s, last => %d]\n",
                    $msg, $action, $realRow, $parentKey, $this->levelIndex[$parentKey]['row'], $this->levelIndex[$parentKey]['level'],
                    $this->levelIndex[$parentKey]['smartLevel'], $this->levelIndex[$parentKey]['last']));
            $level = ++$this->rootLevel;
            $smartLevel = sprintf("%d", $level);
            break;
            }
            default: {
                $parent = $this->getParent($item, $row, $column);
                if (null === $parent) {
                    throw new RuntimeException(sprintf("%s\t\t\t]%s] row[%d,%d] no parent for item %s", $msg, $action, $realRow, $column, $key));
                }
                $parentKey = $parent->getIdentifier();
                $match = $this->levelIndex[$parentKey];
                if (true === boolVal($match) && array_key_exists('last', $match)) {
                    $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] found parent %s for item %s", $msg, $action, $realRow, $parentKey, $key));
                    $predecessorRow = $this->levelIndex[$parentKey]['last'];
                    $predecessor = $this->levels[$predecessorRow]['item'];
                    $predecessorColumn = $this->levels[$predecessorRow]['column'];
                    $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d, %d] found predecessor %s for item %s last updated at row %d, column %d",
                        $msg, $action, $realRow, $column, $parent->getAbbreviatedStatement(), $item->getAbbreviatedStatement(), $predecessorRow, $predecessorColumn));
                        switch (true) {
                        case ( $column - $predecessorColumn) > 0:  // predecessor to the left
                            if ( $column === 5 ) {  // Boundary condition.  No item is to the right of column 5. DO NOT start another level
                                $predecessorKey = $predecessor->getIdentifier();
                                $level = 1 + $this->levelIndex[$predecessorKey]['level'];
                            } else {
                                $level = 1;
                            }
                            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] %s to the left of current item (column [%d] - predecessor column[%d] = %d).  level %d", 
                                $msg, $action, $realRow, $predecessor->getAbbreviatedStatement(), $column, $predecessorColumn, ($column - $predecessorColumn), $level));
                            break;
                        case ( $column - $predecessorColumn) == 0: // predecessor directly above
                            $predecessorKey = $predecessor->getIdentifier();
                            $level = 1 + $this->levelIndex[$predecessorKey]['level'];
                            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] %s above current item (column [%d] - predecessor column[%d] = %d).  level %d", 
                                $msg, $action, $realRow, $predecessor->getAbbreviatedStatement(),$column, $predecessorColumn, ($column - $predecessorColumn), $level));
                            break;
                        break;
                        case ($column - $predecessorColumn ) < 0: // predecessor to the right
                            $predecessorKey = $predecessor->getIdentifier();
                            $level = 1 + $this->levelIndex[$predecessorKey]['level'];
                            $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] %s to the right of current item (column [%d] - predecessor column[%d] = %d).  level %d", 
                                $msg, $action, $realRow, $predecessor->getAbbreviatedStatement(), $column, $predecessorColumn, ($column - $predecessorColumn), $level));
                            break;  
                    }
                }
                if (false === array_key_exists('smartLevel', $match)) {
                    throw new RuntimeException(sprintf("%s\t\t\t[%s] row[%d] no smart level found for item parent %s", $msg, $action, $row, $parentKey));
                }
                $smartLevel = sprintf("%s.%d", $this->levelIndex[$parentKey]['smartLevel'], $level);
                $this->levelIndex[$parentKey]['last'] = $realRow;
            }
        }
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] set level to %d for item %s", $msg, $action, $realRow, $level, $key));
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] set smartLevel to %s for item %s", $msg, $action, $realRow, $smartLevel,  $key));
        $this->levelIndex[$key] = array('row' => $realRow, 'level' => $level, 'smartLevel' => $smartLevel, 'last' => $realRow);
        $this->var_error_log(sprintf("%s\t\t\t[P] row[%d] PARENT[%s][row => %d, level => %d, smartLevel => %s, last => %d]\n",
        $msg, $this->levelIndex[$key]['row'], $parentKey, $this->levelIndex[$parentKey]['row'],
        $this->levelIndex[$parentKey]['level'], $this->levelIndex[$parentKey]['smartLevel'], $this->levelIndex[$parentKey]['last']));
        $this->var_error_log(sprintf("%s\t\t\t[%s] row[%d] index[%s][row => %s, level => %d, smartLevel => %s, last => %d]\n", $msg, $action, $realRow, $key,
            $this->levelIndex[$key]['row'],$this->levelIndex[$key]['level'], $this->levelIndex[$key]['smartLevel'], $this->levelIndex[$key]['last']));
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
                $key = "null";
            } else {
                $action = "N";
                $key = $item->getIdentifier();
            }
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => $level);
            $this->levels[$realRow] = $rowValue;
            $msg = sprintf("%s\t[%s] row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
                $msg, $action, $realRow, $this->levels[$realRow]['code'], $this->levels[$realRow]['column'],
                $this->levels[$realRow]['level'], $key);
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
        $this->levels[$realRow]['level'], $this->levels[$realRow]['item']->getIdentifier() /*getAbbreviatedStatement()*/));
            return;
        } else {
            // Remaining items are repeats.  
            $action = "R";
            if (null === $item) {
                $key = "null";
            } else {
                $key = $item->getIdentifier();
            }
            $rowValue = array('code' => $skillCode, 'item' => $item, 'column' => $column, 'level' => $repeated);
            $this->levels[$realRow] = $rowValue;
            $msg = sprintf("%s\t[%s] row[%d] array(code => %s, column => %d, level => %d, item => %s)\n",
                $msg, $action, $realRow, $this->levels[$realRow]['code'], $this->levels[$realRow]['column'],
        $this->levels[$realRow]['level'], $key /* $statement*/);
            $this->var_error_log($msg);
            return;
        }
    }

    public function importSpine(string $path): LsDoc
    {
        set_time_limit(180); // increase time limit for large files
        ini_set('memory_limit','4G');  // Learning Spines use 1k * rows * 830+ columns  
        $msg = "SpineImport::importSpine()";
        $phpExcelObject = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        if (null === $phpExcelObject) {
            throw new \RuntimeException('Cannot load spine from file'.$path);
        }
        $sheet = $phpExcelObject->getSheetByName('Spine_Template');
        if (null === $sheet) {
            throw new \RuntimeException('This workbook does not container a Learinng Spine.');
        }
        $this->var_error_log(sprintf("%s", $msg));
        $doc = $this->saveDocument($sheet);
        return $doc;
    }

    public function importSkills(string $path): LsDoc
    {
        set_time_limit(180); // increase time limit for large files
        ini_set('memory_limit','4G');  // Learning Spines use 1k * rows * 830+ columns  
        $msg = "SpineImport::importSkills";

        $phpExcelObject = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        if (null === $phpExcelObject) {
            throw new \RuntimeException('Cannot load spine from file'.$path);
        }
        /** @var LsItem[] $items */
        $items = [];
        $itemSmartLevels = [];
        $children = [];
        $levels = [];
        /** @var LsItem[] $smartLevels */
        $smartLevels = [];
        $sheet = $phpExcelObject->getSheetByName('Spine_Template');
        if (null === $sheet) {
            throw new \RuntimeException('This workbook does not container a Learinng Spine.');
        }
        $this->var_error_log(sprintf("%s", $msg));
        $doc = $this->saveDocument($sheet);
        $children[$doc->getIdentifier()] = $doc->getIdentifier();
        $lastRow = $sheet->getHighestRow();

        // Create domain, cluster, strand, sub-strand, sub-strand-2 hierarchy items and skills
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
//                $key = $item->getAbbreviatedStatement();
//                $itemIdentifier = $item->getIdentifier();
//                if (null === $this->hierarchyItemIdentifiers[$key]) {
//                    $this->hierarchyItemIdentifiers[$key] = array('identifier' => $itemIdentifier, 'column' => $column);
//                }
                if (array_key_exists('smartLevel', $this->levelIndex[$item->getIdentifier()])) {
                    $smartLevel = $this->levelIndex[$item->getIdentifier()]['smartLevel'];
                    $items[$item->getIdentifier()] = $item;
                    $smartLevels[$smartLevel] = $item;
                    $itemSmartLevels[$item->getIdentifier()] = $smartLevel;
                }
            }
            $item = $this->saveSkill($sheet, $doc, $row);
            $this->setHierarchyLevel($rowLevel, $hierarchyLevel++, $skillCode, $item);
            if (null === $item) {
                continue;
            }
//            $key = $item->getAbbreviatedStatement();
//            $itemIdentifier = $item->getIdentifier();
//            if (null === $this->hierarchyItemIdentifiers[$key]) {
//                $this->hierarchyItemIdentifiers[$key] = array('identifier' => $itemIdentifier, 'column' => $column);
//            }
            if (array_key_exists('smartLevel', $this->levelIndex[$item->getIdentifier()])) {
                $smartLevel = $this->levelIndex[$item->getIdentifier()]['smartLevel'];
                $items[$item->getIdentifier()] = $item;
                $smartLevels[$smartLevel] = $item;
                $itemSmartLevels[$item->getIdentifier()] = $smartLevel;
            }
        }
                
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
        return $doc;
    }

    public function importAssociations(string $path): LsDoc
    {
        set_time_limit(180); // increase time limit for large files
        ini_set('memory_limit','4G');  // Learning Spines use 1k * rows * 830+ columns  
        $msg = "SpineImport::importAssociations";
        $phpExcelObject = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        if (null === $phpExcelObject) {
            throw new \RuntimeException('Cannot load spine from file'.$path);
        }
        $sheet = $phpExcelObject->getSheetByName('Spine_Template');
        if (null === $sheet) {
            throw new \RuntimeException('This workbook does not container a Learinng Spine.');
        }
        $this->var_error_log(sprintf("%s", $msg));
        $doc = $this->saveDocument($sheet);
        $items[$doc->getIdentifier()] = $doc->getLsItems();
        $children[$doc->getIdentifier()] = $doc->getIdentifier();
        $lastRow = $sheet->getHighestRow();
//        for ($i = 7; $i <= $lastRow; ++$i) {
//            $assoc = $this->saveAssociation($sheet, $doc, $i, $items, $children);
//            if (null !== $assoc) {
//                $associationsIdentifiers[$assoc->getIdentifier()] = null;
//            }
//        }
//                $this->checkRemovedItems($doc, $items);
//                $this->checkRemovedAssociations($doc, $associationsIdentifiers);
        return $doc;
    }

    private function saveDocument(Worksheet $sheet): LsDoc
    {
        $msg = "SpineImport::saveDocument()";
        $fieldNames = [
            'title'         => 1,
            'subject'       => 2,
            'identifier'    => 3,
            'version'       => 4,
        ];

        $id = $this->getCellValueOrNull($sheet, 2, $fieldNames['identifier']);
        if (!empty($id)) {
            $id = strtolower($id);
        } else {
            throw new \InvalidArgumentException(sprintf("%s, The identifier, %s, must not be an empty string.", $msg, $id));
        }
        if (is_string($id) ) {
            if ( Uuid::isValid($id) ) {
                $id = Uuid::fromString($id)->toString();
            } else {
                throw new \InvalidArgumentException(sprintf("%s, The identifier, %s, must be a valid string representation of a UUID.", $msg, $id));
            }
        } else {
            throw new \InvalidArgumentException(sprintf("%s, The identifier, %s, must be a string.", $msg, $id));
        }

        $this->var_error_log(sprintf("%s\t\t[S] Initializing Document %s", $msg, $id));
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
        $doc->setTitle($this->getCellValueOrNull($sheet, 2, $fieldNames['title']));
        $doc->setDescription($this->getCellValueOrNull($sheet, 2, $fieldNames['title']));
        $subject = sprintf("%s", $this->getCellValueOrNull($sheet, 2, $fieldNames['subject']));
        if (empty($subject)) {
            $subject = sprintf("Missing Subject");
        }
        if ( !is_string($subject) ) {
            $subject = sprintf("%s", $subject);
        }
        $this->var_error_log(sprintf("%s\t\t[S] Initializing Document subject to %s", $msg, $subject));
        $doc->setSubject($this->getCellValueOrNull($sheet, 2, $fieldNames['subject']));
        $doc->setVersion($this->getCellValueOrNull($sheet, 2, $fieldNames['version']));
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

    private function saveHierarchyItem(Worksheet $sheet, lsDoc $doc, int $row, int $column): ?LsItem
    {
        $msg = sprintf("SpineImport::saveHierarchyItem() ");
        $realRow = ($this->rowOffset*($row-7))+($column-1);
        /** @var LsItem[] $items */
        $item = null;
        $identifier = null;
        $itemTypeTitle = $this->getCellValueOrNull($sheet, $column, 6);
        $statement = $this->getCellValueOrNull($sheet, $column, $row);
        $this->var_error_log(sprintf("%s\t[L] row[%d] column[%d] lookup hierarchy item [%s]", $msg, $realRow, $column, $statement));
        $identifier = $this->hierarchyItemIdentifiers[$statement]['identifier'];
        $itemType = $this->hierarchyItemIdentifiers[$statement]['column'];
        if (null === $statement) {
            return null;
        }
        if ( !empty($identifier) && Uuid::isValid($identifier) && $column === $itemType) {
            $item = $this->getEntityManager()->getRepository(LsItem::class)
                ->findOneBy(['identifier' => $identifier, 'lsDocIdentifier' => $doc->getIdentifier()]);
            if ( $item !== null) {
                $itemStatement=$item->getFullStatement();
                if ($itemStatement !== $statement ) {
                    $msg = sprintf("%s %s !== %s", $msg, $statement, $itemStatement);
                    throw new \RuntimeException($msg);
                }
                $this->var_error_log(sprintf("%s\t[L] row[%d] column[%d] found hierarchy item [%s: %s]", $msg, $realRow, $column, $item->getAbbreviatedStatement(), $item->getIdentifier()));
                return $item;
            }
        }
        $item = $doc->createItem();
        if (null !== $statement) {
            $item->setFullStatement($statement);
            $item->setAbbreviatedStatement($statement);
        }
        $item->setLanguage("En");
        $itemType = $this->findItemType($itemTypeTitle);
        $itemTypeTitle = $this->getCellValueOrNull($sheet, $column, 6);
        if (null !== $itemTypeTitle) {
            $item->setItemType($itemType);
        }
        $this->var_error_log(sprintf("%s\t[S] row[%d] column[%d] Add hierarchy item [%s: %s]", $msg, $realRow, $column, $item->getIdentifier(), $item->getAbbreviatedStatement()));
        $this->hierarchyItemIdentifiers[$statement] = array('identifier' => $item->getIdentifier(), 'column' => $column);
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
        return $item;
    }

    private function saveSkill(Worksheet $sheet, LsDoc $doc, int $row): ?LsItem
    {
        $msg = "SpineImport::saveSkill()";
        $realRow = ($this->rowOffset*($row-7))+5;
        $item = null;
        $itemTypeTitle = "Skill";
        $skillTitle = $this->getCellValueOrNull($sheet, 6, $row);
        $skillDescription = $this->getCellValueOrNull($sheet, 7, $row);
        $skillGuid = $this->getCellValueOrNull($sheet, 8, $row);
        $skillCode = $this->getCellValueOrNull($sheet, 9, $row);
        $skillLowerGrade = $this->getCellValueOrNull($sheet, 10, $row);
        $skilUpperGrade = $this->getCellValueOrNull($sheet, 11, $row);
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

        $this->var_error_log(sprintf("%s\t\t\t[S] row[%d] skill: [%s: %s]", $msg, $realRow, $item->getIdentifier(), $item->getAbbreviatedStatement()));
        $this->addAdditionalFields($row, $item, $sheet);
        $this->hierarchyItemIdentifiers[$item->getAbbreviatedStatement()] = array('identifier' => $item->getIdentifier(), 'column' => 6);

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

        return sprintf("%s", $cell->getValue());
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
        $columns = array(12, 13);

        foreach ($columns as $column) {
            $fieldName = strtolower(preg_replace('/\s+/', '_', $this->getCellValueOrNull($sheet, $column, $this->rowOffset)));
            if ( !empty($fieldName) && in_array($fieldName, self::$itemCustomFields, true) ) {
                $value = $this->getCellValueOrNull($sheet, $column, $row);
                if ( !empty($value)) {
                    $this->var_error_log(sprintf("SpineImport::addAdditionalFields()\t[A] row[%d] column[%d] custom field %s = %s", ($row - 2), $column, $fieldName, $value));
                    $item->setAdditionalField($fieldName, $value);
                }
            }
        }
    }

    private function setEducationalAlignment(LsItem $item, ?string $passedGradeString): void
    {
        $item->setEducationalAlignment(EducationLevelSet::fromString($passedGradeString)->toString());
    }
}