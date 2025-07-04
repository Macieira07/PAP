<?php
/*
 * ============================================================
 *   API Reservas - Verificação de Disponibilidade - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica e conexão)
 *     - JSON (comunicação com frontend)
 *
 *   Bibliotecas e Frameworks:
 *     - PDO (acesso à base de dados)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (headers, includes)
 *     2. Receção e validação dos parâmetros (GET)
 *     3. Consulta SQL para verificar reservas
 *     4. Resposta JSON para o frontend
 *     5. Tratamento de erros
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require_once __DIR__ . '/../railway/conexao.php'; // Caminho absoluto mais seguro

// ===================== 2. Receção e Validação dos Parâmetros =====================
$casaId = $_GET['casa_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$casaId || !$date) {
    echo json_encode(['isBooked' => false]);
    exit;
}

// ===================== 3. Consulta SQL para Verificar Reservas =====================
try {
    $sql = "SELECT COUNT(*) as total 
            FROM reservas 
            WHERE R_id_casa = :casa_id 
            AND :date BETWEEN R_data_checkin AND DATE_SUB(R_data_checkout, INTERVAL 1 DAY)
            AND R_estado = 'confirmada'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':casa_id', $casaId, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['isBooked' => $result['total'] > 0]);

// ===================== 5. Tratamento de Erros =====================
} catch (PDOException $e) {
    error_log("Erro no reservas.php: " . $e->getMessage());
    echo json_encode(['isBooked' => false, 'error' => 'Erro no servidor']);
}
?>