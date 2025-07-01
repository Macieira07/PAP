<?php
require '../../conexao.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: servicos.php");
    exit;
}

if (isset($_GET['modal'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = $_POST['nome_servico'];
        $descricao = $_POST['descricao'];
        $preco = $_POST['preco'];
        $categoria = $_POST['categoria_servico'];
        
        // Validação
        if (!is_numeric($preco) || $preco <= 0) {
            echo "O preço deve ser um valor positivo.";
            exit;
        }

        // Processar upload de imagem se for enviada uma nova
        $imagemPath = $_POST['imagem_atual'] ?? '';
        if (!empty($_FILES['imagem']['name'])) {
            $targetDir = "../../uploads/servicos/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $fileName = basename($_FILES['imagem']['name']);
            $targetFile = $targetDir . uniqid() . '_' . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            
            // Verificar se é uma imagem real
            $check = getimagesize($_FILES['imagem']['tmp_name']);
            if ($check === false) {
                echo "O arquivo não é uma imagem válida.";
                exit;
            }
            
            // Verificar tamanho do arquivo (5MB máximo)
            if ($_FILES['imagem']['size'] > 5000000) {
                echo "A imagem é muito grande (máximo 5MB).";
                exit;
            }
            
            // Permitir apenas certos formatos
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                echo "Apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
                exit;
            }
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $targetFile)) {
                // Apagar imagem antiga se existir
                if (!empty($imagemPath)) {
                    $oldImage = "../../" . $imagemPath;
                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }
                $imagemPath = str_replace('../../', '', $targetFile);
            } else {
                echo "Ocorreu um erro ao fazer upload da imagem.";
                exit;
            }
        }

        $stmt = $conexao->prepare("UPDATE servicos SET S_nome=?, S_descricao=?, S_preco=?, S_categoria_id=?, S_imagem=? WHERE S_id_servico=?");
        $stmt->bind_param("ssdisi", $nome, $descricao, $preco, $categoria, $imagemPath, $id);
        $stmt->execute();

        echo 'OK';
        exit;
    }

    $stmt = $conexao->prepare("SELECT * FROM servicos WHERE S_id_servico=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $servico = $resultado->fetch_assoc();

    if (!$servico) {
        echo "Serviço não encontrado.";
        exit;
    }
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Editar Serviço</h2>
        <form method="post" id="formWizardServico" enctype="multipart/form-data" data-id="<?= $servico['S_id_servico'] ?>" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Etapa 1 -->
            <div class="wizard-step" id="wizardStep1">
                <label style="display:flex; flex-direction:column; gap:4px;">Nome do Serviço:
                    <input type="text" name="nome_servico" value="<?= htmlspecialchars($servico['S_nome']) ?>" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea name="descricao" style="resize:vertical; min-height:60px; max-width:100%;" required><?= htmlspecialchars($servico['S_descricao']) ?></textarea>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Preço (€):
                    <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($servico['S_preco']) ?>" required>
                </label>
                <button type="button" id="btnWizardProximoServico" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Etapa 2 -->
            <div class="wizard-step" id="wizardStep2" style="display:none;">
                <?php 
                $categorias = $conexao->query("SELECT * FROM categorias_servico");
                ?>
                <label style="display:flex; flex-direction:column; gap:4px;">Categoria:
                    <select name="categoria_servico" required>
                        <?php while($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $servico['S_categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Imagem Atual:
                    <?php if (!empty($servico['S_imagem'])): ?>
                        <img src="../<?= htmlspecialchars($servico['S_imagem']) ?>" style="max-height:100px;">
                        <input type="hidden" name="imagem_atual" value="<?= htmlspecialchars($servico['S_imagem']) ?>">
                    <?php else: ?>
                        (sem imagem)
                    <?php endif; ?>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Alterar imagem:
                    <input type="file" name="imagem" accept="image/*">
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorServico" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Atualizar</button>
                </div>
            </div>
        </form>
    </div>
    <?php
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM servicos WHERE S_id_servico=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$servico = $resultado->fetch_assoc();

if (!$servico) {
    header("Location: servicos.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="../global.css">
    <meta charset="UTF-8">
    <title>Editar Serviço</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
    <h2>Editar Serviço</h2>
</div>

<form method="post" enctype="multipart/form-data">
    Nome do Serviço: <input type="text" name="nome_servico" value="<?= $servico['S_nome'] ?>" required><br><br>
    Descrição: <textarea name="descricao" required><?= $servico['S_descricao'] ?></textarea><br><br>
    Preço (€): <input type="number" step="0.01" name="preco" value="<?= $servico['S_preco'] ?>" required><br><br>
    
    <?php 
    $categorias = $conexao->query("SELECT * FROM categorias_servico");
    ?>
    Categoria:
    <select name="categoria_servico" required>
        <?php while($cat = $categorias->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $servico['S_categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
        <?php endwhile; ?>
    </select><br><br>
    
    Imagem Atual: 
    <?php if (!empty($servico['S_imagem'])): ?>
        <img src="../<?= $servico['S_imagem'] ?>" style="max-height:100px;">
        <input type="hidden" name="imagem_atual" value="<?= $servico['S_imagem'] ?>">
    <?php else: ?>
        (sem imagem)
    <?php endif; ?><br><br>
    
    Alterar imagem: <input type="file" name="imagem" accept="image/*"><br><br>
    
    <button type="submit">Atualizar</button>
</form>

<a href="servicos.php">← Voltar</a>

</body>
</html>