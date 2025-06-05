<?php
include '../components/header.php'; ?>

<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saída a Dois - Quinta Flores</title>
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

    <main class="main-content">
        <section class="section">
            <div class="section__container">
                <a href="../index.php#activities" class="back-btn">
                    <i class="ri-arrow-left-line"></i>
                    Voltar às ofertas   
                </a>

                <p class="section__subheader">Oferta Especial</p>
                <h1 class="section__header">Saída a Dois</h1>

                <div class="offer-details">
                    <div class="offer-image">
                        <img src="../assets/images/tempo_namorar.avif" alt="Saída a Dois">
                        <div class="offer-badge">2 Noites</div>
                        <div class="capacity-badge">Para 2 pessoas</div>
                    </div>
                    <div class="offer-content">
                        <h2 class="offer-title">Escapadinha Romântica</h2>
                        <p class="offer-price">260€</p>
                        <div class="code-highlight">Código para usar ao reservar: <strong>LOVE260</strong></div>
                        <p class="offer-description">
                            Surpreenda quem mais ama com uma experiência única, romântica e cheia de encanto no coração do Minho. Uma escapadinha perfeita para celebrar o amor, longe da rotina e perto da natureza.
                        </p>
                    </div>
                </div>

                <div class="includes-section">
                    <h3 class="includes-title">O que está incluído na oferta</h3>
                    <div class="includes-grid">
                        <div class="include-item">
                            <i class="ri-hotel-bed-line include-icon"></i>
                            <div class="include-content">
                                <h4>2 Noites de Alojamento</h4>
                                <p>Suite confortável e acolhedora, com vista para a natureza e todas as comodidades para uma estadia tranquila.</p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-gift-line include-icon"></i>
                            <div class="include-content">
                                <h4>Cesto de Piquenique Romântico</h4>
                                <p>Deliciosas iguarias locais, bebidas selecionadas e uma manta para desfrutarem juntos de um momento a dois.</p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-sun-foggy-line include-icon"></i>
                            <div class="include-content">
                                <h4>Pequeno-Almoço Especial</h4>
                                <p>Servido no primeiro dia, com sabores frescos e regionais para começar o dia com energia e carinho.</p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-restaurant-line include-icon"></i>
                            <div class="include-content">
                                <h4>Jantar à Luz das Velas</h4>
                                <p>Na segunda noite, um jantar íntimo preparado especialmente para a ocasião, com ambiente encantador e menu exclusivo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cta-section">
                    <h3 class="cta-title">Prontos para uma experiência inesquecível a dois?</h3>
                    <p class="cta-subtitle">Reserve já a vossa escapadinha romântica e crie memórias que durarão para sempre.</p>
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
