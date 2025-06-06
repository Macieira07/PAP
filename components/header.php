<header class="header" id="header">
  <div class="nav__container">
    <!-- Logo -->
    <div class="logo">
      <a href="../index.php">
        <img src="../assets/logos/logotipo1.png" alt="Quinta Flores">
      </a>
    </div>

    <!-- Navegação -->
    <ul class="nav__links" id="navLinks">
      <div class="language-selector">
        <!-- Botões de idioma -->
        <button onclick="changeLanguage('pt')" class="language-btn active" data-lang="pt">
          <img src="../assets/flags/portugal.png" alt="Português"> PT
        </button>
        <button onclick="changeLanguage('en')" class="language-btn" data-lang="en">
          <img src="../assets/flags/reino-unido.png" alt="English"> EN
        </button>
        <button onclick="changeLanguage('es')" class="language-btn" data-lang="es">
          <img src="../assets/flags/espanha.png" alt="Español"> ES
        </button>
        <button onclick="changeLanguage('fr')" class="language-btn" data-lang="fr">
          <img src="../assets/flags/franca.png" alt="Français"> FR
        </button>
      </div>

      <?php
      if (isset($nav_links) && is_array($nav_links)) {
          foreach ($nav_links as $link) {
              $class = isset($link['class']) ? ' ' . $link['class'] : '';
              echo '<li><a href="' . htmlspecialchars($link['href']) . '" class="nav__link' . $class . '">' . htmlspecialchars($link['text']) . '</a></li>';
          }
      }
      ?>
    </ul>
    <script>
  function changeLanguage(lang) {
    // Guarda o idioma no localStorage (opcional)
    localStorage.setItem('selectedLanguage', lang);

    // Redireciona para a mesma página com o parâmetro lang, SEM hash (vai para o topo)
    const url = new URL(window.location.href);
    url.searchParams.set('lang', lang);
    url.hash = '';
    window.location.href = url.toString();
  }

  function updateActiveLanguage() {
    const selectedLanguage = localStorage.getItem('selectedLanguage') || 'pt';
    const buttons = document.querySelectorAll('.language-btn');

    buttons.forEach(button => {
      if (button.dataset.lang === selectedLanguage) {
        button.classList.add('active');
      } else {
        button.classList.remove('active');
      }
    });
  }

  // Quando a página carregar, atualiza o botão ativo
  document.addEventListener('DOMContentLoaded', () => {
    updateActiveLanguage();
  });
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const currentLang = document.documentElement.lang; // 'pt', 'en', etc.
    const buttons = document.querySelectorAll(".language-btn");

    buttons.forEach(btn => {
        if (btn.dataset.lang === currentLang) {
            btn.classList.add("active");
        } else {
            btn.classList.remove("active");
        }
    });
});
</script>


    <!-- Botões -->
    <button class="theme-toggle" id="themeToggle"><i class="ri-sun-line"></i></button>
    <button class="hamburger" id="hamburger"><i class="ri-menu-line"></i></button>
  </div>
</header>
