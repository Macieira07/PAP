<?php
require 'mail_config.php';
require_once 'init.php';

if (enviarEmail('quinta.flores2019@gmail.com', 'Teste de Configuração', 'Funcionou!')) {
    echo "✅ Email enviado! Verifique sua caixa de entrada.";
} else {
    echo "❌ Falha no envio. Verifique o erro em: C:\xampp\php\logs\php_error.log";
}
?>