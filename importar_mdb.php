<?php
/**
 * Importação: MDB → MySQL
 * Execute: php importar_mdb.php
 */

$mdbPath = "C:\\Users\\frede\\OneDrive\\READET~1\\BDVILS~1.MDB";
$mdbPass = "vilsystem2021";

$mysql_host = '127.0.0.1';
$mysql_port = 3306;
$mysql_db   = 'vilsystem_db';
$mysql_user = 'root';
$mysql_pass = '';

echo "Conectando ao MDB...\n";
$conn = new COM("ADODB.Connection");
$conn->Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source='$mdbPath';Jet OLEDB:Database Password=$mdbPass;");
echo "Conectado.\n";

echo "Conectando ao MySQL...\n";
$pdo = new PDO("mysql:host=$mysql_host;port=$mysql_port;dbname=$mysql_db;charset=utf8mb4", $mysql_user, $mysql_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);
echo "Conectado.\n\n";

function toUtf8(string $s): string {
    // COM returns strings in system encoding (Windows-1252 on PT-BR Windows)
    return mb_convert_encoding($s, 'UTF-8', 'Windows-1252');
}
function ns($val): ?string {
    $v = trim(toUtf8((string)$val));
    return $v === '' ? null : $v;
}
function nf($val): ?float {
    $v = trim((string)$val);
    return ($v === '' || $v === 'null') ? null : (float)str_replace(',', '.', $v);
}
function ni($val): ?int {
    $v = trim((string)$val);
    return $v === '' ? null : (int)$v;
}

// ─── SETORES ─────────────────────────────────────────────────────────────────
echo "Setores...\n";
$pdo->exec("DELETE FROM setores");
// Acesso por índice: 0=CódigoAcesso, 1=SetorAcesso, 2=SenhaAcesso
$rs = $conn->Execute("SELECT * FROM [TabSetorAcesso]");
$stmt = $pdo->prepare("INSERT INTO setores (id,nome,sigla,senha,created_at,updated_at) VALUES(?,?,?,?,NOW(),NOW())");
$c = 0;
while (!$rs->EOF) {
    $id    = (int)(string)$rs->Fields->Item(0)->Value;
    $nome  = ns($rs->Fields->Item(1)->Value);
    $senha = ns($rs->Fields->Item(2)->Value) ?? '';
    $sigla = preg_match('/^([A-Z0-9\-]+)\s*-/', $nome ?? '', $m) ? $m[1] : null;
    $stmt->execute([$id, $nome, $sigla, $senha]);
    $c++; $rs->MoveNext();
}
$rs->Close();
echo "  $c setores.\n";

// ─── GRADUAÇÕES ──────────────────────────────────────────────────────────────
echo "Graduacoes...\n";
$pdo->exec("DELETE FROM graduacoes");
// COM usa CP1252; converte o SQL para CP1252 para que Access encontre a tabela
$sql = mb_convert_encoding("SELECT * FROM [TabGraduação]", 'Windows-1252', 'UTF-8');
$rs = $conn->Execute($sql);
$stmt = $pdo->prepare("INSERT INTO graduacoes (codigo,nome) VALUES(?,?)");
$c = 0;
while (!$rs->EOF) {
    $stmt->execute([ns($rs->Fields->Item(0)->Value), ns($rs->Fields->Item(1)->Value)]);
    $c++; $rs->MoveNext();
}
$rs->Close();
echo "  $c graduacoes.\n";

// ─── ESPECIALIDADES ──────────────────────────────────────────────────────────
echo "Especialidades...\n";
$pdo->exec("DELETE FROM especialidades");
$rs = $conn->Execute("SELECT * FROM [TabEspecialidade]");
$stmt = $pdo->prepare("INSERT INTO especialidades (codigo,nome) VALUES(?,?)");
$c = 0;
while (!$rs->EOF) {
    $stmt->execute([ns($rs->Fields->Item(0)->Value), ns($rs->Fields->Item(1)->Value)]);
    $c++; $rs->MoveNext();
}
$rs->Close();
echo "  $c especialidades.\n";

// ─── RESPONSÁVEIS ────────────────────────────────────────────────────────────
echo "Responsaveis...\n";
$pdo->exec("DELETE FROM responsaveis");
// 0=CodResponsavel, 1=NomeResponsavel, 2=Graduação, 3=Especialidade, 4=Setor
$rs = $conn->Execute("SELECT * FROM [TabResponsavel]");
$stmt = $pdo->prepare("INSERT INTO responsaveis (id,nome,graduacao,especialidade,setor,created_at,updated_at) VALUES(?,?,?,?,?,NOW(),NOW())");
$c = 0;
while (!$rs->EOF) {
    $stmt->execute([
        (int)(string)$rs->Fields->Item(0)->Value,
        ns($rs->Fields->Item(1)->Value),
        ns($rs->Fields->Item(2)->Value),
        ns($rs->Fields->Item(3)->Value),
        ns($rs->Fields->Item(4)->Value),
    ]);
    $c++; $rs->MoveNext();
}
$rs->Close();
echo "  $c responsaveis.\n";

