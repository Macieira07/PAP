<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<?php
// Conexão com o banco de dados
include('../conexao.php');

// Buscar casas e hóspedes
$casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível'");
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome FROM hospedes");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="admin.css">
  <title>Adicionar Reserva</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
  <img src="https://img.icons8.com/?size=100&id=vTZ34gSDdvwJ&format=png&color=000000" alt="Ícone Reservas" style="height: 50px;">
  <h1>Adicionar Reserva</h1>
</div>

<form method="POST" action="processar_reserva.php">
  <label for="data_checkin">
    <i class="fa-solid fa-calendar-check"></i> Data de Check-in:
  </label><br>
  <input type="date" id="data_checkin" name="data_checkin" required><br><br>

  <label for="data_checkout">
    <i class="fa-solid fa-calendar-times"></i> Data de Check-out:
  </label><br>
  <input type="date" id="data_checkout" name="data_checkout" required><br><br>

  <label for="id_casa">
    <i class="fa-solid fa-house"></i> Casa:
  </label><br>
  <select name="id_casa" id="id_casa" required>
    <?php while ($c = $casas->fetch_assoc()): ?>
      <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>">
        <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'],2) ?>€/noite)
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <label for="id_hospede">
    <i class="fa-solid fa-user"></i> Hóspede:
  </label>
  <a href="adicionar_hospede.php" style="margin-left:10px;">+ Adicionar Hóspede</a><br>
  <select name="id_hospede" id="id_hospede" required>
    <?php while ($h = $hospedes->fetch_assoc()): ?>
      <option value="<?= $h['H_id_hospede'] ?>">
        <?= htmlspecialchars($h['H_nome']) ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <label for="num_hospedes">
    <i class="fa-solid fa-users"></i> Número de Hóspedes:
  </label><br>
  <input type="number" id="num_hospedes" name="num_hospedes" min="1" max="10" value="1" required><br><br>

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
    <option value="pendente">Pendente</option>
    <option value="confirmada">Confirmada</option>
  </select><br><br>

  <h3>Serviços Adicionais</h3>

  <label>
    <input type="checkbox" id="decoracao_temática" name="decoracao_tematica"> Decoração Temática (€130 - valor único)
  </label><br>

  <div id="observacoes_decoracao" style="display: none; margin-top: 10px;">
    <label>
      Observações para Decoração Temática:<br>
      <textarea name="observacoes_decoracao" rows="4" cols="50" placeholder="Digite as observações aqui..."></textarea>
    </label><br><br>
  </div>

  <label>
    <input type="checkbox" id="limpeza_diaria" name="limpeza_diaria"> Limpeza Diária (€15 por noite)
  </label><br>

  <label>
    <input type="checkbox" name="cesto_boas_vindas"> Cesto de Boas-Vindas (€10 - valor único)
  </label><br><br>

  <p><strong>Preço total estimado: <span id="preco_total">0.00€</span></strong></p>

  <button type="submit">
    <i class="fa-solid fa-check"></i> Confirmar Reserva
  </button>
</form>

<a href="reservas.php">
  <i class="fa-solid fa-arrow-left"></i> Voltar
</a>

<script>
  // Data mínima check-in e check-out
  const hoje = new Date().toISOString().split('T')[0];
  document.getElementById('data_checkin').min = hoje;

  const amanha = new Date();
  amanha.setDate(amanha.getDate() + 1);
  document.getElementById('data_checkout').min = amanha.toISOString().split('T')[0];

  const selectCasa     = document.querySelector('select[name="id_casa"]');
  const inputCheckin   = document.querySelector('input[name="data_checkin"]');
  const inputCheckout  = document.querySelector('input[name="data_checkout"]');
  const spanTotal      = document.getElementById('preco_total');
  const limpezaCheckbox = document.querySelector('input[name="limpeza_diaria"]');

  function atualizarTotal() {
    const opc       = selectCasa.selectedOptions[0];
    const precoNoite= parseFloat(opc.dataset.preco);
    const ci        = new Date(inputCheckin.value);
    const co        = new Date(inputCheckout.value);

    let total = 0;
    if (ci && co && co > ci) {
      const dias = (co - ci) / (1000*60*60*24);
      total = dias * precoNoite;

      if (limpezaCheckbox.checked) {
        total += dias * 15; // Limpeza diária (15€/noite)
      }
    }

    if (document.getElementById('decoracao_temática').checked) total += 130; // Decoração Temática
    if (document.querySelector('input[name="cesto_boas_vindas"]').checked) total += 10; // Cesto de Boas-Vindas

    spanTotal.textContent = total.toFixed(2) + '€';
  }

  // Atualizar preços e datas
  inputCheckin.addEventListener('change', function() {
    const checkin = new Date(this.value);
    const minCheckout = new Date(checkin);
    minCheckout.setDate(minCheckout.getDate() + 1);
    inputCheckout.min = minCheckout.toISOString().split('T')[0];

    if (new Date(inputCheckout.value) <= checkin) {
      inputCheckout.value = minCheckout.toISOString().split('T')[0];
    }

    atualizarTotal();
  });

  selectCasa.addEventListener('change', atualizarTotal);
  inputCheckout.addEventListener('change', atualizarTotal);
  limpezaCheckbox.addEventListener('change', atualizarTotal);
  document.getElementById('decoracao_temática').addEventListener('change', function () {
    const obs = document.getElementById('observacoes_decoracao');
    obs.style.display = this.checked ? 'block' : 'none';
    atualizarTotal();
  });
  document.querySelector('input[name="cesto_boas_vindas"]').addEventListener('change', atualizarTotal);

  // Inicializar total se datas já estiverem preenchidas
  if (inputCheckin.value) atualizarTotal();
</script>
</body>
</html>
