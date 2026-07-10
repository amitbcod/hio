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
echo "numFiles=" . $zip->numFiles . "\n";
for ($i = 0; $i < $zip->numFiles; $i++) {
    echo $zip->getNameIndex($i) . "\n";
}
$zip->close();
