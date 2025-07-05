<?php
require '../../conexao.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = "ID do funcionário não fornecido.";
    $_SESSION['tipo_mensagem'] = "erro";
    header("Location: funcionarios.php");
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = htmlspecialchars($_POST['nome'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $cargo = htmlspecialchars($_POST['cargo'] ?? '');
    $telefone = htmlspecialchars($_POST['telefone'] ?? '');
    
    $erros = [];
    
    if (empty($nome)) $erros[] = "Nome é obrigatório";
    if (empty($email)) $erros[] = "Email é obrigatório";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "Email inválido";
    
    if (!empty($senha) && (strlen($senha) < 8 || !preg_match('/[A-Z]/', $senha))) {
        $erros[] = "Senha deve ter pelo menos 8 caracteres e uma letra maiúscula";
    }
    
    // Verificar se email já existe para outro funcionário
    $stmt = $conexao->prepare("SELECT F_id_funcionario FROM funcionarios WHERE F_email = ? AND F_id_funcionario != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $erros[] = "Email já está em uso por outro funcionário";
    }
    
    if (empty($erros)) {
        try {
            if (!empty($senha)) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("UPDATE funcionarios SET F_nome=?, F_email=?, F_senha=?, F_cargo=?, F_telefone=? WHERE F_id_funcionario=?");
                $stmt->bind_param("sssssi", $nome, $email, $senha_hash, $cargo, $telefone, $id);
            } else {
                $stmt = $conexao->prepare("UPDATE funcionarios SET F_nome=?, F_email=?, F_cargo=?, F_telefone=? WHERE F_id_funcionario=?");
                $stmt->bind_param("ssssi", $nome, $email, $cargo, $telefone, $id);
            }
            
            if ($stmt->execute()) {
                if (isset($_GET['modal'])) {
                    echo 'OK';
                    exit;
                }
                
                $_SESSION['mensagem'] = "Funcionário atualizado com sucesso!";
                $_SESSION['tipo_mensagem'] = "sucesso";
                header("Location: funcionarios.php");
                exit;
            }
        } catch (Exception $e) {
            $erros[] = "Erro ao atualizar funcionário: " . $e->getMessage();
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

// Buscar dados do funcionário
$stmt = $conexao->prepare("SELECT * FROM funcionarios WHERE F_id_funcionario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $_SESSION['mensagem'] = "Funcionário não encontrado.";
    $_SESSION['tipo_mensagem'] = "erro";
    header("Location: funcionarios.php");
    exit;
}

$funcionario = $resultado->fetch_assoc();

if (isset($_GET['modal'])) {
    ?>
    <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">
        <i class="fas fa-user-edit"></i> Editar Funcionário
    </h2>
    
    <form method="post" id="formFuncionario" style="display:flex; flex-direction:column; gap:16px;">
        <!-- Passo 1 - Dados Básicos -->
        <div class="wizard-step active" id="passo1">
            <h3>Dados Básicos</h3>
            
            <div class="form-group">
                <label for="nome">Nome Completo*</label>
                <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($funcionario['F_nome']) ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email*</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($funcionario['F_email']) ?>">
            </div>
            
            <div class="form-group">
                <label for="senha">Nova Senha (deixe em branco para manter a atual)</label>
                <input type="password" id="senha" name="senha">
                <small>Mínimo 8 caracteres, 1 letra maiúscula</small>
            </div>
            
            <div class="wizard-nav">
                <button type="button" id="btnProximo" class="button">Próximo <i class="fas fa-arrow-right"></i></button>
            </div>
        </div>
        
        <!-- Passo 2 - Dados Profissionais -->
        <div class="wizard-step" id="passo2">
            <h3>Dados Profissionais</h3>
            
            <div class="form-group">
                <label for="cargo">Cargo*</label>
                <select id="cargo" name="cargo" required>
                    <option value="">Selecione...</option>
                    <option value="gerente" <?= $funcionario['F_cargo'] === 'gerente' ? 'selected' : '' ?>>Gerente</option>
                    <option value="administrador" <?= $funcionario['F_cargo'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                    <option value="recepcionista" <?= $funcionario['F_cargo'] === 'recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                    <option value="governanta" <?= $funcionario['F_cargo'] === 'governanta' ? 'selected' : '' ?>>Governanta</option>
                    <option value="contabilista" <?= $funcionario['F_cargo'] === 'contabilista' ? 'selected' : '' ?>>Contabilista</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($funcionario['F_telefone']) ?>">
            </div>
            
            <div class="wizard-nav">
                <button type="button" id="btnAnterior" class="button"><i class="fas fa-arrow-left"></i> Anterior</button>
                <button type="button" id="btnAtualizar" class="button button-success">Atualizar <i class="fas fa-check"></i></button>
            </div>
        </div>
    </form>
    
    <script>
    function initWizardFuncionario() {
        const steps = document.querySelectorAll('.wizard-step');
        let currentStep = 0;
        
        document.getElementById('btnProximo')?.addEventListener('click', function() {
            const inputs = steps[currentStep].querySelectorAll('[required]');
            let valido = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'red';
                    valido = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (valido) {
                steps[currentStep].classList.remove('active');
                currentStep++;
                steps[currentStep].classList.add('active');
                atualizarBotoes();
            } else {
                alert('Preencha todos os campos obrigatórios!');
            }
        });
        
        document.getElementById('btnAnterior')?.addEventListener('click', function() {
            steps[currentStep].classList.remove('active');
            currentStep--;
            steps[currentStep].classList.add('active');
            atualizarBotoes();
        });
        
        function atualizarBotoes() {
            const btnAnterior = document.getElementById('btnAnterior');
            const btnProximo = document.getElementById('btnProximo');
            const btnAtualizar = document.getElementById('btnAtualizar');
            
            if (btnAnterior) btnAnterior.style.display = currentStep === 0 ? 'none' : 'block';
            if (btnProximo) btnProximo.style.display = currentStep === steps.length - 1 ? 'none' : 'block';
            if (btnAtualizar) btnAtualizar.style.display = currentStep === steps.length - 1 ? 'block' : 'none';
        }
        
        atualizarBotoes();

        // Impede o submit tradicional do formulário
        const form = document.getElementById('formFuncionario');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
            });
        }

        // AJAX para o botão Atualizar
        const btnAtualizar = document.getElementById('btnAtualizar');
        if (btnAtualizar && form) {
            btnAtualizar.addEventListener('click', function() {
                // Valida todos os campos obrigatórios
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
                    alert('Preencha todos os campos obrigatórios!');
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
                        window.parent.fecharModal('modalFuncionario');
                        window.parent.location.reload();
                    } else {
                        document.getElementById('conteudoModalFuncionario').innerHTML = response;
                        initWizardFuncionario();
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao atualizar funcionário.');
                });
            });
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        initWizardFuncionario();
    });
    </script>
    <?php
    exit;
}

header("Location: funcionarios.php");
exit;
?>