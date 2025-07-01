<?php
require '../../conexao.php';

$id = $_GET['id'];

$tipos_manutencao = [
    "Canalizações (canos, torneiras, autoclismo)" => "Reparação ou substituição de canos, torneiras e autoclismos.",
    "Instalações elétricas (lâmpadas, tomadas, quadro elétrico)" => "Substituição de lâmpadas, tomadas e verificação do quadro elétrico.",
    "Eletrodomésticos (frigorífico, máquina de lavar, micro-ondas)" => "Revisão e reparação de eletrodomésticos essenciais.",
    "Ar-condicionado e aquecimento" => "Limpeza de filtros, verificação de gás e funcionamento geral.",
    "Fechaduras e chaves (portas e janelas)" => "Troca de fechaduras, cópia de chaves e lubrificação.",
    "Pintura e retoques nas paredes" => "Pintura de paredes e retoques em áreas danificadas.",
    "Mobiliário (reparação ou substituição de peças danificadas)" => "Reparação ou substituição de móveis danificados.",
    "Jardinagem (relva, arbustos, rega)" => "Corte de relva, poda de arbustos e verificação do sistema de rega.",
    "Piscina (tratamento da água, limpeza de filtros)" => "Tratamento químico da água e limpeza dos filtros da piscina.",
    "Churrasqueira (limpeza e manutenção da estrutura)" => "Limpeza da estrutura e verificação da integridade da churrasqueira.",
    "Iluminação exterior" => "Substituição de lâmpadas e manutenção de circuitos exteriores.",
    "Extintores e detetores de fumo/gás" => "Verificação da validade e testes de funcionamento dos equipamentos.",
    "Câmaras de segurança e sistemas de alarme" => "Testes e manutenção dos equipamentos de segurança.",
    "Grades ou vedações de segurança" => "Verificação da integridade e reparações necessárias.",
    "Verificações periódicas agendadas (mensais ou trimestrais)" => "Inspeções regulares de todos os sistemas e equipamentos.",
    "Substituição de baterias (comando de portão, detetores de fumo, etc.)" => "Troca preventiva de baterias em dispositivos críticos.",
    "Testes de funcionamento geral antes da chegada de hóspedes" => "Testes e ajustes finais de todos os sistemas para garantir conforto e segurança."
];

if (isset($_GET['modal'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_casa = $_POST['id_casa'];
        $tipo = $_POST['tipo'];
        $descricao = $_POST['descricao'];
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $custo = $_POST['custo'];
        $pago = isset($_POST['pago']) ? 1 : 0;
        
        $stmt = $conexao->prepare("UPDATE manutencao SET M_id_casa=?, M_tipo=?, M_descricao=?, M_data_inicio=?, M_data_fim=?, M_custo=?, M_pago=? WHERE M_id_manutencao=?");
        $stmt->bind_param("issssdii", $id_casa, $tipo, $descricao, $data_inicio, $data_fim, $custo, $pago, $id);
        $stmt->execute();
        
        echo 'OK';
        exit;
    }
    
    $stmt = $conexao->prepare("SELECT * FROM manutencao WHERE M_id_manutencao=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $manutencao = $resultado->fetch_assoc();
    
    $casas = $conexao->query("SELECT C_id_casa, C_nome FROM casas");
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Editar Manutenção</h2>
        <form method="post" id="formWizardManutencao" data-id="<?= $manutencao['M_id_manutencao'] ?>" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Etapa 1 -->
            <div class="wizard-step" id="wizardStep1">
                <label style="display:flex; flex-direction:column; gap:4px;">Casa:
                    <select name="id_casa" required>
                        <?php while ($casa = $casas->fetch_assoc()): ?>
                            <option value="<?= $casa['C_id_casa'] ?>" <?= $manutencao['M_id_casa'] == $casa['C_id_casa'] ? 'selected' : '' ?>><?= htmlspecialchars($casa['C_nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Tipo de Manutenção:
                    <select name="tipo" id="tipo" onchange="atualizarDescricao()" required>
                        <?php foreach ($tipos_manutencao as $tipo => $desc): ?>
                            <option value="<?= htmlspecialchars($tipo) ?>" <?= $manutencao['M_tipo'] == $tipo ? 'selected' : '' ?>><?= htmlspecialchars($tipo) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea id="descricao" name="descricao" rows="4" style="resize:vertical; min-height:60px; max-width:100%;"><?= htmlspecialchars($manutencao['M_descricao']) ?></textarea>
                </label>
                <button type="button" id="btnWizardProximoManutencao" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Etapa 2 -->
            <div class="wizard-step" id="wizardStep2" style="display:none;">
                <label style="display:flex; flex-direction:column; gap:4px;">Data Início:
                    <input type="date" name="data_inicio" value="<?= $manutencao['M_data_inicio'] ?>" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Data Fim:
                    <input type="date" name="data_fim" value="<?= $manutencao['M_data_fim'] ?>">
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Custo (€):
                    <input type="number" name="custo" step="0.01" value="<?= $manutencao['M_custo'] ?>" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">
                    <input type="checkbox" name="pago" value="1" <?= $manutencao['M_pago'] ? 'checked' : '' ?>> Pago
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorManutencao" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Atualizar</button>
                </div>
            </div>
        </form>
    </div>
    <script>
    function atualizarDescricao() {
        const tipoSelect = document.getElementById("tipo");
        const descricaoInput = document.getElementById("descricao");
        const descricoes = <?= json_encode($tipos_manutencao) ?>;
        descricaoInput.value = descricoes[tipoSelect.value] || "";
    }
    
    // Inicializa o wizard
    document.getElementById('btnWizardProximoManutencao').onclick = function() {
        var id_casa = document.querySelector('[name=id_casa]').value;
        var tipo = document.querySelector('[name=tipo]').value;
        
        if (!id_casa || !tipo) {
            alert('Preencha todos os campos obrigatórios.');
            return;
        }
        
        document.getElementById('wizardStep1').style.display = 'none';
        document.getElementById('wizardStep2').style.display = 'block';
    };
    
    document.getElementById('btnWizardAnteriorManutencao').onclick = function() {
        document.getElementById('wizardStep2').style.display = 'none';
        document.getElementById('wizardStep1').style.display = 'block';
    };
    
    // Atualiza a descrição inicial
    window.onload = function() {
        atualizarDescricao();
    };
    </script>
    <?php
    exit;
}

// Restante do código para a versão não-modal (se necessário)
?>