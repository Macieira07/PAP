<?php
/*
 * ============================================================
 *   API Verificar Disponibilidade - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica e conexão)
 *     - JSON (comunicação com frontend)
 *
 *   Bibliotecas e Frameworks:
 *     - PHPMailer (envio de emails, backend - se necessário)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (includes, headers)
 *     2. Receção e validação dos dados do frontend
 *     3. Consulta SQL para verificar disponibilidade
 *     4. Resposta JSON para o frontend
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
// Ativar a exibição de erros para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir a conexão ao banco de dados
include '../conexao.php';

// Recebe os dados do frontend (datas de check-in e check-out)
$data = json_decode(file_get_contents('php://input'), true);

// Verifica se os dados foram recebidos corretamente
if (!$data) {
    echo json_encode(['error' => 'Erro ao ler os dados JSON.']);
    exit;
}

// Extrai as variáveis de check-in e check-out
$checkIn = $data['checkIn'];
$checkOut = $data['checkOut'];

// Prepara a consulta SQL para verificar a disponibilidade
$sql = '
    SELECT COUNT(*) AS reservas
    FROM reservas
    WHERE (R_data_checkin < ? AND R_data_checkout > ?)
';

// Prepara a consulta
$stmt = $conexao->prepare($sql);

// Verifica se a preparação da consulta foi bem-sucedida
if ($stmt === false) {
    echo json_encode(['error' => 'Erro na consulta SQL.']);
    exit;
}

// Vincula os parâmetros de data (o formato deve ser YYYY-MM-DD)
$stmt->bind_param('ss', $checkOut, $checkIn);

// Executa a consulta
$stmt->execute();

// Obtém o resultado
$stmt->bind_result($reservas);
$stmt->fetch();

// Verifica se há reservas durante o período
if ($reservas > 0) {
    echo json_encode([
        'available' => false,
        'message' => '<span style="color: red; font-weight: bold;">Lamentamos, mas as datas selecionadas já estão reservadas. Por favor, tente outras datas.</span>'
    ]);
} else {
    echo json_encode([
        'available' => true,
        'message' => '<span style="color: green; font-weight: bold;">As datas selecionadas estão disponíveis! <a href="login1/pagina_login.php" style="color: darkgreen; text-decoration: underline; font-weight: bold;">Reservar Agora</a></span>'
    ]);
}
// Fecha a declaração e a conexão
$stmt->close();
$conexao->close();
?>
