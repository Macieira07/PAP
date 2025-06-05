<?php
include '../components/header.php'; ?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festa com Amigos - Quinta Flores</title>
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

    <!-- Conteúdo Principal -->
    <main class="main-content">
        <section class="section">
            <div class="section__container">
                <a href="../index.php#activities" class="back-btn">
                    <i class="ri-arrow-left-line"></i>
                    Voltar às ofertas
                </a>

                <p class="section__subheader">Oferta Irresistível</p>
                <h1 class="section__header">Festa com Amigos</h1>

                <div class="offer-details">
                    <div class="offer-image">
                        <img src="../assets/images/festa_amigos.avif" alt="Festa com Amigos">
                        <div class="offer-badge">2 Noites</div>
                        <div class="capacity-badge">Até 10 pessoas</div>
                    </div>
                    <div class="offer-content">
                        <h2 class="offer-title">Festa com Amigos</h2>
                        <p class="offer-price">260€</p>
                        <div class="code-highlight">Código promocional: <strong>PARTY260</strong></div>
                        <p class="offer-description">
                            Reúnam o grupo e venham viver um fim de semana único! A Quinta Flores é o local ideal para celebrar com quem mais gostam. Imaginem música, gargalhadas, boa comida e um ambiente acolhedor — tudo num espaço reservado só para vocês. Uma escapadinha perfeita para criar memórias que ficam para sempre.
                        </p>
                    </div>
                </div>

                <!-- O que está incluído -->
                <div class="includes-section">
                    <h3 class="includes-title">Inclui nesta oferta:</h3>
                    <div class="includes-grid">
                        <div class="include-item">
                            <i class="ri-hotel-bed-line include-icon"></i>
                            <div class="include-content">
                                <h4>2 Noites Confortáveis</h4>
                                <p>Casa equipada para até 10 pessoas, com todo o conforto e privacidade.</p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-gift-line include-icon"></i>
                            <div class="include-content">
                                <h4>Kit de Festa</h4>
                                <p>Snacks e bebidas selecionadas (com e sem álcool) para dar início à diversão.</p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-music-line include-icon"></i>
                            <div class="include-content">
                                <h4>Música no Ar</h4>
                                <p>Coluna portátil para animar a noite com a vossa playlist favorita.</p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-lightbulb-line include-icon"></i>
                            <div class="include-content">
                                <h4>Ambiente Decorado</h4>
                                <p>Balões, luzinhas e outros detalhes festivos para criar a vibe perfeita.</p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-cup-line include-icon"></i>
                            <div class="include-content">
                                <h4>Pequeno-Almoço Delicioso</h4>
                                <p>Pequenos-almoços caseiros para começarem o dia com energia e sabor.</p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-map-pin-line include-icon"></i>
                            <div class="include-content">
                                <h4>Espaço Só Vosso</h4>
                                <p>Desfrutem da quinta com total exclusividade — sem interrupções, só boa companhia.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="cta-section">
                    <h3 class="cta-title">Prontos para o fim de semana mais épico de sempre?</h3>
                    <p class="cta-subtitle">Façam já a vossa reserva e garantam momentos que vão ficar na memória!</p>
                    <a href="../login1/pagina_login.php" class="reserve-btn">
                        Reservar Agora
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>
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
