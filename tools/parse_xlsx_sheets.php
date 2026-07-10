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

$sheets = [];
foreach ($xpath->query('//x:sheets/x:sheet') as $sheet) {
    $name = $sheet->getAttribute('name');
    $rid = $sheet->getAttribute('r:id');
    $sheets[$rid] = $name;
}

$rels = new DOMDocument();
$rels->loadXML($zip->getFromName('xl/_rels/workbook.xml.rels'));
$xpathR = new DOMXPath($rels);
$xpathR->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

$sheetFiles = [];
foreach ($xpathR->query('//r:Relationship') as $rel) {
    $id = $rel->getAttribute('Id');
    $target = $rel->getAttribute('Target');
    $sheetFiles[$id] = $target;
}

foreach ($sheets as $rid => $name) {
    $target = $sheetFiles[$rid] ?? null;
    if (!$target) {
        echo "Sheet $name missing target for rid $rid\n";
        continue;
    }
    $sheetPath = 'xl/' . ltrim($target, '/');
    echo "Sheet: $name -> $sheetPath\n";
    $sheetXml = new SimpleXMLElement($zip->getFromName($sheetPath));
    $sheetXml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rows = $sheetXml->sheetData->row;
    foreach ($rows as $row) {
        $r = (string)$row['r'];
        $cells = [];
        foreach ($row->c as $c) {
            $v = (string)$c->v;
            $t = (string)$c['t'];
            if ($t === 's' && is_numeric($v) && isset($sharedStrings[(int)$v])) {
                $v = $sharedStrings[(int)$v];
            }
            $cells[] = $v;
        }
        echo 'ROW ' . $r . ': ' . implode(' | ', $cells) . "\n";
    }
    echo "\n";
}
$zip->close();
