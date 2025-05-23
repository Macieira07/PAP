<?php
session_start();
require_once '../conexao.php';

$page_title = 'Faça sua Reserva';

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$id_hospede = $_SESSION['id'];

// Consulta os períodos já reservados
$query = "SELECT R_data_checkin, R_data_checkout FROM reservas";
$resultado = $conexao->query($query);

$periodosOcupados = [];
while ($row = $resultado->fetch_assoc()) {
    $periodosOcupados[] = [
        'from' => $row['R_data_checkin'],
        'to' => $row['R_data_checkout']
    ];
}

function gerarDatasEntre($start, $end) {
    $dates = [];
    $current = strtotime($start);
    $end = strtotime($end);
    while ($current < $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}

$todasDatasOcupadas = [];
foreach ($periodosOcupados as $periodo) {
    $todasDatasOcupadas = array_merge($todasDatasOcupadas, gerarDatasEntre($periodo['from'], $periodo['to']));
}
$todasDatasOcupadas = array_unique($todasDatasOcupadas);
sort($todasDatasOcupadas);

// PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $num_hospedes = intval($_POST['num_hospedes'] ?? 2);

    $hoje = new DateTime();
    $amanha = (new DateTime())->modify('+1 day');
    $dataLimite = (new DateTime())->modify('+2 years');

    $dataCheckin = DateTime::createFromFormat('Y-m-d', $checkin);
    $dataCheckout = DateTime::createFromFormat('Y-m-d', $checkout);

    if (!$dataCheckin || !$dataCheckout) {
        $erro = 'Datas inválidas!';
    } elseif ($dataCheckin < $amanha || $dataCheckout <= $dataCheckin) {
        $erro = 'Datas inválidas! O check-in deve ser a partir de amanhã e o check-out depois do check-in.';
    } elseif ($dataCheckin > $dataLimite || $dataCheckout > $dataLimite) {
        $erro = 'Reservas só são permitidas até ' . $dataLimite->format('d/m/Y') . '.';
    } else {
        $query = "SELECT * FROM reservas 
                  WHERE (R_data_checkin <= ? AND R_data_checkout > ?) 
                  OR (R_data_checkin < ? AND R_data_checkout >= ?)";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param('ssss', $checkout, $checkin, $checkout, $checkin);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $erro = 'A casa já está reservada para as datas selecionadas.';
        } else {
            $_SESSION['checkin'] = $checkin;
            $_SESSION['checkout'] = $checkout;
            $_SESSION['num_hospedes'] = $num_hospedes;
            header('Location: pagina2.php');
            exit();
        }
    }
}

$num_noites = 0;
if (!empty($_POST['checkin']) && !empty($_POST['checkout'])) {
    $checkin_date = new DateTime($_POST['checkin']);
    $checkout_date = new DateTime($_POST['checkout']);
    $num_noites = $checkin_date->diff($checkout_date)->days;
}

