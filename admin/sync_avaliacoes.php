<?php
require_once __DIR__ . '../vendor/autoload.php';
require_once '../conexao.php';

// Configurações
$spreadsheetId = 'ID_DA_SUA_PLANILHA'; // Substitua pelo ID da sua planilha
$range = 'Respostas do Formulário 1!A:Q'; // Intervalo de dados

// Autenticação com Google Sheets API
function getClient() {
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets to MySQL Sync');
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY]);
    $client->setAuthConfig(__DIR__ . '/credentials.json'); // Arquivo baixado do Google Cloud
    $client->setAccessType('offline');
    return $client;
}

try {
    // Obter dados da planilha
    $client = getClient();
    $service = new Google_Service_Sheets($client);
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();

    if (empty($values)) {
        die("Nenhum dado encontrado na planilha.");
    }

    // Remover cabeçalho
    $header = array_shift($values);
    
    // Mapear colunas do Google Forms para campos da tabela
    $column_map = [
        0 => 'data_avaliacao', // Carimbo de data/hora
        1 => 'nome_completo',
        2 => 'email',
        3 => 'data_estadia',
        4 => 'experiencia_geral',
        5 => 'gostou',
        6 => 'avalia_ambiente',
        7 => 'avalia_conforto',
        8 => 'avalia_limpeza',
        9 => 'avalia_localizacao',
        10 => 'avalia_comodidades',
        11 => 'bem_recebido',
        12 => 'correspondeu_expectativas',
        13 => 'aspetos_melhorar',
        14 => 'recomendaria',
        15 => 'gostaria_voltar',
        16 => 'comentarios'
    ];

    // Processar cada linha
    $inserted = 0;
    foreach ($values as $row) {
        // Verificar se já existe no banco (para evitar duplicatas)
        $email = $row[2] ?? null;
        $data_estadia = $row[3] ?? null;
        
        if ($email && $data_estadia) {
            $stmt = $conexao->prepare("SELECT id FROM avaliacoes_import WHERE email = ? AND data_estadia = ?");
            $stmt->bind_param("ss", $email, $data_estadia);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                continue; // Já existe, pular
            }
        }

        // Preparar dados para inserção
        $data = [];
        foreach ($column_map as $index => $column) {
            $data[$column] = $row[$index] ?? null;
        }

        // Converter data/hora para formato MySQL
        if (!empty($data['data_avaliacao'])) {
            $data['data_avaliacao'] = date('Y-m-d H:i:s', strtotime($data['data_avaliacao']));
        }
        
        if (!empty($data['data_estadia'])) {
            $data['data_estadia'] = date('Y-m-d', strtotime($data['data_estadia']));
        }

        // Inserir no banco de dados
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $types = str_repeat("s", count($data));
        
        $stmt = $conexao->prepare("INSERT INTO avaliacoes_import ($columns) VALUES ($placeholders)");
        $stmt->bind_param($types, ...array_values($data));
        
        if ($stmt->execute()) {
            $inserted++;
        }
    }

    echo "Sincronização concluída. $inserted novos registros adicionados.";

} catch (Exception $e) {
    die("Erro durante a sincronização: " . $e->getMessage());
}
?>