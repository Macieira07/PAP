<?php
require '../conexao.php';

$resultado = $conexao->query("SELECT * FROM hospedes");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Hóspedes</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h1>Todos os Hóspedes</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_hospede.php">+ Adicionar Hóspede</a>
    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Apelido</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Documento</th>
            <th>Verificado</th>
            <th>Ações</th>
        </tr>
        <?php while ($h = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $h['H_id_hospede'] ?></td>
                <td><?= $h['H_nome'] ?></td>
                <td><?= $h['H_apelido'] ?></td>
                <td><?= $h['H_email'] ?></td>
                <td><?= $h['H_telefone'] ?></td>
                <td><?= $h['H_documento_ident'] ?></td>
                <td><?= $h['H_verificado_email'] ?></td>
                <td>
                    <a href="editar_hospede.php?id=<?= $h['H_id_hospede'] ?>">Editar</a> |
                    <a href="eliminar_hospede.php?id=<?= $h['H_id_hospede'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
