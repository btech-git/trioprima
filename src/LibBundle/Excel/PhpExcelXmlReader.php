<?php

namespace LibBundle\Excel;

use LibBundle\Util\Xml;

class PhpExcelXmlReader
{
    private $phpExcel;
    private $activeSheet = null;
    private $worksheetIndex = 0;
    private $tableIndex = 0;
    private $columnIndex = 0;
    private $rowIndex = 0;
    private $cellIndex = 0;
    private $startCellIndex = 0;
    private $spans = array();
    private $styles = array();
    private $styleReferences = array();
    private $styleId = '';
    private $references = array();

    public function __construct(\PHPExcel $phpExcel)
    {
        $this->phpExcel = $phpExcel;
    }

    public function load($xml)
    {
        $this->processWorkbook(Xml::parse($xml));

        return $this->phpExcel;
    }

    private function processWorkbook($element)
    {
        $this->processStyles($element);
        $this->processWorksheets($element);
        $this->applyStyles();
        $worksheets = $this->phpExcel->getAllSheets();
        if (!empty($worksheets)) {
            $this->phpExcel->setActiveSheetIndex();
            foreach ($worksheets as $worksheet) {
                $worksheet->setSelectedCell();
            }
        }
    }

    private function applyStyles()
    {
        if (!isset($this->references['style']) || !isset($this->references['layout'])) {
            return;
        }
        $styleReferences = array();
        foreach ($this->styleReferences as $id => $parent) {
            if ($parent !== null) {
                $refs = array($parent);
                $ref = $parent;
                while (isset($this->styleReferences[$ref])) {
                    $refs[] = $this->styleReferences[$ref];
                    $ref = $this->styleReferences[$ref];
                }
                $styleReferences[$id] = array_reverse($refs);
            }
        }
        foreach ($this->references['style'] as $items) {
            if (strpos($items['id'], '|') !== false) {
                $styleIds = explode('|', $items['id']);
            } else {
                $styleIds = array($items['id']);
            }
            if (empty($styleIds)) {
                $valid = false;
            } else {
                $valid = true;
                foreach ($styleIds as $styleId) {
                    $valid = $valid && isset($this->styles[$styleId]);
                }
            }
            if ($valid) {
                $sheetIndex = $items['position'][0] - 1;
                $layouts = $this->references['layout'][$items['position'][0]][$items['position'][1]];
                switch ($items['type']) {
                    case 'cell':
                        $cellRange = $items['range'];
                        break;
                    case 'row':
                        $row = $items['position'][2];
                        $column = $this->phpExcel->getSheet($sheetIndex)->getColumnDimensionByColumn($layouts['left'] - 1)->getColumnIndex();
                        $columnTo = $this->phpExcel->getSheet($sheetIndex)->getColumnDimensionByColumn($layouts['right'] - 1)->getColumnIndex();
                        $cellRange = $column . $row . ':' . $columnTo . $row;
                        break;
                    case 'column':
                        $column = $this->phpExcel->getSheet($sheetIndex)->getColumnDimensionByColumn($items['position'][2] - 1)->getColumnIndex();
                        $row = $layouts['top'];
                        $rowTo = $layouts['bottom'];
                        $cellRange = $column . $row . ':' . $column . $rowTo;
                        break;
                    case 'table':
                        $row = $layouts['top'];
                        $rowTo = $layouts['bottom'];
                        $column = $this->phpExcel->getSheet($sheetIndex)->getColumnDimensionByColumn($layouts['left'] - 1)->getColumnIndex();
                        $columnTo = $this->phpExcel->getSheet($sheetIndex)->getColumnDimensionByColumn($layouts['right'] - 1)->getColumnIndex();
                        $cellRange = $column . $row . ':' . $columnTo . $rowTo;
                        break;
                }
                foreach ($styleIds as $styleId) {
                    if (isset($styleReferences[$styleId])) {
                        foreach ($styleReferences[$styleId] as $ref) {
                            $this->phpExcel->getSheet($sheetIndex)->getStyle($cellRange)->applyFromArray($this->styles[$ref]);
                        }
                    }
                    $this->phpExcel->getSheet($sheetIndex)->getStyle($cellRange)->applyFromArray($this->styles[$styleId]);
                }
            }
        }
    }

