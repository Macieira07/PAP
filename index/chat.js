document.addEventListener('DOMContentLoaded', function () {
  // Elementos do chat
  const chatButton = document.querySelector('.chat-button');
  const chatWindow = document.querySelector('.chat-window');
  const chatCloseBtn = document.querySelector('.chat-action-btn');
  const chatInput = document.getElementById('chatInput');
  const sendButton = document.querySelector('.send-button');
  const chatMessages = document.querySelector('.chat-messages');
  const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
  const chatNotification = document.querySelector('.chat-notification');
  const chatTabs = document.querySelectorAll('.chat-tab');
  const chatPanels = document.querySelectorAll('.chat-panel');
  const faqList = document.querySelector('.faq-list');
  const emojiButton = document.querySelector('.chat-extra-btn:first-child');
  const attachButton = document.querySelector('.chat-extra-btn:last-child');

  // Estado do chat
  let chatHistory = [];
  let lastUserMessage = '';
  let lastResponseTime = null;
  let isFirstMessage = true;
  let sessionId = generateSessionId();

  // Base de conhecimento expandida com mais informações sobre a Quinta Flores
  const knowledgeBase = {
    greeting: [
      "Olá! Bem-vindo à Quinta Flores. Como posso ajudar com a sua estadia?",
      "Bem-vindo! Sou o assistente virtual da Quinta Flores. Em que posso ser útil hoje?",
      "Olá! É um prazer recebê-lo na Quinta Flores. Como posso tornar a sua experiência melhor?"
    ],
    goodbye: [
      "Até breve! Obrigado pelo contacto e esperamos recebê-lo em breve na Quinta Flores.",
      "Foi um prazer ajudar! Se precisar de mais informações, estamos sempre disponíveis.",
      "Até à próxima! Não hesite em contactar-nos para qualquer esclarecimento adicional."
    ],
    thanks: [
      "De nada! Estamos aqui para tornar a sua estadia inesquecível.",
      "É um prazer poder ajudar! Alguma outra questão em que possa ser útil?",
      "Sempre às ordens! A sua satisfação é a nossa prioridade."
    ],
    rooms: {
      info: "A Quinta Flores dispõe de 3 quartos acolhedores: um quarto com suite e jacuzzi ideal para casais, e dois quartos familiares para até 4 pessoas cada, que partilham casa de banho.",
      suite: "O nosso quarto com suite tem 25m², cama de casal king-size, casa de banho privativa com jacuzzi e varanda com vista privilegiada para o jardim e piscina.",
      quarto2: "O Quarto 2 é espaçoso com 35m², duas camas de casal confortáveis, decoração rústica e partilha casa de banho com o Quarto 3. Ideal para famílias ou grupos.",
      quarto3: "O Quarto 3 oferece 50m², duas camas de casal, amplo espaço para relaxar e partilha casa de banho com o Quarto 2. Perfeito para grupos que viajam juntos."
    },
    disponibilidade: {
      geral: "Para verificar disponibilidade específica para as suas datas, utilize o formulário de reserva no topo da página ou entre em contacto direto connosco.",
      verao: "Os meses de verão (junho a setembro) são muito procurados, recomendamos reservar com pelo menos 2 meses de antecedência.",
      inverno: "Na época baixa (novembro a março, exceto festas) temos geralmente boa disponibilidade e ofertas especiais para estadias longas."
    },
    precos: {
      geral: "Os preços variam conforme a época do ano e duração da estadia. Consulte nossa tabela no site para valores atualizados.",
      suite: "O quarto com suite e jacuzzi tem preços a partir de 120€ por noite na época baixa e 160€ na época alta.",
      quartos: "Os quartos familiares têm preços a partir de 100€ por noite na época baixa e 140€ na época alta, com capacidade para até 4 pessoas.",
      descontos: "Oferecemos 10% de desconto para estadias de 5 ou mais noites e 15% para estadias superiores a 7 noites."
    },
    politicas: {
      checkin: "O check-in é realizado entre as 15h00 e as 20h00. Check-ins tardios são possíveis mediante aviso prévio.",
      checkout: "O check-out deve ser feito até às 11h00. Podemos guardar as suas bagagens se precisar sair mais tarde.",
      cancelamento: "Cancelamentos gratuitos até 7 dias antes da data de chegada. Após esse período, será cobrado 50% do valor da reserva.",
      criancas: "Crianças são bem-vindas! Temos camas extras e berços disponíveis mediante solicitação prévia.",
      animais: "Infelizmente não podemos acomodar animais de estimação, exceto cães-guia."
    },
    localizacao: {
      geral: "A Quinta Flores está localizada em Calheiros, Ponte de Lima, a apenas 5 minutos de carro do centro histórico da vila mais antiga de Portugal.",
      acesso: "Somos facilmente acessíveis pela A3, com estacionamento gratuito na propriedade. O Aeroporto do Porto fica a 45 minutos de carro.",
      proximidades: "Na região, encontrará o centro histórico de Ponte de Lima, o rio Lima, trilhos pedestres, campos de golfe, praias fluviais e inúmeros locais de interesse cultural."
    },
    comodidades: {
      geral: "Oferecemos Wi-Fi gratuito, piscina exterior, estacionamento privativo, pequeno-almoço regional, área de lazer e jardins exuberantes.",
      pequeno_almoco: "O pequeno-almoço regional inclui produtos frescos locais, frutas da nossa quinta, pão caseiro, compotas artesanais e muito mais.",
      piscina: "A piscina está disponível de maio a outubro, das 9h00 às 20h00, com espreguiçadeiras e toalhas à disposição dos hóspedes.",
      jardins: "Os nossos jardins têm mais de 5000m² com árvores centenárias, flores sazonais e áreas de descanso para relaxar."
    },
    atividades: {
      quinta: "Na quinta, pode desfrutar da piscina, relaxar nos jardins, colher frutas sazonais (quando disponíveis) e participar em workshops gastronómicos ocasionais.",
      regiao: "A região oferece passeios de barco no rio Lima, trilhos pedestres, visitas a vinícolas, praias fluviais, campos de golfe e feiras tradicionais semanais.",
      gastronomia: "Recomendamos vivamente explorar a rica gastronomia minhota nos restaurantes locais. Podemos sugerir os melhores estabelecimentos da região."
    },
    eventos: {
      quinta: "A Quinta Flores é um espaço ideal para pequenos eventos como aniversários, reuniões familiares ou retiros empresariais para até 25 pessoas.",
      casamentos: "Infelizmente não realizamos casamentos, mas podemos recomendar quintas especializadas na região.",
      festas: "Ponte de Lima tem um rico calendário de eventos tradicionais, como a Vaca das Cordas (maio/junho) e as Feiras Novas (setembro)."
    },
    transportes: {
      carro: "Recomendamos o uso de carro para maior flexibilidade. Disponibilizamos estacionamento gratuito e seguro na propriedade.",
      publico: "A estação de autocarros de Ponte de Lima fica a 5 minutos de carro. Podemos arranjar transporte mediante pedido prévio.",
      taxi: "Temos parcerias com serviços de táxi locais que oferecem tarifas especiais para os nossos hóspedes."
    },
    unknown: [
      "Desculpe, não compreendi completamente. Pode reformular a sua pergunta ou especificar melhor o que procura?",
      "Hmm, não tenho certeza se entendi corretamente. Pode perguntar de outra forma?",
      "Peço desculpa, mas não consegui entender. Posso ajudar com informações sobre quartos, preços, disponibilidade, atividades ou localização."
    ]
  };

  // FAQs para a seção de perguntas frequentes
  const faqs = [
    {
      question: "Qual é o horário de check-in e check-out?",
      answer: "O check-in é das 15h00 às 20h00 e o check-out até às 11h00. Flexibilidade mediante disponibilidade."
    },
    {
      question: "A piscina é aquecida?",
      answer: "A piscina não é aquecida, mas durante os meses de verão a temperatura da água é bastante agradável."
    },
    {
      question: "Posso levar o meu animal de estimação?",
      answer: "Infelizmente não aceitamos animais de estimação, com exceção de cães-guia."
    },
    {
      question: "Têm berços disponíveis?",
      answer: "Sim, disponibilizamos berços e camas extras mediante solicitação prévia."
    },
    {
      question: "Como posso chegar à Quinta Flores do Aeroporto do Porto?",
      answer: "O Aeroporto do Porto fica a aproximadamente 45 minutos de carro. Pode alugar um veículo, apanhar um táxi ou solicitar-nos serviço de transfer (com custo adicional)."
    },
    {
      question: "Existe um mínimo de noites para reserva?",
      answer: "Na época alta (junho a setembro) temos um mínimo de 2 noites. Na época baixa, aceitamos reservas de apenas 1 noite, sujeitas a disponibilidade."
    },
    {
      question: "O pequeno-almoço está incluído?",
      answer: "Sim, todas as reservas incluem o nosso pequeno-almoço regional com produtos locais e frescos."
    },
    {
      question: "Têm opções para refeições além do pequeno-almoço?",
      answer: "Não servimos almoço ou jantar regularmente, mas podemos preparar refeições especiais mediante pedido antecipado ou recomendar excelentes restaurantes na região."
    }
  ];

  // Preencher a seção de FAQs
  function populateFAQs() {
    faqs.forEach(faq => {
      const faqItem = document.createElement('div');
      faqItem.className = 'faq-item';
      faqItem.innerHTML = `
        <div class="faq-question">
          <span>${faq.question}</span>
          <i class="ri-arrow-down-s-line"></i>
        </div>
        <div class="faq-answer">${faq.answer}</div>
      `;
      faqList.appendChild(faqItem);
    });

    // Event listeners para FAQs
    document.querySelectorAll('.faq-question').forEach(question => {
      question.addEventListener('click', () => {
        const answer = question.nextElementSibling;
        const icon = question.querySelector('i');
        
        answer.style.maxHeight = answer.style.maxHeight ? null : answer.scrollHeight + 'px';
        icon.className = answer.style.maxHeight ? 'ri-arrow-down-s-line' : 'ri-arrow-up-s-line';
      });
    });
  }

  // Função para adicionar mensagens ao chat com opções mais avançadas
  function addMessage(message, sender, options = {}) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;
    
    // Verificar se é a primeira mensagem do dia para adicionar separador
    const now = new Date();
    if (!lastResponseTime || !isSameDay(lastResponseTime, now)) {
      const separator = document.createElement('div');
      separator.className = 'chat-day-separator';
      separator.innerHTML = `<span>${formatDate(now)}</span>`;
      chatMessages.appendChild(separator);
    }
    
    // Criar bolha de mensagem
    const bubbleDiv = document.createElement('div');
    bubbleDiv.className = 'message-bubble';
    
    // Verificar se é uma mensagem rica (com HTML)
    if (options.isRich) {
      bubbleDiv.innerHTML = message;
    } else {
      // Detectar e formatar URLs
      const formattedMessage = formatMessage(message);
      bubbleDiv.innerHTML = formattedMessage;
    }
    
    // Adicionar tempo
    const timeDiv = document.createElement('div');
    timeDiv.className = 'message-time';
    const messageTime = options.time || now;
    timeDiv.textContent = formatTime(messageTime);
    
    // Adicionar elementos à DOM
    messageDiv.appendChild(bubbleDiv);
    messageDiv.appendChild(timeDiv);
    chatMessages.appendChild(messageDiv);
    
    // Salvar no histórico
    if (sender === 'sent') {
      lastUserMessage = message;
    }
    
    lastResponseTime = now;
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Salvar na sessão
    if (!options.skipHistory) {
      chatHistory.push({
        message: message,
        sender: sender,
        time: now.getTime()
      });
      saveHistory();
    }
    
    return messageDiv;
  }

  // Função para processar mensagens do usuário com sistema de intenções
  function processUserMessage(message) {
    if (!message.trim()) return;
    
    // Adicionar mensagem do usuário ao chat
    addMessage(message, 'sent');
    chatInput.value = '';
    
    // Mostrar indicador de digitação
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'message received typing';
    const typingBubble = document.createElement('div');
    typingBubble.className = 'message-bubble';
    typingBubble.innerHTML = '<div class="typing-indicator"><span></span><span></span><span></span></div>';
    typingIndicator.appendChild(typingBubble);
    chatMessages.appendChild(typingIndicator);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Determinar intenção e resposta
    setTimeout(() => {
      typingIndicator.remove();
      
      // Se for primeira mensagem, mostrar saudação especial
      if (isFirstMessage) {
        const welcomeResponse = getWelcomeResponse(message);
        addMessage(welcomeResponse, 'received');
        isFirstMessage = false;
        
        // Adicionar sugestões após a primeira interação
        setTimeout(() => {
          addSuggestions();
        }, 1000);
      } else {
        const response = getResponse(message.toLowerCase());
        addMessage(response, 'received');
      }
    }, Math.random() * 500 + 800); // Tempo de resposta variável para parecer mais natural
  }

  // Função para obter resposta com base na mensagem - sistema de intenções melhorado
  function getResponse(message) {
    // Palavras-chave para categorias específicas
    const keywords = {
      quartos: ['quarto', 'suite', 'cama', 'dormitório', 'dormir', 'jacuzzi', 'alojamento'],
      precos: ['preço', 'custo', 'valor', 'quanto custa', 'tarifas', 'euros', 'desconto', 'promoção'],
      disponibilidade: ['disponível', 'disponibilidade', 'vaga', 'livre', 'reservar', 'reserva', 'marcar'],
      localizacao: ['localização', 'onde fica', 'endereço', 'como chegar', 'distância', 'perto', 'mapa'],
      comodidades: ['comodidades', 'facilidades', 'wi-fi', 'piscina', 'pequeno-almoço', 'amenities', 'serviços'],
      politicas: ['política', 'regras', 'check-in', 'check-out', 'cancelamento', 'reembolso', 'horário'],
      atividades: ['fazer', 'atividades', 'passeios', 'visitar', 'lazer', 'turismo', 'entretenimento'],
      transportes: ['transporte', 'carro', 'autocarro', 'táxi', 'transfer', 'aeroporto'],
      agradecimento: ['obrigado', 'obrigada', 'agradecido', 'agradeço', 'valeu', 'grato'],
      despedida: ['adeus', 'até logo', 'tchau', 'até breve', 'até amanhã', 'até já']
    };

    // Verificar cada categoria de intenção
    for (const [category, terms] of Object.entries(keywords)) {
      if (terms.some(term => message.includes(term))) {
        // Encontrou uma correspondência
        switch (category) {
          case 'quartos':
            if (message.includes('suite') || message.includes('jacuzzi')) {
              return knowledgeBase.rooms.suite;
            } else if (message.includes('quarto 2')) {
              return knowledgeBase.rooms.quarto2;
            } else if (message.includes('quarto 3')) {
              return knowledgeBase.rooms.quarto3;
            } else {
              return knowledgeBase.rooms.info;
            }
          
          case 'precos':
            if (message.includes('suite') || message.includes('jacuzzi')) {
              return knowledgeBase.precos.suite;
            } else if (message.includes('desconto') || message.includes('promoção')) {
              return knowledgeBase.precos.descontos;
            } else {
              return knowledgeBase.precos.geral;
            }
          
          case 'disponibilidade':
            if (message.includes('verão') || message.includes('julho') || 
                message.includes('agosto') || message.includes('setembro')) {
              return knowledgeBase.disponibilidade.verao;
            } else if (message.includes('inverno') || message.includes('dezembro') || 
                       message.includes('janeiro') || message.includes('fevereiro')) {
              return knowledgeBase.disponibilidade.inverno;
            } else {
              return knowledgeBase.disponibilidade.geral;
            }
          
          case 'localizacao':
            return knowledgeBase.localizacao.geral;
          
          case 'comodidades':
            if (message.includes('pequeno-almoço') || message.includes('pequeno almoço') || message.includes('café')) {
              return knowledgeBase.comodidades.pequeno_almoco;
            } else if (message.includes('piscina')) {
              return knowledgeBase.comodidades.piscina;
            } else if (message.includes('jardim')) {
              return knowledgeBase.comodidades.jardins;
            } else {
              return knowledgeBase.comodidades.geral;
            }
          
          case 'politicas':
            if (message.includes('check-in') || message.includes('checkin') || message.includes('entrada')) {
              return knowledgeBase.politicas.checkin;
            } else if (message.includes('check-out') || message.includes('checkout') || message.includes('saída')) {
              return knowledgeBase.politicas.checkout;
            } else if (message.includes('cancelamento') || message.includes('cancelar')) {
              return knowledgeBase.politicas.cancelamento;
            } else if (message.includes('criança') || message.includes('bebé')) {
              return knowledgeBase.politicas.criancas;
            } else if (message.includes('animal') || message.includes('pet') || message.includes('cão') || message.includes('gato')) {
              return knowledgeBase.politicas.animais;
            } else {
              return knowledgeBase.politicas.checkin;
            }
          
          case 'atividades':
            if (message.includes('quinta') || message.includes('propriedade') || message.includes('local')) {
              return knowledgeBase.atividades.quinta;
            } else if (message.includes('gastronomia') || message.includes('comida') || message.includes('restaurante')) {
              return knowledgeBase.atividades.gastronomia;
            } else {
              return knowledgeBase.atividades.regiao;
            }
          
          case 'transportes':
            if (message.includes('carro')) {
              return knowledgeBase.transportes.carro;
            } else if (message.includes('público') || message.includes('autocarro') || message.includes('ônibus')) {
              return knowledgeBase.transportes.publico;
            } else if (message.includes('táxi') || message.includes('taxi') || message.includes('uber')) {
              return knowledgeBase.transportes.taxi;
            } else {
              return knowledgeBase.transportes.carro;
            }
          
          case 'agradecimento':
            return getRandomResponse(knowledgeBase.thanks);
          
          case 'despedida':
            return getRandomResponse(knowledgeBase.goodbye);
        }
      }
    }
    
    // Verificar saudações simples
    if (message.includes('olá') || message.includes('oi') || message.includes('bom dia') || 
        message.includes('boa tarde') || message.includes('boa noite')) {
      return getRandomResponse(knowledgeBase.greeting);
    }
    
    // Se nenhuma intenção for encontrada
    return getRandomResponse(knowledgeBase.unknown);
  }

  // Função para obter mensagem de boas-vindas personalizada
  function getWelcomeResponse(message) {
    let greeting = getRandomResponse(knowledgeBase.greeting);
    
    // Personalizar se o usuário se apresentou
    const nameMatch = message.match(/me chamo|sou [ao]|meu nome (é|e)|chamo-me/) || [];
    if (nameMatch.length > 0) {
      const parts = message.split(nameMatch[0]);
      if (parts.length > 1) {
        let name = parts[1].trim().split(' ')[0]; // Pegar o primeiro nome
        name = name.charAt(0).toUpperCase() + name.slice(1); // Capitalizar
        
        if (name && name.length > 1) {
          greeting = `Olá ${name}! É um prazer conhecê-lo. Sou o assistente virtual da Quinta Flores. Como posso ajudar com a sua visita?`;
        }
      }
    }
    
    return greeting;
  }

  // Função para adicionar sugestões personalizadas
  function addSuggestions() {
    const suggestionsMessage = `
      <p>Posso ajudar com:</p>
      <div class="chat-chips">
        <span class="chat-chip" data-query="informações sobre quartos">Quartos</span>
        <span class="chat-chip" data-query="preços">Preços</span>
        <span class="chat-chip" data-query="localização">Localização</span>
        <span class="chat-chip" data-query="atividades na região">Atividades</span>
      </div>
    `;
    
    const msgDiv = addMessage(suggestionsMessage, 'received', { isRich: true });
    
    // Adicionar event listeners para os chips
    msgDiv.querySelectorAll('.chat-chip').forEach(chip => {
      chip.addEventListener('click', () => {
        processUserMessage(chip.dataset.query);
      });
    });
  }

  // Funções auxiliares
  function getRandomResponse(responses) {
    return responses[Math.floor(Math.random() * responses.length)];
  }

  function formatMessage(text) {
    // Formatar URLs como links clicáveis
    return text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
  }

  function formatTime(date) {
    return `${date.getHours()}:${date.getMinutes().toString().padStart(2, '0')}`;
  }

  function formatDate(date) {
    const options = { weekday: 'long', day: 'numeric', month: 'long' };
    return date.toLocaleDateString('pt-PT', options);
  }

  function isSameDay(date1, date2) {
    return date1.getDate() === date2.getDate() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getFullYear() === date2.getFullYear();
  }

  function generateSessionId() {
    return 'chat_' + Math.random().toString(36).substring(2, 15);
  }

  function saveHistory() {
    if (chatHistory.length > 0) {
      localStorage.setItem(sessionId, JSON.stringify(chatHistory));
    }
  }

  function loadHistory() {
    const savedHistory = localStorage.getItem(sessionId);
    if (savedHistory) {
      try {
        const history = JSON.parse(savedHistory);
        if (Array.isArray(history) && history.length > 0) {
          chatHistory = history;
          
          // Restaurar até as últimas 10 mensagens para não sobrecarregar
          const limitedHistory = history.slice(-10);
          let lastDate = null;
          
          limitedHistory.forEach((item, index) => {
            const msgDate = new Date(item.time);
            
            // Verificar se precisamos adicionar separador de data
            if (!lastDate || !isSameDay(lastDate, msgDate)) {
              const separator = document.createElement('div');
              separator.className = 'chat-day-separator';
              separator.innerHTML = `<span>${formatDate(msgDate)}</span>`;
              chatMessages.appendChild(separator);
              lastDate = msgDate;
            }
            
            // Adicionar a mensagem
            addMessage(item.message, item.sender, {
              time: msgDate,
              skipHistory: true
            });
            
            // Atualizar estado
            if (item.sender === 'sent') {
              lastUserMessage = item.message;
            }
            lastResponseTime = msgDate;
            isFirstMessage = false;
          });
          
          // Scroll para o final
          chatMessages.scrollTop = chatMessages.scrollHeight;
        }
      } catch (e) {
        console.error('Erro ao carregar histórico:', e);
        localStorage.removeItem(sessionId);
      }
    }
  }

  // Event listeners para tabs do chat
  chatTabs.forEach((tab, index) => {
    tab.addEventListener('click', () => {
      chatTabs.forEach(t => t.classList.remove('active'));
      chatPanels.forEach(p => p.classList.remove('active'));
      
      tab.classList.add('active');
      chatPanels[index].classList.add('active');
    });
  });

  // Event listener para botão de chat
  chatButton.addEventListener('click', () => {
    if (chatWindow.style.display === 'none' || chatWindow.style.display === '') {
      chatWindow.style.display = 'flex';
      chatWindow.classList.add('chat-appear');
      setTimeout(() => chatWindow.classList.remove('chat-appear'), 500);
    } else {
      chatWindow.classList.add('chat-disappear');
      setTimeout(() => {
        chatWindow.style.display = 'none';
        chatWindow.classList.remove('chat-disappear');
      }, 300);
    }
    chatNotification.style.display = 'none';
  });

  // Event listener para fechar chat
  chatCloseBtn.addEventListener('click', () => {
    chatWindow.classList.add('chat-disappear');
    setTimeout(() => {
      chatWindow.style.display = 'none';
      chatWindow.classList.remove('chat-disappear');
    }, 300);
  });

  // Event listener para botão de enviar
  sendButton.addEventListener('click', () => {
    processUserMessage(chatInput.value);
  });

  // Event listener para tecla Enter
  chatInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      processUserMessage(chatInput.value);
    }
  });

  // Auto-resize para o campo de texto
  chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    const newHeight = Math.min(this.scrollHeight, 100);
    this.style.height = newHeight + 'px';
  });

  // Event listener para botões de resposta rápida
  quickReplyButtons.forEach((button) => {
    button.addEventListener('click', () => {
      processUserMessage(button.textContent);
    });
  });

  // Inicialização
  function init() {
    // Carregar histórico se existir
    loadHistory();
    
    // Preencher FAQs
    populateFAQs();
    
    // Mensagem de boas-vindas se não houver histórico prévio
    if (isFirstMessage) {
      setTimeout(() => {
        addMessage(getRandomResponse(knowledgeBase.greeting), 'received');
      }, 1000);
    }
    
    // Notificação simulada após alguns segundos
    if (!localStorage.getItem('chatOpened')) {
      setTimeout(() => {
        if (chatWindow.style.display !== 'flex') {
          chatNotification.style.display = 'flex';
          chatNotification.textContent = '1';
        }
      }, 30000);
    }
  }

  // Iniciar o chat
  init();

  // Marcar que o chat foi aberto nesta sessão
  localStorage.setItem('chatOpened', 'true');
});