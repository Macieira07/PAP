<?php
require '../conexao.php';
require_once 'email.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = $_POST['telefone'];
    $documento = $_POST['documento'];
    $morada = $_POST['morada'];
    $verificado = $_POST['verificado'];
    $aceitou = $_POST['aceitou'];
    
    // Coletar as opções de notas selecionadas
    $notas = [];
    $valor_total = 0;
    $observacoes_decoracao = "";  // Para armazenar as observações da decoração

    if (isset($_POST['decoracao_tematica'])) {
        $notas[] = "Decoração Temática";
        $valor_total += 130;  // Valor único
        // Capturar a observação de decoração, se houver
        if (isset($_POST['observacoes_decoracao']) && !empty($_POST['observacoes_decoracao'])) {
            $observacoes_decoracao = $_POST['observacoes_decoracao'];
            $notas[] = "Observações: " . $observacoes_decoracao;
        }
    }

    if (isset($_POST['limpeza_diaria'])) {
        $notas[] = "Limpeza Diária";
        $valor_total += 15;  // Por noite
    }

    if (isset($_POST['cesto_boas_vindas'])) {
        $notas[] = "Cesto de Boas-Vindas";
        $valor_total += 10;  // Valor único
    }

    $notas_str = implode(", ", $notas);  // Transformar as notas em uma string separada por vírgulas

    $token = bin2hex(random_bytes(16));
    $token_expira = date('Y-m-d H:i:s', strtotime('+1 day'));

    // Verificar se o e-mail já existe
    $verifica = $conexao->prepare("SELECT COUNT(*) FROM hospedes WHERE H_email=?");
    $verifica->bind_param("s", $email);
    $verifica->execute();
    $verifica->bind_result($existe);
    $verifica->fetch();
    $verifica->close();

    if ($existe > 0) {
        header("Location: hospedes.php?erro=Já existe um hóspede com esse e-mail.");
        exit;
    }

    // Inserir novo hóspede
    $stmt = $conexao->prepare("INSERT INTO hospedes 
        (H_nome, H_email, H_senha, H_telefone, H_documento_ident, H_morada, 
         H_verificado_email, H_aceitou_termos_uso, H_notas, H_token_verificacao, H_token_expira, H_valor_notas)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssssssss", $nome, $email, $senha, $telefone, $documento, $morada, 
        $verificado, $aceitou, $notas_str, $token, $token_expira, $valor_total);

    if ($stmt->execute()) {
        // Gerar código de verificação
        $codigo_verificacao = rand(100000, 999999);

        // Guardar o código no campo de verificação
        $stmt2 = $conexao->prepare("UPDATE hospedes SET H_token_verificacao=? WHERE H_email=?");
        $stmt2->bind_param("ss", $codigo_verificacao, $email);
        $stmt2->execute();

        // Enviar o código por email
        $resultadoEmail = enviarEmailCodigo($email, $nome, $codigo_verificacao);
        if ($resultadoEmail !== true) {
            header("Location: hospedes.php?erro=Erro ao enviar email: $resultadoEmail");
            exit;
        }

        header("Location: hospedes.php?sucesso=Hóspede adicionado com sucesso! Código de verificação enviado.");
        exit;
    } else {
        header("Location: hospedes.php?erro=Erro ao adicionar hóspede.");
        exit;
    }
}
?>

<link rel="stylesheet" href="admin.css">
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
    <h2>Adicionar um novo Hóspede</h2>
</div>

<form method="post">
    <label>
        Nome Completo:
        <input type="text" name="nome" required>
    </label><br><br>

    <label>
        Email:
        <input type="email" name="email" required>
    </label><br><br>

    <label>
        Senha:
        <input type="text" name="senha" id="senha" required readonly style="width: 120px;">
        <button type="button" onclick="gerarSenha()">Gerar Código</button>
    </label><br><br>

    <label>
        Telefone:
        <input type="text" name="telefone" required>
    </label><br><br>

    <label>
        Documento:
        <input type="text" name="documento" required>
    </label><br><br>

    <label>
        Morada:
        <input type="text" name="morada">
    </label><br><br>

    <label>
        Verificou Email?
        <select name="verificado">
            <option value="Não">Não</option>
            <option value="Sim">Sim</option>
        </select>
    </label><br><br>

    <label>
        Aceitou os Termos?
        <select name="aceitou">
            <option value="Não">Não</option>
            <option value="Sim">Sim</option>
        </select>
    </label><br><br>


    <button type="submit">Salvar</button>
</form>

<script>
function gerarSenha() {
    const codigo = Math.floor(100000 + Math.random() * 900000);
    document.getElementById('senha').value = codigo;
}

</script>
