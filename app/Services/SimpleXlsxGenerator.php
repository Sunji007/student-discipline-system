<?php
namespace App\Services;

class SimpleXlsxGenerator
{
    /**
     * Generate raw binary XLSX content.
     *
     * @param string $sheetName
     * @param array $titleRows Array of strings for top header (Title, Date range, etc.)
     * @param array $columns Associative array of ['Column Name' => width_in_chars]
     * @param array $dataRows Array of data arrays
     * @return string Binary XLSX file content
     */
    public static function create(string $sheetName, array $titleRows, array $columns, array $dataRows): string
    {
        $zip = new SimpleZipWriter();

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
        $zip->addFile('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
        $zip->addFile('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        $zip->addFile('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $cleanSheetName = htmlspecialchars(mb_substr($sheetName, 0, 31), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets>' .
            '<sheet name="' . $cleanSheetName . '" sheetId="1" r:id="rId1"/>' .
            '</sheets>' .
            '</workbook>';
        $zip->addFile('xl/workbook.xml', $workbook);

        // 5. xl/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="6">' .
            '<font><sz val="11"/><name val="Sarabun"/><family val="2"/></font>' . // 0: regular
            '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Sarabun"/><family val="2"/></font>' . // 1: header white bold
            '<font><b/><sz val="15"/><color rgb="FF1E3A8A"/><name val="Sarabun"/><family val="2"/></font>' . // 2: title
            '<font><sz val="10"/><color rgb="FF64748B"/><name val="Sarabun"/><family val="2"/></font>' . // 3: subtitle
            '<font><b/><sz val="11"/><color rgb="FFDC2626"/><name val="Sarabun"/><family val="2"/></font>' . // 4: red text
            '<font><b/><sz val="11"/><color rgb="FF16A34A"/><name val="Sarabun"/><family val="2"/></font>' . // 5: green text
            '</fonts>' .
            '<fills count="4">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/></patternFill></fill>' . // 2: Navy header
            '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' . // 3: Very light gray
            '</fills>' .
            '<borders count="2">' .
            '<border><left/><right/><top/><bottom/></border>' . // 0: none
            '<border>' . // 1: thin gray
            '<left style="thin"><color rgb="FFCBD5E1"/></left>' .
            '<right style="thin"><color rgb="FFCBD5E1"/></right>' .
            '<top style="thin"><color rgb="FFCBD5E1"/></top>' .
            '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>' .
            '</border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="8">' .
            // 0: Normal data cell
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>' .
            // 1: Header (Navy, White Bold, Centered)
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' .
            // 2: Title
            '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' .
            // 3: Subtitle
            '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>' .
            // 4: Center aligned cell
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
            // 5: Number / Right aligned cell
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>' .
            // 6: Negative score (Red)
            '<xf numFmtId="0" fontId="4" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
            // 7: Positive score (Green)
            '<xf numFmtId="0" fontId="5" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '</cellXfs>' .
            '<cellStyles count="1">' .
            '<cellStyle name="Normal" xfId="0" builtinId="0"/>' .
            '</cellStyles>' .
            '</styleSheet>';
        $zip->addFile('xl/styles.xml', $styles);

        // 6. xl/worksheets/sheet1.xml
        $colIndex = 1;
        $colsXml = '<cols>';
        foreach ($columns as $name => $width) {
            $w = max(10, (float)$width);
            $colsXml .= '<col min="' . $colIndex . '" max="' . $colIndex . '" width="' . $w . '" customWidth="1"/>';
            $colIndex++;
        }
        $colsXml .= '</cols>';

        $sheetData = '<sheetData>';
        $rowIndex = 1;

        // Title rows
        foreach ($titleRows as $tIdx => $tRow) {
            $styleId = ($tIdx === 0) ? 2 : 3;
            $rowHeight = ($tIdx === 0) ? ' ht="26" customHeight="1"' : ' ht="20" customHeight="1"';
            $sheetData .= '<row r="' . $rowIndex . '"' . $rowHeight . '>';
            $cleanText = htmlspecialchars($tRow, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $sheetData .= '<c r="A' . $rowIndex . '" s="' . $styleId . '" t="inlineStr"><is><t>' . $cleanText . '</t></is></c>';
            $sheetData .= '</row>';
            $rowIndex++;
        }

        // Empty space row
        $sheetData .= '<row r="' . $rowIndex . '" ht="14" customHeight="1"/>';
        $rowIndex++;

        // Table Header row
        $sheetData .= '<row r="' . $rowIndex . '" ht="26" customHeight="1">';
        $cIdx = 0;
        foreach (array_keys($columns) as $colTitle) {
            $colLetter = self::getColLetter($cIdx);
            $cleanTitle = htmlspecialchars($colTitle, ENT_QUOTES | ENT_XML1, 'UTF-8');
            $sheetData .= '<c r="' . $colLetter . $rowIndex . '" s="1" t="inlineStr"><is><t>' . $cleanTitle . '</t></is></c>';
            $cIdx++;
        }
        $sheetData .= '</row>';
        $rowIndex++;

        // Data rows
        foreach ($dataRows as $row) {
            $sheetData .= '<row r="' . $rowIndex . '" ht="22" customHeight="1">';
            $cIdx = 0;
            foreach ($row as $val) {
                $colLetter = self::getColLetter($cIdx);
                
                // Determine format/style
                $style = 0; // Default regular
                if ($cIdx === 0 || $cIdx === 1 || $cIdx === 2 || $cIdx === 4) {
                    $style = 4; // Center
                } elseif ($cIdx === 6) { // Score modifier
                    $numVal = is_numeric($val) ? (float)$val : 0;
                    $style = ($numVal < 0) ? 6 : (($numVal > 0) ? 7 : 4);
                } elseif ($cIdx === 7) { // Remaining score
                    $style = 5; // Right
                }

                if (is_numeric($val) && !preg_match('/^0\d+/', (string)$val)) {
                    $sheetData .= '<c r="' . $colLetter . $rowIndex . '" s="' . $style . '"><v>' . $val . '</v></c>';
                } else {
                    $cleanVal = htmlspecialchars((string)($val ?? '-'), ENT_QUOTES | ENT_XML1, 'UTF-8');
                    $sheetData .= '<c r="' . $colLetter . $rowIndex . '" s="' . $style . '" t="inlineStr"><is><t>' . $cleanVal . '</t></is></c>';
                }
                $cIdx++;
            }
            $sheetData .= '</row>';
            $rowIndex++;
        }
        $sheetData .= '</sheetData>';

        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            $colsXml .
            $sheetData .
            '</worksheet>';
        $zip->addFile('xl/worksheets/sheet1.xml', $worksheet);

        return $zip->buildZip();
    }

    private static function getColLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex >= 0) {
            $letter = chr($colIndex % 26 + 65) . $letter;
            $colIndex = intval($colIndex / 26) - 1;
        }
        return $letter;
    }
}
