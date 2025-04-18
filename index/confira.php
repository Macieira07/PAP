<?php
// calendario_completo.php
include '../conexao.php';

$reservas = [];
$sql = "SELECT R_data_checkin, R_data_checkout FROM reservas WHERE R_estado IN ('pendente', 'confirmada')";
$result = $conexao->query($sql);

while ($row = $result->fetch_assoc()) {
    $inicio = new DateTime($row['R_data_checkin']);
    $fim = new DateTime($row['R_data_checkout']);
    while ($inicio <= $fim) {
        $reservas[] = $inicio->format('Y-m-d');
        $inicio->modify('+1 day');
    }
}
$conexao->close();
?>


<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendário de Disponibilidade - Quinta Flores</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <link rel="stylesheet" href="index.css">
  <link href="https://fonts.googleapis.com/css2?family=Garamond&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #6A0DAD;
      --primary-color-dark: #A56EFF;
      --text-dark: #0c0a09;
      --text-light: #78716c;
      --white: #ffffff;
    }

    body {
      font-family: "Garamond", serif;
      color: var(--text-dark);
      background-color: #f9f7f5;
    }

    h1 {
      text-align: center;
      font-family: "Garamond", serif;
      color: var(--primary-color);
      margin-top: 2rem;
      font-size: 2.5rem;
    }

    #calendar {
      max-width: 900px;
      margin: 2rem auto;
      background: var(--white);
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 20px rgba(106, 13, 173, 0.15);
      font-family: "Garamond", serif;
    }

    /* Customizing FullCalendar styles */
    .fc-header-toolbar {
      margin-bottom: 1.5rem !important;
    }

    .fc-button {
      background-color: var(--primary-color) !important;
      border-color: var(--primary-color) !important;
      font-family: "Garamond", serif !important;
      font-size: 1rem !important;
      text-transform: none !important;
      padding: 8px 16px !important;
      transition: background-color 0.3s ease !important;
    }

    .fc-button:hover {
      background-color: var(--primary-color-dark) !important;
    }

    .fc-button-active {
      background-color: var(--primary-color-dark) !important;
    }

    .fc-day-today {
      background-color: rgba(165, 110, 255, 0.1) !important;
    }

    .fc-event {
      background-color: var(--primary-color) !important;
      border-color: var(--primary-color) !important;
      font-family: "Garamond", serif !important;
      padding: 2px 5px !important;
      font-size: 0.9rem !important;
    }

    .fc-daygrid-day-number, .fc-col-header-cell-cushion {
      color: var(--text-dark) !important;
      text-decoration: none !important;
      font-family: "Garamond", serif !important;
    }

    .fc-col-header-cell {
      background-color: rgba(106, 13, 173, 0.05);
      padding: 10px 0 !important;
    }

    .button-container {
      text-align: center;
      margin-top: 2rem;
      margin-bottom: 2rem;
    }

    .button-container a {
      text-decoration: none;
      margin: 0 10px;
    }

    .animated-button1 {
      display: inline-block;
      padding: 12px 24px;
      background-color: var(--primary-color);
      color: var(--white);
      text-decoration: none;
      border-radius: 8px;
      font-family: "Garamond", serif;
      font-size: 1rem;
      font-weight: 500;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
      margin: 10px 5px;
    }

    .animated-button1:hover {
      background-color: var(--primary-color-dark);
      transform: translateY(-3px);
    }
  </style>
</head>
<body>
  <h1>Disponibilidade da Quinta Flores</h1>
  <div id="calendar"></div>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const datasOcupadas = <?php echo json_encode($reservas); ?>;

      const events = datasOcupadas.map(data => ({
        title: 'Reservado',
        start: data,
        allDay: true,
        color: '#6A0DAD'  // Using the primary color from your theme
      }));

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt',
        selectable: false,
        events: events,
        height: 'auto',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,dayGridWeek'
        },
        buttonText: {
          today: 'Hoje',
          month: 'Mês',
          week: 'Semana'
        },
        titleFormat: { 
          month: 'long', 
          year: 'numeric' 
        },
        firstDay: 1, // Start on Monday
        fixedWeekCount: false,
        showNonCurrentDates: true,
        dayMaxEvents: true
      });

      calendar.render();
    });
  </script>

  <!-- Rodapé da página -->
  <footer class="footer" id="contactos">
    <div class="section__container footer__container">
      <div class="footer__col">
        <div class="logo">
          <a href="#contactos"><img src="../logotipos/logotipo2.png" alt="logo" /></a>
        </div>
        <p class="section__description">
          Viva uma experiência única de conforto e hospitalidade no nosso alojamento local, onde cada detalhe foi pensado para tornar a sua estadia inesquecível. Explore a tranquilidade, a beleza e a cultura da região enquanto desfruta de momentos de puro relaxamento.
        </p>
        <a href="login/login.html" class="animated-button1">
          <span></span><span></span><span></span><span></span>
          Fazer Reserva
        </a>
        <a href="../index.html" class="animated-button1">
          <span></span><span></span><span></span><span></span>
          Página Inicial
        </a>
      </div>
      <div class="footer__col">
        <h4>Descubra Ponte de Lima</h4>
        <ul class="footer__links">
          <li><a href="../rodape/atracoes.html">Atrações e Atividades Locais</a></li>
          <li><a href="../rodape/gastronomia.html">Gastronomia e Restaurantes</a></li>
          <li><a href="../rodape/visitar.html">O que visitar no conselho de Ponte de Lima</a></li>
          <li><a href="../rodape/trilhos.html">Trilhos e Percursos</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>NOSSOS SERVIÇOS</h4>
        <ul class="footer__links">
          <li><a href="#">Atendimento Personalizado</a></li>
          <li><a href="#">Opções de Reserva Flexíveis</a></li>
          <li><a href="#">Transferes para o Aeroporto</a></li>
          <li><a href="#">Experiências Locais & Lazer</a></li>
          <li><a href="#">Serviço de Limpeza Diário</a></li>
          <li><a href="#">Guias Locais e Dicas Personalizadas</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4>CONTACTOS</h4>
        <ul class="footer__links">
          <li><a href="mailto:quinta.flores19@gmail.com?subject=Olá&body=Gostaria de mais informações">quinta.flores19@gmail.com</a></li>
          <li><a href="https://wa.me/351912418976" target="_blank" rel="noopener noreferrer">+351 912 418 976</a></li>
          <li><a href="https://www.instagram.com/quintaflores19?igsh=MTAzbXg0OXB3emducA==" target="_blank">@quintaflores19</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bar">
      Copyright © 2025 QUINTA FLORES. Todos os direitos reservados.
    </div>
  </footer>
  <script src="https://unpkg.com/scrollreveal"></script>
  <script src="main.js"></script>
</body>
</html>