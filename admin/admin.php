<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin.css">
    <title>Painel de Administração</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; text-align: center; padding: 40px; }
        .menu { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
        .card {
            background: white; padding: 20px; border-radius: 10px; width: 200px;
            text-decoration: none; color: black; box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .card:hover { background: #eaeaea; transform: scale(1.05); }
    </style>
</head>
<body>
    <h1>Painel de Administração</h1>
    <div class="menu">
        <a class="card" href="casas.php">Gerir Casas</a>
        <a class="card" href="hospedes.php">Gerir Hóspedes</a>
        <a class="card" href="funcionarios.php">Gerir Funcionários</a>
        <a class="card" href="reservas.php">Gerir Reservas</a>
        <a class="card" href="servicos.php">Gerir Servicos</a>
        <a class="card" href="despesas.php">Gerir Despesas</a>
        <a class="card" href="manutencao.php">Gerir Manutenções</a>
    </div>
</body>
</html>