    private function processWorksheets($current)
    {
        $attributes = $current->attributes;
        switch ($current->name) {
            case 'ss:Workbook':
                $this->phpExcel->removeSheetByIndex(0);
                $this->worksheetIndex = 0;
                break;
            case 'ss:Worksheet':
                $this->activeSheet = $this->phpExcel->createSheet();
                if (isset($attributes['ss:Name'])) {
                    $this->activeSheet->setTitle($attributes['ss:Name']);
                }
                $this->worksheetIndex++;
                $this->tableIndex = 0;
                $this->rowIndex = 0;
                $this->startCellIndex = 0;
                break;
            case 'ss:Table':
                if (isset($attributes['ss:TopCell']) && $attributes['ss:TopCell'] > 0) {
                    $this->rowIndex = $attributes['ss:TopCell'] - 1;
                }
                if (isset($attributes['ss:LeftCell']) && $attributes['ss:LeftCell'] > 0) {
                    $this->startCellIndex = $attributes['ss:LeftCell'] - 1;
                }
                $this->spans = array();
                $this->columnIndex = 0;
                $this->tableIndex++;
                $layouts = array('top' => $this->rowIndex + 1, 'bottom' => $this->rowIndex + 1, 'left' => $this->startCellIndex + 1, 'right' => $this->startCellIndex + 1);
                $this->references['layout'][$this->worksheetIndex][$this->tableIndex] = $layouts;
                if (isset($attributes['ss:StyleID'])) {
                    $this->references['style'][] = array(
                        'type' => 'table',
                        'position' => array($this->worksheetIndex, $this->tableIndex),
                        'id' => $attributes['ss:StyleID'],
                    );
                }
                break;
            case 'ss:Column':
                if (isset($attributes['ss:Index']) && $attributes['ss:Index'] > 0) {
                    $this->columnIndex = $attributes['ss:Index'] - 1;
                }
                $span = 0;
                if (isset($attributes['ss:Span']) && $attributes['ss:Span'] > 0) {
                    $span = $attributes['ss:Span'];
                }
                for ($i = 0; $i <= $span; $i++) {
                    $this->columnIndex++;
                    if (isset($attributes['ss:AutoFitWidth']) && in_array($attributes['ss:AutoFitWidth'], array('0', '1'))) {
                        $this->activeSheet->getColumnDimensionByColumn($this->columnIndex - 1)->setAutoSize(boolval($attributes['ss:AutoFitWidth']));
                    }
                    if (isset($attributes['ss:Width']) && $attributes['ss:Width'] >= 0) {
                        $this->activeSheet->getColumnDimensionByColumn($this->columnIndex - 1)->setWidth($attributes['ss:Width']);
                    }
                    if (isset($attributes['ss:Hidden']) && in_array($attributes['ss:Hidden'], array('0', '1'))) {
                        $this->activeSheet->getColumnDimensionByColumn($this->columnIndex - 1)->setVisible(!boolval($attributes['ss:Hidden']));
                    }
                    if (isset($attributes['ss:StyleID'])) {
                        $this->references['style'][] = array(
                            'type' => 'column',
                            'position' => array($this->worksheetIndex, $this->tableIndex, $this->columnIndex),
                            'id' => $attributes['ss:StyleID'],
                        );
                    }
                }
                break;
            case 'ss:Row':
                if (isset($attributes['ss:Index']) && $attributes['ss:Index'] > 0) {
                    $this->rowIndex = $attributes['ss:Index'] - 1;
                }
                $span = 0;
                if (isset($attributes['ss:Span']) && $attributes['ss:Span'] > 0) {
                    $span = $attributes['ss:Span'];
                }
                for ($i = 0; $i <= $span; $i++) {
                    $this->rowIndex++;
                    if (isset($attributes['ss:AutoFitHeight']) && in_array($attributes['ss:AutoFitHeight'], array('0', '1'))) {
                        if (boolval($attributes['ss:AutoFitHeight'])) {
                            $this->activeSheet->getRowDimension($this->rowIndex)->setRowHeight(-1);
                        }
                    }
                    if (isset($attributes['ss:Height']) && $attributes['ss:Height'] >= 0) {
                        $this->activeSheet->getRowDimension($this->rowIndex)->setWidth($attributes['ss:Height']);
                    }
                    if (isset($attributes['ss:Hidden']) && in_array($attributes['ss:Hidden'], array('0', '1'))) {
                        $this->activeSheet->getRowDimension($this->rowIndex)->setVisible(!boolval($attributes['ss:Hidden']));
                    }
                    if (isset($attributes['ss:StyleID'])) {
                        $this->references['style'][] = array(
                            'type' => 'row',
                            'position' => array($this->worksheetIndex, $this->tableIndex, $this->rowIndex),
                            'id' => $attributes['ss:StyleID'],
                        );
                    }
                }
                $this->cellIndex = $this->startCellIndex;
                break;
            case 'ss:Cell':
                if (isset($attributes['ss:Index']) && $attributes['ss:Index'] > 0) {
                    $this->cellIndex = $attributes['ss:Index'] - 1;
                }
                $this->cellIndex++;
                $row = $this->rowIndex;
                $column = $this->activeSheet->getColumnDimensionByColumn($this->cellIndex - 1)->getColumnIndex();
                while (isset($this->spans[$column . $row])) {
                    $this->cellIndex++;
                    $column = $this->activeSheet->getColumnDimensionByColumn($this->cellIndex - 1)->getColumnIndex();
                }
                $rowTo = $row;
                $cellIndexTo = $this->cellIndex;
                if (isset($attributes['ss:MergeDown']) && $attributes['ss:MergeDown'] > 0) {
                    $rowTo += $attributes['ss:MergeDown'];
                }
                if (isset($attributes['ss:MergeAcross']) && $attributes['ss:MergeAcross'] > 0) {
                    for ($i = 0; $i < $attributes['ss:MergeAcross']; $i++) {
                        $cellIndexTo++;
                    }
                }
                $columnTo = $this->activeSheet->getColumnDimensionByColumn($cellIndexTo - 1)->getColumnIndex();
                $cellRange = $column . $row . ':' . $columnTo . $rowTo;
                if ($row !== $rowTo || $column !== $columnTo) {
                    foreach (\PHPExcel_Cell::extractAllCellReferencesInRange($cellRange) as $cellCoordinate) {
                        $this->spans[$cellCoordinate] = true;
                    }
                    $this->activeSheet->mergeCells($cellRange);
                }
                if (isset($attributes['ss:Formula'])) {
                    $this->activeSheet->setCellValue($column . $row, $attributes['ss:Formula']);
                }
                if (isset($attributes['ss:HRef'])) {
                    $this->activeSheet->setHyperlink($column . $row, new \PHPExcel_Cell_Hyperlink($attributes['ss:HRef']));
                }
                $layouts = &$this->references['layout'][$this->worksheetIndex][$this->tableIndex];
                if ($rowTo > $layouts['bottom']) {
                    $layouts['bottom'] = $rowTo;
                }
                if ($cellIndexTo > $layouts['right']) {
                    $layouts['right'] = $cellIndexTo;
                }
                if (isset($attributes['ss:StyleID'])) {
                    $this->references['style'][] = array(
                        'type' => 'cell',
                        'position' => array($this->worksheetIndex, $this->tableIndex, $this->rowIndex, $this->cellIndex),
                        'id' => $attributes['ss:StyleID'],
                        'range' => $cellRange,
                    );
                }
                break;
            case 'ss:Data':
                if ($current->content !== null) {
                    $column = $this->activeSheet->getColumnDimensionByColumn($this->cellIndex - 1)->getColumnIndex();
                    $row = $this->rowIndex;
                    $cell = $this->activeSheet->getCell($column . $row);
                    $cell->setValue($current->content);
                    if (isset($attributes['ss:Type']) && in_array($attributes['ss:Type'], array('String', 'Number', 'Boolean', 'Error'))) {
                        $cell->setDataType(lcfirst($attributes['ss:Type'][0]));
                    }
                }
                break;
        }
        if (!empty($current->children)) {
            foreach ($current->children as $childNode) {
                $this->processWorksheets($childNode, $current);
            }
        }
    }

