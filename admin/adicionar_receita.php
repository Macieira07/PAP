<?php
require '../conexao.php';
require 'verificar_admin.php';

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
        // Inserir no banco de dados
        $query = "INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_observacoes, R_metodo_pagamento, R_comprovativo_entregue)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexao->prepare($query);
        $stmt->bind_param("sdssssi", $descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento, $comprovativo);
        
        if ($stmt->execute()) {
            header("Location: listar_receitas.php?sucesso=1");
            exit();
        } else {
            $erro = "Erro ao adicionar receita: " . $conexao->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Receita | Quinta Flores</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/../public/css/admin.css">
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
            color: #27ae60;
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
            background-color: #27ae60;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #219653;
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
        
        .success-message {
            color: #27ae60;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
            border-left: 4px solid #27ae60;
        }
    </style>
</head>
<body>
    <?php include 'header_admin.php'; ?>
    
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-plus-circle"></i> Adicionar Nova Receita</h1>
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="descricao">Descrição *</label>
                <input type="text" id="descricao" name="descricao" required>
            </div>
            
            <div class="form-group">
                <label for="valor">Valor (€) *</label>
                <input type="number" id="valor" name="valor" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="data">Data *</label>
                <input type="date" id="data" name="data" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="tipo">Tipo de Receita *</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecione...</option>
                    <option value="Reserva">Reserva</option>
                    <option value="Serviço">Serviço</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="metodo_pagamento">Método de Pagamento *</label>
                <select id="metodo_pagamento" name="metodo_pagamento" required>
                    <option value="">Selecione...</option>
                    <option value="Cartão">Cartão</option>
                    <option value="Transferência">Transferência</option>
                    <option value="MB WAY">MB WAY</option>
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="observacoes">Observações</label>
                <textarea id="observacoes" name="observacoes"></textarea>
            </div>
            
            <div class="form-group checkbox-group">
                <input type="checkbox" id="comprovativo" name="comprovativo" value="1">
                <label for="comprovativo">Comprovativo entregue</label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Receita
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