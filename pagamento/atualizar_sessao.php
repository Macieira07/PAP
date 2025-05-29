<?php
session_start();
if (isset($_POST['codigo_oferta'])) {
    $_SESSION['codigo_oferta'] = $_POST['codigo_oferta'];
    echo 'Sessão atualizada';
} else {
    unset($_SESSION['codigo_oferta']);
    echo 'Sessão limpa';
}
?>