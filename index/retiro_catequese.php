<?php
include '../components/header.php'; ?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiro - Quinta Flores</title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" type="text/css" href="lermais.css">
    <link rel="stylesheet" type="text/css" href="chatbot.css">
    <link rel="stylesheet" href="../components/header.css">
    <link rel="stylesheet" href="../components/footer.css">

</head>
<body>
    <!-- Cabeçalho/Header -->
    <!-- Main Content -->
    <main class="main-content" id="sobre">
        <section class="section">
            <div class="section__container">
                <a href="../index.html#activities" class="back-btn">
                    <i class="ri-arrow-left-line"></i>
                    Voltar às ofertas
                </a>                
                <p class="section__subheader">Oferta Especial</p>
                <h1 class="section__header">Retiro da Catequese</h1>
                
                <div class="offer-details">
                    <div class="offer-image">
                        <img src="../assets/images/retiro.avif" alt="Retiro">
                        <div class="offer-badge">4 Noites</div>
                        <div class="capacity-badge">Até 10 pessoas</div>
                    </div>
                    <div class="offer-content">
                        <h2 class="offer-title">Retiro</h2>
                        <p class="offer-price">240€</p>
                        <div class="code-highlight">Código para usar ao reservar: RETIRO240</div>
                        <p class="offer-description">
                            Reúnam a vossa gang e preparem-se para uma festa inesquecível! A nossa Quinta Flores oferece o cenário perfeito para celebrarem com os vossos amigos num ambiente natural e privado. Música, diversão e momentos únicos vos esperam numa experiência criada especialmente para grupos até 10 pessoas.
                        </p>
                    </div>
                </div>
                <!-- Includes Section -->
                <div class="includes-section" id="ofertas">
                    <h3 class="includes-title">O que está incluído na oferta</h3>
                    <div class="includes-grid">
                        <div class="include-item">
                            <i class="ri-hotel-bed-line include-icon"></i>
                            <div class="include-content">
                                <h4>4 Noites de Alojamento</h4>
                                <p>Alojamento completo para até 10 pessoas com todas as comodidades necessárias</p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-gift-line include-icon"></i>
                            <div class="include-content">
                                <h4>Velas</h4>
                                <p>Snacks, bebidas alcoólicas e não alcoólicas selecionados para a vossa celebração</p>
                            </div>
                        </div>      
                        <div class="include-item">
                            <i class="ri-music-line include-icon"></i>
                            <div class="include-content">
                                <h4>Sessão Fotográfica</h4>
                                <p>Sistema de som portátil para criarem a banda sonora perfeita da vossa festa</p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-lightbulb-line include-icon"></i>
                            <div class="include-content">
                                <h4>Materiais de escrita</h4>
                                <p>Balões, luzes decorativas e outros detalhes para criar o ambiente perfeito de festa</p>
                            </div>
                        </div>
                        
                        <div class="include-item">
                            <i class="ri-cup-line include-icon"></i>
                            <div class="include-content">
                                <h4>Ambiente acolhedor</h4>
                                <p>Pequenos-almoços caseiros para recuperarem as energias após a festa</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- CTA Section -->
                <div class="cta-section"id="reserva">
                    <h3 class="cta-title">Prontos para o retiro?</h3>
                    <p class="cta-subtitle">Reservem já as vossas 4 noites de paz e pensamentos</p>
                    <a href="../login1/pagina_login.php" class="reserve-btn">
                        Reservar Agora
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
        <script>
                    function toggleTheme() {
                const html = document.documentElement;
                const themeToggle = document.getElementById('themeToggle');
                const currentTheme = html.getAttribute('data-theme');
                const icon = themeToggle.querySelector('i');
                
                if (currentTheme === 'light') {
                    html.setAttribute('data-theme', 'dark');
                    icon.className = 'ri-moon-line';
                    localStorage.setItem('theme', 'dark');
                } else {
                    html.setAttribute('data-theme', 'light');
                    icon.className = 'ri-sun-line';
                    localStorage.setItem('theme', 'light');
                }
            }
            const savedTheme = localStorage.getItem('theme') || 'light';
            const themeToggle = document.getElementById('themeToggle');
            const icon = themeToggle.querySelector('i');
            document.documentElement.setAttribute('data-theme', savedTheme);
            icon.className = savedTheme === 'dark' ? 'ri-moon-line' : 'ri-sun-line';
            themeToggle.addEventListener('click', toggleTheme);
    </script>
    <?php
include '../components/footer.php'; ?>
</body>
</html>