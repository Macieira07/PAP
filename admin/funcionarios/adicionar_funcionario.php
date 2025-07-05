<?php
require '../../conexao.php';
session_start();

// Log do POST para debug
file_put_contents(__DIR__.'/debug_post.txt', print_r($_POST, true));

// Verifica se o pedido é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST recebido em adicionar_funcionario.php');
    // Sanitização e validação dos dados recebidos
    $nome = htmlspecialchars(trim($_POST['nome'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $cargo = htmlspecialchars(trim($_POST['cargo'] ?? ''));
    $telefone = htmlspecialchars(trim($_POST['telefone'] ?? ''));
    $dataContratacao = date('Y-m-d');
    $tipoFuncionario = 'funcionario';

    $erros = [];

    // Validações obrigatórias
    if (empty($nome)) $erros[] = "O nome é obrigatório.";
    if (empty($email)) $erros[] = "O email é obrigatório.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "O email é inválido.";
    if (empty($senha)) $erros[] = "A palavra-passe é obrigatória.";
    if (strlen($senha) < 8) $erros[] = "A palavra-passe deve ter pelo menos 8 caracteres.";
    if (!preg_match('/[A-Z]/', $senha)) $erros[] = "A palavra-passe deve conter pelo menos uma letra maiúscula.";
    if (empty($cargo)) $erros[] = "O cargo é obrigatório.";

    // Verifica duplicidade de email
    $verificarEmail = $conexao->prepare("SELECT F_id_funcionario FROM funcionarios WHERE F_email = ?");
    $verificarEmail->bind_param("s", $email);
    $verificarEmail->execute();
    $verificarEmail->store_result();

    if ($verificarEmail->num_rows > 0) {
        $erros[] = "Este email já está registado.";
    }

    // Se não houver erros, insere o funcionário
    if (empty($erros)) {
        error_log('Passou validações');
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $inserirFuncionario = $conexao->prepare("
                INSERT INTO funcionarios 
                (F_nome, F_email, F_senha, F_cargo, F_telefone, F_data_contratacao, F_tipo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $inserirFuncionario->bind_param(
                "sssssss",
                $nome,
                $email,
                $senhaHash,
                $cargo,
                $telefone,
                $dataContratacao,
                $tipoFuncionario
            );

            error_log('Antes do insert');
            if ($inserirFuncionario->execute()) {
                error_log('Depois do insert - sucesso');
                // Resposta para requisição via modal
                if (isset($_GET['modal'])) {
                    echo 'OK';
                    exit;
                }

                $_SESSION['mensagem'] = "Funcionário adicionado com sucesso!";
                $_SESSION['tipo_mensagem'] = "sucesso";
                header("Location: funcionarios.php");
                exit;
            } else {
                error_log('Erro ao adicionar funcionário: ' . $inserirFuncionario->error);
                $erros[] = "Erro ao adicionar funcionário: " . $inserirFuncionario->error;
            }

        } catch (Exception $e) {
            error_log('Erro no servidor: ' . $e->getMessage());
            $erros[] = "Erro no servidor: " . $e->getMessage();
        }
    }

    // Resposta se houver erros (modal ou normal)
    if (isset($_GET['modal'])) {
        echo '<div class="mensagem erro">' . implode('<br>', $erros) . '</div>';
        exit;
    } else {
        $_SESSION['mensagem'] = implode('<br>', $erros);
        $_SESSION['tipo_mensagem'] = "erro";
        header("Location: funcionarios.php");
        exit;
    }
}

// Se for modal, apresenta o formulário em modo wizard
if (isset($_GET['modal'])): ?>
    <h2 style="margin:0 0 18px; text-align:center; color:#2e5090;">
        <i class="fas fa-user-plus"></i> Adicionar Funcionário
    </h2>

    <form method="post" id="formFuncionario" action="adicionar_funcionario.php?modal=1" style="display:flex; flex-direction:column; gap:16px;">
        <!-- Passo 1: Dados Pessoais -->
        <div class="wizard-step active" id="passo1">
            <h3>Dados Pessoais</h3>

            <div class="form-group">
                <label for="nome">Nome Completo*</label>
                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="senha">Palavra-passe* (mínimo 8 caracteres, 1 maiúscula)</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <div class="wizard-nav">
                <button type="button" id="btnProximo" class="button">
                    Próximo <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Passo 2: Dados Profissionais -->
        <div class="wizard-step" id="passo2">
            <h3>Dados Profissionais</h3>

            <div class="form-group">
                <label for="cargo">Cargo*</label>
                <select id="cargo" name="cargo" required>
                    <option value="">Selecione...</option>
                    <option value="gerente" <?= ($_POST['cargo'] ?? '') === 'gerente' ? 'selected' : '' ?>>Gerente</option>
                    <option value="administrador" <?= ($_POST['cargo'] ?? '') === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                    <option value="recepcionista" <?= ($_POST['cargo'] ?? '') === 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                    <option value="governanta" <?= ($_POST['cargo'] ?? '') === 'governanta' ? 'selected' : '' ?>>Governanta</option>
                    <option value="contabilista" <?= ($_POST['cargo'] ?? '') === 'contabilista' ? 'selected' : '' ?>>Contabilista</option>
                </select>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
            </div>

            <div class="wizard-nav">
                <button type="button" id="btnAnterior" class="button">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>
                <button type="button" id="btnFinalizar" class="button button-success">
                    Finalizar <i class="fas fa-check"></i>
                </button>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const passos = document.querySelectorAll('.wizard-step');
            let passoAtual = 0;

            const btnProximo = document.getElementById('btnProximo');
            const btnAnterior = document.getElementById('btnAnterior');
            const btnFinalizar = document.getElementById('btnFinalizar');
            const form = document.getElementById('formFuncionario');

            // Impede o submit tradicional do formulário
            form.addEventListener('submit', function(e) {
                e.preventDefault();
            });

            btnProximo.addEventListener('click', () => {
                const inputs = passos[passoAtual].querySelectorAll('[required]');
                let valido = true;

                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = 'red';
                        valido = false;
                    } else {
                        input.style.borderColor = '';
                    }
                });

                if (!valido) {
                    alert('Preencha todos os campos obrigatórios.');
                    return;
                }

                passos[passoAtual].classList.remove('active');
                passoAtual++;
                passos[passoAtual].classList.add('active');
            });

            btnAnterior.addEventListener('click', () => {
                passos[passoAtual].classList.remove('active');
                passoAtual--;
                passos[passoAtual].classList.add('active');
            });

            // Event listener para o botão Finalizar
            btnFinalizar.addEventListener('click', function() {
                // Valida todos os campos obrigatórios de todos os passos
                const allRequiredInputs = form.querySelectorAll('[required]');
                let valido = true;

                allRequiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = 'red';
                        valido = false;
                    } else {
                        input.style.borderColor = '';
                    }
                });

                if (!valido) {
                    alert('Preencha todos os campos obrigatórios.');
                    return;
                }

                // Envia via AJAX
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(response => {
                    if (response === 'OK') {
                        // Fecha o modal e recarrega a página
                        window.parent.fecharModal('modalFuncionario');
                        window.parent.location.reload();
                    } else {
                        // Mostra erro no modal
                        document.getElementById('conteudoModalFuncionario').innerHTML = response;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao adicionar funcionário.');
                });
            });
        });
    </script>
<?php
    exit;
endif;

// Redireciona se não for modal
header("Location: funcionarios.php");
exit;
?>
