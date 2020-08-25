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
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Null_;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls\Worksheet as XlsWorksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet as XlsxWorksheet;
use PhpParser\Node\Expr\Cast\String_;
use Proxies\__CG__\App\Entity\Framework\LsDefAssociationGrouping as FrameworkLsDefAssociationGrouping;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Psr\Log;
use Ramsey\Uuid\Rfc4122\UuidV4;

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
    private $documents = [];
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
        $this->documents = [
            'alabama'           => array('ela'  =>  Uuid::fromString('c64926b7-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6492c21-d7cb-11e8-824f-0242ac160002')),
            'alaska'            => array('ela'  =>  Uuid::fromString('c64930d4-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649e0a2-d7cb-11e8-824f-0242ac160002')),
            'arizona'           => array('ela'  =>  Uuid::fromString('c664d506-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6498415-d7cb-11e8-824f-0242ac160002')),
            'arkansas'          => array('ela'  =>  Uuid::fromString('c664b830-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649def2-d7cb-11e8-824f-0242ac160002')),
            'california'        => array('ela'  =>  Uuid::fromString('c6486d6e-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6487102-d7cb-11e8-824f-0242ac160002')),
            'colorado'          => array('ela'  =>  Uuid::fromString('c664a5c0-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649e65b-d7cb-11e8-824f-0242ac160002')),
            'connecticut'       => array('ela'  =>  Uuid::fromString('c6487f78-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6487bda-d7cb-11e8-824f-0242ac160002')),
            'delaware'          => array('ela'  =>  Uuid::fromString('c6488303-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648867e-d7cb-11e8-824f-0242ac160002')),
            'florida'           => array('ela'  =>  Uuid::fromString('c649a48e-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6399b4d-d7cb-11e8-824f-0242ac160002')),
            'georgia'           => array('ela'  =>  Uuid::fromString('355bdb74-46f9-11e7-9dd8-56d474a21250'),   'math'  =>  Uuid::fromString('23a8e45a-9d5a-11e7-81bc-064e21a83c7c')),
            'hawaii'            => array('ela'  =>  Uuid::fromString('c608ad09-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c608afbe-d7cb-11e8-824f-0242ac160002')),
            'hmh'               => array('ela'  =>  Uuid::fromString('63c2b7d0-cd69-4e8a-9761-c90623104b8c'),   'math'  =>  Uuid::fromString('01b3eaba-1353-4f4b-9833-f60c393230bc')),
            'idaho'             => array('ela'  =>  Uuid::fromString('c6488a01-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6488e0d-d7cb-11e8-824f-0242ac160002')),
            'illinois'          => array('ela'  =>  Uuid::fromString('c64891a3-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6489520-d7cb-11e8-824f-0242ac160002')),
            'indiana'           => array('ela'  =>  Uuid::fromString('c639d0e1-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c639d471-d7cb-11e8-824f-0242ac160002')),
            'iowa'              => array('ela'  =>  Uuid::fromString('c64898a5-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6489c22-d7cb-11e8-824f-0242ac160002')),
            'kansas'            => array('ela'  =>  Uuid::fromString('c6652039-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649d99c-d7cb-11e8-824f-0242ac160002')),
            'kentucky'          => array('ela'  =>  Uuid::fromString('15efb167-eb8f-11e9-9f9f-0242ac140002'),   'math'  =>  Uuid::fromString('15efb3f5-eb8f-11e9-9f9f-0242ac140002')),
            'louisiana'         => array('ela'  =>  Uuid::fromString('c6493f4d-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c64942d8-d7cb-11e8-824f-0242ac160002')),
            'maine'             => array('ela'  =>  Uuid::fromString('c648a8bf-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648ad45-d7cb-11e8-824f-0242ac160002')),
            'maryland'          => array('ela'  =>  Uuid::fromString('c648b1d1-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648b6d8-d7cb-11e8-824f-0242ac160002')),
            'massachusetts'     => array('ela'  =>  Uuid::fromString('c607e8f1-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c607d627-d7cb-11e8-824f-0242ac160002')),
            'michigan'          => array('ela'  =>  Uuid::fromString('c648bb7e-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648c128-d7cb-11e8-824f-0242ac160002')),
            'minnesota'         => array('ela'  =>  Uuid::fromString('c66529b3-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63aa11e-d7cb-11e8-824f-0242ac160002')),
            'mississippi'       => array('ela'  =>  Uuid::fromString('c664c0fa-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6647f96-d7cb-11e8-824f-0242ac160002')),
            'missouri'          => array('ela'  =>  Uuid::fromString('c63aabba-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63aaddb-d7cb-11e8-824f-0242ac160002')),
            'montana'           => array('ela'  =>  Uuid::fromString('c648c5d7-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648ca60-d7cb-11e8-824f-0242ac160002')),
            'national'          => array('ela'  =>  Uuid::fromString('c64961be-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6496676-d7cb-11e8-824f-0242ac160002')),
            'nebraska'          => array('ela'  =>  Uuid::fromString('c63ac8da-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63ac74f-d7cb-11e8-824f-0242ac160002')),
            'nevada'            => array('ela'  =>  Uuid::fromString('c648ce31-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648d1c5-d7cb-11e8-824f-0242ac160002')),
            'new_hampshire'     => array('ela'  =>  Uuid::fromString('c648d542-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648d8ba-d7cb-11e8-824f-0242ac160002')),
            'new_jersey'        => array('ela'  =>  Uuid::fromString('c6485eaa-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6486244-d7cb-11e8-824f-0242ac160002')),
            'new_mexico'        => array('ela'  =>  Uuid::fromString('c648dcb9-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648e049-d7cb-11e8-824f-0242ac160002')),
            'new_york'          => array('ela'  =>  Uuid::fromString('c649cfd7-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649d172-d7cb-11e8-824f-0242ac160002')),
            'north_carolina'    => array('ela'  =>  Uuid::fromString('c649d674-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649d809-d7cb-11e8-824f-0242ac160002')),
            'north_dakota'      => array('ela'  =>  Uuid::fromString('c63b0002-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63b0631-d7cb-11e8-824f-0242ac160002')),
            'ohio'              => array('ela'  =>  Uuid::fromString('c648e3cc-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63b33bf-d7cb-11e8-824f-0242ac160002')),
            'oklahoma'          => array('ela'  =>  Uuid::fromString('c63b4f24-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63b48a8-d7cb-11e8-824f-0242ac160002')),
            'oregon'            => array('ela'  =>  Uuid::fromString('c648eb12-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648ef39-d7cb-11e8-824f-0242ac160002')),
            'pennsylvania'      => array('ela'  =>  Uuid::fromString('c638bb1c-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c627f1db-d7cb-11e8-824f-0242ac160002')),
            'rhode_island'      => array('ela'  =>  Uuid::fromString('c648f2bf-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648f635-d7cb-11e8-824f-0242ac160002')),
            'south_carolina'    => array('ela'  =>  Uuid::fromString('c63b24cf-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63b2343-d7cb-11e8-824f-0242ac160002')),
            'south_dakota'      => array('ela'  =>  Uuid::fromString('c648f9b3-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c648fd31-d7cb-11e8-824f-0242ac160002')),
            'tennessee'         => array('ela'  =>  Uuid::fromString('c607fa0c-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649db2b-d7cb-11e8-824f-0242ac160002')),
            'texas'             => array('ela'  =>  Uuid::fromString('c22d9405-c1f7-51e6-9883-b3c807e67e6c'),   'math'  =>  Uuid::fromString('bc997e24-7f3b-5df0-a0cd-3a8ac9cf0e2e')),
            'utah'              => array('ela'  =>  Uuid::fromString('c6494d63-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c63bc986-d7cb-11e8-824f-0242ac160002')),
            'vermont'           => array('ela'  =>  Uuid::fromString('c64900aa-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c64906b8-d7cb-11e8-824f-0242ac160002')),
            'virginia'          => array('ela'  =>  Uuid::fromString('c6083284-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c608bd3a-d7cb-11e8-824f-0242ac160002')),
            'washington'        => array('ela'  =>  Uuid::fromString('c6490c75-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6491155-d7cb-11e8-824f-0242ac160002')),
            'west_virginia'     => array('ela'  =>  Uuid::fromString('c647fb93-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6648c2e-d7cb-11e8-824f-0242ac160002')),
            'wisconsin'         => array('ela'  =>  Uuid::fromString('c6491502-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c6491886-d7cb-11e8-824f-0242ac160002')),
            'wyoming'           => array('ela'  =>  Uuid::fromString('c6491c0d-d7cb-11e8-824f-0242ac160002'),   'math'  =>  Uuid::fromString('c649757a-d7cb-11e8-824f-0242ac160002')),
            ];


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
        $this->var_error_log(sprintf("%s\t\t[I] import associations for %s", $msg, $doc->getTitle()));
        $items[$doc->getIdentifier()] = $doc->getLsItems();
        $children[$doc->getIdentifier()] = $doc->getIdentifier();
        $lastRow = $sheet->getHighestRow();
        $lastAssociationColumn = 27; // 855;
        $skill['skill GUID'] = 8;
        $itemRepo = $this->getEntityManager()->getRepository(LsItem::class);
        for ($column = 14; $column <= $lastAssociationColumn; ++$column) {
            // Get the column header
            $heading = $this->getCellValueOrNull($sheet, $column, $this->rowOffset);
            if (empty($heading)) {
                throw new \RuntimeException(sprintf("%s\t\t[L] empty heading at position[%d, %d]", $msg, $this->rowOffset, $column));
            }
            // Skip these columns for now
            if (1 == preg_match("/^([Oo]ther|[rR][lL]*|[Pp]re[rR]eq*)/", $heading) ) {
                $this->var_error_log(sprintf("%s\t\t[L] column[%d] skipping %s", $msg, $column, $heading));
                continue;
            }
            $this->var_error_log(sprintf("%s\t\t[L] row[%d,%d] processing heading %s", $msg, $this->rowOffset, $column, $heading));
            // lookup the associated document based on the heading
            $associatedDoc = $this->getAssociatedDocument($doc, $heading, $this->rowOffset, $column);
            $this->var_error_log(sprintf("%s\t\t[M] found associated document %s", $msg, $associatedDoc->getTitle()));
            for ($row = 7; $row <= $lastRow; ++$row) {
                $skillGuid = $this->getCellValueOrNull($sheet, $skill['skill GUID'], $row);
                $item = $itemRepo->findOneBy(['identifier' => $skillGuid, 'lsDocIdentifier' => $doc->getIdentifier()]);
                if (null === $item) {
                    throw new \RuntimeException(sprintf("%s\t\t[L] row[%d, %d] Missing item %s for document %s", $msg, $row, $column, $skillGuid, $doc->getTitle()));
                }
//                $this->var_error_log(sprintf("%s\t\t[I] row[%d, %d] import associations for %s [%s]", $msg, $row, $column, $item->getIdentifier(), $item->getAbbreviatedStatement()));
                $associatedItems = $this->getAssociatedItems($sheet, $row, $column, $associatedDoc);
                if ($associatedItems) {
                    foreach ($associatedItems as $associatedItem) {
                        $this->var_error_log(sprintf("%s\t\t[L] row[%d,%d] looking up associations for %s[%s]", $msg, $row, $column, $associatedItem->getIdentifier(), $associatedItem->getFullStatement()));
                        $this->getAssociation($item, $associatedItem, $doc, $associatedDoc, $heading, $row, $column);
                    }
                }
            }
        }
        return $doc;
    }

    private function getAssociation(LsItem $item, LsItem $associatedItem, LsDoc $doc, LsDoc $associatedDoc, string $heading, int $row, int $column): LsAssociation
    {
        $msg = "SpineImport::getAssociation()";
        $this->var_error_log(sprintf("%s\t\t\t[L] row[%d, %d] looking up %s associations between %s[%s] and %s[%s]", $msg, $row, $column, LsAssociation::EXACT_MATCH_OF,
            $item->getIdentifier(), $item->getAbbreviatedStatement(), $associatedItem->getIdentifier(), $associatedItem->getAbbreviatedStatement()));
        $associationRepo = $this->getEntityManager()->getRepository(LsAssociation::class);
        if (null === $associationRepo) {
            throw new \RuntimeException(sprintf("%s\t\t\t[L] row[%d, %d] could not find an association repo while looking up associations for %s[%s]", $msg, $row, $colun, $item->getIdentifier(), $item->getAbbreviatedStatement()));
        }
        $association = $associationRepo->findOneBy([
            'originNodeIdentifier' => str_replace('_', '', $item->getIdentifier()),
            'type' => LsAssociation::EXACT_MATCH_OF,
            'destinationNodeIdentifier' => str_replace('_', '', $associatedItem->getIdentifier()),
        ]);
        if ( null === $association) {
            $this->var_error_log(sprintf("%s\t\t\t[N] [%d, %d] creating an exact-match association from %s[%s] to %s[%s]", $msg, $row, $column, $item->getIdentifier(), $item->getAbbreviatedStatement(), $associatedItem->getIdentifier(), $associatedItem->getAbbreviatedStatement()));
            $association = $doc->createAssociation();
            $association->setOrigin($item);
            $association->setDestination($associatedItem);
            $association->setType(LsAssociation::EXACT_MATCH_OF);
            $this->var_error_log(sprintf("%s\t\t\t[N] [%d, %d] Persisting association of type %s from %s[%s] to %s[%s]", $msg, $row, $column,
                'LsAssociation::EXACT_MATCH_OF',
                $association->getOriginLsItem()->getIdentifier(), $association->getOriginLsItem()->getFullStatement(),
                $association->getDestinationLsItem()->getIdentifier(), $association->getDestinationLsItem()->getFullStatement()));
            $this->getEntityManager()->persist($association);
        }
        $associatedGroup = $this->hasAssociatedGroup($association, $doc, $heading, $row, $column);
        if ( null === $associatedGroup) {
            $associatedGroup = new LsDefAssociationGrouping();
            $associatedGroup->setTitle($heading);
            $associatedGroup->setLsDoc($doc);
            $this->getEntityManager()->persist($associatedGroup);
            $this->var_error_log(sprintf("%s\t\t\t[N] row[%d, %d] Created association group %s", $msg, $this->rowOffset, $column, $associatedGroup->getTitle()));
            $doc->addAssociationGrouping($associatedGroup);
            $this->var_error_log(sprintf("%s\t\t\t[N] row[%d, %d] Addeded association group %s[%s] to document %s[%s]", $msg, $row, $column, $associatedGroup->getIdentifier(), $associatedGroup->getTitle(), $doc->getIdentifier(), $doc->getTitle()));
        }
        $association->setGroup($associatedGroup);
        return $association;
    }

    private function hasAssociatedGroup(LsAssociation $association, LsDoc $doc, string $heading, int $row, int $column): ?LsDefAssociationGrouping
    {
        $msg = "SpineImport::hasAssociatedGroup()";
        $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] looking up association group %s", $msg, $row, $column, $heading));
        $associatedGroups = $doc->getAssociationGroupings();
        if (null === $associatedGroups) {
            $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] no groups found for association %s referenceing %s[%s]", $msg, $row, $column, $association->getIdentifier(), $association->getOriginLsItem()->getIdentifier(), $association->getOriginLsItem()->getAbbreviatedStatement()));
            return null;

        }
        if ( 0 === count($associatedGroups)) {
            $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] no group for title %s", $msg, $row, $column, $heading));
            return null;
        }
        foreach($associatedGroups as $associatedGroup) {
            if ( null === $associatedGroup) {
                $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] found null group", $msg, $row, $column));
                continue;
            }
            $associatedGroupTitle = $associatedGroup->getTitle();
            if ( empty($associatedGroupTitle) ) {
                $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] found group %s with no title", $msg, $row, $column, $associatedGroup->getIdentifier()));
                continue;
            }
            $associatedGroupTitle = sprintf("%s", $associatedGroupTitle);
            $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] found %s", $msg, $row, $column, $associatedGroupTitle));
                if ( 0 == strcmp($heading, $associatedGroupTitle) ) {
                    $this->var_error_log(sprintf("%s\t\t[] row[%d, %d] matched  association group %s[%s]", $msg, $row, $column, $associatedGroup->getIdentifier(), $associatedGroupTitle));
                    return $associatedGroup;
                }
        }
        return null;
    }

    private function getAssociatedDocument(LsDoc $doc, string $heading, int $row, int $column): ?LsDoc
    {
        $msg = "SpineImport::getAssociatedDocument()";
        $headings = preg_split("/[\s,]+/", strtolower($heading));
        $docRef = $headings[0];
        if (empty($docRef)) {
            throw new \RuntimeException(sprintf("%s\t[L] bad heading format %s", $msg, $heading));
        }
        if ($docRef == "new" || $docRef == "north" || $docRef == "south" || $docRef == "west" || $docRef == "rhode") {
            $docRef = sprintf("%s_%s", $docRef, $headings[1]);
        }
        $hmhRef = $this->documents['hmh'];
        $this->var_error_log(sprintf("%s\t[L] row[%d, %d] Looking up associated documents for %s and %s", $msg, $row, $column, $hmhRef['ela'], $hmhRef['math']));
        if (null === $hmhRef) {
            throw new \RuntimeException(sprintf("%s\t[L] cannot find document identifiers for HMH", $msg));
        }
        $associatedDocIdentifier = $this->documents[$docRef];
        if (null === $associatedDocIdentifier) {
            throw new \RuntimeException(sprintf("%s\t[L] cannot find document identifier for %s", $msg, $docRef));
        }
        $hmhDocId = $doc->getIdentifier();
        switch (true) {
            case ($hmhDocId == $hmhRef['ela']):
                $associatedDocIdentifier = $associatedDocIdentifier['ela'];
                break;
            case ($hmhDocId == $hmhRef['math']):
                $associatedDocIdentifier = $associatedDocIdentifier['math'];
                break;
            default:
                throw new \RuntimeException(sprintf("%s\t[L] %s does not match an HMH learning spine", $msg, $doc->getIdentifier()));
        }
        $associatedDoc = $this->getEntityManager()->getRepository(LsDoc::class)->findOneByIdentifier($associatedDocIdentifier);
        if (null === $associatedDoc) {
            throw new \RuntimeException(sprintf("%s\t[L] no document found for identifier %s", $msg, $associatedDocIdentifier));
        }
        return $associatedDoc;
    }

    private function getAssociatedItems($sheet, $row, $column, LsDoc $associatedDoc): ?array
    {
        $msg = "SpineImport::getAssociatedItems()";
        $associatedItems = [];
        $levels = [];
        $labels = $this->getCellValueOrNull($sheet, $column, $row);
        if (empty($labels)) {
//            $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] no labels", $msg, $row, $column));
            return null;
        }
        $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] found labels %s", $msg, $row, $column, $labels));
        $labels = explode(",", $labels);
        $itemRepo = $this->getEntityManager()->getRepository(LsItem::class);
        foreach ($labels as $label) {
            $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] process label %s", $msg, $row, $column, $label));
            $subLabels = explode('.', $label);
            $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] found %d subLabels in %s", $msg, $row, $column, count($subLabels), $label));
            if ($subLabels) {
                switch (true) {
                    case count($subLabels) == 1 || count($subLabels) == 2:
                        throw new \RuntimeException(sprintf("%s label %s missing human coding scheme for label %s.%s", $msg, $subLabels[0], $subLabels[1]));
                    break;
                    case (count($subLabels) == 3):
                        $level = $this->getEducationalLevel($row, $column, $subLabels[1]);
                        $scheme = sprintf("%s.", $subLabels[2]);
                        $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] educational level %s coding scheme %s", $msg, $row, $column, $subLabels[1], $scheme));
                    break;
                    case (count($subLabels) == 4):
                        $level = $this->getEducationalLevel($row, $column, $subLabels[1]);
                        $scheme = sprintf("%s%s.", $subLabels[2], $subLabels[3]);
                        $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] educational level %s coding scheme %s", $msg, $row, $column, $subLabels[1], $scheme));
                    break;
                    default:
                        throw new \RuntimeException(sprintf("%s bad label format", $msg));
                }
                $this->var_error_log(sprintf("%s\t\t[L] row[%d, %d] lookup '%s' for educational level '%s' in %s", $msg, $row, $column, $scheme, $level, $associatedDoc->getTitle()));
                $associatedItem = $itemRepo->findOneBy(['lsDocIdentifier' => $associatedDoc->getIdentifier(), 'humanCodingScheme' => $scheme, 'educationalAlignment' => $level]);
                if (null === $associatedItem) {
                        throw new \RuntimeException(sprintf("%s\t\t[L] row[%d, %d] no item found for human coding scheme '%s' and educatioanal alignment '%s' in %s", $msg, $row, $column, $scheme, $level, $associatedDoc->getTitle()));
                }
                array_push($associatedItems, $associatedItem);
                $this->var_error_log(sprintf("%s\t\t[M] row[%d, %d] %s[%s] matched human coding scheme %s and educational level %s in %s", $msg, $row, $column, $associatedItem->getIdentifier(), $associatedItem->getFullStatement(), $scheme, $level, $associatedDoc->getTitle()));
            }
            continue;
        }
        return $associatedItems;
    }

    private function getEducationalLevel(int $row, int $column, string $label): String
    {
        $msg = "SpineImport::getEducationalLevel()";
        $educationalLevel = "";
        $this->var_error_log(sprintf("%s,\t\t[L] row[%d, %d] grade range %s", $msg, $row, $column, $label));
        if (preg_match("/^([kK]|[0-9]|10|11)-([0-9]|1[0|1|2])$/", $label, $gradeRange)) {
            array_shift($gradeRange);
            for ($i=$gradeRange[0]; $i <= $gradeRange[1] ;$i++) {
                if (intval($i) >=10) {
                    $educationalLevel = sprintf("%s%s,", $educationalLevel, $i);
                } else {
                   $educationalLevel = sprintf("%s0%s,", $educationalLevel, $i);
                }
            }
            $educationalLevel = substr($educationalLevel, 0, strrpos($educationalLevel, ','));
            $this->var_error_log(sprintf("%s,\t\t[L] row[%d, %d] label matched '/^([kK]|[0-9]|10|11)-([0-9]|1[0|1|2])$/'.  Converted to educationalLevel %s", $msg, $row, $column, $educationalLevel));
            return $educationalLevel;
        }
        if (preg_match("/^([kK])$/", $label)) {
            $educationalLevel = sprintf("%sG", strtoupper($label));
            $this->var_error_log(sprintf("%s,\t\t[L] row[%d, %d] label matched '/^[kK]$/'. Converted to educationalLevel %s", $msg, $row, $column, $educationalLevel));
            return $educationalLevel;
        }
        if (preg_match("/^[1-9]$/", $label)) {
            $educationalLevel = sprintf("0%s", strtoupper($label));
            $this->var_error_log(sprintf("%s,\t\t[L] row[%d, %d] label matched '/^[1-9]$/'.  Converted to educationalLevel %s", $msg, $row, $column, $educationalLevel));
            return $educationalLevel;
        }
        if (preg_match("/^(1[0|1|2])$/", $label)) {
            $educationalLevel = sprintf("%s", $label);
            $this->var_error_log(sprintf("%s,\t\t[L] row[%d, %d] label matched '/^(1[0|1|2])$'.  Converted educationalLevel from %d is %s", $msg, $row, $column, $educationalLevel));
            return $educationalLevel;
        }
        throw new \RuntimeException(sprintf("%s\t\t[L] row[%d, %d] unknown format label %s", $msg, $row, $column, $label));
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