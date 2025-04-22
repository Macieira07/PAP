<?php
include('../conexao.php');
$sql = "SELECT reservas.*, hospedes.H_nome FROM reservas JOIN hospedes ON reservas.R_id_hospede = hospedes.H_id_hospede";
$r = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reservas</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <button class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </button>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon"><i class="fas fa-hotel"></i></div>
            <h2>Admin Hotel</h2>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt icon"></i> Dashboard</a></li>
                <li><a href="admin_hospedes.php"><i class="fas fa-users icon"></i> Hóspedes</a></li>
                <li><a href="admin_reservas.php" class="active"><i class="fas fa-calendar-check icon"></i> Reservas</a></li>
                <li><a href="admin_casas.php"><i class="fas fa-home icon"></i> Casas</a></li>
                <li class="logout"><a href="logout.php"><i class="fas fa-sign-out-alt icon"></i> Sair</a></li>
            </ul>
        </nav>
    </div>
    
    <div class="main">
        <div class="welcome-card">
            <h1>Gerenciamento de <span class="admin-name">Reservas</span></h1>
            <p>Visualize, edite ou adicione novas reservas no sistema.</p>
        </div>
        
        <div class="action-bar">
            <a href="editar_reserva.php" class="btn-primary"><i class="fas fa-plus"></i> Nova Reserva</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hóspede</th>
                        <th>Casa</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($res = $r->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $res['R_id_reserva'] ?></td>
                        <td><?= $res['H_nome'] ?></td>
                        <td><?= $res['R_id_casa'] ?></td>
                        <td><?= $res['R_data_checkin'] ?></td>
                        <td><?= $res['R_data_checkout'] ?></td>
                        <td>
                            <span class="status-badge <?= strtolower($res['R_estado']) ?>">
                                <?= $res['R_estado'] ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="editar_reserva.php?id=<?= $res['R_id_reserva'] ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="excluir_reserva.php?id=<?= $res['R_id_reserva'] ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta reserva?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>