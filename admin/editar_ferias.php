<?php
require '../conexao.php';

// Verificar se o ID foi passado pela URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID da férias/ausência não fornecido.");
}

$id_ferias = $_GET['id'];

// Buscar os dados da férias/ausência atual
$stmt = $conexao->prepare("SELECT fa.*, f.F_nome FROM ferias_ausencias fa 
                         JOIN funcionarios f ON fa.F_id_funcionario = f.F_id_funcionario 
                         WHERE fa.F_id_ausencia = ?");
$stmt->bind_param("i", $id_ferias);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Registro de férias/ausência não encontrado.");
}

$ferias = $resultado->fetch_assoc();

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter os dados do formulário
    $tipo_ausencia = $_POST['tipo_ausencia'] ?? null;
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;

    // Validar se os campos obrigatórios estão preenchidos
    if (!$data_inicio || !$data_fim) {
        $erro = "As datas de início e fim são obrigatórias.";
    } else {
        // Validar datas
        try {
            $data_inicio_obj = new DateTime($data_inicio);
            $data_fim_obj = new DateTime($data_fim);

            if ($data_fim_obj < $data_inicio_obj) {
                $erro = "A data de fim não pode ser anterior à data de início.";
            } else {
                // Validar a duração das férias
                $dias_ferias = $data_inicio_obj->diff($data_fim_obj)->days + 1;

                if ($tipo_ausencia === 'Férias' && $dias_ferias != 15) {
                    $erro = "O período de férias deve ser exatamente 15 dias.";
                } else {
                    // Verificar se já existem férias na mesma semana
                    $stmt_verificar = $conexao->prepare("
                        SELECT * FROM ferias_ausencias 
                        WHERE tipo_ausencia = 'Férias' 
                        AND F_id_ausencia != ? 
                        AND (
                            WEEK(data_inicio, 1) = WEEK(?, 1) 
                            OR WEEK(data_fim, 1) = WEEK(?, 1)
                        )
                    ");
                    $stmt_verificar->bind_param("iss", $id_ferias, $data_inicio, $data_fim);
                    $stmt_verificar->execute();
                    $resultado_verificar = $stmt_verificar->get_result();

                    if ($resultado_verificar->num_rows > 0 && $tipo_ausencia === 'Férias') {
                        $erro = "Já existem férias marcadas para a mesma semana. Escolha outra data.";
                    } else {
                        // Atualizar os dados da férias/ausência
                        $stmt_update = $conexao->prepare("UPDATE ferias_ausencias SET tipo_ausencia = ?, data_inicio = ?, data_fim = ? WHERE F_id_ausencia = ?");
                        $stmt_update->bind_param("sssi", $tipo_ausencia, $data_inicio, $data_fim, $id_ferias);

                        if ($stmt_update->execute()) {
                            $mensagem = "Registro de férias/ausência atualizado com sucesso!";
                            $tipo = "sucesso";
                            header("Location: funcionarios.php?mensagem=$mensagem&tipo=$tipo");
                            exit;
                        } else {
                            $erro = "Erro ao atualizar o registro: " . $conexao->error;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $erro = "Formato de data inválido.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Férias/Ausência</title>
    <style>
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        select, input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .erro {
            color: #f44336;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=11705&format=png&color=000000" alt="Ícone Férias" style="height: 50px;">
        <h1>Editar Férias/Ausência</h1>
    </div>
    
    <div class="form-container">
        <h2>Funcionário: <?= $ferias['F_nome'] ?></h2>
        
        <?php if (isset($erro)): ?>
            <div class="erro"><?= $erro ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <label for="tipo_ausencia">Tipo de Ausência:</label>
            <select id="tipo_ausencia" name="tipo_ausencia" required>
                <option value="Férias" <?= $ferias['tipo_ausencia'] == 'Férias' ? 'selected' : '' ?>>Férias</option>
                <option value="Falta" <?= $ferias['tipo_ausencia'] == 'Falta' ? 'selected' : '' ?>>Falta</option>
            </select>
            
            <label for="data_inicio">Data de Início:</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= $ferias['data_inicio'] ?>" required>
            
            <label for="data_fim">Data de Fim:</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= $ferias['data_fim'] ?>" required>
            
            <button type="submit">Atualizar Registro</button>
        </form>
    </div>
    
    <a href="funcionarios.php">← Voltar para Lista de Funcionários</a>
</body>
</html>