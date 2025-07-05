<?php
require '../../conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario_id = $_POST['funcionario_id'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $motivo = $_POST['motivo'] ?? null;
    
    $erros = [];
    
    if (empty($funcionario_id)) $erros[] = "Selecione um funcionário";
    if (empty($tipo)) $erros[] = "Selecione o tipo de ausência";
    if (empty($data_inicio)) $erros[] = "Data de início é obrigatória";
    if (empty($data_fim)) $erros[] = "Data de fim é obrigatória";
    
    if ($tipo === 'Falta' && empty($motivo)) {
        $erros[] = "Motivo é obrigatório para faltas";
    }
    
    // Validar datas
    if (empty($erros)) {
        try {
            $inicio = new DateTime($data_inicio);
            $fim = new DateTime($data_fim);
            
            if ($fim < $inicio) {
                $erros[] = "Data de fim não pode ser anterior à data de início";
            }
            
            // Validação específica para férias
            if ($tipo === 'Férias') {
                $dias = $inicio->diff($fim)->days + 1;
                if ($dias != 15) {
                    $erros[] = "O período de férias deve ser exatamente 15 dias";
                }
                
                // Verificar se já existem férias marcadas para o mesmo período
                $stmt = $conexao->prepare("SELECT F_id_ausencia FROM ferias_ausencias 
                                         WHERE F_id_funcionario = ? 
                                         AND tipo_ausencia = 'Férias'
                                         AND ((data_inicio BETWEEN ? AND ?) OR (data_fim BETWEEN ? AND ?))");
                $stmt->bind_param("issss", $funcionario_id, $data_inicio, $data_fim, $data_inicio, $data_fim);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows > 0) {
                    $erros[] = "Já existem férias marcadas para este funcionário no período selecionado";
                }
            }
        } catch (Exception $e) {
            $erros[] = "Formato de data inválido";
        }
    }
    
    if (empty($erros)) {
        try {
            $stmt = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, tipo_ausencia, data_inicio, data_fim, motivo) 
                                      VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $funcionario_id, $tipo, $data_inicio, $data_fim, $motivo);
            
            if ($stmt->execute()) {
                if (isset($_GET['modal'])) {
                    echo 'OK';
                    exit;
                }
                
                $_SESSION['mensagem'] = "Registro de ausência adicionado com sucesso!";
                $_SESSION['tipo_mensagem'] = "sucesso";
                header("Location: funcionarios.php");
                exit;
            }
        } catch (Exception $e) {
            $erros[] = "Erro ao adicionar registro: " . $e->getMessage();
        }
    }
    
    if (isset($_GET['modal'])) {
        echo '<div class="mensagem erro">' . implode('<br>', $erros) . '</div>';
    } else {
        $_SESSION['mensagem'] = implode('<br>', $erros);
        $_SESSION['tipo_mensagem'] = "erro";
        header("Location: funcionarios.php");
        exit;
    }
}

if (isset($_GET['modal'])) {
    ?>
    <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">
        <i class="fas fa-calendar-plus"></i> Adicionar Férias/Falta
    </h2>
    
    <form method="post" id="formFeriasFalta" style="display:flex; flex-direction:column; gap:16px;">
        <div class="form-group">
            <label for="funcionario_id">Funcionário*</label>
            <select id="funcionario_id" name="funcionario_id" required>
                <option value="">Selecione...</option>
                <?php
                $result = $conexao->query("SELECT F_id_funcionario, F_nome FROM funcionarios ORDER BY F_nome");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['F_id_funcionario']}'>{$row['F_nome']}</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="tipo">Tipo de Ausência*</label>
            <select id="tipo" name="tipo" required onchange="mostrarCamposAusencia()">
                <option value="">Selecione...</option>
                <option value="Férias">Férias</option>
                <option value="Falta">Falta</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="data_inicio">Data de Início*</label>
            <input type="date" id="data_inicio" name="data_inicio" required>
        </div>
        
        <div class="form-group">
            <label for="data_fim">Data de Fim*</label>
            <input type="date" id="data_fim" name="data_fim" required>
        </div>
        
        <div class="form-group" id="divMotivo" style="display:none;">
            <label for="motivo">Motivo*</label>
            <input type="text" id="motivo" name="motivo" placeholder="Informe o motivo da falta">
        </div>
        
        <div id="resumoFerias" style="display:none; background:#e3f6fd; padding:10px; border-radius:5px; margin-bottom:15px;">
            <p><strong>Período de Férias:</strong> <span id="periodoFerias"></span></p>
            <p>O período de férias será automaticamente ajustado para 15 dias.</p>
        </div>
        
        <button type="submit" class="button button-success">
            <i class="fas fa-save"></i> Salvar
        </button>
    </form>
    
    <script>
    function mostrarCamposAusencia() {
        const tipo = document.getElementById('tipo').value;
        const divMotivo = document.getElementById('divMotivo');
        const resumoFerias = document.getElementById('resumoFerias');
        
        if (tipo === 'Falta') {
            divMotivo.style.display = 'block';
            resumoFerias.style.display = 'none';
            document.getElementById('motivo').required = true;
        } else if (tipo === 'Férias') {
            divMotivo.style.display = 'none';
            resumoFerias.style.display = 'block';
            document.getElementById('motivo').required = false;
            atualizarResumoFerias();
        } else {
            divMotivo.style.display = 'none';
            resumoFerias.style.display = 'none';
        }
    }
    
    function atualizarResumoFerias() {
        const dataInicio = document.getElementById('data_inicio').value;
        if (dataInicio) {
            const inicio = new Date(dataInicio);
            const fim = new Date(inicio);
            fim.setDate(inicio.getDate() + 14);
            
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            document.getElementById('periodoFerias').textContent = 
                inicio.toLocaleDateString('pt-PT', options) + ' a ' + 
                fim.toLocaleDateString('pt-PT', options);
            
            // Auto-preenche a data de fim
            document.getElementById('data_fim').value = fim.toISOString().split('T')[0];
        }
    }
    
    document.getElementById('data_inicio').addEventListener('change', function() {
        if (document.getElementById('tipo').value === 'Férias') {
            atualizarResumoFerias();
        }
    });
    
    // Inicializa campos ao carregar
    document.addEventListener('DOMContentLoaded', function() {
        const tipoSelect = document.getElementById('tipo');
        if (tipoSelect.value) {
            mostrarCamposAusencia();
        }
    });
    </script>
    <?php
    exit;
}

header("Location: funcionarios.php");
exit;
?>