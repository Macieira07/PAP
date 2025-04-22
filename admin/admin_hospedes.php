    <?php
    session_start();
    include('../conexao.php');
    $sql = "SELECT * FROM hospedes";
    $resultado = $conexao->query($sql);
    ?>

    <!DOCTYPE html>
    <html lang="pt-PT">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hóspedes - Painel da Quinta Flores</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <link rel="stylesheet" href="admin.css">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>

        </style>
    </head>
    <body>
        <div class="menu-toggle" data-tooltip="Mostrar Menu">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="sidebar">
            <div class="sidebar-header">
                <span class="logo-icon">🌼</span>
                <h2>Quinta Flores</h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="admin_index.php"><span class="icon">🏠</span> Início</a></li>
                    <li><a href="admin_funcionarios.php"><span class="icon">👷</span> Funcionários</a></li>
                    <li><a href="admin_hospedes.php" class="active"><span class="icon">🧍</span> Hóspedes</a></li>
                    <li><a href="admin_reservas.php"><span class="icon">📅</span> Reservas</a></li>
                    <li class="logout"><a href="../index.html"><span class="icon">🚪</span> Sair</a></li>
                </ul>
            </nav>
        </div>

        <div class="main">
            <h1 class="page-title">Gestão de Hóspedes</h1>
            <a href="editar_hospede.php" class="add-btn">
                <span>+</span> Adicionar Novo Hóspede
            </a>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($h = $resultado->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $h['H_id_hospede'] ?></td>
                        <td><?= $h['H_nome'] . " " . $h['H_apelido'] ?></td>
                        <td><?= $h['H_email'] ?></td>
                        <td><?= $h['H_telefone'] ?></td>
                        <td>
                            <a href="editar_hospede.php?id=<?= $h['H_id_hospede'] ?>" class="action-btn edit-btn">Editar</a>
                            <a href="excluir_hospede.php?id=<?= $h['H_id_hospede'] ?>" class="action-btn delete-btn" onclick="return confirm('Tem certeza que deseja excluir este hóspede?')">Excluir</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const menuToggle = document.querySelector('.menu-toggle');
                const body = document.body;
                
                // Efeito inicial - mostrar sidebar automaticamente
                setTimeout(() => {
                    body.classList.toggle('sidebar-open');
                    menuToggle.setAttribute('data-tooltip', 'Esconder Menu');
                }, 800);
                
                menuToggle.addEventListener('click', function() {
                    body.classList.toggle('sidebar-open');
                    
                    // Atualizar tooltip do botão
                    if (body.classList.contains('sidebar-open')) {
                        menuToggle.setAttribute('data-tooltip', 'Esconder Menu');
                    } else {
                        menuToggle.setAttribute('data-tooltip', 'Mostrar Menu');
                    }
                });
                
                // Efeito de hover nas linhas da tabela
                const tableRows = document.querySelectorAll('.data-table tbody tr');
                tableRows.forEach(row => {
                    row.addEventListener('mouseenter', function() {
                        this.style.transform = 'translateX(5px)';
                    });
                    
                    row.addEventListener('mouseleave', function() {
                        this.style.transform = '';
                    });
                });
            });
        </script>
    </body>
    </html>