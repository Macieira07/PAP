<?php
session_start();
require '../conexao.php';

// Verificar se o usuário está logado e é um funcionário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'funcionario') {
    header('Location: ../login.php');
    exit;
}

// Função para obter o saldo atual (igual à usada em despesas.php)
function obterSaldoAtual() {
    global $conexao;
    
    // Obter total de receitas
    $sql_receitas = "SELECT COALESCE(SUM(m.valor), 0) as total 
                     FROM movimentacoes m 
                     WHERE m.tipo = 'receita'";
    $resultado_receitas = $conexao->query($sql_receitas);
    $total_receitas = $resultado_receitas->fetch_assoc()['total'];
    
    // Obter total de despesas
    $sql_despesas = "SELECT COALESCE(SUM(m.valor), 0) as total 
                    FROM movimentacoes m 
                    WHERE m.tipo = 'despesa'";
    $resultado_despesas = $conexao->query($sql_despesas);
    $total_despesas = $resultado_despesas->fetch_assoc()['total'];
    
    // Calcular saldo (receitas - despesas)
    $saldo = $total_receitas - $total_despesas;
    
    return $saldo;
}

$id = $_SESSION['id'];  
$stmt = $conexao->prepare("SELECT F_nome, F_email FROM funcionarios WHERE F_id_funcionario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$funcionario = $resultado->fetch_assoc();

// Usar a nova função para calcular o saldo
$saldoAtual = obterSaldoAtual();

// Consultar notificações não lidas
$resultado_notificacoes = $conexao->query("SELECT * FROM notificacoes WHERE lida = 0 ORDER BY data_criacao DESC LIMIT 5");
$total_notificacoes = $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE lida = 0")->fetch_assoc()['total'];
?>;
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - QUINTA FLORES</title>
    <style>
        /* Estilos Gerais */
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }

        /* Container Principal */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Cabeçalho */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .admin-header h1 {
            color: #2e5090;
            margin: 0;
            font-size: 28px;
        }

        /* Conta do Usuário - Agora à esquerda */
        .conta-usuario {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            order: 1;
        }

        .conta-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #2e5090;
        }

        .conta-dropdown {
            display: none;
            position: absolute;
            top: 50px;
            left: 0; /* Alterado para left em vez de right */
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            min-width: 200px;
            z-index: 100;
            padding: 10px;
        }

        .conta-usuario:hover .conta-dropdown {
            display: block;
        }

        .conta-dropdown p {
            margin: 5px 0;
            padding: 5px 10px;
        }

        .conta-dropdown a {
            color: #2e5090;
            text-decoration: none;
            display: block;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .conta-dropdown a:hover {
            background-color: #f0f4ff;
        }
        /* Saldo - Estilo mais formal e sério */
.saldo-disponivel {
    border: 1px solid #e0e0e0;
    background-color: #f8f9fa;
    padding: 10px 15px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    order: 2;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    min-width: 120px;
}

.indicator-title {
    font-size: 12px;
    color: #505050;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.indicator-value {
    font-size: 16px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 3px;
    display: inline-block;
}

.saldo-positivo {
    color: #006E33;
    background-color: #E6F4EA;
    border-left: 3px solid #0D8843;
}

.saldo-negativo {
    color: #AF1C1C;
    background-color: #FDECEA;
    border-left: 3px solid #D93025;
}

.saldo-neutro {
    color: #915D10;
    background-color: #FEF7E0;
    border-left: 3px solid #F29900;
}



        /* Menu de Cards */
        .menu-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card-opcao {
            background: white;
            padding: 25px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #343a40;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 150px;
        }

        .card-opcao:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-decoration: none;
        }

        .card-opcao img {
            width: 50px;
            height: 50px;
            margin-bottom: 15px;
            opacity: 0.9;
        }

        .card-opcao h3 {
            margin: 0;
            font-size: 16px;
            text-align: center;
            font-weight: 600;
        }

        /* Notificações - Agora à direita */
        .notificacoes-container {
            position: fixed;
            top: 70px;
            right: 30px;
            width: 380px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            z-index: 1000;
            display: none;
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .notificacoes-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f9f9f9;
            border-radius: 8px 8px 0 0;
        }

        .notificacoes-header h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }

        .notificacoes-header .fechar {
            cursor: pointer;
            color: #666;
            font-size: 20px;
            line-height: 1;
        }

        .notificacoes-body {
            padding: 0;
        }

        .notificacao-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f5f5f5;
            transition: background-color 0.2s;
            display: flex;
            align-items: flex-start;
        }

        .notificacao-item:last-child {
            border-bottom: none;
        }

        .notificacao-item:hover {
            background-color: #fafafa;
        }

        .notificacao-icone {
            margin-right: 15px;
            color: #2e5090;
            font-size: 18px;
            min-width: 24px;
            text-align: center;
        }

        .notificacao-conteudo {
            flex-grow: 1;
        }

        .notificacao-mensagem {
            color: #333;
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 4px;
        }

        .notificacao-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notificacao-tempo {
            font-size: 12px;
            color: #888;
        }

        .notificacao-acoes {
            margin-left: 10px;
        }

        .notificacao-btn {
            background: none;
            border: none;
            color: #2e5090;
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .notificacao-btn:hover {
            background-color: #f0f4ff;
        }

        .sem-notificacoes {
            padding: 30px 20px;
            text-align: center;
            color: #888;
            font-size: 14px;
        }

        .icone-notificacao {
            position: relative;
            cursor: pointer;
            display: flex;
            align-items: center;
            order: 3;
        }

        .icone-notificacao img {
            width: 24px;
            height: 24px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .icone-notificacao:hover img {
            opacity: 1;
        }

        .contador-notificacoes {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -5px;
            right: -5px;
            font-weight: bold;
        }

        /* Barra de Ações - Nova organização */
        .acoes-superiores {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .acoes-superiores {
                width: 100%;
                justify-content: space-between;
                flex-direction: row;
            }
            
            .menu-cards {
                grid-template-columns: 1fr 1fr;
            }
            
            .notificacoes-container {
                width: 90%;
                right: 5%;
            }
        }

        @media (max-width: 480px) {
            .menu-cards {
                grid-template-columns: 1fr;
            }
            
            .acoes-superiores {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Painel de Administração</h1>
            
            <div class="acoes-superiores">
                <!-- Conta do Usuário - Agora à esquerda -->
                <div class="conta-usuario">
                    <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Conta" class="conta-avatar">
                    <div class="conta-dropdown">
                        <p><strong><?= htmlspecialchars($funcionario['F_nome']) ?></strong></p>
                        <p><?= htmlspecialchars($funcionario['F_email']) ?></p>
                        <a href="../login1/pagina_login.php">Terminar Sessão</a>
                    </div>
                </div>
                
                <!-- Saldo Disponível - Agora no meio -->
<div class="saldo-disponivel">
    <div class="indicator-title">Saldo Atual</div>
    <div class="indicator-value <?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
        €<?= number_format($saldoAtual, 2, ',', '.'); ?>
    </div>
</div>
                
                <!-- Ícone de Notificações - Agora à direita -->
                <div class="icone-notificacao" onclick="mostrarNotificacoes()">
                    <img src="https://cdn-icons-png.flaticon.com/512/565/565422.png" alt="Notificações">
                    <?php if ($total_notificacoes > 0): ?>
                    <div class="contador-notificacoes"><?= $total_notificacoes ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Menu de Opções -->
        <div class="menu-cards">
            <a class="card-opcao" href="casas.php">
                <img src="https://img.icons8.com/?size=100&id=8BBH2HJBM6Nz&format=png&color=000000" alt="Casas">
                <h3>Gerir Casas</h3>
            </a>
            <a class="card-opcao" href="hospedes.php">
                <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Hóspedes">
                <h3>Gerir Hóspedes</h3>
            </a>
            <a class="card-opcao" href="funcionarios.php">
                <img src="https://img.icons8.com/?size=100&id=37174&format=png&color=000000" alt="Funcionários">
                <h3>Gerir Funcionários</h3>
            </a>
            <a class="card-opcao" href="reservas.php">
                <img src="https://img.icons8.com/?size=100&id=vTZ34gSDdvwJ&format=png&color=000000" alt="Reservas">
                <h3>Gerir Reservas</h3>
            </a>
            <a class="card-opcao" href="servicos.php">
                <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Serviços">
                <h3>Gerir Serviços</h3>
            </a>
            <a class="card-opcao" href="despesas.php">
                <img src="https://img.icons8.com/?size=100&id=2975&format=png&color=000000" alt="Despesas">
                <h3>Gerir Despesas</h3>
            </a>
            <a class="card-opcao" href="manutencao.php">
                <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Manutenções">
                <h3>Gerir Manutenções</h3>
            </a>
            <a class="card-opcao" href="receitas.php">
                <img src="https://img.icons8.com/?size=100&id=24836&format=png&color=000000" alt="Receitas">
                <h3>Gerir Receitas</h3>
            </a>
            <a class="card-opcao" href="relatorio_gastos_ganhos.php">
                <img src="https://img.icons8.com/?size=100&id=57714&format=png&color=000000" alt="Relatórios">
                <h3>Relatório Ganhos vs Gastos</h3>
            </a>
        </div>
    </div>

    <!-- Container de Notificações -->
    <div class="notificacoes-container" id="notificacoesContainer">
        <div class="notificacoes-header">
            <h3>Notificações</h3>
            <span class="fechar" onclick="fecharNotificacoes()">×</span>
        </div>
        <div class="notificacoes-body">
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
                ?>
                    <div class="notificacao-item" id="notificacao-<?= $notificacao['id'] ?>">
                        <div class="notificacao-icone">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                            </svg>
                        </div>
                        <div class="notificacao-conteudo">
                            <div class="notificacao-mensagem"><?= htmlspecialchars($notificacao['mensagem']) ?></div>
                            <div class="notificacao-meta">
                                <span class="notificacao-tempo"><?= $tempo ?></span>
                                <div class="notificacao-acoes">
                                    <button class="notificacao-btn" onclick="marcarComoLida(<?= $notificacao['id'] ?>, event)">Marcar como lida</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="sem-notificacoes">Não há novas notificações</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Funções para Notificações
        function mostrarNotificacoes() {
            const container = document.getElementById('notificacoesContainer');
            container.style.display = container.style.display === 'block' ? 'none' : 'block';
        }

        function fecharNotificacoes() {
            document.getElementById('notificacoesContainer').style.display = 'none';
        }

        function marcarComoLida(idNotificacao, event) {
            event.stopPropagation();
            
            fetch('marcar_como_lida.php?id=' + idNotificacao)
                .then(response => response.text())
                .then(data => {
                    if (data === 'sucesso') {
                        const notificacao = document.getElementById('notificacao-' + idNotificacao);
                        notificacao.style.opacity = '0.5';
                        notificacao.style.pointerEvents = 'none';
                        
                        // Atualizar contador
                        const contador = document.querySelector('.contador-notificacoes');
                        if (contador) {
                            let novoValor = parseInt(contador.textContent) - 1;
                            if (novoValor <= 0) {
                                contador.style.display = 'none';
                            } else {
                                contador.textContent = novoValor;
                            }
                        }
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        // Fechar notificações ao clicar fora
        document.addEventListener('click', function(event) {
            const notificacoes = document.getElementById('notificacoesContainer');
            const iconeNotificacao = document.querySelector('.icone-notificacao');
            
            if (notificacoes.style.display === 'block' && 
                !notificacoes.contains(event.target) && 
                !iconeNotificacao.contains(event.target)) {
                notificacoes.style.display = 'none';
            }
        });
    </script>
</body>
</html>