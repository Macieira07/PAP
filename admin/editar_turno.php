<?php
require '../conexao.php';

// Verificar se o ID do turno foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID do turno não fornecido.");
}

$id_turno = $_GET['id'];

// Obter os dados do turno atual
$stmt = $conexao->prepare("SELECT t.*, f.F_nome FROM turnos t 
                         JOIN funcionarios f ON t.F_id_funcionario = f.F_id_funcionario 
                         WHERE t.T_id_turno = ?");
$stmt->bind_param("i", $id_turno);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Turno não encontrado.");
}

$turno = $resultado->fetch_assoc();

// Garantir que os valores de horário estejam definidos
$horario_inicio = $turno['T_inicio'] ?? '';
$horario_fim = $turno['T_fim'] ?? '';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter os dados do formulário
    $tipo_turno = $_POST['tipo_turno'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Validar a duração do turno
    $inicio = new DateTime($horario_inicio);
    $fim = new DateTime($horario_fim);
    $intervalo = $inicio->diff($fim);
    $horas = $intervalo->h + ($intervalo->days * 24);

    if ($horas != 8) {
        $erro = "O turno deve ter exatamente 8 horas.";
    } else {
        // Atualizar os dados do turno
        $stmt_update = $conexao->prepare("UPDATE turnos SET turno = ?, data_inicio = ?, data_fim = ?, horario_inicio = ?, horario_fim = ? WHERE T_id_turno = ?");
        $stmt_update->bind_param("sssssi", $tipo_turno, $data_inicio, $data_fim, $horario_inicio, $horario_fim, $id_turno);

        if ($stmt_update->execute()) {
            $mensagem = "Turno atualizado com sucesso!";
            $tipo = "sucesso";
        } else {
            $mensagem = "Erro ao atualizar o turno: " . $conexao->error;
            $tipo = "erro";
        }

        // Redirecionar de volta para a página de funcionários com mensagem
        header("Location: funcionarios.php?mensagem=$mensagem&tipo=$tipo");
        exit;
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
    <title>Editar Turno</title>
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
        
        select, input[type="date"], input[type="time"] {
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
        <img src="https://img.icons8.com/?size=100&id=19202&format=png&color=000000" alt="Ícone Turno" style="height: 50px;">
        <h1>Editar Turno</h1>
    </div>
    
    <script>
        // Script para definir automaticamente os horários com base no tipo de turno
        function atualizarHorarios() {
            const tipoTurno = document.getElementById('tipo_turno').value;
            const horarioInicio = document.getElementById('horario_inicio');
            const horarioFim = document.getElementById('horario_fim');

            if (tipoTurno === 'Manhã') {
                horarioInicio.value = '08:00';
                horarioFim.value = '16:00';
            } else if (tipoTurno === 'Tarde') {
                horarioInicio.value = '16:00';
                horarioFim.value = '00:00';
            } else if (tipoTurno === 'Noite') {
                horarioInicio.value = '00:00';
                horarioFim.value = '08:00';
            }
        }

        // Chamar a função ao carregar a página para definir os horários iniciais
        window.onload = function() {
            atualizarHorarios();
        };
    </script>
    
    <div class="form-container">
        <h2>Funcionário: <?= $turno['F_nome'] ?></h2>
        
        <?php if (isset($erro)): ?>
            <div class="erro"><?= $erro ?></div>
        <?php endif; ?>
        
        <form method="post">
            <label for="tipo_turno">Tipo de Turno:</label>
            <select id="tipo_turno" name="tipo_turno" onchange="atualizarHorarios()" required>
                <option value="Manhã" <?= $turno['turno'] == 'Manhã' ? 'selected' : '' ?>>Manhã (08:00 - 16:00)</option>
                <option value="Tarde" <?= $turno['turno'] == 'Tarde' ? 'selected' : '' ?>>Tarde (16:00 - 00:00)</option>
                <option value="Noite" <?= $turno['turno'] == 'Noite' ? 'selected' : '' ?>>Noite (00:00 - 08:00)</option>
            </select>
            
            <label for="horario_inicio">Horário de Início:</label>
            <input type="time" id="horario_inicio" name="horario_inicio" value="<?= $horario_inicio ?>" required>
            
            <label for="horario_fim">Horário de Fim:</label>
            <input type="time" id="horario_fim" name="horario_fim" value="<?= $horario_fim ?>" required>
            
            <label for="data_inicio">Data de Início:</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= $turno['data_inicio'] ?>" required>
            
            <label for="data_fim">Data de Fim:</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= $turno['data_fim'] ?>" required>
            
            <button type="submit">Atualizar Turno</button>
        </form>
    </div>
    
    <a href="funcionarios.php">← Voltar para Lista de Funcionários</a>
</body>
</html>