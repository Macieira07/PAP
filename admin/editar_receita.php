<?php
require '../conexao.php';
require 'verificar_admin.php';

// Verificar se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_receitas.php");
    exit();
}

$id = limparDados($_GET['id']);

// Buscar a receita no banco de dados
$query = "SELECT * FROM receitas WHERE R_id_receita = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: listar_receitas.php");
    exit();
}

$receita = $resultado->fetch_assoc();

// Processar formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = limparDados($_POST['descricao']);
    $valor = limparDados($_POST['valor']);
    $data = limparDados($_POST['data']);
    $tipo = limparDados($_POST['tipo']);
    $observacoes = limparDados($_POST['observacoes']);
    $metodo_pagamento = limparDados($_POST['metodo_pagamento']);
    $comprovativo = isset($_POST['comprovativo']) ? 1 : 0;
    
    // Validar valor
    if (!is_numeric($valor) || $valor <= 0) {
        $erro = "O valor deve ser um número positivo.";
    } else {
        // Atualizar no banco de dados
        $query = "UPDATE receitas SET 
                  R_descricao = ?,
                  R_valor = ?,
                  R_data = ?,
                  R_tipo = ?,
                  R_observacoes = ?,
                  R_metodo_pagamento = ?,
                  R_comprovativo_entregue = ?
                  WHERE R_id_receita = ?";
        
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("sdssssii", $descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento, $comprovativo, $id);
        
        if ($stmt->execute()) {
            header("Location: listar_receitas.php?sucesso=2");
            exit();
        } else {
            $erro = "Erro ao atualizar receita: " . $conexao->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Receita | Quinta Flores</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .form-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-header h1 {
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-header i {
            font-size: 1.5em;
            color: #3498db;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input {
            width: auto;
        }
        
        .btn-submit {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #2980b9;
        }
        
        .btn-cancel {
            background-color: #e74c3c;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
            transition: background-color 0.3s;
        }
        
        .btn-cancel:hover {
            background-color: #c0392b;
        }
        
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fdecea;
            border-radius: 4px;
            border-left: 4px solid #e74c3c;
        }
    </style>
</head>
<body>
    <?php include 'header_admin.php'; ?>
    
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-edit"></i> Editar Receita</h1>
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="descricao">Descrição *</label>
                <input type="text" id="descricao" name="descricao" required value="<?php echo htmlspecialchars($receita['R_descricao']); ?>">
            </div>
            
            <div class="form-group">
                <label for="valor">Valor (€) *</label>
                <input type="number" id="valor" name="valor" step="0.01" min="0" required value="<?php echo htmlspecialchars($receita['R_valor']); ?>">
            </div>
            
            <div class="form-group">
                <label for="data">Data *</label>
                <input type="date" id="data" name="data" required value="<?php echo htmlspecialchars($receita['R_data']); ?>">
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo de Receita *</label>
                <select id="tipo" name="tipo" required>
                    <option value="Reserva" <?php echo $receita['R_tipo'] == 'Reserva' ? 'selected' : ''; ?>>Reserva</option>
                    <option value="Serviço" <?php echo $receita['R_tipo'] == 'Serviço' ? 'selected' : ''; ?>>Serviço</option>
                    <option value="Outro" <?php echo $receita['R_tipo'] == 'Outro' ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="metodo_pagamento">Método de Pagamento *</label>
                <select id="metodo_pagamento" name="metodo_pagamento" required>
                    <option value="Cartão" <?php echo $receita['R_metodo_pagamento'] == 'Cartão' ? 'selected' : ''; ?>>Cartão</option>
                    <option value="Transferência" <?php echo $receita['R_metodo_pagamento'] == 'Transferência' ? 'selected' : ''; ?>>Transferência</option>
                    <option value="MB WAY" <?php echo $receita['R_metodo_pagamento'] == 'MB WAY' ? 'selected' : ''; ?>>MB WAY</option>
                    <option value="Dinheiro" <?php echo $receita['R_metodo_pagamento'] == 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
                    <option value="Outro" <?php echo $receita['R_metodo_pagamento'] == 'Outro' ? 'selected' : ''; ?>>Outro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea id="observacoes" name="observacoes"><?php echo htmlspecialchars($receita['R_observacoes']); ?></textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="comprovativo" name="comprovativo" value="1" <?php echo $receita['R_comprovativo_entregue'] ? 'checked' : ''; ?>>
                <label for="comprovativo">Comprovativo entregue</label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Atualizar Receita
                </button>
                <a href="listar_receitas.php" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
    
    <?php include 'footer_admin.php'; ?>
    
    <script>
        // Formatação automática do valor monetário
        document.getElementById('valor').addEventListener('blur', function() {
            let value = parseFloat(this.value);
            if (!isNaN(value)) {
                this.value = value.toFixed(2);
            }
        });
    </script>
</body>
</html>