<?php
// Conexão com o banco de dados
include('../conexao.php');

// Obter o ID da reserva a ser editada
$reserva_id = $_GET['id'] ?? null;

if ($reserva_id === null) {
    die("Reserva não encontrada.");
}

// Buscar detalhes da reserva
$stmt = $conexao->prepare("SELECT R_id_casa, R_id_hospede, R_data_checkin, R_data_checkout, R_estado, R_num_hospedes FROM reservas WHERE R_id_reserva = ?");
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    die("Reserva não encontrada.");
}

// Buscar casas e hóspedes
$casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível'");
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome FROM hospedes");

// Buscar datas ocupadas
$ocupadas = [];
$stmt = $conexao->prepare("SELECT R_data_checkin, R_data_checkout FROM reservas WHERE R_estado != 'cancelada'");
$stmt->execute();
$stmt->bind_result($data_checkin, $data_checkout);
while ($stmt->fetch()) {
    $checkin = new DateTime($data_checkin);
    $checkout = new DateTime($data_checkout);
    while ($checkin <= $checkout) {
        $ocupadas[] = $checkin->format('Y-m-d');
        $checkin->modify('+1 day');
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
      <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css">
  <title>Editar Reserva</title>
  <style>
    .ocupada {
      background-color: red !important;
      color: white;
    }
  </style>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
  <img src="https://img.icons8.com/?size=100&id=vTZ34gSDdvwJ&format=png&color=000000" alt="Ícone Reservas" style="height: 50px;">
  <h1>Editar Reserva</h1>
</div>

<form method="POST" action="processar_edicao_reserva.php">
  <input type="hidden" name="reserva_id" value="<?= $reserva['R_id_reserva'] ?>">

  <label for="data_checkin">
    <i class="fa-solid fa-calendar-check"></i> Data de Check-in:
  </label><br>
  <input type="date" id="data_checkin" name="data_checkin" value="<?= $reserva['R_data_checkin'] ?>" required><br><br>

  <label for="data_checkout">
    <i class="fa-solid fa-calendar-times"></i> Data de Check-out:
  </label><br>
  <input type="date" id="data_checkout" name="data_checkout" value="<?= $reserva['R_data_checkout'] ?>" required><br><br>

  <label for="id_casa">
    <i class="fa-solid fa-house"></i> Casa:
  </label><br>
  <select name="id_casa" id="id_casa" required>
    <?php while ($c = $casas->fetch_assoc()): ?>
      <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>" <?= $c['C_id_casa'] == $reserva['R_id_casa'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'], 2) ?>€/noite)
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <label for="id_hospede">
    <i class="fa-solid fa-user"></i> Hóspede:
  </label><br>
  <select name="id_hospede" id="id_hospede" required>
    <?php while ($h = $hospedes->fetch_assoc()): ?>
      <option value="<?= $h['H_id_hospede'] ?>" <?= $h['H_id_hospede'] == $reserva['R_id_hospede'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($h['H_nome']) ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <label for="num_hospedes">
    <i class="fa-solid fa-users"></i> Número de Hóspedes:
  </label><br>
  <input type="number" id="num_hospedes" name="num_hospedes" min="1" max="10" value="<?= $reserva['R_num_hospedes'] ?>" required><br><br>

  <label for="metodo_pagamento">
    <i class="fa-solid fa-credit-card"></i> Método de Pagamento:
  </label><br>
  <select name="metodo_pagamento" id="metodo_pagamento" required>
    <option value="mbway">MB Way</option>
    <option value="dinheiro">Dinheiro</option>
    <option value="transferencia">Transferência</option>
    <option value="cartao">Cartão de Crédito</option>
  </select><br><br>

  <label for="estado">
    <i class="fa-solid fa-info-circle"></i> Estado:
  </label><br>
  <select name="estado" id="estado" required>
    <option value="pendente" <?= $reserva['R_estado'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
    <option value="confirmada" <?= $reserva['R_estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
  </select><br><br>

  <p><strong>Preço total estimado: <span id="preco_total">0.00€</span></strong></p>

  <button type="submit">
    <i class="fa-solid fa-check"></i> Atualizar Reserva
  </button>
</form>
<script>
// Verifica se o estado foi alterado para 'confirmada'
if ($novo_estado === 'confirmada') {
    // Obtém o valor da reserva
    $stmt = $conexao->prepare("SELECT R_preco_total FROM reservas WHERE R_id_reserva = ?");
    $stmt->bind_param("i", $id_reserva);
    $stmt->execute();
    $stmt->bind_result($preco_total);
    $stmt->fetch();
    $stmt->close();

    // Atualiza o saldo da conta virtual (id = 1, ou outro se necessário)
    $conexao->query("UPDATE conta_virtual SET saldo = saldo + $preco_total WHERE id = 1");
}
</script>
<a href="reservas.php">
  <i class="fa-solid fa-arrow-left"></i> Voltar
</a>

<script>
  // Atualizar preço total
  const selectCasa = document.querySelector('select[name="id_casa"]');
  const inputCheckin = document.querySelector('input[name="data_checkin"]');
  const inputCheckout = document.querySelector('input[name="data_checkout"]');
  const spanTotal = document.getElementById('preco_total');

  function atualizarTotal() {
    const opc = selectCasa.selectedOptions[0];
    const precoNoite = parseFloat(opc.dataset.preco);
    const ci = new Date(inputCheckin.value);
    const co = new Date(inputCheckout.value);

    let total = 0;
    if (ci && co && co > ci) {
      const dias = (co - ci) / (1000 * 60 * 60 * 24);
      total = dias * precoNoite;
    }

    spanTotal.textContent = total.toFixed(2) + '€';
  }

  inputCheckin.addEventListener('change', atualizarTotal);
  inputCheckout.addEventListener('change', atualizarTotal);
  selectCasa.addEventListener('change', atualizarTotal);

  // Marcar datas ocupadas
  const ocupadas = <?php echo json_encode($ocupadas); ?>;
  const dateInputs = [inputCheckin, inputCheckout];

  dateInputs.forEach(input => {
    input.addEventListener('focus', function () {
      const inputDate = this;

      inputDate.addEventListener('change', function () {
        const selectedDate = this.value;

        if (ocupadas.includes(selectedDate)) {
          this.classList.add('ocupada');
        } else {
          this.classList.remove('ocupada');
        }
      });
    });
  });
</script>
</body>
</html>
