<?php
$desktop = getenv('USERPROFILE') . '\\OneDrive\\' . "\xC3\x81rea de Trabalho";
$mdb = $desktop . '\\BD VilSystem MC EEAR SET28 6.0.mdb';
echo "Path: $mdb\n";
echo file_exists($mdb) ? "ARQUIVO EXISTE\n" : "ARQUIVO NAO ENCONTRADO\n";

// Tenta com caminho 8.3
$mdb2 = 'C:\\Users\\frede\\OneDrive\\' . "\xC3\x81rea de Trabalho" . '\\BD VilSystem MC EEAR SET28 6.0.mdb';
echo "Path2: $mdb2\n";
echo file_exists($mdb2) ? "ARQUIVO2 EXISTE\n" : "ARQUIVO2 NAO ENCONTRADO\n";
