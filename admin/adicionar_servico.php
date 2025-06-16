<?php
require '../conexao.php';

// Recupera as categorias de serviços para o dropdown
$stmt = $conexao->prepare("SELECT * FROM categorias_servico");
$stmt->execute();
$categorias = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_servico = $_POST['nome_servico'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $categoria_servico = $_POST['categoria_servico'];

    // Validação de preço
    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço deve ser um valor positivo.";
        exit;
    }

    // Tratamento da imagem (opcional)
    $imagem_path = null;

    if (isset($_FILES['imagem_servico']) && $_FILES['imagem_servico']['error'] === UPLOAD_ERR_OK) {
        $arquivo_tmp = $_FILES['imagem_servico']['tmp_name'];
        $nome_arquivo = basename($_FILES['imagem_servico']['name']);
        $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));

        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($extensao, $extensoes_permitidas)) {
            $novo_nome = uniqid('servico_') . '.' . $extensao;
            $diretorio_upload = '../fotos_servicos/';

            if (!is_dir($diretorio_upload)) {
                mkdir($diretorio_upload, 0755, true);
            }

            $caminho_final = $diretorio_upload . $novo_nome;

            if (move_uploaded_file($arquivo_tmp, $caminho_final)) {
                $imagem_path = 'fotos_servicos/' . $novo_nome;
            } else {
                echo "Erro ao salvar a imagem.";
                exit;
            }
        } else {
            echo "Formato de imagem não permitido. Use jpg, jpeg, png ou gif.";
            exit;
        }
    }

    // Inserção com ou sem imagem
    if ($imagem_path) {
        $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco, S_categoria_id, S_imagem) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $nome_servico, $descricao, $preco, $categoria_servico, $imagem_path);
    } else {
        $stmt = $conexao->prepare("INSERT INTO servicos (S_nome, S_descricao, S_preco, S_categoria_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $nome_servico, $descricao, $preco, $categoria_servico);
    }

    $stmt->execute();

    header("Location: servicos.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Serviço</title>
    <link rel="stylesheet" href="../public/css/admin.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    
    <script>
    const servicos = {
        servico_limpeza_piscina: {
            nome: "Limpeza da Piscina",
            preco: 200,
            descricao: "Serviço completo de limpeza da piscina.",
            categoria: "Serviços de Limpeza"
        },
        servico_manutencao_jardim: {
            nome: "Manutenção do Jardim",
            preco: 300,
            descricao: "Manutenção e cuidado do jardim.",
            categoria: "Serviços de Limpeza"
        },
        servico_reparacao_equipamentos: {
            nome: "Reparação de Equipamentos (TV, AC, etc.)",
            preco: 0,
            descricao: "Reparação de equipamentos variados. Preço variável conforme o serviço.",
            categoria: "Serviços Técnicos"
        },
        servico_lavandaria: {
            nome: "Serviço de Lavandaria (Toalhas/Roupas de cama)",
            preco: 80,
            descricao: "Lavagem e tratamento de toalhas e roupas de cama.",
            categoria: "Serviços Adicionais"
        },
        servico_compra_higiene: {
            nome: "Compra de Produtos de Higiene",
            preco: 40,
            descricao: "Aquisição de produtos de higiene pessoal e limpeza.",
            categoria: "Serviços Adicionais"
        },
        servico_reposicao_gas_carvao: {
            nome: "Reposição de Gás / Carvão",
            preco: 35,
            descricao: "Reposição de gás ou carvão para churrasqueira ou fogão.",
            categoria: "Serviços Gerais"
        },
        servico_desinfestacao: {
            nome: "Desinfestação / Controlo de Pragas",
            preco: 150,
            descricao: "Serviço de desinfestação e controlo de pragas.",
            categoria: "Serviços de Limpeza"
        },
        servico_limpeza_geral: {
            nome: "Serviço de Limpeza Geral (final de estadia)",
            preco: 100,
            descricao: "Limpeza completa após saída dos hóspedes.",
            categoria: "Serviços de Limpeza"
        },
        servico_canalizacao_eletricidade: {
            nome: "Serviço de Canalização / Eletricidade",
            preco: 120,
            descricao: "Reparações e manutenção de canalização e eletricidade.",
            categoria: "Serviços Técnicos"
        },
        servico_manutencao_churrasco: {
            nome: "Manutenção do Churrasco / Grelhador",
            preco: 50,
            descricao: "Limpeza e manutenção do churrasco ou grelhador.",
            categoria: "Serviços Gerais"
        },
        servico_compra_consumiveis: {
            nome: "Compra de Consumíveis (Papel, Detergentes, etc.)",
            preco: 30,
            descricao: "Compra de consumíveis para o funcionamento da casa.",
            categoria: "Serviços Adicionais"
        },
        servico_transporte_materiais: {
            nome: "Transporte de materiais / Entregas",
            preco: 25,
            descricao: "Serviço de transporte e entregas diversas.",
            categoria: "Serviços Gerais"
        },
        servico_renovacao_plantas: {
            nome: "Renovação de Plantas / Jardins",
            preco: 60,
            descricao: "Renovação e reposição de plantas e jardins.",
            categoria: "Serviços de Limpeza"
        },
        servico_pintura_reparacao: {
            nome: "Pintura / Reparação de Estrutura",
            preco: 200,
            descricao: "Serviços de pintura e reparação de estruturas.",
            categoria: "Serviços Técnicos"
        },
        servico_checkup_seguranca: {
            nome: "Check-up de Segurança (extintores, alarmes, etc.)",
            preco: 90,
            descricao: "Verificação e manutenção de sistemas de segurança.",
            categoria: "Serviços de Segurança"
        }
    };

    function atualizarPreco() {
        let precoTotal = 0;
        let nomesSelecionados = [];
        let descricoesSelecionadas = [];
        let ultimaCategoria = "outros";

        for (let id in servicos) {
            const checkbox = document.getElementById(id);
            if (checkbox && checkbox.checked) {
                const servico = servicos[id];
                precoTotal += servico.preco;
                nomesSelecionados.push(servico.nome);
                descricoesSelecionadas.push(servico.descricao);
                ultimaCategoria = servico.categoria;
            }
        }

        document.getElementById('nome_servico').value = nomesSelecionados.join(" + ");
        document.getElementById('descricao').value = descricoesSelecionadas.join("\n");
        document.getElementById('preco').value = precoTotal.toFixed(2);

        const categoriaSelect = document.getElementById("categoria_servico");
        for (let i = 0; i < categoriaSelect.options.length; i++) {
            if (categoriaSelect.options[i].text === ultimaCategoria) {
                categoriaSelect.selectedIndex = i;
                break;
            }
        }
    }
    </script>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Adicionar Novo Serviço</h1>
    </div>

    <form method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>Selecione os Serviços:</legend>
            <?php foreach ($servicos = [
                'servico_limpeza_piscina' => ['nome'=>'Limpeza da Piscina', 'preco'=>200, 'descricao'=>'Serviço completo de limpeza da piscina.', 'categoria'=>'Serviços de Limpeza'],
                'servico_manutencao_jardim' => ['nome'=>'Manutenção do Jardim', 'preco'=>300, 'descricao'=>'Manutenção e cuidado do jardim.', 'categoria'=>'Serviços de Limpeza'],
                'servico_reparacao_equipamentos' => ['nome'=>'Reparação de Equipamentos (TV, AC, etc.)', 'preco'=>0, 'descricao'=>'Reparação de equipamentos variados. Preço variável conforme o serviço.', 'categoria'=>'Serviços Técnicos'],
                'servico_lavandaria' => ['nome'=>'Serviço de Lavandaria (Toalhas/Roupas de cama)', 'preco'=>80, 'descricao'=>'Lavagem e tratamento de toalhas e roupas de cama.', 'categoria'=>'Serviços Adicionais'],
                'servico_compra_higiene' => ['nome'=>'Compra de Produtos de Higiene', 'preco'=>40, 'descricao'=>'Aquisição de produtos de higiene pessoal e limpeza.', 'categoria'=>'Serviços Adicionais'],
                'servico_reposicao_gas_carvao' => ['nome'=>'Reposição de Gás / Carvão', 'preco'=>35, 'descricao'=>'Reposição de gás ou carvão para churrasqueira ou fogão.', 'categoria'=>'Serviços Gerais'],
                'servico_desinfestacao' => ['nome'=>'Desinfestação / Controlo de Pragas', 'preco'=>150, 'descricao'=>'Serviço de desinfestação e controlo de pragas.', 'categoria'=>'Serviços de Limpeza'],
                'servico_limpeza_geral' => ['nome'=>'Serviço de Limpeza Geral (final de estadia)', 'preco'=>100, 'descricao'=>'Limpeza completa após saída dos hóspedes.', 'categoria'=>'Serviços de Limpeza'],
                'servico_canalizacao_eletricidade' => ['nome'=>'Serviço de Canalização / Eletricidade', 'preco'=>120, 'descricao'=>'Reparações e manutenção de canalização e eletricidade.', 'categoria'=>'Serviços Técnicos'],
                'servico_manutencao_churrasco' => ['nome'=>'Manutenção do Churrasco / Grelhador', 'preco'=>50, 'descricao'=>'Limpeza e manutenção do churrasco ou grelhador.', 'categoria'=>'Serviços Gerais'],
                'servico_compra_consumiveis' => ['nome'=>'Compra de Consumíveis (Papel, Detergentes, etc.)', 'preco'=>30, 'descricao'=>'Compra de consumíveis para o funcionamento da casa.', 'categoria'=>'Serviços Adicionais'],
                'servico_transporte_materiais' => ['nome'=>'Transporte de materiais / Entregas', 'preco'=>25, 'descricao'=>'Serviço de transporte e entregas diversas.', 'categoria'=>'Serviços Gerais'],
                'servico_renovacao_plantas' => ['nome'=>'Renovação de Plantas / Jardins', 'preco'=>60, 'descricao'=>'Renovação e reposição de plantas e jardins.', 'categoria'=>'Serviços de Limpeza'],
                'servico_pintura_reparacao' => ['nome'=>'Pintura / Reparação de Estrutura', 'preco'=>200, 'descricao'=>'Serviços de pintura e reparação de estruturas.', 'categoria'=>'Serviços Técnicos'],
                'servico_checkup_seguranca' => ['nome'=>'Check-up de Segurança (extintores, alarmes, etc.)', 'preco'=>90, 'descricao'=>'Verificação e manutenção de sistemas de segurança.', 'categoria'=>'Serviços de Segurança'],
            ] as $id => $s): ?>
                <label>
                    <input type="checkbox" id="<?= $id ?>" onclick="atualizarPreco()">
                    <?= $s['nome'] ?> (€<?= $s['preco'] ?>)
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <br>
        Nome do Serviço: <input type="text" id="nome_servico" name="nome_servico" required><br><br>
        Descrição:<br>
        <textarea id="descricao" name="descricao" rows="5" cols="40" required></textarea><br><br>

        Categoria:
        <select id="categoria_servico" name="categoria_servico" required>
            <?php while ($categoria = $categorias->fetch_assoc()): ?>
                <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nome']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        Preço (€): <input type="number" step="0.01" id="preco" name="preco" required><br><br>

        Imagem do Serviço: <input type="file" name="imagem_servico" accept="image/*"><br><br>

        <button type="submit">Adicionar Serviço</button>
    </form>

    <a href="servicos.php">← Voltar</a>
</body>
</html>
