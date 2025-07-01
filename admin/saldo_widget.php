<?php
if (!function_exists('obterSaldoAtual')) {
    function obterSaldoAtual($conexao) {
        $resultado = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
        if ($resultado && $resultado->num_rows > 0) {
            return (float) $resultado->fetch_assoc()['saldo'];
        } else {
            return 0;
        }
    }
}
if (!isset($conexao)) {
    require_once __DIR__ . '/../conexao.php';
}
$saldoAtual = obterSaldoAtual($conexao);
?>
<div class="saldo-disponivel" style="margin-bottom:12px;">
    Saldo:
    <span class="saldo-valor <?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
        €<?= number_format($saldoAtual, 2, ',', '.'); ?>
    </span>
</div> 