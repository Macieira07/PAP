<?php
require '../../conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conexao->prepare("DELETE FROM ferias_ausencias WHERE F_id_ausencia = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Ausência eliminada com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "Erro ao eliminar ausência!";
        $_SESSION['tipo_mensagem'] = "error";
    }
    
    header("Location: funcionarios.php");
    exit;
} else {
    header("Location: funcionarios.php");
    exit;
}
?>