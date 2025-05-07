<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $descricao = $_POST['descricao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $custo = $_POST['custo'];
    $id_casa = $_POST['id_casa'];

    // Atualizar a manutenção no banco de dados
    $stmt = $conexao->prepare("UPDATE manutencao SET M_tipo=?, M_descricao=?, M_data_inicio=?, M_data_fim=?, M_custo=?, M_id_casa=? WHERE M_id_manutencao=?");
    $stmt->bind_param("ssssdii", $tipo, $descricao, $data_inicio, $data_fim, $custo, $id_casa, $id);
    $stmt->execute();

    header("Location: manutencao.php");
    exit;
}

// Recuperar os dados da manutenção
$stmt = $conexao->prepare("SELECT * FROM manutencao WHERE M_id_manutencao=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$manutencao = $resultado->fetch_assoc();

// Recuperar as casas para a lista de seleção
$casas_resultado = $conexao->query("SELECT * FROM casas");

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Manutenção</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Ícone Manutencao" style="height: 50px;">
        <h1>Editar Manutenção</h1>
    </div>
    <form method="post">
        Tipo de Manutenção:
        <select name="tipo" required>
            <option value="Canalizações" <?= $manutencao['M_tipo'] == 'Canalizações' ? 'selected' : '' ?>>Canalizações (canos, torneiras, autoclismo)</option>
            <option value="Instalações elétricas" <?= $manutencao['M_tipo'] == 'Instalações elétricas' ? 'selected' : '' ?>>Instalações elétricas (lâmpadas, tomadas, quadro elétrico)</option>
            <option value="Eletrodomésticos" <?= $manutencao['M_tipo'] == 'Eletrodomésticos' ? 'selected' : '' ?>>Eletrodomésticos (frigorífico, máquina de lavar, micro-ondas)</option>
            <option value="Ar-condicionado e aquecimento" <?= $manutencao['M_tipo'] == 'Ar-condicionado e aquecimento' ? 'selected' : '' ?>>Ar-condicionado e aquecimento</option>
            <option value="Fechaduras e chaves" <?= $manutencao['M_tipo'] == 'Fechaduras e chaves' ? 'selected' : '' ?>>Fechaduras e chaves (portas e janelas)</option>
            <option value="Pintura e retoques" <?= $manutencao['M_tipo'] == 'Pintura e retoques' ? 'selected' : '' ?>>Pintura e retoques nas paredes</option>
            <option value="Mobiliário" <?= $manutencao['M_tipo'] == 'Mobiliário' ? 'selected' : '' ?>>Mobiliário (reparação ou substituição de peças danificadas)</option>
            <option value="Jardinagem" <?= $manutencao['M_tipo'] == 'Jardinagem' ? 'selected' : '' ?>>Jardinagem (relva, arbustos, rega)</option>
            <option value="Piscina" <?= $manutencao['M_tipo'] == 'Piscina' ? 'selected' : '' ?>>Piscina (tratamento da água, limpeza de filtros)</option>
            <option value="Churrasqueira" <?= $manutencao['M_tipo'] == 'Churrasqueira' ? 'selected' : '' ?>>Churrasqueira (limpeza e manutenção da estrutura)</option>
            <option value="Iluminação exterior" <?= $manutencao['M_tipo'] == 'Iluminação exterior' ? 'selected' : '' ?>>Iluminação exterior</option>
            <option value="Extintores e detetores" <?= $manutencao['M_tipo'] == 'Extintores e detetores' ? 'selected' : '' ?>>Extintores e detetores de fumo/gás</option>
            <option value="Câmaras de segurança" <?= $manutencao['M_tipo'] == 'Câmaras de segurança' ? 'selected' : '' ?>>Câmaras de segurança e sistemas de alarme</option>
            <option value="Grades ou vedações" <?= $manutencao['M_tipo'] == 'Grades ou vedações' ? 'selected' : '' ?>>Grades ou vedações de segurança</option>
            <option value="Verificações periódicas" <?= $manutencao['M_tipo'] == 'Verificações periódicas' ? 'selected' : '' ?>>Verificações periódicas agendadas (mensais ou trimestrais)</option>
            <option value="Substituição de baterias" <?= $manutencao['M_tipo'] == 'Substituição de baterias' ? 'selected' : '' ?>>Substituição de baterias (comando de portão, detetores de fumo, etc.)</option>
            <option value="Testes de funcionamento" <?= $manutencao['M_tipo'] == 'Testes de funcionamento' ? 'selected' : '' ?>>Testes de funcionamento geral antes da chegada de hóspedes</option>
        </select><br><br>

        Descrição: <textarea name="descricao" required><?= $manutencao['M_descricao'] ?></textarea><br><br>

        Data de Início: <input type="date" name="data_inicio" value="<?= $manutencao['M_data_inicio'] ?>" required><br><br>

        Data de Fim: <input type="date" name="data_fim" value="<?= $manutencao['M_data_fim'] ?>"><br><br>

        Custo (€): <input type="number" step="0.01" name="custo" value="<?= $manutencao['M_custo'] ?>" required><br><br>

        Casa:
        <select name="id_casa" required>
            <?php while ($casa = $casas_resultado->fetch_assoc()): ?>
                <option value="<?= $casa['C_id_casa'] ?>" <?= $manutencao['M_id_casa'] == $casa['C_id_casa'] ? 'selected' : '' ?>><?= $casa['C_nome'] ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Atualizar Manutenção</button>
    </form>
    <a href="manutencao.php">← Voltar</a>
</body>
</html>
