<?php
require '../conexao.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario = (int)$_POST['funcionario'];
    $inicio = $_POST['inicio'];
    $fim = $_POST['fim'];
    $tipo = $_POST['tipo'];
    $motivo = $_POST['motivo'];
    
    $stmt = $conexao->prepare("INSERT INTO ferias_ausencias 
                              (F_id_funcionario, data_inicio, data_fim, tipo_ausencia, FA_motivo) 
                              VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $funcionario, $inicio, $fim, $tipo, $motivo);
    $stmt->execute();
    $stmt->close();
}

// Obter funcionários
$funcionarios = $conexao->query("SELECT F_id_funcionario, F_nome FROM funcionarios ORDER BY F_nome");

// Obter férias/ausências
$ferias = $conexao->query("SELECT f.*, fu.F_nome 
                          FROM ferias_ausencias f
                          JOIN funcionarios fu ON f.F_id_funcionario = fu.F_id_funcionario
                          ORDER BY f.data_inicio DESC");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <title>Gestão de Férias e Ausências</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { padding: 8px; }
        .button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Gestão de Férias e Ausências</h1>
    
    <h2>Registrar Nova Ausência</h2>
    <form method="post">
        <div class="form-group">
            <label for="funcionario">Funcionário:</label>
            <select name="funcionario" id="funcionario" required>
                <option value="">-- Selecione --</option>
                <?php while ($f = $funcionarios->fetch_assoc()): ?>
                    <option value="<?= $f['F_id_funcionario'] ?>"><?= htmlspecialchars($f['F_nome']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="tipo">Tipo:</label>
            <select name="tipo" id="tipo" required>
                <option value="Férias">Férias</option>
                <option value="Falta">Falta</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="inicio">Data Início:</label>
            <input type="date" name="inicio" id="inicio" required>
        </div>
        
        <div class="form-group">
            <label for="fim">Data Fim:</label>
            <input type="date" name="fim" id="fim" required>
        </div>
        
        <div class="form-group">
            <label for="motivo">Motivo:</label>
            <input type="text" name="motivo" id="motivo" required>
        </div>
        
        <button type="submit" class="button">Registrar</button>
    </form>
    
    <h2>Registros Existentes</h2>
    <table>
        <thead>
            <tr>
                <th>Funcionário</th>
                <th>Tipo</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($f = $ferias->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($f['F_nome']) ?></td>
                    <td><?= htmlspecialchars($f['tipo_ausencia']) ?></td>
                    <td><?= $f['data_inicio'] ?></td>
                    <td><?= $f['data_fim'] ?></td>
                    <td><?= htmlspecialchars($f['FA_motivo']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>