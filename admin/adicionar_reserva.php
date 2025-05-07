<?php
// Conexão com o banco de dados
include('../conexao.php');

// Buscar casas e hóspedes
$casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível'");
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome, H_apelido FROM hospedes");
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
    <label for="data_checkin">Data de Check-in:</label><br>
    <input type="date" id="data_checkin" name="data_checkin" required><br><br>

    <label for="data_checkout">Data de Check-out:</label><br>
    <input type="date" id="data_checkout" name="data_checkout" required><br><br>

    <label for="id_casa">Casa:</label><br>
    <select name="id_casa" id="id_casa" required>
      <?php while ($c = $casas->fetch_assoc()): ?>
        <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>">
          <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'],2) ?>€/noite)
        </option>
      <?php endwhile; ?>
    </select><br><br>

    <label for="id_hospede">Hóspede:</label>
    <a href="adicionar_hospede.php" style="margin-left:10px;">+ Adicionar Hóspede</a><br>
    <select name="id_hospede" id="id_hospede" required>
      <?php while ($h = $hospedes->fetch_assoc()): ?>
        <option value="<?= $h['H_id_hospede'] ?>">
          <?= htmlspecialchars($h['H_nome'] . ' ' . $h['H_apelido']) ?>
        </option>
      <?php endwhile; ?>
    </select><br><br>

    <label for="num_hospedes">Número de Hóspedes:</label><br>
    <input type="number" id="num_hospedes" name="num_hospedes" min="1" max="10" value="1" required><br><br>

    <label for="metodo_pagamento">Método de Pagamento:</label><br>
    <select name="metodo_pagamento" id="metodo_pagamento" required>
      <option value="mbway">MB Way</option>
      <option value="dinheiro">Dinheiro</option>
      <option value="transferencia">Transferência</option>
      <option value="cartao">Cartão de Crédito</option>
    </select><br><br>

    <label for="estado">Estado:</label><br>
    <select name="estado" id="estado" required>
      <option value="pendente">Pendente</option>
      <option value="confirmada">Confirmada</option>
    </select><br><br>

    <p><strong>Preço total estimado: <span id="preco_total">0.00€</span></strong></p>

    <button type="submit">Confirmar Reserva</button>
  </form>
  <a href="reservas.php">← Voltar</a>

  <script>
    // Definir data mínima para check-in (hoje)
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('data_checkin').min = hoje;
    
    // Definir data mínima para check-out (amanhã)
    const amanha = new Date();
    amanha.setDate(amanha.getDate() + 1);
    document.getElementById('data_checkout').min = amanha.toISOString().split('T')[0];
    
    const selectCasa     = document.querySelector('select[name="id_casa"]');
    const inputCheckin   = document.querySelector('input[name="data_checkin"]');
    const inputCheckout  = document.querySelector('input[name="data_checkout"]');
    const spanTotal      = document.getElementById('preco_total');

    function atualizarTotal() {
      const opc       = selectCasa.selectedOptions[0];
      const precoNoite= parseFloat(opc.dataset.preco);
      const ci        = new Date(inputCheckin.value);
      const co        = new Date(inputCheckout.value);
      if (ci && co && co > ci) {
        const dias = (co - ci) / (1000*60*60*24);
        spanTotal.textContent = (dias * precoNoite).toFixed(2) + '€';
      } else {
        spanTotal.textContent = '0.00€';
      }
    }
    
    // Verificar validade das datas de check-in e check-out
    inputCheckin.addEventListener('change', function() {
      const checkin = new Date(this.value);
      const checkout = new Date(inputCheckout.value);
      
      // Atualizar data mínima para check-out
      const minCheckout = new Date(checkin);
      minCheckout.setDate(minCheckout.getDate() + 1);
      inputCheckout.min = minCheckout.toISOString().split('T')[0];
      
      // Se checkout for anterior ao novo checkin + 1 dia, atualize-o
      if (checkout <= checkin) {
        inputCheckout.value = minCheckout.toISOString().split('T')[0];
      }
      
      atualizarTotal();
    });

    selectCasa.addEventListener('change', atualizarTotal);
    inputCheckout.addEventListener('change', atualizarTotal);
    
    // Inicializar com valores padrão
    if (inputCheckin.value) {
      atualizarTotal();
    }
  </script>
</body>
</html>