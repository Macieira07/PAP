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

        // Processar upload de imagem
        $imagemPath = '';
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
                $imagemPath = str_replace('../../', '', $targetFile);
            } else {
                echo "Ocorreu um erro ao fazer upload da imagem.";
                exit;
            }
        }

        $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco, S_categoria_id, S_imagem) 
                                   VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $nome, $descricao, $preco, $categoria, $imagemPath);
        $stmt->execute();

        echo 'OK';
        exit;
    }
    ?>
    <div>
        <h2 style="margin-top:0; margin-bottom:18px; text-align:center; color:#2e5090;">Adicionar Serviço</h2>
        <form method="post" id="formWizardServico" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:10px;">
            <!-- Etapa 1 -->
            <div class="wizard-step" id="wizardStep1">
                <label style="display:flex; flex-direction:column; gap:4px;">Nome do Serviço:
                    <input type="text" name="nome_servico" required>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea name="descricao" style="resize:vertical; min-height:60px; max-width:100%;" required></textarea>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Preço (€):
                    <input type="number" step="0.01" name="preco" required>
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
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label style="display:flex; flex-direction:column; gap:4px;">Imagem:
                    <input type="file" name="imagem" accept="image/*">
                </label>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorServico" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Confirmar</button>
                </div>
            </div>
        </form>
    </div>
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

    <button type="submit">Salvar</button>
</form>

<a href="servicos.php">← Voltar</a>

</body>
</html> 