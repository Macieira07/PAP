<?php
require '../../conexao.php';

// Consulta para obter os dados das manutenções
$resultado = $conexao->query("SELECT manutencao.*, casas.C_nome FROM manutencao INNER JOIN casas ON manutencao.M_id_casa = casas.C_id_casa");

// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Consulta para calcular o total gasto em manutenções
$resultado_total = $conexao->query("SELECT SUM(M_custo) AS total_gasto FROM manutencao");
$total_gasto = $resultado_total->fetch_assoc()['total_gasto'] ?? 0.0;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Manutenções</title>
    <link rel="stylesheet" href="../global.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <style>
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        .flash-message.success { background-color: #4CAF50; }
        .flash-message.error { background-color: #f44336; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            min-width: 350px;
            max-width: 90vw;
            position: relative;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../saldo_widget.php'; ?>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Ícone Manutencao" style="height: 50px;">
        <h1>Lista de Manutenções</h1>
    </div>

    <a href="admin.php">← Voltar</a> | 
    <a href="#" id="btnAdicionarManutencao">+ Adicionar Manutenção</a>

    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Casa</th>
            <th>Tipo de Manutenção</th>
            <th>Data Início</th>
            <th>Data Fim</th>
            <th>Custo (€)</th>
            <th>Pago</th>
            <th>Ações</th>
        </tr>
        <?php while ($manutencao = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $manutencao['M_id_manutencao'] ?></td>
                <td><?= htmlspecialchars($manutencao['C_nome']) ?></td>
                <td><?= htmlspecialchars($manutencao['M_tipo']) ?></td>
                <td><?= $manutencao['M_data_inicio'] ?></td>
                <td><?= $manutencao['M_data_fim'] ? $manutencao['M_data_fim'] : 'Não definida' ?></td>
                <td><?= number_format($manutencao['M_custo'], 2, ',', '.') ?>€</td>
                <td>
                    <?php if ($manutencao['M_pago']): ?>
                        <span style="background:#4CAF50;color:#fff;padding:3px 8px;border-radius:8px;font-size:0.95em;">Pago</span>
                    <?php else: ?>
                        <span style="background:#f44336;color:#fff;padding:3px 8px;border-radius:8px;font-size:0.95em;">Por pagar</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="#" class="btnEditarManutencao" data-id="<?= $manutencao['M_id_manutencao'] ?>">Editar</a> |
                    <a href="eliminar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                    <?php if (!$manutencao['M_pago']): ?>
                        | <button class="btnPagarManutencao" data-id="<?= $manutencao['M_id_manutencao'] ?>" style="background:#2e5090;color:#fff;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;">Pagar</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Exibe o total gasto -->
    <div style="margin-top: 20px;">
        <strong>Total Gasto em Manutenções: <?= number_format($total_gasto, 2, ',', '.') ?>€</strong>
    </div>

    <!-- Modal para adicionar/editar manutenção -->
    <div id="modalManutencao" class="modal-overlay">
        <div class="modal-content">
            <button onclick="fecharModalManutencao()" style="position:absolute; top:10px; right:10px; font-size:20px; background:none; border:none; cursor:pointer;">&times;</button>
            <div id="modalConteudoManutencao"></div>
        </div>
    </div>

    <script>
    function abrirModalManutencao() {
        document.getElementById('modalManutencao').style.display = 'flex';
    }
    function fecharModalManutencao() {
        document.getElementById('modalManutencao').style.display = 'none';
        document.getElementById('modalConteudoManutencao').innerHTML = '';
    }
    
    // Adicionar manutenção
    document.getElementById('btnAdicionarManutencao').onclick = function(e) {
        e.preventDefault();
        fetch('adicionar_manutencao.php?modal=1')
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalConteudoManutencao').innerHTML = html;
                abrirModalManutencao();
                bindFormAjaxManutencao();
            });
    };
    
    // Editar manutenção
    document.querySelectorAll('.btnEditarManutencao').forEach(btn => {
        btn.onclick = function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            fetch('editar_manutencao.php?id=' + id + '&modal=1')
                .then(r => r.text())
                .then(html => {
                    document.getElementById('modalConteudoManutencao').innerHTML = html;
                    abrirModalManutencao();
                    bindFormAjaxManutencao();
                });
        };
    });
    
    // Submissão AJAX do formulário
    function bindFormAjaxManutencao() {
        const form = document.querySelector('#modalConteudoManutencao form');
        if (form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);
                
                // Detectar se é edição ou adição pelo atributo data-id
                let url = 'adicionar_manutencao.php?modal=1';
                if (form.hasAttribute('data-id')) {
                    const id = form.getAttribute('data-id');
                    url = 'editar_manutencao.php?id=' + id + '&modal=1';
                }
                
                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(resp => {
                    if (resp.trim() === 'OK') {
                        fecharModalManutencao();
                        window.location.reload();
                    } else {
                        document.getElementById('modalConteudoManutencao').innerHTML = resp;
                        bindFormAjaxManutencao();
                    }
                });
            };
        }
    }
    </script>
    <script>
    // Array de tipos e descrições (igual ao do adicionar_manutencao.php)
    window.tiposManutencao = {
        "Canalizações (canos, torneiras, autoclismo)": "Reparação ou substituição de canos, torneiras e autoclismos.",
        "Instalações elétricas (lâmpadas, tomadas, quadro elétrico)": "Substituição de lâmpadas, tomadas e verificação do quadro elétrico.",
        "Eletrodomésticos (frigorífico, máquina de lavar, micro-ondas)": "Revisão e reparação de eletrodomésticos essenciais.",
        "Ar-condicionado e aquecimento": "Limpeza de filtros, verificação de gás e funcionamento geral.",
        "Fechaduras e chaves (portas e janelas)": "Troca de fechaduras, cópia de chaves e lubrificação.",
        "Pintura e retoques nas paredes": "Pintura de paredes e retoques em áreas danificadas.",
        "Mobiliário (reparação ou substituição de peças danificadas)": "Reparação ou substituição de móveis danificados.",
        "Jardinagem (relva, arbustos, rega)": "Corte de relva, poda de arbustos e verificação do sistema de rega.",
        "Piscina (tratamento da água, limpeza de filtros)": "Tratamento químico da água e limpeza dos filtros da piscina.",
        "Churrasqueira (limpeza e manutenção da estrutura)": "Limpeza da estrutura e verificação da integridade da churrasqueira.",
        "Iluminação exterior": "Substituição de lâmpadas e manutenção de circuitos exteriores.",
        "Extintores e detetores de fumo/gás": "Verificação da validade e testes de funcionamento dos equipamentos.",
        "Câmaras de segurança e sistemas de alarme": "Testes e manutenção dos equipamentos de segurança.",
        "Grades ou vedações de segurança": "Verificação da integridade e reparações necessárias.",
        "Verificações periódicas agendadas (mensais ou trimestrais)": "Inspeções regulares de todos os sistemas e equipamentos.",
        "Substituição de baterias (comando de portão, detetores de fumo, etc.)": "Troca preventiva de baterias em dispositivos críticos.",
        "Testes de funcionamento geral antes da chegada de hóspedes": "Testes e ajustes finais de todos os sistemas para garantir conforto e segurança."
    };

    // Event delegation para preencher descrição ao mudar o tipo
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'tipo') {
            const tipoSelect = e.target;
            const descricaoInput = document.getElementById('descricao');
            const descricaoHidden = document.getElementById('descricao_hidden');
            const descricoes = window.tiposManutencao || {};
            if (descricaoInput) {
                descricaoInput.value = descricoes[tipoSelect.value] || "";
            }
            if (descricaoHidden) {
                descricaoHidden.value = descricaoInput.value;
            }
        }
    });

    // Event delegation para os botões Próximo e Anterior do wizard
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'btnWizardProximoManutencao') {
            var form = e.target.closest('form');
            if (!form) return;
            var id_casa = form.querySelector('[name=id_casa]').value;
            var tipo = form.querySelector('[name=tipo]').value;
            if (!id_casa || !tipo) {
                alert('Preencha todos os campos obrigatórios.');
                return;
            }
            form.querySelector('#wizardStep1').style.display = 'none';
            form.querySelector('#wizardStep2').style.display = 'block';
        }
        if (e.target && e.target.id === 'btnWizardAnteriorManutencao') {
            var form = e.target.closest('form');
            if (!form) return;
            form.querySelector('#wizardStep2').style.display = 'none';
            form.querySelector('#wizardStep1').style.display = 'block';
        }
    });

    // Event delegation para o botão Pagar manutenção
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btnPagarManutencao')) {
            e.preventDefault();
            var id = e.target.getAttribute('data-id');
            if (!id) return;
            if (!confirm('Tem a certeza que deseja pagar esta manutenção?')) return;
            // Redireciona para o endpoint de pagamento (pode ser AJAX, mas aqui é simples)
            window.location.href = '../admin/pagar_manutencao.php?id=' + id;
        }
    });
    </script>
</body>
</html>