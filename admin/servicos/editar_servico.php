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
        if (!empty($_FILES['imagem']['name']) && !empty($_FILES['imagem']['tmp_name'])) {
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

        // Processar upload de novas imagens extras (galeria)
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
                    $stmtImg->bind_param("is", $id, $galeriaPath);
                    $stmtImg->execute();
                }
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
                <label for="nome_servico" style="display:flex; flex-direction:column; gap:4px;">Nome do Serviço:
                    <input type="text" id="nome_servico" name="nome_servico" value="<?= htmlspecialchars($servico['S_nome']) ?>" required>
                </label>
                <label for="descricao" style="display:flex; flex-direction:column; gap:4px;">Descrição:
                    <textarea id="descricao" name="descricao" style="resize:vertical; min-height:60px; max-width:100%;" required><?= htmlspecialchars($servico['S_descricao']) ?></textarea>
                </label>
                <label for="preco" style="display:flex; flex-direction:column; gap:4px;">Preço (€):
                    <input type="number" id="preco" step="0.01" name="preco" value="<?= htmlspecialchars($servico['S_preco']) ?>" required>
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
                            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $servico['S_categoria_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nome']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </label>
                <label for="imagem" style="display:flex; flex-direction:column; gap:4px;">Alterar imagem:
                    <input type="file" id="imagem" name="imagem" accept="image/*">
                </label>
                <label for="galeria" style="display:flex; flex-direction:column; gap:4px;">Adicionar novas imagens à galeria:
                    <input type="file" id="galeria" name="galeria[]" accept="image/*" multiple>
                </label>
                <div style="margin:10px 0 10px 0;">
                    <button type="button" onclick="abrirLightboxEditar()" style="background:#2e5090;color:#fff;padding:6px 18px;border:none;border-radius:4px;cursor:pointer;">Ver fotos</button>
                    <div style="display:none;">
                    <?php
                    $imagens = [];
                    if (!empty($servico['S_imagem'])) {
                        $imagens[] = ['src' => '/' . htmlspecialchars($servico['S_imagem']), 'alt' => 'Imagem do serviço ' . htmlspecialchars($servico['S_nome'])];
                    }
                    $galeria = $conexao->query("SELECT id, caminho_imagem FROM servicos_imagens WHERE servico_id = " . (int)$servico['S_id_servico']);
                    while ($img = $galeria->fetch_assoc()) {
                        $imagens[] = ['src' => '/' . htmlspecialchars($img['caminho_imagem']), 'alt' => 'Galeria de ' . htmlspecialchars($servico['S_nome'])];
                    }
                    ?>
                    <script>
                    window.lightboxImagensEditar = <?= json_encode($imagens) ?>;
                    </script>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:10px;">
                    <button type="button" id="btnWizardAnteriorServico" class="atalho-btn">&larr; Anterior</button>
                    <button type="submit" class="atalho-btn">Atualizar</button>
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
    <!-- Lightbox HTML para editar -->
    <div id="lightboxOverlayEditar" class="lightbox-overlay" style="display:none;">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="fecharLightboxEditar()">&times;</button>
            <button class="lightbox-nav left" onclick="navegarLightboxEditar(-1)">&#8592;</button>
            <img id="lightboxImgEditar" class="lightbox-img" src="" alt="">
            <button class="lightbox-nav right" onclick="navegarLightboxEditar(1)">&#8594;</button>
        </div>
    </div>
    <script>
    let lightboxIndexEditar = 0;
    function abrirLightboxEditar(index = 0) {
        lightboxIndexEditar = index;
        atualizarLightboxEditar();
        document.getElementById('lightboxOverlayEditar').style.display = 'flex';
    }
    function fecharLightboxEditar() {
        document.getElementById('lightboxOverlayEditar').style.display = 'none';
    }
    function navegarLightboxEditar(delta) {
        const imagens = window.lightboxImagensEditar;
        lightboxIndexEditar += delta;
        if (lightboxIndexEditar < 0) lightboxIndexEditar = imagens.length - 1;
        if (lightboxIndexEditar >= imagens.length) lightboxIndexEditar = 0;
        atualizarLightboxEditar();
    }
    function atualizarLightboxEditar() {
        const imagens = window.lightboxImagensEditar;
        const img = imagens[lightboxIndexEditar];
        document.getElementById('lightboxImgEditar').src = img.src;
        document.getElementById('lightboxImgEditar').alt = img.alt;
    }
    document.addEventListener('keydown', function(e) {
        if (document.getElementById('lightboxOverlayEditar').style.display === 'flex') {
            if (e.key === 'ArrowLeft') navegarLightboxEditar(-1);
            if (e.key === 'ArrowRight') navegarLightboxEditar(1);
            if (e.key === 'Escape') fecharLightboxEditar();
        }
    });
    </script>
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