<?php
function conexao() {
    $host = 'db'; // Certifique-se de que o 'db' é o nome correto do host
    $dbname = 'basedados_pap';
    $username = 'root';
    $password = '';

    try {
        // Corrija a string de conexão
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        // Defina o modo de erro
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Corrigido
        return $pdo;
    } catch (PDOException $e) {
        die("Erro ao conectar com o banco de dados: " . $e->getMessage());
    }
}
?>
