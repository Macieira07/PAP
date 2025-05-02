<?php
session_start();

function verificarAdmin() {
    if (!isset($_SESSION['admin_logado'])) { // <- parêntese fechado aqui
        header('Location: ../login.php');
        exit();
    }
    
    // Verificar se o cargo é administrador para funções sensíveis
    if ($_SESSION['admin_cargo'] != 'administrador') {
        header('Location: ../admin_index.php');
        exit();
    }
}

function verificarLogin() {
    if (!isset($_SESSION['admin_logado'])) {
        header('Location: ../login1/login.php');
        exit();
    }
}
?>
