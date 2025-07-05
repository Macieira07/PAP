<?php
require '../../conexao.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: funcionarios.php");
    exit;
}

$id = $_GET['id'];

// Buscar dados do funcionário
$stmt = $conexao->prepare("SELECT * FROM funcionarios WHERE F_id_funcionario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: funcionarios.php");
    exit;
}

$funcionario = $resultado->fetch_assoc();

// Buscar turno atual
$stmt = $conexao->prepare("SELECT * FROM turnos WHERE F_id_funcionario = ? ORDER BY data_inicio DESC LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$turno = $stmt->get_result()->fetch_assoc();

// Buscar férias/faltas
$stmt = $conexao->prepare("SELECT * FROM ferias_ausencias WHERE F_id_funcionario = ? ORDER BY data_inicio DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$feriasFaltas = $stmt->get_result();

if (isset($_GET['modal'])) {
    ?>
    <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">
        <i class="fas fa-user"></i> Detalhes do Funcionário
    </h2>
    
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div>
            <h3>Informações Pessoais</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div><strong>Nome:</strong> <?= htmlspecialchars($funcionario['F_nome']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($funcionario['F_email']) ?></div>
                <div><strong>Cargo:</strong> <?= htmlspecialchars($funcionario['F_cargo']) ?></div>
                <div><strong>Telefone:</strong> <?= htmlspecialchars($funcionario['F_telefone']) ?></div>
                <div><strong>Data de Contratação:</strong> <?= date('d/m/Y', strtotime($funcionario['F_data_contratacao'])) ?></div>
            </div>
        </div>
        
        <?php if ($turno): ?>
        <div>
            <h3>Turno Atual</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div><strong>Tipo:</strong> <?= htmlspecialchars($turno['turno']) ?></div>
                <div><strong>Horário:</strong> <?= htmlspecialchars($turno['T_inicio']) ?> - <?= htmlspecialchars($turno['T_fim']) ?></div>
                <div><strong>Data Início:</strong> <?= date('d/m/Y', strtotime($turno['data_inicio'])) ?></div>
                <div><strong>Data Fim:</strong> <?= $turno['data_fim'] ? date('d/m/Y', strtotime($turno['data_fim'])) : 'N/A' ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($feriasFaltas->num_rows > 0): ?>
        <div>
            <h3>Histórico de Férias/Faltas</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f0f4ff;">
                        <th style="padding: 8px; border: 1px solid #ddd;">Tipo</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Início</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Fim</th>
                        <th style="padding: 8px; border: 1px solid #ddd;">Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ff = $feriasFaltas->fetch_assoc()): ?>
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <span class="badge <?= $ff['tipo_ausencia'] === 'Férias' ? 'badge-ferias' : 'badge-falta' ?>">
                                <?= htmlspecialchars($ff['tipo_ausencia']) ?>
                            </span>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?= date('d/m/Y', strtotime($ff['data_inicio'])) ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?= date('d/m/Y', strtotime($ff['data_fim'])) ?></td>
                        <td style="padding: 8px; border: 1px solid #ddd;"><?= htmlspecialchars($ff['motivo'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 20px; text-align: center;">
        <button onclick="fecharModal('modalFuncionario')" class="button">
            <i class="fas fa-times"></i> Fechar
        </button>
    </div>
    <?php
    exit;
}

header("Location: funcionarios.php");
exit;
?>