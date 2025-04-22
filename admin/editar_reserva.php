<?php
include('../conexao.php');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome FROM hospedes");
$casas = $conexao->query("SELECT C_id_casa, C_nome FROM casas");

$R_id_hospede = $R_id_casa = $R_data_checkin = $R_data_checkout = $R_estado = "";
if ($id > 0) {
    $res = $conexao->query("SELECT * FROM reservas WHERE R_id_reserva = $id")->fetch_assoc();
    $R_id_hospede = $res['R_id_hospede'];
    $R_id_casa = $res['R_id_casa'];
    $R_data_checkin = $res['R_data_checkin'];
    $R_data_checkout = $res['R_data_checkout'];
    $R_estado = $res['R_estado'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? "Editar" : "Nova" ?> Reserva</title>
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
            <h1><?= $id > 0 ? "Editar" : "Nova" ?> <span class="admin-name">Reserva</span></h1>
            <p>Preencha os campos abaixo para <?= $id > 0 ? "atualizar a" : "criar uma nova" ?> reserva.</p>
        </div>
        
        <div class="form-container">
            <form method="POST" action="atualizar_reserva.php" class="admin-form">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="form-group">
                    <label for="id_hospede"><i class="fas fa-user"></i> Hóspede:</label>
                    <select name="id_hospede" id="id_hospede" required>
                        <option value="">Selecione um hóspede</option>
                        <?php while($h = $hospedes->fetch_assoc()) { ?>
                            <option value="<?= $h['H_id_hospede'] ?>" <?= ($h['H_id_hospede'] == $R_id_hospede) ? "selected" : "" ?>>
                                <?= $h['H_nome'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_casa"><i class="fas fa-home"></i> Casa:</label>
                    <select name="id_casa" id="id_casa" required>
                        <option value="">Selecione uma casa</option>
                        <?php while($c = $casas->fetch_assoc()) { ?>
                            <option value="<?= $c['C_id_casa'] ?>" <?= ($c['C_id_casa'] == $R_id_casa) ? "selected" : "" ?>>
                                <?= $c['C_nome'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="checkin"><i class="fas fa-calendar-alt"></i> Check-in:</label>
                    <input type="date" name="checkin" id="checkin" value="<?= $R_data_checkin ?>" required>
                </div>

                <div class="form-group">
                    <label for="checkout"><i class="fas fa-calendar-alt"></i> Check-out:</label>
                    <input type="date" name="checkout" id="checkout" value="<?= $R_data_checkout ?>" required>
                </div>

                <div class="form-group">
                    <label for="estado"><i class="fas fa-info-circle"></i> Estado:</label>
                    <select name="estado" id="estado" required>
                        <option value="Confirmada" <?= ($R_estado == "Confirmada") ? "selected" : "" ?>>Confirmada</option>
                        <option value="Pendente" <?= ($R_estado == "Pendente") ? "selected" : "" ?>>Pendente</option>
                        <option value="Cancelada" <?= ($R_estado == "Cancelada") ? "selected" : "" ?>>Cancelada</option>
                        <option value="Concluída" <?= ($R_estado == "Concluída") ? "selected" : "" ?>>Concluída</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar</button>
                    <a href="admin_reservas.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>