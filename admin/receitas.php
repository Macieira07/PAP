<?php
// Inclui a conexão com o banco de dados
require '../conexao.php';

// Consultar todas as receitas
$query = "SELECT * FROM receitas ORDER BY R_data DESC";
$resultado = $conexao->query($query);

?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
<link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Listar Receitas</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=24836&format=png&color=000000" alt="Ícone Receitas" style="height: 50px;">
        <h1>Todas as Receitas</h1>
    </div>
    
    <table border="1">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Data</th>
                <th>Tipo</th>
                <th>Observações</th>
                <th>Método de Pagamento</th>
                <th>Comprovativo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($receita = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?php echo $receita['R_descricao']; ?></td>
                <td><?php echo number_format($receita['R_valor'], 2, ',', '.'); ?></td>
                <td><?php echo $receita['R_data']; ?></td>
                <td><?php echo $receita['R_tipo']; ?></td>
                <td><?php echo $receita['R_observacoes']; ?></td>
                <td><?php echo $receita['R_metodo_pagamento']; ?></td>
                <td><?php echo $receita['R_comprovativo_entregue'] ? 'Sim' : 'Não'; ?></td>
                <td>
                    <!-- Link para Editar -->
                    <a href="editar_receita.php?id=<?php echo $receita['R_id_receita']; ?>">Editar</a> |
                    <!-- Link para Deletar -->
                    <a href="eliminar_receita.php?id=<?php echo $receita['R_id_receita']; ?>" onclick="return confirm('Tem certeza que deseja deletar esta receita?')">Deletar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <br>
    <a href="adicionar_receita.php">Adicionar Nova Receita</a><br><br>
    
    <label><a href="admin.php">Voltar para o Painel de Administração</a></label>
</body>
</html>

<?php
// Fechar a conexão
$conexao->close();
?>
