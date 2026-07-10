<?php

$path = 'C:\\Users\\lenovo\\Downloads\\hio_transport_06_data_entry_sheet_2026_approved.xlsx';
if (!file_exists($path)) {
    echo "MISSING: $path\n";
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($path) !== true) {
    echo "OPENFAIL\n";
    exit(1);
}

$sharedStrings = [];
if (($idx = $zip->locateName('xl/sharedStrings.xml')) !== false) {
    $xml = new SimpleXMLElement($zip->getFromIndex($idx));
    foreach ($xml->si as $si) {
        if (isset($si->t)) {
            $sharedStrings[] = (string)$si->t;
        } else {
            $text = '';
            foreach ($si->r as $r) {
                $text .= (string)$r->t;
            }
            $sharedStrings[] = $text;
        }
    }
}

$rels = new SimpleXMLElement($zip->getFromName('xl/_rels/workbook.xml.rels'));
$relMap = [];
foreach ($rels->Relationship as $rel) {
    $relMap[(string)$rel['Id']] = (string)$rel['Target'];
}

$wb = new SimpleXMLElement($zip->getFromName('xl/workbook.xml'));
foreach ($wb->sheets->sheet as $sheet) {
    $name = (string)$sheet['name'];
    $rid = (string)$sheet['{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id'];
    $sheetPath = 'xl/' . ltrim($relMap[$rid], '/');
    echo "Sheet: $name\n";
    $sheetXml = new SimpleXMLElement($zip->getFromName($sheetPath));
    $rows = $sheetXml->sheetData->row;
    $count = 0;
    foreach ($rows as $row) {
        if ($count++ >= 10) break;
        $cells = [];
        foreach ($row->c as $c) {
            $v = (string)$c->v;
            $t = (string)$c['t'];
            if ($t === 's' && isset($sharedStrings[(int)$v])) {
                $v = $sharedStrings[(int)$v];
            }
            $cells[] = $v;
        }
        echo 'ROW ' . $row['r'] . ': ' . implode(' | ', $cells) . "\n";
    }
    echo "\n";
}
$zip->close();
