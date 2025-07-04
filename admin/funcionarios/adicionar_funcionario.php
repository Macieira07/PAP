<?php
require '../../conexao.php';
$mensagem = "";
$tipo_mensagem = "";
if (isset($_GET['modal'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validação de campos obrigatórios
        if (empty($_POST['nome']) || empty($_POST['email']) || empty($_POST['senha'])) {
            $mensagem = "Todos os campos obrigatórios devem ser preenchidos!";
            $tipo_mensagem = "erro";
        } else {
            // Sanitização de dados
            $nome = htmlspecialchars($_POST['nome']);
            $email = htmlspecialchars($_POST['email']);
            $senha = $_POST['senha'];
            $cargo = htmlspecialchars($_POST['cargo']);
            $telefone = htmlspecialchars($_POST['telefone']);
            $data_contratacao = date('Y-m-d'); // Data automática
            // Dados de turno
            $turno_inicio = $_POST['turno_inicio'] ?? null;
            // Dados de ausência
            $tipo_ausencia = $_POST['tipo_ausencia'] ?? '';
            $ferias_inicio = $_POST['ferias_inicio'] ?? null;
            $falta_inicio = $_POST['falta_inicio'] ?? null;
            $falta_fim = $_POST['falta_fim'] ?? null;
            $motivo_falta = isset($_POST['motivo_falta']) ? htmlspecialchars($_POST['motivo_falta']) : null;

            // Validação do formato do email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensagem = "Email inválido!";
                $tipo_mensagem = "erro";
            } elseif (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha)) {
                $mensagem = "A senha deve ter pelo menos 8 caracteres e uma letra maiúscula!";
                $tipo_mensagem = "erro";
            } elseif ($tipo_ausencia === 'Férias' && empty($ferias_inicio)) {
                $mensagem = "Selecione a data de início das férias.";
                $tipo_mensagem = "erro";
            } elseif ($tipo_ausencia === 'Falta' && (empty($falta_inicio) || empty($falta_fim) || empty($motivo_falta))) {
                $mensagem = "Preencha todos os campos de falta, incluindo o motivo.";
                $tipo_mensagem = "erro";
            } else {
                // Verificação se o email já existe no banco
                $stmt = $conexao->prepare("SELECT F_email FROM funcionarios WHERE F_email=?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows > 0) {
                    $mensagem = "Email já registrado!";
                    $tipo_mensagem = "erro";
                } else {
                    // Inserir o novo funcionário
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $conexao->prepare("INSERT INTO funcionarios (F_nome, F_email, F_senha, F_cargo, F_telefone, F_data_contratacao) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $nome, $email, $senha_hash, $cargo, $telefone, $data_contratacao);
                    $stmt->execute();

                    $funcionario_id = $conexao->insert_id;

                    // Inserir turno
                    if ($turno_inicio) {
                        $stmt_turno = $conexao->prepare("INSERT INTO turnos (F_id_funcionario, turno, data_inicio, data_fim) VALUES (?, ?, CURDATE(), CURDATE())");
                        $stmt_turno->bind_param("is", $funcionario_id, $turno_inicio);
                        $stmt_turno->execute();
                    }

                    // Inserir ausência (férias ou falta)
                    if ($tipo_ausencia === 'Férias' && $ferias_inicio) {
                        $ferias_fim = (new DateTime($ferias_inicio))->modify('+14 days')->format('Y-m-d');
                        // Verificar se já existem férias na mesma semana para este funcionário
                        $stmt_verificar = $conexao->prepare("
                            SELECT * FROM ferias_ausencias 
                            WHERE tipo_ausencia = 'Férias' 
                            AND F_id_funcionario = ?
                            AND (
                                (data_inicio <= ? AND data_fim >= ?)
                                OR (data_inicio <= ? AND data_fim >= ?)
                            )
                        ");
                        $stmt_verificar->bind_param("issss", $funcionario_id, $ferias_fim, $ferias_fim, $ferias_inicio, $ferias_inicio);
                        $stmt_verificar->execute();
                        $resultado_verificar = $stmt_verificar->get_result();
                        if ($resultado_verificar->num_rows > 0) {
                            $mensagem = "Já existem férias marcadas para este funcionário nesse período. Escolha outra data.";
                            $tipo_mensagem = "erro";
                        } else {
                            $tipo_ausencia_db = 'Férias';
                            $stmt_ferias = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, tipo_ausencia, data_inicio, data_fim) VALUES (?, ?, ?, ?)");
                            $stmt_ferias->bind_param("isss", $funcionario_id, $tipo_ausencia_db, $ferias_inicio, $ferias_fim);
                            $stmt_ferias->execute();
                        }
                    } elseif ($tipo_ausencia === 'Falta' && $falta_inicio && $falta_fim && $motivo_falta) {
                        $tipo_ausencia_db = 'Falta';
                        $stmt_falta = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, tipo_ausencia, data_inicio, data_fim, motivo) VALUES (?, ?, ?, ?, ?)");
                        $stmt_falta->bind_param("issss", $funcionario_id, $tipo_ausencia_db, $falta_inicio, $falta_fim, $motivo_falta);
                        $stmt_falta->execute();
                    }

                    if ($tipo_mensagem !== "erro") {
                        $mensagem = "Funcionário adicionado com sucesso!";
                        $tipo_mensagem = "sucesso";
                    }
                }
            }
        }
        if ($tipo_mensagem === "sucesso") {
            echo 'OK';
            exit;
        } else {
            // Mostra o formulário novamente com mensagem de erro
        }
    }
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Adicionar Funcionário</h2>
        <?php if ($mensagem): ?>
            <div class="mensagem <?= $tipo_mensagem ?>" style="margin-bottom:10px;"> <?= $mensagem ?> </div>
        <?php endif; ?>
        <form method="post" id="formFuncionario" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Passo 1 -->
            <div class="wizard-step" id="wizardStep1Funcionario">
                <label>Nome:<input type="text" name="nome" required></label>
                <label>Email:<input type="email" name="email" required></label>
                <label>Senha:<input type="password" name="senha" required></label>
                <label>Cargo:
                    <select name="cargo" required>
                        <option value="gerente">Gerente</option>
                        <option value="administrador">Administrador</option>
                        <option value="recepcionista">Recepcionista</option>
                        <option value="governanta">Governanta</option>
                        <option value="contabilista">Contabilista</option>
                    </select>
                </label>
                <label>Telefone:<input type="text" name="telefone"></label>
                <button type="button" id="btnWizardProximoFuncionario" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Passo 2 -->
            <div class="wizard-step" id="wizardStep2Funcionario" style="display:none;">
                <h3>Turno</h3>
                <label>Selecione o Turno:
                    <select name="turno_inicio">
                        <option value="08:00-16:00">Manhã (08:00 - 16:00)</option>
                        <option value="16:00-00:00">Tarde (16:00 - 00:00)</option>
                        <option value="00:00-08:00">Noite (00:00 - 08:00)</option>
                    </select>
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorFuncionario" class="atalho-btn">&larr; Anterior</button>
                    <button type="button" id="btnWizardProximoAusencia" class="atalho-btn">Próximo &rarr;</button>
                </div>
            </div>
            <!-- Passo 3: Ausência/Férias/Falta -->
            <div class="wizard-step" id="wizardStep3Funcionario" style="display:none;">
                <h3>Ausência Inicial</h3>
                <label>Tipo de Ausência:
                    <select name="tipo_ausencia" id="tipo_ausencia_select">
                        <option value="">Nenhuma</option>
                        <option value="Férias">Férias</option>
                        <option value="Falta">Falta</option>
                    </select>
                </label>
                <div id="feriasFields" style="display:none;">
                    <label>Data de Início das Férias:<input type="date" name="ferias_inicio"></label>
                    <p>O período de férias será automaticamente ajustado para 15 dias.</p>
                </div>
                <div id="faltaFields" style="display:none;">
                    <label>Data de Início da Falta:<input type="date" name="falta_inicio"></label>
                    <label>Data de Fim da Falta:<input type="date" name="falta_fim"></label>
                    <label>Motivo:<input type="text" name="motivo_falta" maxlength="100"></label>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorAusencia" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Salvar</button>
                </div>
            </div>
        </form>
    </div>
    <style>
        .mensagem.sucesso { background: #4CAF50; color: #fff; padding: 8px; border-radius: 4px; }
        .mensagem.erro { background: #f44336; color: #fff; padding: 8px; border-radius: 4px; }
    </style>
    <?php
    exit;
}
?>