    private function processStyles($current)
    {
        $attributes = $current->attributes;
        switch ($current->name) {
            case 'ss:Workbook':
                break;
            case 'ss:Styles':
                $this->styles = array();
                break;
            case 'ss:Style':
                if (!isset($attributes['ss:ID'])) {
                    return;
                }
                $this->styleId = $attributes['ss:ID'];
                $this->styles[$this->styleId] = array();
                $this->styleReferences[$this->styleId] = isset($attributes['ss:Parent']) ? $attributes['ss:Parent'] : null;
                break;
            case 'ss:Alignment':
                if (isset($attributes['ss:Horizontal']) && in_array($attributes['ss:Horizontal'], array('Left', 'Center', 'Right', 'Justify'))) {
                    $this->styles[$this->styleId]['alignment']['horizontal'] = lcfirst($attributes['ss:Horizontal']);
                }
                if (isset($attributes['ss:Vertical']) && in_array($attributes['ss:Vertical'], array('Top', 'Center', 'Bottom', 'Justify'))) {
                    $this->styles[$this->styleId]['alignment']['vertical'] = lcfirst($attributes['ss:Vertical']);
                }
                if (isset($attributes['ss:WrapText']) && in_array($attributes['ss:WrapText'], array('0', '1'))) {
                    $this->styles[$this->styleId]['alignment']['wrap'] = boolval($attributes['ss:WrapText']);
                }
                if (isset($attributes['ss:ShrinkToFit']) && in_array($attributes['ss:ShrinkToFit'], array('0', '1'))) {
                    $this->styles[$this->styleId]['alignment']['shrinkToFit'] = boolval($attributes['ss:ShrinkToFit']);
                }
                if (isset($attributes['ss:Indent']) && $attributes['ss:Indent'] >= 0) {
                    $this->styles[$this->styleId]['alignment']['indent'] = $attributes['ss:Indent'];
                }
                break;
            case 'ss:Borders':
                $this->styles[$this->styleId]['borders'] = array();
                break;
            case 'ss:Border':
                if (isset($attributes['ss:Position']) && in_array($attributes['ss:Position'], array('Left', 'Top', 'Right', 'Bottom'))) {
                    $position = lcfirst($attributes['ss:Position']);
                    if (isset($attributes['ss:Color']) && preg_match('/#[[:xdigit:]]{6}/', $attributes['ss:Color'])) {
                        $this->styles[$this->styleId]['borders'][$position]['color'] = array('rgb' => ltrim($attributes['ss:Color'], '#'));
                    }
                    if (isset($attributes['ss:LineStyle']) && in_array($attributes['ss:LineStyle'], array('Continuous', 'Dash', 'Dot', 'DashDot', 'DashDotDot', 'SlantDashDot', 'Double'))) {
                        $style = $attributes['ss:LineStyle'] === 'Continuous' ? 'Hair' : $attributes['ss:LineStyle'];
                        if (isset($attributes['ss:Weight']) && in_array($attributes['ss:Weight'], array('0', '1', '2', '3'))) {
                            $weights = array('Hair', 'Thin', 'Medium', 'Thick');
                            $weight = $weights[$attributes['ss:Weight']];
                            if ($weight === 'Thin') {
                                $style = $style[0] === 'D' || $style[0] === 'S' ? $style : 'Thin';
                            } else if ($weight === 'Medium') {
                                $style = 'Medium' . ($style[0] === 'D' && strpos($style, 'Dash') !== false ? $style : '');
                            } else {
                                $style = $weight;
                            }
                        }
                        $style .= $style === 'Dot' ? 'ted' : '';
                        $style .= $style === 'Dash' || $style === 'MediumDash' ? 'ed' : '';
                        $this->styles[$this->styleId]['borders'][$position]['style'] = lcfirst($style);
                    }
                }
                break;
            case 'ss:Font':
                if (isset($attributes['ss:FontName']) && !is_numeric($attributes['ss:FontName'])) {
                    $this->styles[$this->styleId]['font']['name'] = $attributes['ss:FontName'];
                }
                if (isset($attributes['ss:Size']) && is_numeric($attributes['ss:Size']) && $attributes['ss:Size'] > 0) {
                    $this->styles[$this->styleId]['font']['size'] = $attributes['ss:Size'];
                }
                if (isset($attributes['ss:Color']) && preg_match('/#[[:xdigit:]]{6}/', $attributes['ss:Color'])) {
                    $this->styles[$this->styleId]['font']['color'] = array('rgb' => ltrim($attributes['ss:Color'], '#'));
                }
                if (isset($attributes['ss:Bold']) && in_array($attributes['ss:Bold'], array('0', '1'))) {
                    $this->styles[$this->styleId]['font']['bold'] = boolval($attributes['ss:Bold']);
                }
                if (isset($attributes['ss:Italic']) && in_array($attributes['ss:Italic'], array('0', '1'))) {
                    $this->styles[$this->styleId]['font']['italic'] = boolval($attributes['ss:Italic']);
                }
                if (isset($attributes['ss:Underline']) && in_array($attributes['ss:Underline'], array('Single', 'Double'))) {
                    $this->styles[$this->styleId]['font']['underline'] = true;
                }
                if (isset($attributes['ss:StrikeThrough']) && in_array($attributes['ss:StrikeThrough'], array('0', '1'))) {
                    $this->styles[$this->styleId]['font']['strike'] = boolval($attributes['ss:StrikeThrough']);
                }
                break;
            case 'ss:Interior':
                if (isset($attributes['ss:Pattern']) && in_array($attributes['ss:Pattern'], array('Solid', 'Gray75', 'Gray50', 'Gray25', 'Gray125', 'Gray0625'))) {
                    $grayPatterns = array('Gray75' => 'DarkGray', 'Gray50' => 'MediumGray', 'Gray25' => 'LightGray');
                    $pattern = isset($grayPatterns[$attributes['ss:Pattern']]) ? $grayPatterns[$attributes['ss:Pattern']] : $attributes['ss:Pattern'];
                    $this->styles[$this->styleId]['fill']['type'] = lcfirst($pattern);
                    if (isset($attributes['ss:Color']) && preg_match('/#[[:xdigit:]]{6}/', $attributes['ss:Color'])) {
                        if ($pattern === 'Solid') {
                            $this->styles[$this->styleId]['fill']['color'] = array('rgb' => ltrim($attributes['ss:Color'], '#'));
                        } else if (isset($attributes['ss:PatternColor']) && preg_match('/#[[:xdigit:]]{6}/', $attributes['ss:PatternColor'])) {
                            $this->styles[$this->styleId]['fill']['startcolor'] = array('rgb' => ltrim($attributes['ss:Color'], '#'));
                            $this->styles[$this->styleId]['fill']['endcolor'] = array('rgb' => ltrim($attributes['ss:PatternColor'], '#'));
                        }
                    }
                }
                break;
            case 'ss:NumberFormat':
                if (isset($attributes['ss:Format'])) {
                    $this->styles[$this->styleId]['numberformat']['code'] = $attributes['ss:Format'];
                }
                break;
            case 'ss:Protection':
                if (isset($attributes['ss:Protected']) && in_array($attributes['ss:Protected'], array('0', '1'))) {
                    $this->styles[$this->styleId]['protection']['locked'] = boolval($attributes['ss:Protected']) ? \PHPExcel_Style_Protection::PROTECTION_PROTECTED : \PHPExcel_Style_Protection::PROTECTION_UNPROTECTED;
                }
                break;
        }
        if (!empty($current->children)) {
            foreach ($current->children as $childNode) {
                $this->processStyles($childNode, $current);
            }
        }
    }
}
