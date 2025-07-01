<?php
require '../conexao.php';
$id = $_GET['id'];
if (isset($_GET['modal'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome'];
        $descricao = $_POST['descricao'];
        $capacidade = $_POST['capacidade'];
        $preco = $_POST['preco'];
        $caracteristicas = $_POST['caracteristicas'];
        $estado = $_POST['estado'];
        // Validação
        if (!is_numeric($preco) || $preco <= 0) {
            echo "O preço por noite deve ser um valor positivo.";
            exit;
        }
        if (!is_numeric($capacidade) || $capacidade <= 0) {
            echo "A capacidade deve ser um valor positivo.";
            exit;
        }
        $stmt = $conexao->prepare("UPDATE casas SET C_nome=?, C_descricao=?, C_capacidade=?, C_preco_noite=?, C_caracteristicas=?, C_estado=? WHERE C_id_casa=?");
        $stmt->bind_param("sssdssi", $nome, $descricao, $capacidade, $preco, $caracteristicas, $estado, $id);
        $stmt->execute();

        echo 'OK';
        exit;
    }
    $stmt = $conexao->prepare("SELECT * FROM casas WHERE C_id_casa=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $casa = $resultado->fetch_assoc();
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Editar Alojamento</h2>
        <form method="post" id="formWizardCasa" data-id="<?= $casa['C_id_casa'] ?>" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Etapa 1 -->
            <div class="wizard-step" id="wizardStep1">
                <label style="display:flex; flex-direction:column; gap:4px;">Nome:
                    <input type="text" name="nome" value="<?= htmlspecialchars($casa['C_nome']) ?>" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea name="descricao" style="resize:vertical; min-height:60px; max-width:100%;"><?= htmlspecialchars($casa['C_descricao']) ?></textarea>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Capacidade:
                    <input type="number" name="capacidade" value="<?= $casa['C_capacidade'] ?>" required>
                </label>
                <button type="button" id="btnWizardProximoCasa" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Etapa 2 -->
            <div class="wizard-step" id="wizardStep2" style="display:none;">
                <label style="display:flex; flex-direction:column; gap:4px;">Preço por Noite (€):
                    <input type="number" step="0.01" name="preco" value="<?= $casa['C_preco_noite'] ?>" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Características:
                    <textarea name="caracteristicas" style="resize:vertical; min-height:60px; max-width:100%;"><?= htmlspecialchars($casa['C_caracteristicas']) ?></textarea>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Estado:
                    <select name="estado" id="estadoSelectCasa" style="font-weight:bold;">
                        <option value="disponível" style="color:#28a745;" <?= $casa['C_estado'] == 'disponível' ? 'selected' : '' ?>>Disponível</option>
                        <option value="ocupada" style="color:#ff9800;" <?= $casa['C_estado'] == 'ocupada' ? 'selected' : '' ?>>Ocupada</option>
                        <option value="manutenção" style="color:#1976d2;" <?= $casa['C_estado'] == 'manutenção' ? 'selected' : '' ?>>Manutenção</option>
                    </select>
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorCasa" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Atualizar</button>
                </div>
            </div>
        </form>
    </div>
    <?php
    exit;
}
$stmt = $conexao->prepare("SELECT * FROM casas WHERE C_id_casa=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$casa = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="global.css">
    <meta charset="UTF-8">
    <title>Editar Casa</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=8BBH2HJBM6Nz&format=png&color=000000" alt="Ícone Casas" style="height: 50px;">
        <h2>Editar Alojamento</h2>
    </div>
    <form method="post">
        Nome: <input type="text" name="nome" value="<?= $casa['C_nome'] ?>" required><br><br>
        Descrição: <textarea name="descricao"><?= $casa['C_descricao'] ?></textarea><br><br>
        Capacidade: <input type="number" name="capacidade" value="<?= $casa['C_capacidade'] ?>" required><br><br>
        Preço por Noite (€): <input type="number" step="0.01" name="preco" value="<?= $casa['C_preco_noite'] ?>" required><br><br>
        Características: <textarea name="caracteristicas"><?= $casa['C_caracteristicas'] ?></textarea><br><br>
        Estado:
        <select name="estado">
            <option value="disponível" <?= $casa['C_estado'] == 'disponível' ? 'selected' : '' ?>>Disponível</option>
            <option value="ocupada" <?= $casa['C_estado'] == 'ocupada' ? 'selected' : '' ?>>Ocupada</option>
            <option value="manutenção" <?= $casa['C_estado'] == 'manutenção' ? 'selected' : '' ?>>Manutenção</option>
        </select><br><br>
        <button type="submit">Atualizar</button>
    </form>
    <a href="casas.php">← Voltar</a>
</body>
</html>
