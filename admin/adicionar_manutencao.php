<?php
require '../conexao.php';

// Lista predefinida de tipos e descrições
$tipos_manutencao = [
    "Canalizações (canos, torneiras, autoclismo)" => "Reparação ou substituição de canos, torneiras e autoclismos.",
    "Instalações elétricas (lâmpadas, tomadas, quadro elétrico)" => "Substituição de lâmpadas, tomadas e verificação do quadro elétrico.",
    "Eletrodomésticos (frigorífico, máquina de lavar, micro-ondas)" => "Revisão e reparação de eletrodomésticos essenciais.",
    "Ar-condicionado e aquecimento" => "Limpeza de filtros, verificação de gás e funcionamento geral.",
    "Fechaduras e chaves (portas e janelas)" => "Troca de fechaduras, cópia de chaves e lubrificação.",
    "Pintura e retoques nas paredes" => "Pintura de paredes e retoques em áreas danificadas.",
    "Mobiliário (reparação ou substituição de peças danificadas)" => "Reparação ou substituição de móveis danificados.",
    "Jardinagem (relva, arbustos, rega)" => "Corte de relva, poda de arbustos e verificação do sistema de rega.",
    "Piscina (tratamento da água, limpeza de filtros)" => "Tratamento químico da água e limpeza dos filtros da piscina.",
    "Churrasqueira (limpeza e manutenção da estrutura)" => "Limpeza da estrutura e verificação da integridade da churrasqueira.",
    "Iluminação exterior" => "Substituição de lâmpadas e manutenção de circuitos exteriores.",
    "Extintores e detetores de fumo/gás" => "Verificação da validade e testes de funcionamento dos equipamentos.",
    "Câmaras de segurança e sistemas de alarme" => "Testes e manutenção dos equipamentos de segurança.",
    "Grades ou vedações de segurança" => "Verificação da integridade e reparações necessárias.",
    "Verificações periódicas agendadas (mensais ou trimestrais)" => "Inspeções regulares de todos os sistemas e equipamentos.",
    "Substituição de baterias (comando de portão, detetores de fumo, etc.)" => "Troca preventiva de baterias em dispositivos críticos.",
    "Testes de funcionamento geral antes da chegada de hóspedes" => "Testes e ajustes finais de todos os sistemas para garantir conforto e segurança."
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_casa = $_POST['id_casa'];
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];  // Novo campo
    $custo = $_POST['custo'];

    // Corrigido para utilizar o nome correto da tabela 'manutencao'
    $stmt = $conexao->prepare("INSERT INTO manutencao (M_id_casa, M_tipo, M_descricao, M_data_inicio, M_data_fim, M_custo) VALUES (?, ?, ?, , ?, ?)");
    $stmt->bind_param("isssd", $id_casa, $tipo, $data_inicio, $data_fim, $custo);
    $stmt->execute();

    header("Location: manutencao.php");
    exit;
}

$casas = $conexao->query("SELECT C_id_casa, C_nome FROM casas");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <title>Adicionar Manutenção</title>
    <link rel="stylesheet" href="admin.css">
    <script>
        function atualizarDescricao() {
            const tipoSelect = document.getElementById("tipo");
            const descricaoInput = document.getElementById("descricao");
            const descricoes = <?= json_encode($tipos_manutencao) ?>;
            descricaoInput.value = descricoes[tipoSelect.value] || "";
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Ícone Manutencao" style="height: 50px;">
        <h1>Adicionar Manutenção</h1>
    </div>
    <form method="post">
        <div>
            <label>Casa: <i class="fa-solid fa-house"></i></label>
            <select name="id_casa" required>
                <option value="">Selecione...</option>
                <?php while ($casa = $casas->fetch_assoc()): ?>
                    <option value="<?= $casa['C_id_casa'] ?>"><?= htmlspecialchars($casa['C_nome']) ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <label>Tipo de Manutenção: <i class="fa-solid fa-tools"></i></label>
            <select name="tipo" id="tipo" onchange="atualizarDescricao()" required>
                <option value="">Selecione...</option>
                <?php foreach ($tipos_manutencao as $tipo => $desc): ?>
                    <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Data Início: <i class="fa-solid fa-calendar-alt"></i></label>
            <input type="date" name="data_inicio" required><br><br>

            <label>Data Fim: <i class="fa-solid fa-calendar-check"></i></label>
            <input type="date" name="data_fim"><br><br>

            <label>Custo : <i class="fa-solid fa-euro-sign"></i></label>
            <input type="number" name="custo" step="0.01" required><br><br>

            <button type="submit">Salvar <i class="fa-solid fa-save"></i></button>
        </div>
    </form>
    <br>
    <a href="manutencao.php"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
</body>
</html>
