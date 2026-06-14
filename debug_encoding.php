<?php
$conn = new COM("ADODB.Connection");
$conn->Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source='C:\\Users\\frede\\OneDrive\\READET~1\\BDVILS~1.MDB';Jet OLEDB:Database Password=vilsystem2021;");

$rs = $conn->Execute("SELECT * FROM [TabSetorAcesso]");
$raw = (string)$rs->Fields->Item(1)->Value;
echo "Raw string: $raw\n";
echo "Hex: " . bin2hex($raw) . "\n";

// Try different source encodings
echo "From CP1252: " . mb_convert_encoding($raw, 'UTF-8', 'CP1252') . "\n";
echo "From Latin1: " . mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1') . "\n";
echo "Detect: " . mb_detect_encoding($raw, ['UTF-8','CP1252','ISO-8859-1','UTF-16'], true) . "\n";

$rs->Close();
$conn->Close();
