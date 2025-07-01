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
            
            // Dados de turno e férias
            $turno_inicio = $_POST['turno_inicio'] ?? null;
            $ferias_inicio = $_POST['ferias_inicio'] ?? null;

            // Validação do formato do email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensagem = "Email inválido!";
                $tipo_mensagem = "erro";
            } elseif (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha)) {
                $mensagem = "A senha deve ter pelo menos 8 caracteres e uma letra maiúscula!";
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
                    $stmt = $conexao->prepare("INSERT INTO funcionarios (F_nome, F_email, F_senha, F_cargo, F_telefone) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssss", $nome, $email, $senha_hash, $cargo, $telefone);
                    $stmt->execute();

                    $funcionario_id = $conexao->insert_id;

                    // Inserir turno
                    if ($turno_inicio) {
                        $stmt_turno = $conexao->prepare("INSERT INTO turnos (F_id_funcionario, turno, data_inicio, data_fim) VALUES (?, ?, CURDATE(), CURDATE())");
                        $stmt_turno->bind_param("is", $funcionario_id, $turno_inicio);
                        $stmt_turno->execute();
                    }

                    // Inserir férias
                    if ($ferias_inicio) {
                        $ferias_fim = (new DateTime($ferias_inicio))->modify('+14 days')->format('Y-m-d');

                        // Verificar se já existem férias na mesma semana
                        $stmt_verificar = $conexao->prepare("
                            SELECT * FROM ferias_ausencias 
                            WHERE tipo_ausencia = 'Férias' 
                            AND (
                                WEEK(data_inicio, 1) = WEEK(?, 1) 
                                OR WEEK(data_fim, 1) = WEEK(?, 1)
                            )
                        ");
                        $stmt_verificar->bind_param("ss", $ferias_inicio, $ferias_fim);
                        $stmt_verificar->execute();
                        $resultado_verificar = $stmt_verificar->get_result();

                        if ($resultado_verificar->num_rows > 0) {
                            $mensagem = "Já existem férias marcadas para a mesma semana. Escolha outra data.";
                            $tipo_mensagem = "erro";
                        } else {
                            $tipo_ausencia = 'Férias';
                            $stmt_ferias = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, tipo_ausencia, data_inicio, data_fim) VALUES (?, ?, ?, ?)");
                            $stmt_ferias->bind_param("isss", $funcionario_id, $tipo_ausencia, $ferias_inicio, $ferias_fim);
                            $stmt_ferias->execute();
                        }
                    }

                    $mensagem = "Funcionário adicionado com sucesso!";
                    $tipo_mensagem = "sucesso";
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
                <h3>Férias</h3>
                <label>Data de Início:<input type="date" name="ferias_inicio"></label>
                <p>O período de férias será automaticamente ajustado para 15 dias.</p>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorFuncionario" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Salvar</button>
                </div>
            </div>
        </form>
    </div>
    <style>
        .mensagem.sucesso { background: #4CAF50; color: #fff; padding: 8px; border-radius: 4px; }
        .mensagem.erro { background: #f44336; color: #fff; padding: 8px; border-radius: 4px; }
    </style>
    <script>
    // Wizard navegação
    document.getElementById('btnWizardProximoFuncionario').onclick = function() {
        // Validação simples dos campos do passo 1
        var form = document.getElementById('formFuncionario');
        var nome = form.querySelector('[name=nome]').value.trim();
        var email = form.querySelector('[name=email]').value.trim();
        var senha = form.querySelector('[name=senha]').value.trim();
        var cargo = form.querySelector('[name=cargo]').value.trim();
        if (!nome || !email || !senha || !cargo) {
            alert('Preencha todos os campos obrigatórios do passo 1.');
            return;
        }
        document.getElementById('wizardStep1Funcionario').style.display = 'none';
        document.getElementById('wizardStep2Funcionario').style.display = 'block';
    };
    document.getElementById('btnWizardAnteriorFuncionario').onclick = function() {
        document.getElementById('wizardStep2Funcionario').style.display = 'none';
        document.getElementById('wizardStep1Funcionario').style.display = 'block';
    };
    </script>
    <?php
    exit;
}
?>