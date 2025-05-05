<?php
require_once '../conexao.php';  

try {
    // Receber dados do POST
    $data = json_decode(file_get_contents('php://input'), true);
    
    $checkin = $data['checkin'];
    $checkout = $data['checkout'];
    $num_hospedes = $data['guests'];
    
    // Validar datas
    if (empty($checkin) || empty($checkout)) {
        throw new Exception("Datas de check-in e check-out são obrigatórias.");
    }
    
    if (strtotime($checkout) <= strtotime($checkin)) {
        throw new Exception("A data de check-out deve ser posterior à data de check-in.");
    }
    
    // Verificar disponibilidade na base de dados
    $query = "SELECT C_id_casa, C_nome, C_descricao, C_capacidade, C_preco_noite, C_caracteristicas 
              FROM casas 
              WHERE C_id_casa = 1 
              AND C_estado = 'disponível' 
              AND C_id_casa NOT IN (
                  SELECT R_id_casa 
                  FROM reservas 
                  WHERE (
                      (R_data_checkin <= ? AND R_data_checkout >= ?) OR
                      (R_data_checkin >= ? AND R_data_checkin < ?) OR
                      (R_data_checkout > ? AND R_data_checkout <= ?)
                  )
                  AND R_estado IN ('confirmada', 'pendente')
              )";
    
    $stmt = $conexao->prepare($query);
    $stmt->bind_param("ssssss", $checkout, $checkin, $checkin, $checkout, $checkin, $checkout);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $casa = $result->fetch_assoc();
        
        // Calcular número de noites e preço total
        $date1 = new DateTime($checkin);
        $date2 = new DateTime($checkout);
        $interval = $date1->diff($date2);
        $num_noites = $interval->days;
        $preco_total = $num_noites * $casa['C_preco_noite'];
        
        // Formatar resposta
        $response = [
            'disponivel' => true,
            'casa' => [
                'id' => $casa['C_id_casa'],
                'nome' => $casa['C_nome'],
                'descricao' => $casa['C_descricao'],
                'capacidade' => $casa['C_capacidade'],
                'preco_noite' => $casa['C_preco_noite'],
                'caracteristicas' => $casa['C_caracteristicas'],
                'num_noites' => $num_noites,
                'preco_total' => $preco_total
            ]
        ];
    } else {
        $response = [
            'disponivel' => false,
            'mensagem' => 'A Quinta Flores não está disponível para as datas selecionadas.'
        ];
    }
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Verificar disponibilidade de alojamento na Quinta Flores em Ponte de Lima">
  
  <!-- Pré-carregamentos -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  
  <!-- Fontes -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Bibliotecas de Ícones -->
  <script src="https://kit.fontawesome.com/6dda5f6271.js" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet"/>
  
  <!-- Título da página exibido na aba do navegador -->
  <title>Verificar Disponibilidade | QUINTA FLORES</title>
  <link rel="icon" type="image/png" href="assets/logos/logotipo1.png" sizes="1000x1000">

  <style>
    /* Variáveis de estilo - Mesmas do seu CSS original */
    :root {
      --primary-color: #4a805b;
      --primary-light: #e8f0ea;
      --primary-dark: #3a704b;
      --text-color: #333;
      --text-light: #777;
      --bg-color: #fff;
      --bg-light: #f5f5f5;
      --border-color: #e0e0e0;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }

    [data-theme="dark"] {
      --primary-color: #5a906b;
      --primary-light: #2a3a30;
      --primary-dark: #4a805b;
      --text-color: #eee;
      --text-light: #aaa;
      --bg-color: #222;
      --bg-light: #333;
      --border-color: #444;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Estilos Base */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Inter", sans-serif;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background-color: var(--bg-light);
      color: var(--text-color);
      line-height: 1.6;
    }

    a {
      text-decoration: none;
      color: var(--primary-color);
      transition: var(--transition);
    }

    a:hover {
      color: var(--primary-dark);
    }

    img {
      max-width: 100%;
      height: auto;
    }

    ul {
      list-style: none;
    }

    /* Header e Navegação */
    .header {
      background-color: var(--bg-color);
      box-shadow: var(--shadow);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 100;
    }

    nav {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .nav__bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px;
    }

    .logo img {
      height: 50px;
    }

    .nav__links {
      display: flex;
      gap: 20px;
    }

    .nav__links li a {
      color: var(--text-color);
      font-weight: 500;
      padding: 8px 12px;
      border-radius: 4px;
      transition: var(--transition);
    }

    .nav__links li a:hover {
      background-color: var(--primary-light);
      color: var(--primary-dark);
    }

    .language-selector {
      display: flex;
      gap: 8px;
      margin-left: 20px;
    }

    .language-flag {
      width: 24px;
      height: 16px;
      cursor: pointer;
      opacity: 0.6;
      transition: var(--transition);
    }

    .language-flag.active, .language-flag:hover {
      opacity: 1;
    }

    .theme-toggle {
      display: flex;
      align-items: center;
      margin-left: 20px;
    }

    .theme-icon {
      margin: 0 8px;
      color: var(--text-light);
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 40px;
      height: 20px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .theme-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: var(--border-color);
      transition: var(--transition);
      border-radius: 20px;
    }

    .theme-slider:before {
      position: absolute;
      content: "";
      height: 16px;
      width: 16px;
      left: 2px;
      bottom: 2px;
      background-color: white;
      transition: var(--transition);
      border-radius: 50%;
    }

    input:checked + .theme-slider {
      background-color: var(--primary-color);
    }

    input:checked + .theme-slider:before {
      transform: translateX(20px);
    }

    .mobile-menu-btn {
      display: none;
      background: none;
      border: none;
      color: var(--text-color);
      font-size: 24px;
      cursor: pointer;
    }

    /* Estilos para o conteúdo principal */
    .main-container {
      max-width: 1200px;
      margin: 100px auto 40px;
      padding: 0 20px;
    }

    .page-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .page-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      color: var(--primary-dark);
    }

    .page-header p {
      color: var(--text-light);
      font-size: 1.1rem;
    }

    /* Sistema de Disponibilidade */
    .availability-system {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      margin-bottom: 40px;
    }

    .search-section {
      flex: 1;
      min-width: 300px;
      background-color: var(--bg-color);
      border-radius: 10px;
      padding: 25px;
      box-shadow: var(--shadow);
    }

    .search-section h2 {
      margin-bottom: 20px;
      color: var(--primary-dark);
      font-size: 1.5rem;
      border-bottom: 2px solid var(--primary-light);
      padding-bottom: 10px;
    }

    .search-form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-group label {
      font-weight: 500;
      color: var(--text-color);
    }

    .form-group input, 
    .form-group select {
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      background-color: var(--bg-light);
      color: var(--text-color);
      font-size: 16px;
      transition: var(--transition);
    }

    .form-group input:focus, 
    .form-group select:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 2px var(--primary-light);
    }

    .search-btn {
      margin-top: 10px;
      padding: 14px 20px;
      background-color: var(--primary-color);
      color: white;
      font-weight: 600;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: var(--transition);
      font-size: 16px;
    }

    .search-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }

    .results-section {
      flex: 2;
      min-width: 300px;
      background-color: var(--bg-color);
      border-radius: 10px;
      padding: 25px;
      box-shadow: var(--shadow);
    }

    .results-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 2px solid var(--primary-light);
      padding-bottom: 10px;
    }

    .results-header h2 {
      color: var(--primary-dark);
      font-size: 1.5rem;
    }

    .results-filter {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .results-filter select {
      padding: 8px 12px;
      border: 1px solid var(--border-color);
      border-radius: 6px;
      background-color: var(--bg-light);
      color: var(--text-color);
    }

    .calendar-view {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
      margin-bottom: 20px;
    }

    .calendar-view th {
      background-color: var(--primary-light);
      color: var(--primary-dark);
      font-weight: 600;
      padding: 12px;
      text-align: center;
      border-top: 1px solid var(--border-color);
      border-bottom: 1px solid var(--border-color);
    }

    .calendar-view th:first-child {
      border-left: 1px solid var(--border-color);
      border-top-left-radius: 6px;
    }

    .calendar-view th:last-child {
      border-right: 1px solid var(--border-color);
      border-top-right-radius: 6px;
    }

    .calendar-view td {
      padding: 12px;
      text-align: center;
      border-bottom: 1px solid var(--border-color);
      border-left: 1px solid var(--border-color);
    }

    .calendar-view td:last-child {
      border-right: 1px solid var(--border-color);
    }

    .calendar-view tr:last-child td:first-child {
      border-bottom-left-radius: 6px;
    }

    .calendar-view tr:last-child td:last-child {
      border-bottom-right-radius: 6px;
    }

    .calendar-view .date-number {
      font-weight: 600;
      margin-bottom: 5px;
    }

    .calendar-view .available {
      color: #27ae60;
      font-weight: 600;
    }

    .calendar-view .unavailable {
      color: #e74c3c;
      font-weight: 600;
    }

    .calendar-view .limited {
      color: #f39c12;
      font-weight: 600;
    }

    .calendar-cell {
      height: 100px;
      min-width: 120px;
      position: relative;
    }

    .cell-content {
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .house-details {
      margin-top: 30px;
      background-color: var(--bg-light);
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .house-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .house-name {
      font-size: 1.5rem;
      color: var(--primary-dark);
    }

    .house-price {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary-dark);
    }

    .house-price small {
      font-size: 1rem;
      color: var(--text-light);
    }

    .house-description {
      margin-bottom: 20px;
      line-height: 1.7;
    }

    .house-features {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }

    .house-feature {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .house-feature i {
      color: var(--primary-color);
      font-size: 1.2rem;
    }

    .book-now-container {
      text-align: center;
      margin-top: 30px;
    }

    .book-now-btn {
      padding: 15px 30px;
      background-color: var(--primary-color);
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      font-size: 1.1rem;
      cursor: pointer;
      transition: var(--transition);
    }

    .book-now-btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Estilo para o resultado vazio quando não há disponibilidade */
    .no-results {
      text-align: center;
      padding: 40px 0;
    }

    .no-results i {
      font-size: 48px;
      color: var(--text-light);
      margin-bottom: 15px;
    }

    .no-results p {
      font-size: 1.2rem;
      color: var(--text-color);
      margin-bottom: 20px;
    }

    /* Rodapé */
    .footer {
      background-color: var(--bg-color);
      padding: 40px 0 0;
      margin-top: 60px;
    }

    .footer__container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .footer__col h4 {
      margin-bottom: 20px;
      color: var(--primary-dark);
      font-size: 1.2rem;
    }

    .footer__links li {
      margin-bottom: 10px;
    }

    .footer__links li a,
    .footer__links li span {
      color: var(--text-light);
      transition: var(--transition);
    }

    .footer__links li a:hover {
      color: var(--primary-color);
      padding-left: 5px;
    }

    .footer__links li i {
      margin-right: 10px;
      color: var(--primary-color);
    }

    .footer__socials {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .footer__socials a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background-color: var(--primary-light);
      color: var(--primary-dark);
      border-radius: 50%;
      transition: var(--transition);
    }

    .footer__socials a:hover {
      background-color: var(--primary-color);
      color: white;
      transform: translateY(-3px);
    }

    .footer__bar {
      text-align: center;
      padding: 20px;
      margin-top: 40px;
      border-top: 1px solid var(--border-color);
      color: var(--text-light);
    }

    /* Media Queries */
    @media screen and (max-width: 1024px) {
      .nav__links {
        display: none;
      }
      
      .nav__links.active {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 80px;
        left: 0;
        width: 100%;
        background-color: var(--bg-color);
        box-shadow: var(--shadow);
        padding: 20px;
        z-index: 100;
      }
      
      .mobile-menu-btn {
        display: block;
      }

      .house-features {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      }
    }

    @media screen and (max-width: 768px) {
      .main-container {
        margin-top: 80px;
      }

      .page-header h1 {
        font-size: 2rem;
      }

      .availability-system {
        flex-direction: column;
      }

      .calendar-scroll {
        overflow-x: auto;
      }

      .calendar-view {
        min-width: 650px;
      }

      .house-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <!-- Header Section -->
  <header class="header">
    <nav>
      <div class="nav__bar">
        <div class="logo">
          <a href="index.html">
            <img src="assets/logos/logotipo1.png" alt="logo">
          </a>
        </div>
        <ul class="nav__links">
          <li><a href="index.html#home">Início</a></li>
          <li><a href="index.html#about">Sobre Nós</a></li>
          <li><a href="index.html#rooms">Quartos</a></li>
          <li><a href="index.html#gallery">Galeria</a></li>
          <li><a href="index.html#amenities">Comodidades</a></li>
          <li><a href="index.html#location">Localização</a></li>
          <li><a href="index.html#contacto">Contactos</a></li>
        </ul>
        <div class="language-selector">
          <img src="assets/flags/pt.png" alt="Português" class="language-flag active" title="Português">
          <img src="assets/flags/en.png" alt="English" class="language-flag" title="English">
          <img src="assets/flags/es.png" alt="Español" class="language-flag" title="Español">
          <img src="assets/flags/fr.png" alt="Français" class="language-flag" title="Français">
        </div>
        <div class="theme-toggle">
          <i class="ri-sun-line theme-icon"></i>
          <label class="switch">
            <input type="checkbox" id="themeToggle">
            <span class="theme-slider"></span>
          </label>
          <i class="ri-moon-line theme-icon"></i>
        </div>
        <button class="mobile-menu-btn">
          <i class="ri-menu-line"></i>
        </button>
      </div>
    </nav>
  </header>

  <!-- Main Content -->
  <main class="main-container">
    <div class="page-header">
      <h1>Verifique a Disponibilidade</h1>
      <p>Verifique as datas disponíveis para alugar a Quinta Flores completa</p>
    </div>

    <div class="availability-system">
      <!-- Filtros e Pesquisa -->
      <aside class="search-section">
        <h2>Pesquisar Disponibilidade</h2>
        <form class="search-form">
          <div class="form-group">
            <label for="checkin">Data de Chegada</label>
            <input type="date" id="checkin" required>
          </div>
          
          <div class="form-group">
            <label for="checkout">Data de Partida</label>
            <input type="date" id="checkout" required>
          </div>
          
          <div class="form-group">
            <label for="guests">Número de Hóspedes</label>
            <select id="guests" required>
              <option value="1">1 Pessoa</option>
              <option value="2">2 Pessoas</option>
              <option value="3">3 Pessoas</option>
              <option value="4">4 Pessoas</option>
              <option value="5">5 Pessoas</option>
              <option value="6">6 Pessoas</option>
              <option value="7">7 Pessoas</option>
              <option value="8">8 Pessoas</option>
              <option value="9">9 Pessoas</option>
              <option value="10" selected>10 Pessoas</option>
            </select>
          </div>
          
          <button type="button" class="search-btn" id="searchBtn">Verificar Disponibilidade</button>
        </form>

        <div class="amenities-filter">
          <h3>Comodidades</h3>
          <div class="amenity-option">
            <input type="checkbox" id="wifi" checked>
            <label for="wifi">Wi-Fi Gratuito</label>
          </div>
          <div class="amenity-option">
            <input type="checkbox" id="pool" checked>
            <label for="pool">Acesso à Piscina</label>
          </div>
          <div class="amenity-option">
            <input type="checkbox" id="breakfast" checked>
            <label for="breakfast">Pequeno-Almoço Incluído</label>
          </div>
          <div class="amenity-option">
            <input type="checkbox" id="parking" checked>
            <label for="parking">Estacionamento Gratuito</label>
          </div>
          <div class="amenity-option">
            <input type="checkbox" id="bikes">
            <label for="bikes">Bicicletas</label>
          </div>
        </div>
      </aside>

      <!-- Resultados -->
      <section class="results-section">
        <div class="results-header">
          <h2>Disponibilidade</h2>
          <div class="results-filter">
            <label for="view-type">Ver por:</label>
            <select id="view-type">
              <option value="list">Lista</option>
              <option value="calendar">Calendário</option>
            </select>
          </div>
        </div>

        <!-- Vista de Calendário -->
        <div class="calendar-view-container" id="calendarView" style="display:none">
          <div class="calendar-scroll">
            <table class="calendar-view">
              <thead>
                <tr>
                  <th>Segunda</th>
                  <th>Terça</th>
                  <th>Quarta</th>
                  <th>Quinta</th>
                  <th>Sexta</th>
                  <th>Sábado</th>
                  <th>Domingo</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">1</div>
                      <div class="available">Disponível</div>
                    </div>
                  </td>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">2</div>
                      <div class="available">Disponível</div>
                    </div>
                  </td>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">3</div>
                      <div class="available">Disponível</div>
                    </div>
                  </td>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">4</div>
                      <div class="available">Disponível</div>
                    </div>
                  </td>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">5</div>
                      <div class="unavailable">Indisponível</div>
                    </div>
                  </td>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">6</div>
                      <div class="unavailable">Indisponível</div>
                    </div>
                  </td>
                  <td class="calendar-cell">
                    <div class="cell-content">
                      <div class="date-number">7</div>
                      <div class="unavailable">Indisponível</div>
                    </div>
                  </td>
                </tr>
                <!-- Mais linhas do calendário podem ser adicionadas aqui -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- Vista de Lista -->
        <div class="list-view-container" id="listView">
          <div class="house-details">
            <div class="house-header">
              <h2 class="house-name">Quinta Flores - Casa Completa</h2>
              <div class="house-price">€120 <small>por noite</small></div>
            </div>
            
            <div class="house-description">
              <p>Alugue a Quinta Flores completa para até 10 pessoas. Desfrute de toda a privacidade e comodidade da nossa casa de campo, com piscina, jardins e todas as comodidades necessárias para uma estadia memorável.</p>
            </div>
            
            <div class="house-features">
              <div class="house-feature">
                <i class="ri-user-fill"></i>
                <span>Capacidade: 10 pessoas</span>
              </div>
              <div class="house-feature">
                <i class="ri-home-4-fill"></i>
                <span>3 quartos</span>
              </div>
              <div class="house-feature">
                <i class="ri-swimming-pool-fill"></i>
                <span>Piscina privada</span>
              </div>
              <div class="house-feature">
                <i class="ri-wifi-fill"></i>
                <span>Wi-Fi gratuito</span>
              </div>
              <div class="house-feature">
                <i class="ri-car-fill"></i>
                <span>Estacionamento</span>
              </div>
              <div class="house-feature">
                <i class="ri-restaurant-fill"></i>
                <span>Cozinha equipada</span>
              </div>
            </div>
            
            <div class="book-now-container">
              <button class="book-now-btn">Reservar Agora</button>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>

  <!-- Rodapé -->
  <footer class="footer">
    <div class="footer__container">
      <div class="footer__col">
        <h4>Quinta Flores</h4>
        <ul class="footer__links">
          <li><a href="index.html#home">Início</a></li>
          <li><a href="index.html#about">Sobre Nós</a></li>
          <li><a href="index.html#rooms">Quartos</a></li>
          <li><a href="index.html#gallery">Galeria</a></li>
          <li><a href="index.html#amenities">Comodidades</a></li>
          <li><a href="index.html#location">Localização</a></li>
          <li><a href="index.html#contacto">Contactos</a></li>
        </ul>
      </div>
      
      <div class="footer__col">
        <h4>Contactos</h4>
        <ul class="footer__links">
          <li>
            <i class="ri-map-pin-fill"></i>
            <span>Ponte de Lima, Portugal</span>
          </li>
          <li>
            <i class="ri-phone-fill"></i>
            <a href="tel:+351123456789">+351 123 456 789</a>
          </li>
          <li>
            <i class="ri-mail-fill"></i>
            <a href="mailto:info@quintaflores.com">info@quintaflores.com</a>
          </li>
        </ul>
      </div>
      
      <div class="footer__col">
        <h4>Redes Sociais</h4>
        <div class="footer__socials">
          <a href="#"><i class="ri-facebook-fill"></i></a>
          <a href="#"><i class="ri-instagram-fill"></i></a>
          <a href="#"><i class="ri-pinterest-fill"></i></a>
          <a href="#"><i class="ri-youtube-fill"></i></a>
        </div>
      </div>
    </div>
    
    <div class="footer__bar">
      <p>© 2023 Quinta Flores. Todos os direitos reservados.</p>
    </div>
  </footer>

  <script>
   <script>
    // Toggle do menu mobile
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav__links');
    
    mobileMenuBtn.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });
    
    // Toggle do tema claro/escuro
    const themeToggle = document.getElementById('themeToggle');
    const htmlElement = document.documentElement;
    
    // Verificar preferência do usuário
    if (localStorage.getItem('theme') === 'dark') {
        htmlElement.setAttribute('data-theme', 'dark');
        themeToggle.checked = true;
    }
    
    themeToggle.addEventListener('change', () => {
        if (themeToggle.checked) {
            htmlElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        } else {
            htmlElement.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
        }
    });
    
    // Alternar entre visualização de lista e calendário
    const viewTypeSelect = document.getElementById('view-type');
    const calendarView = document.getElementById('calendarView');
    const listView = document.getElementById('listView');
    
    viewTypeSelect.addEventListener('change', () => {
        if (viewTypeSelect.value === 'calendar') {
            calendarView.style.display = 'block';
            listView.style.display = 'none';
        } else {
            calendarView.style.display = 'none';
            listView.style.display = 'block';
        }
    });
    
    // Seleção de idioma
    const languageFlags = document.querySelectorAll('.language-flag');
    
    languageFlags.forEach(flag => {
        flag.addEventListener('click', () => {
            languageFlags.forEach(f => f.classList.remove('active'));
            flag.classList.add('active');
            // Aqui você pode adicionar a lógica para mudar o idioma
        });
    });
    
    // Botão de pesquisa - Verificar disponibilidade
    const searchBtn = document.getElementById('searchBtn');
    const availabilityForm = document.getElementById('availabilityForm');
    
    availabilityForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const checkin = document.getElementById('checkin').value;
        const checkout = document.getElementById('checkout').value;
        const guests = document.getElementById('guests').value;
        
        try {
            const response = await fetch('check_availability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    checkin,
                    checkout,
                    guests
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Erro ao verificar disponibilidade');
            }
            
            if (data.disponivel) {
                // Atualizar a visualização com os dados da casa
                updateHouseDetails(data.casa);
                
                // Mostrar mensagem de sucesso
                alert(`Disponível! Preço total: €${data.casa.preco_total} (${data.casa.num_noites} noites)`);
            } else {
                // Mostrar mensagem de indisponibilidade
                alert(data.mensagem);
            }
            
        } catch (error) {
            console.error('Erro:', error);
            alert(error.message);
        }
    });
    
    // Função para atualizar os detalhes da casa
    function updateHouseDetails(casa) {
        document.querySelector('.house-name').textContent = casa.nome;
        document.querySelector('.house-price').innerHTML = `€${casa.preco_noite} <small>por noite</small>`;
        document.querySelector('.house-description p').textContent = casa.descricao;
        
        // Atualizar características (opcional)
        // Você pode adicionar mais lógica aqui para exibir as características específicas
    }
    
    // Carregar calendário de disponibilidade (opcional)
    async function loadAvailabilityCalendar() {
        try {
            const response = await fetch('get_availability_calendar.php');
            const data = await response.json();
            
            if (response.ok) {
                // Atualizar o calendário com os dados de disponibilidade
                updateCalendar(data);
            }
        } catch (error) {
            console.error('Erro ao carregar calendário:', error);
        }
    }
    
    // Chamar a função para carregar o calendário quando a página carrega
    window.addEventListener('load', () => {
        if (viewTypeSelect.value === 'calendar') {
            loadAvailabilityCalendar();
        }
    });
</script>
  </script>
</body>
</html>