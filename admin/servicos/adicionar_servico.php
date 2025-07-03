<?php
require '../../conexao.php';

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

        // Processar upload de imagem principal
        $imagemPath = '';
        if (!empty($_FILES['imagem']['name']) && !empty($_FILES['imagem']['tmp_name'])) {
            $targetDir = "../../uploads/servicos/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $fileName = basename($_FILES['imagem']['name']);
            $targetFile = $targetDir . uniqid() . '_' . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            
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
                $imagemPath = str_replace('../../', '', $targetFile);
            } else {
                echo "Ocorreu um erro ao fazer upload da imagem.";
                exit;
            }
        }

        // Inserir serviço
        $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco, S_categoria_id, S_imagem) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $nome, $descricao, $preco, $categoria, $imagemPath);
        $stmt->execute();
        $servico_id = $conexao->insert_id;

        // Processar upload de imagens extras (galeria)
        if (!empty($_FILES['galeria']['name'][0])) {
            foreach ($_FILES['galeria']['name'] as $i => $galeriaName) {
                if (empty($galeriaName) || empty($_FILES['galeria']['tmp_name'][$i])) continue;
                $galeriaTmp = $_FILES['galeria']['tmp_name'][$i];
                $galeriaSize = $_FILES['galeria']['size'][$i];
                $galeriaType = strtolower(pathinfo($galeriaName, PATHINFO_EXTENSION));
                $targetDir = "../../uploads/servicos/";
                $galeriaFile = $targetDir . uniqid('gal_') . '_' . basename($galeriaName);
                $check = getimagesize($galeriaTmp);
                if ($check === false) continue;
                if ($galeriaSize > 5000000) continue;
                if (!in_array($galeriaType, ['jpg', 'jpeg', 'png', 'gif'])) continue;
                if (move_uploaded_file($galeriaTmp, $galeriaFile)) {
                    $galeriaPath = str_replace('../../', '', $galeriaFile);
                    $stmtImg = $conexao->prepare("INSERT INTO servicos_imagens (servico_id, caminho_imagem) VALUES (?, ?)");
                    $stmtImg->bind_param("is", $servico_id, $galeriaPath);
                    $stmtImg->execute();
                }
            }
        }

        echo 'OK';
        exit;
    }
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Adicionar Serviço</h2>
        <form method="post" id="formWizardServico" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Etapa 1 -->
            <div class="wizard-step" id="wizardStep1">
                <label for="nome_servico" style="display:flex; flex-direction:column; gap:4px;">Nome do Serviço:
                    <input type="text" id="nome_servico" name="nome_servico" required>
                </label>
                <label for="descricao" style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea id="descricao" name="descricao" style="resize:vertical; min-height:60px; max-width:100%;" required></textarea>
                </label>
                <label for="preco" style="display:flex; flex-direction:column; gap:4px;">Preço (€):
                    <input type="number" id="preco" step="0.01" name="preco" required>
                </label>
                <button type="button" id="btnWizardProximoServico" class="atalho-btn" style="align-self:flex-end; margin-top:10px;">Próximo &rarr;</button>
            </div>
            <!-- Etapa 2 -->
            <div class="wizard-step" id="wizardStep2" style="display:none;">
                <?php 
                $categorias = $conexao->query("SELECT * FROM categorias_servico");
                ?>
                <label for="categoria_servico" style="display:flex; flex-direction:column; gap:4px;">Categoria:
                    <select id="categoria_servico" name="categoria_servico" required>
                        <?php while($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label for="imagem" style="display:flex; flex-direction:column; gap:4px;">Imagem principal:
                    <input type="file" id="imagem" name="imagem" accept="image/*">
                </label>
                <label for="galeria" style="display:flex; flex-direction:column; gap:4px;">Imagens extras (galeria):
                    <input type="file" id="galeria" name="galeria[]" accept="image/*" multiple>
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorServico" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Confirmar</button>
                </div>
            </div>
        </form>
    </div>
    <?php if (isset($erroMsg)): ?>
        <div style="background:#f44336;color:#fff;padding:10px;border-radius:5px;margin-bottom:10px;"> <?= $erroMsg ?> </div>
    <?php endif; ?>
    <?php if (isset($sucessoMsg)): ?>
        <div style="background:#4CAF50;color:#fff;padding:10px;border-radius:5px;margin-bottom:10px;"> <?= $sucessoMsg ?> </div>
    <?php endif; ?>
    <?php
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
    <title>Adicionar Novo Serviço</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
    <h2>Adicionar Novo Serviço</h2>
</div>

<form method="post" enctype="multipart/form-data">
    <label>
        Nome do Serviço:
        <input type="text" name="nome_servico" required>
    </label><br><br>

    <label>
        Descrição:
        <textarea name="descricao" required></textarea>
    </label><br><br>

    <label>
        Preço (€):
        <input type="number" step="0.01" name="preco" required>
    </label><br><br>

    <?php 
    $categorias = $conexao->query("SELECT * FROM categorias_servico");
    ?>
    <label>
        Categoria:
        <select name="categoria_servico" required>
            <?php while($cat = $categorias->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
            <?php endwhile; ?>
        </select>
    </label><br><br>

    <label>
        Imagem:
        <input type="file" name="imagem" accept="image/*">
    </label><br><br>

    <label>
        Galeria:
        <input type="file" name="galeria[]" accept="image/*" multiple>
    </label><br><br>

    <button type="submit">Salvar</button>
</form>

<a href="servicos.php">← Voltar</a>

</body>
</html> 