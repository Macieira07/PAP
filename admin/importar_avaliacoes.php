<?php
require '../conexao.php'; // usa o teu ficheiro de conexão

// Ficheiro CSV - muda para o caminho correto
$csvFile = "C:/xampp/htdocs/PAP/admin/Avaliações_Feedbacks - Respostas do Formulário 1.csv";


if (!file_exists($csvFile)) {
    die("Ficheiro CSV não encontrado.");
}

if (($handle = fopen($csvFile, 'r')) !== false) {
    // Ler a primeira linha com os nomes das colunas (header)
    $header = fgetcsv($handle, 1000, ',');

    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
        // Mapeia o CSV para as variáveis - ajusta os índices conforme o CSV
        $nome_completo = $conexao->real_escape_string($data[0]);
        $email = $conexao->real_escape_string($data[1]);
        $data_estadia = $conexao->real_escape_string($data[2]);
        $experiencia_geral = $conexao->real_escape_string($data[3]);
        $gostou = $conexao->real_escape_string($data[4]);
        $avalia_ambiente = $conexao->real_escape_string($data[5]);
        $avalia_conforto = $conexao->real_escape_string($data[6]);
        $avalia_limpeza = $conexao->real_escape_string($data[7]);
        $avalia_localizacao = $conexao->real_escape_string($data[8]);
        $avalia_comodidades = $conexao->real_escape_string($data[9]);
        $bem_recebido = $conexao->real_escape_string($data[10]);
        $correspondeu_expectativas = $conexao->real_escape_string($data[11]);
        $aspetos_melhorar = $conexao->real_escape_string($data[12]);
        $recomendaria = $conexao->real_escape_string($data[13]);
        $gostaria_voltar = $conexao->real_escape_string($data[14]);
        $comentarios = $conexao->real_escape_string($data[15]);

        $sql = "INSERT INTO avaliacoes_import 
            (nome_completo, email, data_estadia, experiencia_geral, gostou, avalia_ambiente, avalia_conforto, avalia_limpeza, avalia_localizacao, avalia_comodidades, bem_recebido, correspondeu_expectativas, aspetos_melhorar, recomendaria, gostaria_voltar, comentarios)
            VALUES
            ('$nome_completo', '$email', '$data_estadia', '$experiencia_geral', '$gostou', '$avalia_ambiente', '$avalia_conforto', '$avalia_limpeza', '$avalia_localizacao', '$avalia_comodidades', '$bem_recebido', '$correspondeu_expectativas', '$aspetos_melhorar', '$recomendaria', '$gostaria_voltar', '$comentarios')";

        if (!$conexao->query($sql)) {
            echo "Erro ao inserir linha: " . $conexao->error . "<br>";
        }
    }
    fclose($handle);
    echo "Importação concluída!";
} else {
    echo "Não foi possível abrir o ficheiro CSV.";
}
?>
