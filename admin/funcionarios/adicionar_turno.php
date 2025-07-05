<?php
require '../../conexao.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario_id = $_POST['funcionario_id'];
    $tipo_turno = $_POST['tipo_turno'];
    $horario_inicio = $_POST['horario_inicio'];
    $horario_fim = $_POST['horario_fim'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'] ?? $data_inicio;
    
    $erros = [];
    
    if (empty($funcionario_id)) $erros[] = "Selecione um funcionário";
    if (empty($tipo_turno)) $erros[] = "Selecione o tipo de turno";
    if (empty($horario_inicio)) $erros[] = "Horário de início é obrigatório";
    if (empty($horario_fim)) $erros[] = "Horário de fim é obrigatório";
    if (empty($data_inicio)) $erros[] = "Data de início é obrigatória";
    
    // Validar duração do turno (8 horas)
    if (empty($erros)) {
        $inicio = new DateTime($data_inicio . ' ' . $horario_inicio);
        $fim = new DateTime($data_inicio . ' ' . $horario_fim);
        
        // Se o horário de fim for menor que o de início, assume-se que é no dia seguinte
        if ($fim < $inicio) {
            $fim->modify('+1 day');
        }
        
        $intervalo = $inicio->diff($fim);
        $horas = $intervalo->h + ($intervalo->days * 24);
        
        if ($horas != 8) {
            $erros[] = "O turno deve ter exatamente 8 horas";
        }
    }
    
    if (empty($erros)) {
        $stmt = $conexao->prepare("INSERT INTO turnos (F_id_funcionario, turno, data_inicio, data_fim, T_inicio, T_fim) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $funcionario_id, $tipo_turno, $data_inicio, $data_fim, $horario_inicio, $horario_fim);
        
        if ($stmt->execute()) {
            if (isset($_GET['modal'])) {
                echo 'OK';
                exit;
            }
            
            $_SESSION['mensagem'] = "Turno adicionado com sucesso!";
            $_SESSION['tipo_mensagem'] = "sucesso";
            header("Location: funcionarios.php");
            exit;
        } else {
            $erros[] = "Erro ao adicionar turno: " . $conexao->error;
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
        <i class="fas fa-clock"></i> Adicionar Turno
    </h2>
    
    <form method="post" id="formTurno" style="display:flex; flex-direction:column; gap:16px;">
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
            <label for="tipo_turno">Tipo de Turno*</label>
            <select id="tipo_turno" name="tipo_turno" required onchange="definirHorarioPadrao()">
                <option value="">Selecione...</option>
                <option value="Manhã">Manhã (08:00 - 16:00)</option>
                <option value="Tarde">Tarde (16:00 - 00:00)</option>
                <option value="Noite">Noite (00:00 - 08:00)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="horario_inicio">Horário de Início*</label>
            <input type="time" id="horario_inicio" name="horario_inicio" required onchange="calcularHorarioFim()">
        </div>
        
        <div class="form-group">
            <label for="horario_fim">Horário de Fim*</label>
            <input type="time" id="horario_fim" name="horario_fim" required readonly>
        </div>
        
        <div class="form-group">
            <label for="data_inicio">Data de Início*</label>
            <input type="date" id="data_inicio" name="data_inicio" required>
        </div>
        
        <div class="form-group">
            <label for="data_fim">Data de Fim</label>
            <input type="date" id="data_fim" name="data_fim">
        </div>
        
        <button type="submit" class="button button-success">
            <i class="fas fa-save"></i> Salvar
        </button>
    </form>
    
    <script>
    function definirHorarioPadrao() {
        const tipo = document.getElementById('tipo_turno').value;
        let inicio = '';
        
        switch(tipo) {
            case 'Manhã': inicio = '08:00'; break;
            case 'Tarde': inicio = '16:00'; break;
            case 'Noite': inicio = '00:00'; break;
        }
        
        if (inicio) {
            document.getElementById('horario_inicio').value = inicio;
            calcularHorarioFim();
        }
    }
    
    function calcularHorarioFim() {
        const inicio = document.getElementById('horario_inicio').value;
        if (inicio) {
            const [hora, minuto] = inicio.split(':').map(Number);
            const fimHora = (hora + 8) % 24;
            const fimStr = `${fimHora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`;
            document.getElementById('horario_fim').value = fimStr;
        }
    }
    
    // Preenche a data atual como padrão
    document.addEventListener('DOMContentLoaded', function() {
        const hoje = new Date().toISOString().split('T')[0];
        document.getElementById('data_inicio').value = hoje;
    });
    </script>
    <?php
    exit;
}

header("Location: funcionarios.php");
exit;
?>