// SÓ AQUI carregas o header.php, depois de todos os headers e validações
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
</head>
<body>
    <div class="container">
        <h1 class="fade-in">Faça sua Reserva</h1>
        
        <div class="progress-steps">
            <div class="progress-step active">
                <span>Datas</span>
            </div>
            <div class="progress-step">
                <span>Dados Pessoais</span>
            </div>
            <div class="progress-step">
                <span>Pagamento</span>
            </div>
            <div class="progress-step">
                <span>Confirmação</span>
            </div>
        </div>
        
        <?php if (!empty($erro)): ?>
            <div class="error-message" style="display: block;">
                <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
            </div>
        <?php endif; ?>
        
        <form action="pagina1.php" method="POST" id="reservaForm" class="fade-in">
            <div class="resumo-reserva">
                <h3><i class="fas fa-calendar-check"></i> Resumo da Reserva</h3>
                <div class="resumo-item">
                    <span>Check-in:</span>
                    <span id="display-checkin"><?= isset($_POST['checkin']) ? date('d/m/Y', strtotime($_POST['checkin'])) : '--/--/----' ?></span>
                </div>
                <div class="resumo-item">
                    <span>Check-out:</span>
                    <span id="display-checkout"><?= isset($_POST['checkout']) ? date('d/m/Y', strtotime($_POST['checkout'])) : '--/--/----' ?></span>
                </div>
                <div class="resumo-item">
                    <span>Noites:</span>
                    <span id="display-noites"><?= $num_noites > 0 ? $num_noites : '--' ?></span>
                </div>
                <div class="resumo-item">
                    <span>Hóspedes:</span>
                    <span id="display-hospedes"><?= $_POST['num_hospedes'] ?? '--' ?></span>
                </div>
            </div>

            <div class="form-group">
                <label for="checkin"><i class="far fa-calendar-alt"></i> Data de Check-in</label>
                <input type="text" id="checkin" name="checkin" class="form-control" placeholder="Selecione a data" required>
            </div>

            <div class="form-group">
                <label for="checkout"><i class="far fa-calendar-alt"></i> Data de Check-out</label>
                <input type="text" id="checkout" name="checkout" class="form-control" placeholder="Selecione a data" required>
            </div>

            <div class="form-group">
                <label for="num_hospedes"><i class="fas fa-users"></i> Número de Hóspedes</label>
                <select id="num_hospedes" name="num_hospedes" class="form-control" required>
                    <?php for($i=1; $i<=10; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == ($_POST['num_hospedes'] ?? 2)) ? 'selected' : '' ?>>
                            <?= $i ?> <?= $i === 1 ? 'pessoa' : 'pessoas' ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="../index.html" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <button type="submit" class="btn btn-primary pulse">
                    Continuar <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <script>
        const datasOcupadas = <?php echo json_encode($todasDatasOcupadas); ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Configuração do Flatpickr
            const checkinPicker = flatpickr("#checkin", {
                locale: "pt",
                minDate: "today",
                dateFormat: "Y-m-d",
                disable: datasOcupadas,
                onChange: function(selectedDates, dateStr) {
                    document.getElementById('display-checkin').textContent = formatarData(dateStr);
                    atualizarResumo();
                    
                    // Configura o checkout para ser pelo menos um dia após o checkin
                    checkoutPicker.set('minDate', dateStr);
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.set('maxDate', new Date().fp_incr(730)); // 2 anos
                },
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                    if (datasOcupadas.includes(dateStr)) {
                        dayElem.classList.add('reserved');
                        dayElem.title = "Indisponível";
                    }
                }
            });
            
            const checkoutPicker = flatpickr("#checkout", {
                locale: "pt",
                dateFormat: "Y-m-d",
                disable: datasOcupadas,
                onChange: function(selectedDates, dateStr) {
                    document.getElementById('display-checkout').textContent = formatarData(dateStr);
                    atualizarResumo();
                },
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                    if (datasOcupadas.includes(dateStr)) {
                        dayElem.classList.add('reserved');
                        dayElem.title = "Indisponível";
                    }
                }
            });
            
            // Atualizar número de hóspedes no resumo
            document.getElementById('num_hospedes').addEventListener('change', function() {
                const num = this.value;
                document.getElementById('display-hospedes').textContent = num + (num == 1 ? ' pessoa' : ' pessoas');
            });
            
            // Validação do formulário
            document.getElementById('reservaForm').addEventListener('submit', function(e) {
                const checkin = document.getElementById('checkin').value;
                const checkout = document.getElementById('checkout').value;
                const errorElement = document.querySelector('.error-message');
                
                if (!checkin || !checkout) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Por favor, selecione as datas de check-in e check-out.';
                    return false;
                }
                
                const hoje = new Date();
                const amanha = new Date(hoje);
                amanha.setDate(amanha.getDate() + 1);
                
                const dataCheckin = new Date(checkin);
                const dataCheckout = new Date(checkout);
                const dataLimite = new Date(hoje);
                dataLimite.setFullYear(dataLimite.getFullYear() + 2);
                
                if (dataCheckin < amanha || dataCheckout <= dataCheckin) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Datas inválidas! O check-in deve ser a partir de amanhã e o check-out depois do check-in.';
                    return false;
                }
                
                if (dataCheckin > dataLimite || dataCheckout > dataLimite) {
                    e.preventDefault();
                    errorElement.style.display = 'block';
                    errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Reservas só são permitidas até ' + formatarData(dataLimite.toISOString().split('T')[0]) + '.';
                    return false;
                }
                
                return true;
            });
        });
        
        function atualizarResumo() {
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            
            if (checkin && checkout) {
                const diffTime = Math.abs(new Date(checkout) - new Date(checkin));
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                document.getElementById('display-noites').textContent = diffDays;
            }
        }
        
        function formatarData(dataStr) {
            if (!dataStr) return '--/--/----';
            const [ano, mes, dia] = dataStr.split('-');
            return `${dia}/${mes}/${ano}`;
        }
    </script>
    <?php require_once 'footer.php'; ?>
</body>
</html>