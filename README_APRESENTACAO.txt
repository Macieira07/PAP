============================================================
  APRESENTAÇÃO DO PROJETO - QUINTA FLORES
============================================================

Este projeto é um sistema completo de gestão e apresentação para o alojamento Quinta Flores, incluindo site institucional, área administrativa, reservas, chatbot, internacionalização e funcionalidades de autenticação.

------------------------------------------------------------
Linguagens Utilizadas
------------------------------------------------------------
- PHP (backend, lógica, APIs, includes)
- HTML5 (estrutura das páginas)
- CSS3 (estilos, temas, responsividade)
- JavaScript (interatividade, validação, AJAX, chatbot)
- JSON (comunicação entre frontend e backend, i18n)

------------------------------------------------------------
Principais Bibliotecas e Frameworks
------------------------------------------------------------
- PHPMailer (envio de emails)
- TCPDF/FPDF (geração de PDFs)
- TinyMCE (editor rich text)
- Font Awesome & Remixicon (ícones)
- Google Fonts (fontes personalizadas)
- i18n customizado (internacionalização multi-idioma)
- AOS (animações on scroll, em algumas páginas)

------------------------------------------------------------
Estrutura de Pastas
------------------------------------------------------------
- admin/         → Área administrativa (gestão de casas, reservas, despesas, receitas, etc.)
- assets/        → Imagens, bandeiras, logos, arquivos de internacionalização
- chatbot/       → Componente de chatbot (JS, CSS, PHP, config)
- components/    → Componentes reutilizáveis (header, footer, etc.)
- index/         → Páginas principais do site, APIs públicas, traduções
- login1/        → Autenticação, registo, recuperação de senha, PHPMailer
- pagamento/     → Gestão de pagamentos, comprovativos, TCPDF
- public/        → CSS e JS públicos
- uploads/       → Uploads de imagens e documentos
- vendor/        → Dependências instaladas via Composer (PHPMailer, TCPDF, etc.)

------------------------------------------------------------
Funcionalidades Principais
------------------------------------------------------------
- Página inicial institucional com ofertas, galeria, comodidades, localização, testemunhos
- Sistema de reservas online com verificação de disponibilidade
- Área administrativa para gestão de hóspedes, casas, receitas, despesas, manutenções, etc.
- Autenticação de utilizadores (registo, login, recuperação e redefinição de senha)
- Envio de emails automáticos (confirmação, recuperação, newsletter)
- Chatbot interativo multilíngue
- Internacionalização completa (PT, EN, ES, FR)
- Geração de PDFs (recibos, comprovativos)
- Newsletter e contacto

------------------------------------------------------------
Observações Técnicas
------------------------------------------------------------
- Estrutura modular e comentada, facilitando manutenção e expansão
- Uso de prepared statements para segurança nas queries
- Suporte a temas claro/escuro (em algumas páginas)
- Código CSS e JS organizado por componentes
- Logs de erros para debugging (login, registo, recuperação)
- Suporte a uploads de imagens e documentos

------------------------------------------------------------
