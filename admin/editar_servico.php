<?php
require '../conexao.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: servicos.php");
    exit;
}

// Buscar categorias
$stmtCategorias = $conexao->prepare("SELECT * FROM categorias_servico");
$stmtCategorias->execute();
$categorias = $stmtCategorias->get_result();

// Buscar dados do serviço
$stmt = $conexao->prepare("SELECT * FROM servicos WHERE S_id_servico=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$servico = $result->fetch_assoc();

if (!$servico) {
    echo "Serviço não encontrado.";
    exit;
}

// Array PHP com os mesmos serviços do adicionar.php
$servicos = [
    'limpeza_piscina' => [
        'nome' => 'Limpeza da Piscina',
        'preco' => 200,
        'descricao' => 'Serviço de limpeza e manutenção da piscina.',
        'categoria' => 'Manutenção'
    ],
    'manutencao_jardim' => [
        'nome' => 'Manutenção do Jardim',
        'preco' => 300,
        'descricao' => 'Cuidados e manutenção do jardim.',
        'categoria' => 'Manutenção'
    ],
    'reparacao_equipamentos' => [
        'nome' => 'Reparação de Equipamentos (TV, AC, etc.)',
        'preco' => 0,
        'descricao' => 'Reparação e manutenção de equipamentos eletrónicos e eletrodomésticos.',
        'categoria' => 'Reparação'
    ],
    'servico_lavandaria' => [
        'nome' => 'Serviço de Lavandaria (Toalhas/Roupas de cama)',
        'preco' => 80,
        'descricao' => 'Lavagem e tratamento de toalhas e roupas de cama.',
        'categoria' => 'Serviços'
    ],
    'compra_produtos_higiene' => [
        'nome' => 'Compra de Produtos de Higiene',
        'preco' => 40,
        'descricao' => 'Compra de produtos de higiene para o alojamento.',
        'categoria' => 'Serviços'
    ],
    'reposicao_gas_carvao' => [
        'nome' => 'Reposição de Gás / Carvão',
        'preco' => 35,
        'descricao' => 'Reposição de gás ou carvão para uso no alojamento.',
        'categoria' => 'Serviços'
    ],
    'desinfestacao_controle_pragas' => [
        'nome' => 'Desinfestação / Controlo de Pragas',
        'preco' => 150,
        'descricao' => 'Serviço de desinfestação e controlo de pragas.',
        'categoria' => 'Serviços'
    ],
    'servico_limpeza_geral' => [
        'nome' => 'Serviço de Limpeza Geral (final de estadia)',
        'preco' => 100,
        'descricao' => 'Limpeza geral ao final da estadia.',
        'categoria' => 'Limpeza'
    ],
    'servico_canalizacao_eletricidade' => [
        'nome' => 'Serviço de Canalização / Eletricidade',
        'preco' => 120,
        'descricao' => 'Reparação e manutenção de canalização e eletricidade.',
        'categoria' => 'Reparação'
    ],
    'manutencao_churrasco' => [
        'nome' => 'Manutenção do Churrasco / Grelhador',
        'preco' => 50,
        'descricao' => 'Manutenção e limpeza do churrasco ou grelhador.',
        'categoria' => 'Manutenção'
    ],
    'compra_consumiveis' => [
        'nome' => 'Compra de Consumíveis (Papel, Detergentes, etc.)',
        'preco' => 30,
        'descricao' => 'Compra de consumíveis necessários para o alojamento.',
        'categoria' => 'Serviços'
    ],
    'transporte_materiais' => [
        'nome' => 'Transporte de materiais / Entregas',
        'preco' => 25,
        'descricao' => 'Serviço de transporte e entrega de materiais.',
        'categoria' => 'Logística'
    ],
    'renovacao_plantas_jardins' => [
        'nome' => 'Renovação de Plantas / Jardins',
        'preco' => 60,
        'descricao' => 'Renovação e cuidado de plantas e jardins.',
        'categoria' => 'Manutenção'
    ],
    'pintura_reparacao_estrutura' => [
        'nome' => 'Pintura / Reparação de Estrutura',
        'preco' => 200,
        'descricao' => 'Serviço de pintura e reparação da estrutura do imóvel.',
        'categoria' => 'Reparação'
    ],
    'checkup_seguranca' => [
        'nome' => 'Check-up de Segurança (extintores, alarmes, etc.)',
        'preco' => 90,
        'descricao' => 'Verificação de segurança dos equipamentos.',
        'categoria' => 'Segurança'
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = $_POST['nome_servico'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_servico = $_POST['categoria_servico'];

    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço deve ser um valor positivo.";
        exit;
    }

    $imagem_nome = $servico['S_imagem'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $tmp_arquivo = $_FILES['imagem']['tmp_name'];
        $nome_arquivo = basename($_FILES['imagem']['name']);
        $ext = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $permitidos)) {
            echo "Formato de imagem inválido.";
            exit;
        }

        $novo_nome = uniqid('serv_') . '.' . $ext;
        $destino = "../fotos_servicos/" . $novo_nome;

        if (move_uploaded_file($tmp_arquivo, $destino)) {
            if (!empty($imagem_nome) && file_exists("../" . $imagem_nome)) {
                unlink("../" . $imagem_nome);
            }
            $imagem_nome = 'fotos_servicos/' . $novo_nome;
        } else {
            echo "Erro ao mover a imagem.";
            exit;
        }
    }

    $stmtUpdate = $conexao->prepare("UPDATE servicos SET S_nome=?, S_descricao=?, S_preco=?, S_categoria_id=?, S_imagem=? WHERE S_id_servico=?");
    $stmtUpdate->bind_param("ssdisi", $nome_servico, $descricao, $preco, $categoria_servico, $imagem_nome, $id);
    $stmtUpdate->execute();

    header("Location: servicos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Serviço</title>
    <link rel="stylesheet" href="../public/css/admin.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">

    <script>
    // Mesmo objeto JavaScript do adicionar.php
    const servicos = {
        limpeza_piscina: {
            nome: "Limpeza da Piscina",
            preco: 200,
            descricao: "Serviço de limpeza e manutenção da piscina.",
            categoria: "Manutenção"
        },
        manutencao_jardim: {
            nome: "Manutenção do Jardim",
            preco: 300,
            descricao: "Cuidados e manutenção do jardim.",
            categoria: "Manutenção"
        },
        reparacao_equipamentos: {
            nome: "Reparação de Equipamentos (TV, AC, etc.)",
            preco: 0,
            descricao: "Reparação e manutenção de equipamentos eletrónicos e eletrodomésticos.",
            categoria: "Reparação"
        },
        servico_lavandaria: {
            nome: "Serviço de Lavandaria (Toalhas/Roupas de cama)",
            preco: 80,
            descricao: "Lavagem e tratamento de toalhas e roupas de cama.",
            categoria: "Serviços"
        },
        compra_produtos_higiene: {
            nome: "Compra de Produtos de Higiene",
            preco: 40,
            descricao: "Compra de produtos de higiene para o alojamento.",
            categoria: "Serviços"
        },
        reposicao_gas_carvao: {
            nome: "Reposição de Gás / Carvão",
            preco: 35,
            descricao: "Reposição de gás ou carvão para uso no alojamento.",
            categoria: "Serviços"
        },
        desinfestacao_controle_pragas: {
            nome: "Desinfestação / Controlo de Pragas",
            preco: 150,
            descricao: "Serviço de desinfestação e controlo de pragas.",
            categoria: "Serviços"
        },
        servico_limpeza_geral: {
            nome: "Serviço de Limpeza Geral (final de estadia)",
            preco: 100,
            descricao: "Limpeza geral ao final da estadia.",
            categoria: "Limpeza"
        },
        servico_canalizacao_eletricidade: {
            nome: "Serviço de Canalização / Eletricidade",
            preco: 120,
            descricao: "Reparação e manutenção de canalização e eletricidade.",
            categoria: "Reparação"
        },
        manutencao_churrasco: {
            nome: "Manutenção do Churrasco / Grelhador",
            preco: 50,
            descricao: "Manutenção e limpeza do churrasco ou grelhador.",
            categoria: "Manutenção"
        },
        compra_consumiveis: {
            nome: "Compra de Consumíveis (Papel, Detergentes, etc.)",
            preco: 30,
            descricao: "Compra de consumíveis necessários para o alojamento.",
            categoria: "Serviços"
        },
        transporte_materiais: {
            nome: "Transporte de materiais / Entregas",
            preco: 25,
            descricao: "Serviço de transporte e entrega de materiais.",
            categoria: "Logística"
        },
        renovacao_plantas_jardins: {
            nome: "Renovação de Plantas / Jardins",
            preco: 60,
            descricao: "Renovação e cuidado de plantas e jardins.",
            categoria: "Manutenção"
        },
        pintura_reparacao_estrutura: {
            nome: "Pintura / Reparação de Estrutura",
            preco: 200,
            descricao: "Serviço de pintura e reparação da estrutura do imóvel.",
            categoria: "Reparação"
        },
        checkup_seguranca: {
            nome: "Check-up de Segurança (extintores, alarmes, etc.)",
            preco: 90,
            descricao: "Verificação de segurança dos equipamentos.",
            categoria: "Segurança"
        }
    };

    function atualizarPreco() {
        let precoTotal = 0;
        let nomes = [];
        let descricoes = [];
        let ultimaCategoria = "";

        for (let id in servicos) {
            const cb = document.getElementById(id);
            if (cb && cb.checked) {
                const s = servicos[id];
                precoTotal += s.preco;
                nomes.push(s.nome);
                descricoes.push(s.descricao);
                ultimaCategoria = s.categoria;
            }
        }

        document.getElementsByName('nome_servico')[0].value = nomes.join(" + ");
        document.getElementsByName('descricao')[0].value = descricoes.join("\n");
        document.getElementsByName('preco')[0].value = precoTotal.toFixed(2);

        const sel = document.getElementsByName('categoria_servico')[0];
        for (let i=0; i<sel.options.length; i++) {
            if (sel.options[i].text === ultimaCategoria) {
                sel.selectedIndex = i;
                break;
            }
        }
    }
    </script>
</head>
<body>
    <h1>Editar Serviço</h1>

    <form method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>Selecione os Serviços:</legend>
            <?php foreach ($servicos as $id => $s): ?>
                <label>
                    <input type="checkbox" id="<?= $id ?>" onclick="atualizarPreco()">
                    <?= htmlspecialchars($s['nome']) ?> (€<?= number_format($s['preco'],2,',','.') ?>)
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <br>

        Nome do Serviço:<br>
        <input type="text" name="nome_servico" value="<?= htmlspecialchars($servico['S_nome']) ?>" required><br><br>

        Descrição:<br>
        <textarea name="descricao" rows="5" cols="50" required><?= htmlspecialchars($servico['S_descricao']) ?></textarea><br><br>

        Categoria:<br>
        <select name="categoria_servico" required>
            <?php
            $stmtCategorias->data_seek(0);
            while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?= $categoria['id'] ?>" <?= $categoria['id']==$servico['S_categoria_id']?'selected':''?>>
                    <?= htmlspecialchars($categoria['nome']) ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        Preço (€):<br>
        <input type="number" step="0.01" name="preco" value="<?= htmlspecialchars($servico['S_preco']) ?>" required><br><br>

        Imagem Atual:<br>
        <?php if (!empty($servico['S_imagem']) && file_exists("../".$servico['S_imagem'])): ?>
            <img src="../<?= htmlspecialchars($servico['S_imagem']) ?>" style="max-height:100px;"><br><br>
        <?php else: ?>
            Sem imagem.<br><br>
        <?php endif; ?>

        Alterar imagem:<br>
        <input type="file" name="imagem" accept="image/*"><br><br>

        <button type="submit">Atualizar Serviço</button>
    </form>

    <a href="servicos.php">← Voltar</a>
</body>
</html>
