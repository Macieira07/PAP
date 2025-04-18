<?php
$host = 'localhost'; // Endereço do servidor
$usuario = 'root';   // Usuário do banco de dados
$senha = '';         // Senha do banco de dados
$banco = 'basedados_pap'; // Substitua pelo nome do seu banco de dados

// Criação da conexão usando a classe mysqli
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verifica se a conexão foi bem-sucedida
if ($conexao->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}
?>