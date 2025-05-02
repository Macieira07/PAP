<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarLogin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$erro = '';
$reserva = null;
$casas = [];
$servicos = [];

try {
    $pdo = conexao();
    
    // Carregar dados da reserva
    $stmt = $pdo->prepare("SELECT * FROM reservas WHERE R_id_reserva = ?");
    $stmt->execute([$id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reserva) {
        header('Location: listar.php');
        exit();
    }
    
    // Carregar casas disponíveis
    $casas = $pdo->query("SELECT * FROM casas WHERE C_estado = 'disponível' OR C_id_casa = " . $reserva['R_id_casa'])->fetchAll(PDO::FETCH_ASSOC);
    
    // Carregar serviços disponíveis
    $servicosDisponiveis = $pdo->query("SELECT * FROM servicos WHERE S_disponivel = 'Sim'")->fetchAll(PDO::FETCH_ASSOC);
    
    // Carregar serviços da reserva
    $servicosReserva = $pdo->prepare("SELECT * FROM reservas_servicos WHERE RS_id_reserva = ?");
    $servicosReserva->execute([$id]);
    $servicosReserva = $servicosReserva->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar formulário
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_casa = $_POST['id_casa'];
        $id_hospede = $_POST['id_hospede'];
        $data_checkin = $_POST['data_checkin'];
        $data_checkout = $_POST['data_checkout'];
        $num_hospedes = $_POST['num_hospedes'];
        $estado = $_POST['estado'];
        $metodo_pagamento = $_POST['metodo_pagamento'];
        $observacoes = $_POST['observacoes'];
        $servicos_selecionados = $_POST['servicos'] ?? [];
        
        // Validar datas
        if (new DateTime($data_checkin) >= new DateTime($data_checkout)) {
            $erro = "A data de check-out deve ser posterior à data de check-in!";
        } else {
            // Calcular preço total
            $stmt = $pdo->prepare("SELECT C_preco_noite FROM casas WHERE C_id_casa = ?");
            $stmt->execute([$id_casa]);
            $preco_noite = $stmt->fetchColumn();
            
            $dias = (new DateTime($data_checkin))->diff(new DateTime($data_checkout))->days;
            $preco_total = $dias * $preco_noite;
            
            // Adicionar serviços ao preço total
            foreach ($servicos_selecionados as $id_servico) {
                $stmt = $pdo->prepare("SELECT S_preco FROM servicos WHERE S_id_servico = ?");
                $stmt->execute([$id_servico]);
                $preco_servico = $stmt->fetchColumn();
                $preco_total += $preco_servico;
            }
            
            // Atualizar reserva
            $stmt = $pdo->prepare("UPDATE reservas SET 
                                 R_id_casa = ?, 
                                 R_id_hospede = ?, 
                                 R_data_checkin = ?, 
                                 R_data_checkout = ?, 
                                 R_num_hospedes = ?, 
                                 R_preco_total = ?, 
                                 R_estado = ?, 
                                 R_metodo_pagamento = ?, 
                                 R_observacoes = ? 
                                 WHERE R_id_reserva = ?");
            $stmt->execute([
                $id_casa, $id_hospede, $data_checkin, $data_checkout, 
                $num_hospedes, $preco_total, $estado, $metodo_pagamento, 
                $observacoes, $id
            ]);
            
            // Atualizar serviços
            $pdo->prepare("DELETE FROM reservas_servicos WHERE RS_id_reserva = ?")->execute([$id]);
            
            foreach ($servicos_selecionados as $id_servico) {
                $stmt = $pdo->prepare("SELECT S_preco FROM servicos WHERE S_id_servico = ?");
                $stmt->execute([$id_servico]);
                $preco_servico = $stmt->fetchColumn();
                
                $stmt = $pdo->prepare("INSERT INTO reservas_servicos (RS_id_reserva, RS_id_servico, RS_preco_unitario) VALUES (?, ?, ?)");
                $stmt->execute([$id, $id_servico, $preco_servico]);
            }
            
            $_SESSION['mensagem'] = "Reserva atualizada com sucesso!";
            header('Location: detalhes.php?id=' . $id);
            exit();
        }
    }
} catch (PDOException $e) {
    $erro = "Erro ao atualizar reserva: " . $e->getMessage();
}

$titulo = "Editar Reserva - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Editar Reserva #<?= $reserva['R_id_reserva'] ?></h1>
</div>

<div class="form-container">
    <?php if ($erro): ?>
        <div class="error-message"><?= $erro ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label for="id_casa">Casa *</label>
                <select id="id_casa" name="id_casa" required>
                    <?php foreach ($casas as $casa): ?>
                        <option value="<?= $casa['C_id_casa'] ?>" <?= $casa['C_id_casa'] == $reserva['R_id_casa'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($casa['C_nome']) ?> (<?= number_format($casa['C_preco_noite'], 2, ',', '.') ?> €/noite)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="id_hospede">Hóspede *</label>
                <input type="text" id="id_hospede" name="id_hospede" value="<?= $reserva['R_id_hospede'] ?>" required>
                <small><a href="#" id="buscar_hospede">Buscar hóspede</a></small>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="data_checkin">Check-in *</label>
                <input type="date" id="data_checkin" name="data_checkin" value="<?= $reserva['R_data_checkin'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="data_checkout">Check-out *</label>
                <input type="date" id="data_checkout" name="data_checkout" value="<?= $reserva['R_data_checkout'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="num_hospedes">Nº de Hóspedes *</label>
                <input type="number" id="num_hospedes" name="num_hospedes" min="1" value="<?= $reserva['R_num_hospedes'] ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="estado">Estado *</label>
                <select id="estado" name="estado" required>
                    <option value="pendente" <?= $reserva['R_estado'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="confirmada" <?= $reserva['R_estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="cancelada" <?= $reserva['R_estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    <option value="concluída" <?= $reserva['R_estado'] == 'concluída' ? 'selected' : '' ?>>Concluída</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="metodo_pagamento">Método de Pagamento</label>
                <select id="metodo_pagamento" name="metodo_pagamento">
                    <option value="">Selecione...</option>
                    <option value="Cartão" <?= $reserva['R_metodo_pagamento'] == 'Cartão' ? 'selected' : '' ?>>Cartão</option>
                    <option value="Transferência" <?= $reserva['R_metodo_pagamento'] == 'Transferência' ? 'selected                <option value="MB WAY" <?= $reserva['R_metodo_pagamento'] == 'MB WAY' ? 'selected' : '' ?>>MB WAY</option>
                <option value="Dinheiro" <?= $reserva['R_metodo_pagamento'] == 'Dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Serviços Adicionais</label>
        <div class="servicos-grid">
            <?php foreach ($servicosDisponiveis as $servico): ?>
            <div class="servico-item">
                <input type="checkbox" id="servico_<?= $servico['S_id_servico'] ?>" name="servicos[]" value="<?= $servico['S_id_servico'] ?>"
                    <?= in_array($servico['S_id_servico'], array_column($servicosReserva, 'RS_id_servico')) ? 'checked' : '' ?>>
                <label for="servico_<?= $servico['S_id_servico'] ?>">
                    <?= htmlspecialchars($servico['S_nome_servico']) ?> (<?= number_format($servico['S_preco'], 2, ',', '.') ?> €)
                </label>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="observacoes">Observações</label>
        <textarea id="observacoes" name="observacoes"><?= htmlspecialchars($reserva['R_observacoes']) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-salvar">
            <i class="fas fa-save"></i> Atualizar Reserva
        </button>
        <a href="detalhes.php?id=<?= $id ?>" class="btn-cancelar">
            <i class="fas fa-times"></i> Cancelar
        </a>
    </div>
</form></div><script> // Buscar hóspede document.getElementById('buscar_hospede').addEventListener('click', function(e) { e.preventDefault(); // Implementar lógica para buscar hóspede (pode usar um modal ou API) alert('Funcionalidade de busca de hóspede será implementada aqui'); }); </script><?php include '../includes/footer.php'; ?>