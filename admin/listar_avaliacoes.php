<?php
require '../conexao.php'; // usa o teu ficheiro de conexão

$sql = "SELECT * FROM avaliacoes_import ORDER BY data_avaliacao DESC";
$result = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8" />
<title>Avaliações</title>
<style>
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 1px solid #ddd; padding: 8px; }
  th { background-color: #f2f2f2; }
  tr:hover { background-color: #f9f9f9; }
</style>
</head>
<body>

<h1>Lista de Avaliações</h1>

<table>
  <thead>
    <tr>
      <th>Nome Completo</th>
      <th>Email</th>
      <th>Data da Estadia</th>
      <th>Experiência Geral</th>
      <th>O que gostou</th>
      <th>Ambiente</th>
      <th>Conforto</th>
      <th>Limpeza</th>
      <th>Localização</th>
      <th>Comodidades</th>
      <th>Bem Recebido?</th>
      <th>Correspondeu às Expectativas?</th>
      <th>Aspectos a Melhorar</th>
      <th>Recomendaria?</th>
      <th>Gostaria de Voltar?</th>
      <th>Comentários</th>
      <th>Data Avaliação</th>
    </tr>
  </thead>
  <tbody>
    <?php
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['nome_completo']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['data_estadia']) . "</td>";
            echo "<td>" . htmlspecialchars($row['experiencia_geral']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['gostou'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['avalia_ambiente']) . "</td>";
            echo "<td>" . htmlspecialchars($row['avalia_conforto']) . "</td>";
            echo "<td>" . htmlspecialchars($row['avalia_limpeza']) . "</td>";
            echo "<td>" . htmlspecialchars($row['avalia_localizacao']) . "</td>";
            echo "<td>" . htmlspecialchars($row['avalia_comodidades']) . "</td>";
            echo "<td>" . htmlspecialchars($row['bem_recebido']) . "</td>";
            echo "<td>" . htmlspecialchars($row['correspondeu_expectativas']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['aspetos_melhorar'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['recomendaria']) . "</td>";
            echo "<td>" . htmlspecialchars($row['gostaria_voltar']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['comentarios'])) . "</td>";
            echo "<td>" . $row['data_avaliacao'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='17'>Nenhuma avaliação encontrada.</td></tr>";
    }
    ?>
  </tbody>
</table>

</body>
</html>

<?php
$conexao->close();
?>
