// Chat IA para Quinta Flores
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do chat
    const chatButton = document.querySelector('.chat-button');
    const chatWindow = document.querySelector('.chat-window');
    const chatCloseBtn = document.querySelector('.chat-action-btn');
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.querySelector('.send-button');
    const chatMessages = document.querySelector('.chat-messages');
    const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
    const chatNotification = document.querySelector('.chat-notification');
  
    // Base de conhecimento da Quinta Flores
    const knowledgeBase = {
      // Informações gerais
      greeting: ["Olá!", "Bem-vindo à Quinta Flores!", "Como posso ajudar?"],
      goodbye: ["Até breve!", "Obrigado pelo contacto!", "Tenha um ótimo dia!"],
      thanks: ["De nada!", "Estamos aqui para ajudar!", "É um prazer!"],
      
      // Sobre o alojamento
      rooms: {
        info: "Temos 3 quartos disponíveis: um quarto com suite e jacuzzi para 2 pessoas, e dois quartos para 4 pessoas cada que partilham casa de banho.",
        suite: "O nosso quarto com suite tem 25m², cama de casal, jacuzzi privativo e varanda com vista para o jardim.",
        quarto2: "O nosso quarto 2 tem 35m² e dispõe de duas camas de casal. Partilha casa de banho com o quarto vizinho.",
        quarto3: "O nosso quarto 3 é um espaçoso quarto de 50m² com duas camas de casal, ideal para famílias ou pequenos grupos."
      },
      
      // Disponibilidade e reservas
      disponibilidade: "Para verificar disponibilidade, indique-nos as datas pretendidas e o número de pessoas, ou use o formulário de disponibilidade no topo da página.",
      reservar: "Para fazer uma reserva, pode usar o botão 'Reservar Agora' no topo da página ou contactar-nos diretamente pelo telefone +351 912 418 976 ou email quinta.flores2019@gmail.com.",
      
      // Preços
      precos: {
        general: "Os nossos preços variam conforme a época do ano e o tipo de quarto.",
        baixa: "Na época baixa (Novembro a Março), os preços começam em 75€ por noite.",
        alta: "Na época alta (Abril a Outubro), os preços começam em 95€ por noite.",
        suite: "O quarto com suite tem um acréscimo de 20€ por noite."
      },
      
      // Localização e transportes
      localizacao: "A Quinta Flores está localizada na Travessa da Seara 265, 4490-575 Calheiros, Ponte de Lima, Viana do Castelo, Portugal.",
      chegar: "Estamos a 45 minutos do Aeroporto do Porto (OPO) e a 10 minutos da estação de Ponte de Lima.",
      
      // Comodidades
      comodidades: "Oferecemos Wi-Fi gratuito, piscina exterior, estacionamento gratuito, pequeno-almoço regional, bicicletas disponíveis e massagens (sob marcação).",
      
      // Gastronomia e atividades
      pequeno_almoco: "O nosso pequeno-almoço regional inclui produtos frescos locais, como pão caseiro, compotas artesanais, queijos regionais e frutas da época.",
      atividades: "Na região pode desfrutar de passeios de bicicleta, caminhadas, visitas a vinhas, explorar o centro histórico de Ponte de Lima e muito mais.",
      
      // Políticas
      check_in: "O check-in é das 15h00 às 20h00. Para chegadas fora deste horário, por favor entre em contacto connosco.",
      check_out: "O check-out deve ser feito até às 12h00.",
      cancelamento: "A nossa política de cancelamento permite cancelamentos gratuitos até 7 dias antes da data de check-in. Após esse prazo, será cobrada a primeira noite.",
      animais: "Lamentamos, mas não aceitamos animais de estimação.",
      criancas: "As crianças são bem-vindas! Temos berços disponíveis mediante solicitação prévia."
    };
  
    // Reconhecimento de intenções
    function recognizeIntent(message) {
      message = message.toLowerCase();
      
      // Comprimentos e despedidas
      if (containsAny(message, ['olá', 'ola', 'bom dia', 'boa tarde', 'boa noite', 'hi', 'hello'])) {
        return 'greeting';
      }
      
      if (containsAny(message, ['adeus', 'tchau', 'até logo', 'até breve'])) {
        return 'goodbye';
      }
      
      if (containsAny(message, ['obrigado', 'obrigada', 'agradecido', 'thanks'])) {
        return 'thanks';
      }
      
      // Quartos
      if (containsAny(message, ['quarto', 'quartos', 'suite', 'suíte', 'dormir', 'cama', 'acomodações'])) {
        if (message.includes('suite') || message.includes('suíte') || message.includes('jacuzzi')) {
          return 'rooms.suite';
        } else if (containsAny(message, ['quarto 2', 'segundo quarto', 'quarto dois'])) {
          return 'rooms.quarto2';
        } else if (containsAny(message, ['quarto 3', 'terceiro quarto', 'quarto três'])) {
          return 'rooms.quarto3';
        } else {
          return 'rooms.info';
        }
      }
      
      // Disponibilidade e reservas
      if (containsAny(message, ['disponibilidade', 'disponível', 'vaga', 'livre', 'quando'])) {
        return 'disponibilidade';
      }
      
      if (containsAny(message, ['reserva', 'reservar', 'marcar', 'agendar', 'booking'])) {
        return 'reservar';
      }
      
      // Preços
      if (containsAny(message, ['preço', 'precos', 'preços', 'custo', 'valor', 'quanto custa', 'tarifa'])) {
        if (message.includes('baixa') || containsAny(message, ['inverno', 'novembro', 'dezembro', 'janeiro', 'fevereiro', 'março'])) {
          return 'precos.baixa';
        } else if (message.includes('alta') || containsAny(message, ['verão', 'verao', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro'])) {
          return 'precos.alta';
        } else if (message.includes('suite') || message.includes('suíte')) {
          return 'precos.suite';
        } else {
          return 'precos.general';
        }
      }
      
      // Localização
      if (containsAny(message, ['local', 'localização', 'localizacao', 'endereço', 'morada', 'onde fica'])) {
        return 'localizacao';
      }
      
      if (containsAny(message, ['chegar', 'como ir', 'transporte', 'aeroporto', 'estação', 'comboio', 'autocarro'])) {
        return 'chegar';
      }
      
      // Comodidades
      if (containsAny(message, ['comodidade', 'comodidades', 'facilidades', 'wifi', 'piscina', 'estacionamento', 'oferece'])) {
        return 'comodidades';
      }
      
      // Gastronomia e atividades
      if (containsAny(message, ['pequeno almoço', 'pequeno-almoço', 'café da manhã', 'breakfast'])) {
        return 'pequeno_almoco';
      }
      
      if (containsAny(message, ['fazer', 'atividade', 'atividades', 'passeio', 'passeios', 'visitar', 'turismo'])) {
        return 'atividades';
      }
      
      // Políticas
      if (containsAny(message, ['check-in', 'check in', 'checkin', 'entrada'])) {
        return 'check_in';
      }
      
      if (containsAny(message, ['check-out', 'check out', 'checkout', 'saída', 'saida'])) {
        return 'check_out';
      }
      
      if (containsAny(message, ['cancelar', 'cancelamento', 'desistir', 'reembolso'])) {
        return 'cancelamento';
      }
      
      if (containsAny(message, ['animal', 'animais', 'pet', 'pets', 'cão', 'cao', 'gato'])) {
        return 'animais';
      }
      
      if (containsAny(message, ['criança', 'criancas', 'crianças', 'bebé', 'bebe', 'bebê', 'filho'])) {
        return 'criancas';
      }
      
      // Resposta padrão para mensagens não reconhecidas
      return 'unknown';
    }
  
    // Função auxiliar para verificar se a mensagem contém alguma das palavras-chave
    function containsAny(text, keywords) {
      return keywords.some(keyword => text.includes(keyword));
    }
  
    // Obter resposta com base na intenção reconhecida
    function getResponse(intent) {
      // Dividir o caminho da intenção (ex: "rooms.suite" -> ["rooms", "suite"])
      const path = intent.split('.');
      
      // Se for uma intenção desconhecida
      if (intent === 'unknown') {
        return "Desculpe, não compreendi completamente. Pode reformular ou perguntar sobre os nossos quartos, preços, disponibilidade, localização ou comodidades?";
      }
      
      // Para intenções com subcategorias (com ponto)
      if (path.length > 1) {
        try {
          const response = knowledgeBase[path[0]][path[1]];
          return response;
        } catch (error) {
          return "Desculpe, não tenho essa informação específica.";
        }
      }
      
      // Para intenções simples (sem ponto)
      if (Array.isArray(knowledgeBase[intent])) {
        // Se for um array, escolher uma resposta aleatória
        const responses = knowledgeBase[intent];
        return responses[Math.floor(Math.random() * responses.length)];
      } else {
        return knowledgeBase[intent] || "Desculpe, não tenho essa informação.";
      }
    }
  
    // Função para adicionar mensagem ao chat
    function addMessage(message, sender) {
      const messageDiv = document.createElement('div');
      messageDiv.className = `message ${sender}`;
      
      const bubbleDiv = document.createElement('div');
      bubbleDiv.className = 'message-bubble';
      bubbleDiv.textContent = message;
      
      const timeDiv = document.createElement('div');
      timeDiv.className = 'message-time';
      
      // Obter hora atual formatada (HH:MM)
      const now = new Date();
      const hours = now.getHours().toString().padStart(2, '0');
      const minutes = now.getMinutes().toString().padStart(2, '0');
      timeDiv.textContent = `${hours}:${minutes}`;
      
      messageDiv.appendChild(bubbleDiv);
      messageDiv.appendChild(timeDiv);
      
      chatMessages.appendChild(messageDiv);
      
      // Rolar para a mensagem mais recente
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }
  
    // Função para simular digitação da IA (efeito de "está digitando...")
    function simulateTyping() {
      const typingDiv = document.createElement('div');
      typingDiv.className = 'message received typing';
      
      const bubbleDiv = document.createElement('div');
      bubbleDiv.className = 'message-bubble';
      bubbleDiv.innerHTML = '<span class="typing-indicator"><span>.</span><span>.</span><span>.</span></span>';
      
      typingDiv.appendChild(bubbleDiv);
      chatMessages.appendChild(typingDiv);
      chatMessages.scrollTop = chatMessages.scrollHeight;
      
      return typingDiv;
    }
  
    // Função para processar mensagem do usuário
    function processUserMessage(message) {
      if (!message.trim()) return;
      
      // Adicionar mensagem do usuário ao chat
      addMessage(message, 'sent');
      
      // Limpar input
      chatInput.value = '';
      
      // Simular "está digitando..."
      const typingDiv = simulateTyping();
      
      // Reconhecer intenção e obter resposta
      const intent = recognizeIntent(message);
      const response = getResponse(intent);
      
      // Simular tempo de resposta (entre 1 e 2 segundos)
      const responseTime = Math.floor(Math.random() * 1000) + 1000;
      
      setTimeout(() => {
        // Remover indicador de digitação
        typingDiv.remove();
        
        // Adicionar resposta da IA
        addMessage(response, 'received');
        
        // Sugerir respostas adicionais com base na intenção
        suggestFollowUp(intent);
      }, responseTime);
    }
  
    // Sugerir perguntas de acompanhamento
    function suggestFollowUp(intent) {
      // Limpar sugestões existentes
      document.querySelector('.quick-replies').innerHTML = '';
      
      const suggestions = [];
      
      // Sugestões baseadas na intenção anterior
      switch (intent) {
        case 'greeting':
          suggestions.push('Disponibilidade', 'Preços', 'Localização');
          break;
        case 'rooms.info':
        case 'rooms.suite':
        case 'rooms.quarto2':
        case 'rooms.quarto3':
          suggestions.push('Preços', 'Disponibilidade', 'Comodidades');
          break;
        case 'disponibilidade':
          suggestions.push('Preços', 'Como reservar', 'Política de cancelamento');
          break;
        case 'precos.general':
        case 'precos.baixa':
        case 'precos.alta':
        case 'precos.suite':
          suggestions.push('Disponibilidade', 'Como reservar', 'O que está incluído');
          break;
        case 'localizacao':
        case 'chegar':
          suggestions.push('Atividades na região', 'Estacionamento', 'Transporte');
          break;
        case 'comodidades':
          suggestions.push('Piscina', 'Wi-Fi', 'Pequeno-almoço');
          break;
        default:
          suggestions.push('Quartos', 'Preços', 'Disponibilidade');
      }
      
      // Limitar a no máximo 3 sugestões
      const limitedSuggestions = suggestions.slice(0, 3);
      
      // Adicionar sugestões ao DOM
      limitedSuggestions.forEach(suggestion => {
        const button = document.createElement('button');
        button.className = 'quick-reply-btn';
        button.textContent = suggestion;
        button.addEventListener('click', () => processUserMessage(suggestion));
        document.querySelector('.quick-replies').appendChild(button);
      });
    }
  
    // Event Listeners
    
    // Toggle chat window
    chatButton.addEventListener('click', () => {
      chatWindow.style.display = chatWindow.style.display === 'flex' ? 'none' : 'flex';
      chatNotification.style.display = 'none'; // Remover notificação quando abrir o chat
    });
    
    // Close chat window
    chatCloseBtn.addEventListener('click', () => {
      chatWindow.style.display = 'none';
    });
    
    // Send message on button click
    sendButton.addEventListener('click', () => {
      processUserMessage(chatInput.value);
    });
    
    // Send message on Enter key press
    chatInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        processUserMessage(chatInput.value);
      }
    });
    
    // Quick reply buttons
    quickReplyButtons.forEach(button => {
      button.addEventListener('click', () => {
        processUserMessage(button.textContent);
      });
    });
  
    // Adicionar mensagem inicial de boas-vindas após um pequeno delay
    setTimeout(() => {
      const welcomeMessage = "Olá! Bem-vindo à Quinta Flores. Como posso ajudar com a sua estadia?";
      addMessage(welcomeMessage, 'received');
      
      // Sugestões iniciais
      suggestFollowUp('greeting');
    }, 1000);
  });