// ─── LOCAIS ──────────────────────────────────────────────────────────────────
echo "Locais Setoriais...\n";
$pdo->exec("DELETE FROM locais");
// 0=CodLocalSetorial, 1=NomeLocalSetorial, 2=SetorDoLocal
$rs = $conn->Execute("SELECT * FROM [TabLocalSetorial]");
$stmt = $pdo->prepare("INSERT INTO locais (id,nome,setor,created_at,updated_at) VALUES(?,?,?,NOW(),NOW())");
$c = 0;
while (!$rs->EOF) {
    $stmt->execute([
        (int)(string)$rs->Fields->Item(0)->Value,
        ns($rs->Fields->Item(1)->Value),
        ns($rs->Fields->Item(2)->Value),
    ]);
    $c++; $rs->MoveNext();
}
$rs->Close();
echo "  $c locais.\n";

// ─── MATERIAIS (48k registros) ───────────────────────────────────────────────
echo "Material de Carga (aguarde)...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("DELETE FROM materiais");
$pdo->exec("ALTER TABLE materiais AUTO_INCREMENT = 1");

// Usar aliases para campos com caracteres especiais
// Campos: DEPENDENCIA(0), CONTA(1), CLASSE(2), Nº BMP(3), NOMECLATURA/COMPONENTE(4),
//         Nº SERIE(5), FCG(6), Nº PN(7), Nº SISPAT(8), EIQUETA METÁLICA(9),
//         QTD(10), VL ATUALIZ(11), VL DEPREC ACUM(12), VL LIQUIDO(13),
//         SITUACAO(14), LocalSetorial(15), EmUso(16), Funcionando(17),
//         MaisInformações(18), Responsável(19), LocalSetorial2(20)
$rs = $conn->Execute("SELECT * FROM [TabMaterialCarga]");

$stmtM = $pdo->prepare("
    INSERT INTO materiais
    (dependencia,conta,classe,num_bmp,nomenclatura,num_serie,fcg,num_pn,
     num_sispat,etiqueta_metalica,quantidade,valor_atualizado,valor_depreciacao,
     valor_liquido,situacao,em_uso,funcionando,mais_informacoes,responsavel_id,
     local_id,created_at,updated_at)
    VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
");

$c = 0;
$pdo->beginTransaction();
while (!$rs->EOF) {
    $sit  = ns($rs->Fields->Item(14)->Value);
    $uso  = ns($rs->Fields->Item(16)->Value);
    $func = ns($rs->Fields->Item(17)->Value);

    if (!in_array($sit,  ['A','D','P','R'])) $sit = null;
    if (!in_array($uso,  ['SIM','NÃO']))     $uso = null;
    if (!in_array($func, ['SIM','NÃO']))     $func = null;

    $stmtM->execute([
        ns($rs->Fields->Item(0)->Value),   // dependencia
        ns($rs->Fields->Item(1)->Value),   // conta
        ns($rs->Fields->Item(2)->Value),   // classe
        ni($rs->Fields->Item(3)->Value),   // num_bmp
        ns($rs->Fields->Item(4)->Value),   // nomenclatura
        ns($rs->Fields->Item(5)->Value),   // num_serie
        ns($rs->Fields->Item(6)->Value),   // fcg
        ns($rs->Fields->Item(7)->Value),   // num_pn
        ns($rs->Fields->Item(8)->Value),   // num_sispat
        ns($rs->Fields->Item(9)->Value),   // etiqueta_metalica
        (int)(ns($rs->Fields->Item(10)->Value) ?? 1), // quantidade
        nf($rs->Fields->Item(11)->Value) ?? 0,  // valor_atualizado
        nf($rs->Fields->Item(12)->Value) ?? 0,  // valor_depreciacao
        nf($rs->Fields->Item(13)->Value) ?? 0,  // valor_liquido
        $sit,
        $uso,
        $func,
        ns($rs->Fields->Item(18)->Value),  // mais_informacoes
        ni($rs->Fields->Item(19)->Value),  // responsavel_id
        ni($rs->Fields->Item(20)->Value),  // local_id
    ]);

    $c++;
    if ($c % 2000 === 0) {
        $pdo->commit();
        $pdo->beginTransaction();
        echo "  $c registros...\n";
    }

    $rs->MoveNext();
}
$pdo->commit();
$rs->Close();
echo "  $c materiais importados.\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS=1");
$conn->Close();
echo "\nImportacao concluida!\n";
