<!-- Footer -->
<footer class="footer">
  <!-- Newsletter -->
  <div class="newsletter">
    <h4>Subscreve a nossa newsletter</h4>
    <form id="newsletterForm">
      <input type="email" id="newsletter-email" name="email" placeholder="O teu email..." required>
      <button type="submit">Subscrever</button>
    </form>
    <p id="newsletter-msg" style="color: lightgreen; display: none; margin-top: 10px;">Obrigado pela subscrição!</p>
  </div>

  <!-- Conteúdo do footer -->
  <div class="section__container footer__container">
    <!-- Logótipo + descrição -->
    <div class="footer__col">
      <img src="../assets/logos/logotipo1.png" alt="Logótipo Quinta Flores" style="width: 150px; margin-bottom: 1rem;">
      <p style="color: #a3a3a3;">Alojamento local em Ponte de Lima, harmonizando tradição minhota e comodidade contemporânea para uma estadia memorável.</p>
    </div>

    <!-- Descobrir a Região -->
    <div class="footer__col">
      <h4>Descobrir a Região</h4>
      <ul class="footer__links">
        <li class="footer__link">
          <a href="../index/trilhos.php">
            <img src="https://img.icons8.com/?size=100&id=sssl0bdjFnpD&format=png&color=2e7d32" alt="Trilhos" width="16" style="vertical-align: middle; margin-right: 6px;">
            Trilhos e Natureza
          </a>
        </li>
        <li class="footer__link">
          <a href="../index/gastronomia.php">
            <img src="https://img.icons8.com/?size=100&id=bSjYRwluTLTm&format=png&color=2e7d32" alt="Gastronomia" width="16" style="vertical-align: middle; margin-right: 6px;">
            Sabores do Minho
          </a>
        </li>
        <li class="footer__link">
          <a href="../index/passeios_culturais.php">
            <img src="https://img.icons8.com/?size=100&id=juiXdfFuXLd1&format=png&color=2e7d32" alt="Passeios Culturais" width="16" style="vertical-align: middle; margin-right: 6px;">
            Passeios Culturais
          </a>
        </li>
      </ul>
    </div>

    <!-- Contactos -->
    <div class="footer__col">
      <h4>Contactos</h4>
      <ul class="footer__links">
        <li class="footer__link">
          <i class="ri-map-pin-line"></i>
          <span>Travessa da Seara 265, 4490-575 Calheiros, Ponte de Lima, Portugal</span>
        </li>
        <li class="footer__link">
          <i class="ri-phone-line"></i>
          <a href="tel:+351912418976">+351 912 418 976</a>
        </li>
        <li class="footer__link">
          <i class="ri-mail-line"></i>
          <a href="mailto:quinta.flores2019@gmail.com">quinta.flores2019@gmail.com</a>
        </li>
        <li class="footer__link">
          <i class="ri-whatsapp-line"></i>
          <a href="https://wa.me/351912418976" target="_blank">Contactar via WhatsApp</a>
        </li>
        <li class="footer__link">
          <i class="ri-map-pin-2-line"></i>
          <a href="https://www.google.com/maps/place/Travessa+da+Seara,+Calheiros,+Ponte+de+Lima" target="_blank">Ver no Google Maps</a>
        </li>
        <li class="footer__link">
          <i class="ri-instagram-fill"></i>
          <a href="https://www.instagram.com/quintaflores19" target="_blank" aria-label="Instagram da Quinta Flores">Segue-nos no Instagram</a>
        </li>
      </ul>
    </div>
  </div>

  <!-- Barra inferior -->
  <div class="footer__bar">
    Copyright © 2025 QUINTA FLORES. Todos os direitos reservados.
  </div>

  <!-- Botão Voltar ao Topo -->
  <a href="#" class="back-to-top" title="Voltar ao topo">↑</a>
</footer>

<script>
  // Submissão AJAX da newsletter
  document.getElementById("newsletterForm").addEventListener("submit", function (e) {
    e.preventDefault();
    
    const emailInput = document.getElementById("newsletter-email");
    const email = emailInput.value;
    const msgBox = document.getElementById("newsletter-msg");

    fetch("../index/newsletter_submeter.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `email=${encodeURIComponent(email)}`
    })
    .then(response => response.json())
    .then(data => {
      msgBox.textContent = data.mensagem;
      msgBox.style.color = data.tipo === "sucesso" ? "lightgreen" : "red";
      msgBox.style.display = "block";

      if (data.tipo === "sucesso") {
        emailInput.value = ""; // limpa campo
      }
    })
    .catch(() => {
      msgBox.textContent = "Erro ao submeter. Tenta novamente.";
      msgBox.style.color = "red";
      msgBox.style.display = "block";
    });
  });
</script>
