<?php
// Script para processar receitas recorrentes
require '../conexao.php';

// Busca todas as receitas recorrentes ativas
$sql = "SELECT * FROM receitas WHERE recorrente = 1 AND (data_fim_recorrencia IS NULL OR data_fim_recorrencia >= CURDATE())";
$res = $conexao->query($sql);
if (!$res) {
    die('Erro ao buscar receitas recorrentes: ' . $conexao->error);
}

while ($r = $res->fetch_assoc()) {
    // Descobre a última data lançada para esta recorrência
    $id = $r['R_id_receita'];
    $descricao = $r['R_descricao'];
    $valor = $r['R_valor'];
    $tipo = $r['R_tipo'];
    $origem = $r['R_origem'];
    $origem_id = $r['R_origem_id'];
    $periodicidade = $r['periodicidade'];
    $data_fim = $r['data_fim_recorrencia'];
    $data_ultima = $r['R_data'];

    // Busca o último lançamento desta recorrência
    $sqlUlt = "SELECT MAX(R_data) as ultima_data FROM receitas WHERE recorrente = 1 AND R_descricao = ? AND R_valor = ? AND periodicidade = ? AND R_origem = ? AND R_origem_id = ?";
    $stmtUlt = $conexao->prepare($sqlUlt);
    $stmtUlt->bind_param('sdssi', $descricao, $valor, $periodicidade, $origem, $origem_id);
    $stmtUlt->execute();
    $resUlt = $stmtUlt->get_result();
    $rowUlt = $resUlt->fetch_assoc();
    $data_ultima = $rowUlt['ultima_data'] ?? $data_ultima;
    $stmtUlt->close();

    // Calcula próxima data
    $prox_data = null;
    if ($data_ultima) {
        $dt = new DateTime($data_ultima);
        if ($periodicidade === 'mensal') {
            $dt->modify('+1 month');
        } elseif ($periodicidade === 'semanal') {
            $dt->modify('+1 week');
        } elseif ($periodicidade === 'anual') {
            $dt->modify('+1 year');
        }
        $prox_data = $dt->format('Y-m-d');
    }

    // Só lança se a próxima data for hoje ou anterior (e não existir lançamento para essa data)
    if ($prox_data && $prox_data <= date('Y-m-d')) {
        // Verifica se já existe lançamento para a próxima data
        $sqlCheck = "SELECT COUNT(*) as total FROM receitas WHERE recorrente = 1 AND R_descricao = ? AND R_valor = ? AND periodicidade = ? AND R_origem = ? AND R_origem_id = ? AND R_data = ?";
        $stmtCheck = $conexao->prepare($sqlCheck);
        $stmtCheck->bind_param('sdssis', $descricao, $valor, $periodicidade, $origem, $origem_id, $prox_data);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $rowCheck = $resCheck->fetch_assoc();
        $stmtCheck->close();
        if ($rowCheck['total'] == 0) {
            // Insere nova receita
            $stmtIns = $conexao->prepare("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id, recorrente, periodicidade, data_fim_recorrencia) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)");
            $stmtIns->bind_param('sdssisss', $descricao, $valor, $prox_data, $tipo, $origem, $origem_id, $periodicidade, $data_fim);
            if ($stmtIns->execute()) {
                // Atualiza saldo
                $stmtSaldo = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo + ? WHERE id = 1");
                $stmtSaldo->bind_param('d', $valor);
                $stmtSaldo->execute();
                $stmtSaldo->close();
                echo "Receita recorrente lançada para $prox_data: $descricao (€$valor)<br>\n";
            } else {
                echo "Erro ao lançar receita recorrente: " . $conexao->error . "<br>\n";
            }
            $stmtIns->close();
        }
    }
}

// Processar DESPESAS recorrentes
// Busca todas as despesas recorrentes ativas
$sql = "SELECT * FROM despesas WHERE recorrente = 1 AND (data_fim_recorrencia IS NULL OR data_fim_recorrencia >= CURDATE())";
$res = $conexao->query($sql);
if (!$res) {
    die('Erro ao buscar despesas recorrentes: ' . $conexao->error);
}

while ($d = $res->fetch_assoc()) {
    $id = $d['id'];
    $nome = $d['D_nome'];
    $valor = $d['D_valor'];
    $descricao = $d['D_descricao'];
    $periodicidade = $d['periodicidade'];
    $data_fim = $d['data_fim_recorrencia'];
    $data_ultima = $d['D_data'];

    // Busca o último lançamento desta recorrência
    $sqlUlt = "SELECT MAX(D_data) as ultima_data FROM despesas WHERE recorrente = 1 AND D_nome = ? AND D_valor = ? AND periodicidade = ?";
    $stmtUlt = $conexao->prepare($sqlUlt);
    $stmtUlt->bind_param('sds', $nome, $valor, $periodicidade);
    $stmtUlt->execute();
    $resUlt = $stmtUlt->get_result();
    $rowUlt = $resUlt->fetch_assoc();
    $data_ultima = $rowUlt['ultima_data'] ?? $data_ultima;
    $stmtUlt->close();

    // Calcula próxima data
    $prox_data = null;
    if ($data_ultima) {
        $dt = new DateTime($data_ultima);
        if ($periodicidade === 'mensal') {
            $dt->modify('+1 month');
        } elseif ($periodicidade === 'semanal') {
            $dt->modify('+1 week');
        } elseif ($periodicidade === 'anual') {
            $dt->modify('+1 year');
        }
        $prox_data = $dt->format('Y-m-d');
    }

    // Só lança se a próxima data for hoje ou anterior (e não existir lançamento para essa data)
    if ($prox_data && $prox_data <= date('Y-m-d')) {
        // Verifica se já existe lançamento para a próxima data
        $sqlCheck = "SELECT COUNT(*) as total FROM despesas WHERE recorrente = 1 AND D_nome = ? AND D_valor = ? AND periodicidade = ? AND D_data = ?";
        $stmtCheck = $conexao->prepare($sqlCheck);
        $stmtCheck->bind_param('sdss', $nome, $valor, $periodicidade, $prox_data);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        $rowCheck = $resCheck->fetch_assoc();
        $stmtCheck->close();
        if ($rowCheck['total'] == 0) {
            // Insere nova despesa
            $stmtIns = $conexao->prepare("INSERT INTO despesas (D_nome, D_valor, D_data, D_descricao, recorrente, periodicidade, data_fim_recorrencia) VALUES (?, ?, ?, ?, 1, ?, ?)");
            $stmtIns->bind_param('sdssss', $nome, $valor, $prox_data, $descricao, $periodicidade, $data_fim);
            if ($stmtIns->execute()) {
                // Atualiza saldo
                $stmtSaldo = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo - ? WHERE id = 1");
                $stmtSaldo->bind_param('d', $valor);
                $stmtSaldo->execute();
                $stmtSaldo->close();
                echo "Despesa recorrente lançada para $prox_data: $nome (€$valor)<br>\n";
            } else {
                echo "Erro ao lançar despesa recorrente: " . $conexao->error . "<br>\n";
            }
            $stmtIns->close();
        }
    }
}

echo "Processamento concluído."; 