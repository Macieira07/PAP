<?php
session_start();
require_once '../conexao.php';

// Configurações
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#6A0DAD');
define('SECONDARY_COLOR', '#A56EFF');
define('BACKGROUND_COLOR', '#f8f9fa');
define('TEXT_COLOR', '#333333');
define('LIGHT_COLOR', '#f8f8ff');

if ($conexao->connect_error) {
    die('<div class="error-container">Falha na conexão: ' . $conexao->connect_error . '</div>');
}

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

// Função para gerar todas as datas entre check-in e check-out
function gerarDatasEntre($start, $end) {
    $dates = [];
    $current = strtotime($start);
    $end = strtotime($end);
    
    while($current < $end) { // Note que usamos < em vez de <= para não incluir o último dia
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    
    return $dates;
}

// Gera todas as datas ocupadas
$todasDatasOcupadas = [];
foreach ($periodosOcupados as $periodo) {
    $todasDatasOcupadas = array_merge($todasDatasOcupadas, 
        gerarDatasEntre($periodo['from'], $periodo['to']));
}

// Remove duplicatas e ordena
$todasDatasOcupadas = array_unique($todasDatasOcupadas);
sort($todasDatasOcupadas);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $checkin = isset($_POST['checkin']) ? trim($_POST['checkin']) : '';
    $checkout = isset($_POST['checkout']) ? trim($_POST['checkout']) : '';
    $num_hospedes = isset($_POST['num_hospedes']) ? intval($_POST['num_hospedes']) : 2;

    // Validação das datas
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
        // Verifica disponibilidade da casa
        $query = "SELECT * FROM reservas 
                  WHERE (R_data_checkin <= ? AND R_data_checkout > ?) 
                  OR (R_data_checkin < ? AND R_data_checkout >= ?)";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param('ssss', $checkout, $checkin, $checkout, $checkin);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $erro = 'A casa já está reservada para as datas selecionadas. Escolha outras datas.';
        } else {
            // Define os dados na sessão
            $_SESSION['checkin'] = $checkin;
            $_SESSION['checkout'] = $checkout;
            $_SESSION['num_hospedes'] = $num_hospedes;
            header('Location: pagina2.php');
            exit();
        }
    }
}

// Calcula número de noites se datas existirem
$num_noites = 0;
if (isset($_POST['checkin']) && isset($_POST['checkout'])) {
    $checkin_date = new DateTime($_POST['checkin']);
    $checkout_date = new DateTime($_POST['checkout']);
    $num_noites = $checkin_date->diff($checkout_date)->days;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
    <style>
        :root {
            --primary-color: <?= PRIMARY_COLOR ?>;
            --secondary-color: <?= SECONDARY_COLOR ?>;
            --background-color: <?= BACKGROUND_COLOR ?>;
            --text-color: <?= TEXT_COLOR ?>;
            --light-color: <?= LIGHT_COLOR ?>;
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            counter-reset: step;
        }
        
        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .progress-step:before {
            content: counter(step);
            counter-increment: step;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border: 2px solid var(--primary-color);
            display: block;
            text-align: center;
            margin: 0 auto 10px;
            border-radius: 50%;
            background-color: white;
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .progress-step.active:before {
            background-color: var(--primary-color);
            color: white;
        }
        
        .progress-step:after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: #ddd;
            top: 15px;
            left: -50%;
            z-index: -1;
        }
        
        .progress-step:first-child:after {
            content: none;
        }
        
        .progress-step.active:after,
        .progress-step.completed:after {
            background-color: var(--primary-color);
        }
        
        .resumo-reserva {
            background-color: var(--light-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }
        
        .resumo-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .resumo-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .resumo-item span:first-child {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        .input-group select {
            flex: 1;
        }
        
        .input-group input {
            flex: 3;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .btn {
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        
        .error-message {
            color: #dc3545;
            margin-top: 10px;
            padding: 10px;
            background-color: #ffecec;
            border-radius: 5px;
            display: none;
        }
        
        /* Estilos para datas reservadas */
        .flatpickr-day.reserved {
            background-color: #ff0000 !important;
            color: white !important;
            border-color: #ff0000 !important;
        }

        .flatpickr-day.reserved:hover {
            background-color: #cc0000 !important;
            border-color: #cc0000 !important;
        }
        
        @media (max-width: 768px) {
            .progress-steps {
                font-size: 14px;
            }
            
            .progress-step:before {
                width: 25px;
                height: 25px;
                line-height: 25px;
            }
            
            .progress-step:after {
                top: 12px;
            }
            
            .form-actions {
                flex-direction: column-reverse;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
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
                <a href="../index.php" class="btn btn-secondary">
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
</body>
</html>