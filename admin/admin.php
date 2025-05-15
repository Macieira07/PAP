<?php
session_start();
require '../conexao.php';

// Verificar se o usuário está logado e é um funcionário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'funcionario') {
    header('Location: ../login.php');
    exit;
}

$id = $_SESSION['id'];  
$stmt = $conexao->prepare("SELECT F_nome, F_email FROM funcionarios WHERE F_id_funcionario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$funcionario = $resultado->fetch_assoc();

// Cálculo do saldo de forma mais completa (igual ao das despesas)
$saldoAtual = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1")->fetch_assoc()['saldo'] ?? 0;

// Consultar notificações não lidas
$resultado_notificacoes = $conexao->query("SELECT * FROM notificacoes WHERE lida = 0 ORDER BY data_criacao DESC LIMIT 5");
$total_notificacoes = $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE lida = 0")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin.css">
    <title>Painel de Administração - QUINTA FLORES </title>
    <style>
        body { font-family: Arial; background: #f5f5f5; text-align: center; padding: 40px; }
        .menu { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }

        .card {
            background: white; padding: 20px; border-radius: 10px; width: 200px;
            text-decoration: none; color: black; box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .card:hover { background: #eaeaea; transform: scale(1.05); }

        .card img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .saldo {
            position: absolute;
            top: 20px;
            right: 100px;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .positivo { background-color: #28a745; color: white; }
        .negativo { background-color: #dc3545; color: white; }
        .neutro { background-color: #ffc107; color: black; }
        body { font-family: Arial; background: #f5f5f5; text-align: center; padding: 40px; }
        .menu { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }

        .card {
            background: white; padding: 20px; border-radius: 10px; width: 200px;
            text-decoration: none; color: black; box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .card:hover { background: #eaeaea; transform: scale(1.05); }

        .card img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .saldo {
            position: absolute;
            top: 20px;
            right: 100px;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .verde { background-color: #28a745; color: white; }
        .vermelho { background-color: #dc3545; color: white; }
        .amarelo { background-color: #ffc107; color: black; }

        .conta {
            position: absolute;
            top: 20px;
            left: 40px;
            cursor: pointer;
        }
        .conta img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 70px;
            left: 40px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: left;
            padding: 10px;
            min-width: 200px;
        }

        .conta:hover .dropdown {
            display: block;
        }

        .dropdown p {
            margin: 0;
            padding: 5px 0;
        }

        .notificacoes {
            position: fixed;
            top: 70px;
            right: 40px;
            width: 300px;
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            z-index: 10;
            display: none;
            max-height: 400px;
            overflow-y: auto;
            text-align: left;
        }

        .notificacao {
            background-color: #28a745;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
        }

        .notificacao-conteudo {
            display: flex;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .notificacao img.icon {
            width: 24px;
            height: 24px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .notificacao-texto {
            flex-grow: 1;
        }

        .notificacao-tempo {
            font-size: 0.8em;
            opacity: 0.8;
            margin-top: 3px;
        }

        .notificacao-acoes {
            display: flex;
            justify-content: flex-end;
        }

        .notificacao button {
            background-color: #ffc107;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            color: #000;
            font-weight: bold;
        }

        .notificacao button:hover {
            background-color: #e0a800;
        }

        .icone-notificacao {
            position: absolute;
            top: 20px;
            right: 40px;
            cursor: pointer;
            font-size: 25px;
            color: #28a745;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .contador-notificacoes {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .sem-notificacoes {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Ícone do utilizador -->
    <div class="conta">
        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Conta">
        <div class="dropdown">
            <p><strong><?= htmlspecialchars($funcionario['F_nome']) ?></strong></p>
            <p>Email: <?= htmlspecialchars($funcionario['F_email']) ?></p>
            <p><a href="../login1/pagina_login.php">Terminar Sessão</a></p>
        </div>
    </div>
    <!-- Exibe o saldo (igual ao das despesas) -->
    <div class="saldo <?= $saldoAtual > 0 ? 'positivo' : ($saldoAtual < 0 ? 'negativo' : 'neutro') ?>">
        Saldo Disponível: <?= number_format($saldoAtual, 2, ',', '.') ?>€
    </div>

    <!-- Ícone de notificações -->
    <div class="icone-notificacao" onclick="mostrarNotificacoes()">
        <img src="https://img.icons8.com/?size=100&id=49387&format=png&color=000000" alt="Notificações" style="width: 30px; height: 30px;">
        <?php if ($total_notificacoes > 0): ?>
        <div class="contador-notificacoes"><?= $total_notificacoes ?></div>
        <?php endif; ?>
    </div>

    <h1>Painel de Administração</h1>
    <div class="menu">
        <a class="card" href="casas.php">
            <img src="https://img.icons8.com/?size=100&id=8BBH2HJBM6Nz&format=png&color=000000" alt="Casas">
            Gerir Casas
        </a>
        <a class="card" href="hospedes.php">
            <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Hóspedes">
            Gerir Hóspedes
        </a>
        <a class="card" href="funcionarios.php">
            <img src="https://img.icons8.com/?size=100&id=37174&format=png&color=000000" alt="Funcionários">
            Gerir Funcionários
        </a>
        <a class="card" href="reservas.php">
            <img src="https://img.icons8.com/?size=100&id=vTZ34gSDdvwJ&format=png&color=000000" alt="Reservas">
            Gerir Reservas
        </a>
        <a class="card" href="servicos.php">
            <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Serviços">
            Gerir Serviços
        </a>
        <a class="card" href="despesas.php">
            <img src="https://img.icons8.com/?size=100&id=2975&format=png&color=000000" alt="Despesas">
            Gerir Despesas
        </a>
        <a class="card" href="manutencao.php">
            <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Manutenções">
            Gerir Manutenções
        </a>
        <a class="card" href="receitas.php">
            <img src="https://img.icons8.com/?size=100&id=24836&format=png&color=000000" alt="Receitas">
            Gerir Receitas
        </a>
        <a class="card" href="relatorio_gastos_ganhos.php">
            <img src="https://img.icons8.com/?size=100&id=57714&format=png&color=000000" alt="Relatórios">
            Relatório Ganhos vs Gastos
        </a>
    </div>

    <!-- Exibição das notificações -->
    <div class="notificacoes">
        <h3>Notificações</h3>
        <?php if ($resultado_notificacoes->num_rows > 0) : ?>
            <?php while ($notificacao = $resultado_notificacoes->fetch_assoc()) : 
                $data_criacao = new DateTime($notificacao['data_criacao']);
                $agora = new DateTime();
                $diferenca = $data_criacao->diff($agora);

                if ($diferenca->days > 0) {
                    $tempo = $diferenca->days . ' dias atrás';
                } elseif ($diferenca->h > 0) {
                    $tempo = $diferenca->h . ' horas atrás';
                } elseif ($diferenca->i > 0) {
                    $tempo = $diferenca->i . ' minutos atrás';
                } else {
                    $tempo = 'Agora mesmo';
                }

                $icone = 'https://cdn-icons-png.flaticon.com/512/633/633741.png';
                if (isset($notificacao['tipo']) && $notificacao['tipo'] === 'reserva') {
                    $icone = 'https://cdn-icons-png.flaticon.com/512/633/633741.png';
                }
            ?>
                <div class="notificacao" id="notificacao-<?= $notificacao['id'] ?>">
                    <div class="notificacao-conteudo">
                        <img src="<?= $icone ?>" alt="Notificação" class="icon">
                        <div class="notificacao-texto">
                            <div><?= htmlspecialchars($notificacao['mensagem']) ?></div>
                            <div class="notificacao-tempo"><?= $tempo ?></div>
                        </div>
                    </div>
                    <div class="notificacao-acoes">
                        <button onclick="marcarComoLida(<?= $notificacao['id'] ?>)">Marcar como lida</button>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="sem-notificacoes">Nenhuma notificação nova.</div>
        <?php endif; ?>
    </div>

    <script>
        function mostrarNotificacoes() {
            const notificacoesDiv = document.querySelector('.notificacoes');
            notificacoesDiv.style.display = notificacoesDiv.style.display === 'block' ? 'none' : 'block';
        }

        function marcarComoLida(idNotificacao) {
            fetch('marcar_como_lida.php?id=' + idNotificacao)
                .then(response => response.text())
                .then(data => {
                    if (data === 'sucesso') {
                        const notificacaoDiv = document.getElementById('notificacao-' + idNotificacao);
                        notificacaoDiv.style.display = 'none';
                        
                        const contador = document.querySelector('.contador-notificacoes');
                        if (contador) {
                            let novoValor = parseInt(contador.textContent) - 1;
                            if (novoValor <= 0) {
                                contador.style.display = 'none';
                                const notificacoes = document.querySelectorAll('.notificacao');
                                let visiveis = 0;
                                notificacoes.forEach(n => {
                                    if (n.style.display !== 'none') visiveis++;
                                });
                                if (visiveis === 0) {
                                    const notificacoesDiv = document.querySelector('.notificacoes');
                                    notificacoesDiv.innerHTML = '<h3>Notificações</h3><div class="sem-notificacoes">Nenhuma notificação nova.</div>';
                                }
                            } else {
                                contador.textContent = novoValor;
                            }
                        }
                    }
                })
                .catch(error => console.log('Erro ao marcar como lida:', error));
        }
    </script>
</body>
</html>
