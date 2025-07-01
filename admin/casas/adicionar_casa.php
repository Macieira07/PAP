<?php
require '../../conexao.php';

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

        $stmt = $conexao->prepare("INSERT INTO casas (C_nome, C_descricao, C_capacidade, C_preco_noite, C_caracteristicas, C_estado)
                                   VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $nome, $descricao, $capacidade, $preco, $caracteristicas, $estado);
        $stmt->execute();

        echo 'OK';
        exit;
    }
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Adicionar Alojamento</h2>
        <form method="post" id="formWizardCasa" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Etapa 1 -->
            <div class="wizard-step" id="wizardStep1">
                <label style="display:flex; flex-direction:column; gap:4px;">Nome:
                    <input type="text" name="nome" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea name="descricao" style="resize:vertical; min-height:60px; max-width:100%;"></textarea>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Capacidade:
                    <input type="number" name="capacidade" required>
                </label>
                <button type="button" id="btnWizardProximoCasa" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Etapa 2 -->
            <div class="wizard-step" id="wizardStep2" style="display:none;">
                <label style="display:flex; flex-direction:column; gap:4px;">Preço por Noite (€):
                    <input type="number" step="0.01" name="preco" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Características:
                    <textarea name="caracteristicas" style="resize:vertical; min-height:60px; max-width:100%;"></textarea>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Estado:
                    <select name="estado" id="estadoSelectCasa" style="font-weight:bold;">
                        <option value="disponível" style="color:#28a745;">Disponível</option>
                        <option value="ocupada" style="color:#ff9800;">Ocupada</option>
                        <option value="manutenção" style="color:#1976d2;">Manutenção</option>
                    </select>
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorCasa" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Confirmar</button>
                </div>
            </div>
        </form>
    </div>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="../global.css">
    <meta charset="UTF-8">
    <title>Adicionar Nova Casa</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=8BBH2HJBM6Nz&format=png&color=000000" alt="Ícone Casas" style="height: 50px;">
    <h2>Adicionar Novo Alojamento</h2>
</div>

<form method="post">
    <label>
        <i class="fa-solid fa-home"></i> Nome:
        <input type="text" name="nome" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-align-left"></i> Descrição:
        <textarea name="descricao"></textarea>
    </label><br><br>

    <label>
        <i class="fa-solid fa-users"></i> Capacidade:
        <input type="number" name="capacidade" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-euro-sign"></i> Preço por Noite (€):
        <input type="number" step="0.01" name="preco" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-cogs"></i> Características:
        <textarea name="caracteristicas"></textarea>
    </label><br><br>

    <label>
        <i class="fa-solid fa-clipboard-list"></i> Estado:
        <select name="estado">
            <option value="disponível">Disponível</option>
            <option value="ocupada">Ocupada</option>
            <option value="manutenção">Manutenção</option>
        </select>
    </label><br><br>

    <button type="submit">Salvar</button>
</form>

<a href="casas.php">← Voltar</a>

</body>
</html>
