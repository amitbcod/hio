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
            $txt = '';
            foreach ($si->r as $r) {
                $txt .= (string)$r->t;
            }
            $sharedStrings[] = $txt;
        }
    }
}

$workbook = new DOMDocument();
$workbook->loadXML($zip->getFromName('xl/workbook.xml'));
$xpath = new DOMXPath($workbook);
$xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
$xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

$rels = new DOMDocument();
$rels->loadXML($zip->getFromName('xl/_rels/workbook.xml.rels'));
$xpathR = new DOMXPath($rels);
$xpathR->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
$sheetFiles = [];
foreach ($xpathR->query('//r:Relationship') as $rel) {
    $sheetFiles[$rel->getAttribute('Id')] = $rel->getAttribute('Target');
}

foreach ($xpath->query('//x:sheets/x:sheet') as $sheet) {
    $name = $sheet->getAttribute('name');
    $rid = $sheet->getAttribute('r:id');
    $target = $sheetFiles[$rid] ?? null;
    echo "Sheet: $name";
    if ($target) {
        echo " -> $target";
    }
    echo "\n";
    if (!$target) continue;
    $sheetPath = 'xl/' . ltrim($target, '/');
    $sheetXml = new SimpleXMLElement($zip->getFromName($sheetPath));
    $count = 0;
    foreach ($sheetXml->sheetData->row as $row) {
        if ($count++ >= 1) break;
        $cells = [];
        foreach ($row->c as $c) {
            $v = (string)$c->v;
            $t = (string)$c['t'];
            if ($t === 's' && is_numeric($v) && isset($sharedStrings[(int)$v])) {
                $v = $sharedStrings[(int)$v];
            }
            $cells[] = $v;
        }
        echo 'ROW ' . $row['r'] . ': ' . implode(' | ', $cells) . "\n";
    }
    echo "\n";
}
$zip->close();
