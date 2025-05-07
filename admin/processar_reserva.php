<?php
require __DIR__ . '/../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Envio inválido.');
}

// Capturar dados
$data_checkin     = $_POST['data_checkin'];
$data_checkout    = $_POST['data_checkout'];
$id_casa          = (int)$_POST['id_casa'];
$id_hospede       = (int)$_POST['id_hospede'];
$num_hospedes     = max(1, min(10, (int)$_POST['num_hospedes']));
$metodo_pagamento = $_POST['metodo_pagamento'];
$estado           = $_POST['estado'];

// Validar datas
$ci = new DateTime($data_checkin);
$co = new DateTime($data_checkout);
if ($co <= $ci) {
    die('Check-out deve ser posterior ao check-in.');
}

// Buscar preço da casa
$stmt = $conexao->prepare("SELECT C_preco_noite FROM casas WHERE C_id_casa = ?");
$stmt->bind_param("i", $id_casa);
$stmt->execute();
$stmt->bind_result($preco_noite);
if (!$stmt->fetch()) {
    die('Casa inválida.');
}
$stmt->close();

// Calcular total
$dias        = $co->diff($ci)->days;
$preco_total = $dias * $preco_noite;

// Verificar disponibilidade
$stmt = $conexao->prepare("
    SELECT COUNT(*) FROM reservas
    WHERE R_id_casa = ? 
      AND (
        (R_data_checkin < ? AND R_data_checkout > ?)
        OR (R_data_checkin < ? AND R_data_checkout > ?)
        OR (R_data_checkin >= ? AND R_data_checkout <= ?)
      )
");
$stmt->bind_param(
    "issssss",
    $id_casa,
    $data_checkout, $data_checkout,
    $data_checkin,  $data_checkin,
    $data_checkin,  $data_checkout
);
$stmt->execute();
$stmt->bind_result($ocupadas);
$stmt->fetch();
$stmt->close();

if ($ocupadas > 0) {
    die('Já existe reserva para esse período.');
}

// Buscar nome do hóspede para a notificação
$stmt = $conexao->prepare("SELECT H_nome, H_apelido FROM hospedes WHERE H_id_hospede = ?");
$stmt->bind_param("i", $id_hospede);
$stmt->execute();
$stmt->bind_result($nome_hospede, $apelido_hospede);
$stmt->fetch();
$stmt->close();

// Buscar nome da casa para a notificação
$stmt = $conexao->prepare("SELECT C_nome FROM casas WHERE C_id_casa = ?");
$stmt->bind_param("i", $id_casa);
$stmt->execute();
$stmt->bind_result($nome_casa);
$stmt->fetch();
$stmt->close();

// Iniciar transação para garantir consistência
$conexao->begin_transaction();

try {
    // Inserir reserva
    $stmt = $conexao->prepare("
        INSERT INTO reservas
          (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout,
           R_num_hospedes, R_preco_total, R_metodo_pagamento, R_estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "iissidss",
        $id_hospede,
        $id_casa,
        $data_checkin,
        $data_checkout,
        $num_hospedes,
        $preco_total,
        $metodo_pagamento,
        $estado
    );

    if (!$stmt->execute()) {
        throw new Exception("Erro ao criar reserva: " . $stmt->error);
    }
    $reserva_id = $stmt->insert_id;
    $stmt->close();

    // Se a reserva foi criada como confirmada, atualizar o saldo
    if ($estado == 'confirmada') {
        $stmt = $conexao->prepare("UPDATE conta SET C_valor = C_valor + ? WHERE C_id_conta = 1");
        $stmt->bind_param("d", $preco_total);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao atualizar saldo: " . $stmt->error);
        }
        $stmt->close();
    }

    // Criar notificação para a nova reserva
    $nome_completo = trim($nome_hospede . ' ' . ($apelido_hospede ?? ''));
    $data_formatada = date('d/m/Y', strtotime($data_checkin));
    $mensagem = "Nova reserva: {$nome_completo} reservou {$nome_casa} para {$data_formatada}";
    $tipo = "reserva";
    
    $stmt = $conexao->prepare("
        INSERT INTO notificacoes
          (tipo, mensagem, lida)
        VALUES (?, ?, 0)
    ");
    $stmt->bind_param("ss", $tipo, $mensagem);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao criar notificação: " . $stmt->error);
    }
    $stmt->close();

    // Confirmar todas as operações
    $conexao->commit();

    // Redirecionar para página de sucesso
    header("Location: reserva_sucesso.php?id={$reserva_id}");
    exit;

} catch (Exception $e) {
    // Reverter em caso de erro
    $conexao->rollback();
    die($e->getMessage());
}
?>