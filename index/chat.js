document.addEventListener('DOMContentLoaded', function () {
  // Elementos do chat
  const chatButton = document.querySelector('.chat-button');
  const chatWindow = document.querySelector('.chat-window');
  const chatCloseBtn = document.querySelector('.close-btn');
  const chatMinimizeBtn = document.querySelector('.minimize-btn');
  const chatInput = document.getElementById('chatInput');
  const sendButton = document.querySelector('.send-button');
  const chatMessages = document.querySelector('.chat-messages');
  const quickReplyButtons = document.querySelectorAll('.quick-reply-btn');
  const chatNotification = document.querySelector('.chat-notification');
  const chatTabs = document.querySelectorAll('.chat-tab');
  const chatPanels = document.querySelectorAll('.chat-panel');
  const faqList = document.querySelector('.faq-list');
  const emojiButton = document.querySelector('.emoji-btn');
  const attachButton = document.querySelector('.attach-btn');
  const voiceButton = document.querySelector('.voice-btn');
  const locationButton = document.querySelector('.location-btn');
  const feedbackButtons = document.querySelectorAll('.feedback-btn');
  const themeToggle = document.querySelector('.theme-toggle');

  // Estado do chat
  let chatHistory = [];
  let lastUserMessage = '';
  let lastResponseTime = null;
  let isFirstMessage = true;
  let sessionId = generateSessionId();
  let userPreferences = loadUserPreferences();
  let typingTimeout = null;
  let suggestionsTimeout = null;
  let messageQueue = [];
  let isProcessingQueue = false;
  let userContext = {
    name: '',
    interests: [],
    previousVisits: false,
    preferredRoom: '',
    visitDates: {
      arrival: null,
      departure: null
    },
    guestCount: {
      adults: 0,
      children: 0
    },
    bookingStage: 'inquiry', // inquiry, interested, ready, confirmed
    specialNeeds: []
  };

  // Sistema de NLP simplificado
  const nlp = {
    tokenize: (text) => text.toLowerCase().replace(/[^\w\s]/gi, '').split(/\s+/),
    
    removeStopWords: (tokens) => {
      const stopWords = ['de', 'a', 'o', 'que', 'e', 'do', 'da', 'em', 'um', 'para', 'é', 'com', 'não', 'uma', 'os', 'no', 'se', 'na', 'por', 'mais', 'as', 'dos', 'como', 'mas', 'foi', 'ao'];
      return tokens.filter(token => !stopWords.includes(token));
    },
    
    stem: (word) => {
      // Stemming simplificado para português
      return word.replace(/amento$|imentos$|adora$|adores$|ações$|ezas$|idades$|ismos$|istas$|mente$|ar$|er$|ir$|ou$|ando$|endo$|indo$|ados$|idos$|adas$|idas$|ões$|am$|em$|es$|ia$|ei$|eu$/g, '');
    },
    
    process: (text) => {
      const tokens = nlp.tokenize(text);
      const withoutStopWords = nlp.removeStopWords(tokens);
      return withoutStopWords.map(token => nlp.stem(token));
    },
    
    findIntent: (text) => {
      const processed = nlp.process(text);
      let bestMatch = { category: 'unknown', score: 0 };
      
      for (const [category, terms] of Object.entries(keywords)) {
        let score = 0;
        const processedTerms = terms.map(term => nlp.process(term).join(' '));
        
        processed.forEach(token => {
          processedTerms.forEach(term => {
            if (term.includes(token)) {
              score++;
            }
          });
        });
        
        // Verificar também por combinações exatas
        terms.forEach(term => {
          if (text.toLowerCase().includes(term.toLowerCase())) {
            score += 2;
          }
        });
        
        if (score > bestMatch.score) {
          bestMatch = { category, score };
        }
      }
      
      return bestMatch.score > 0 ? bestMatch.category : 'unknown';
    },
    
    extractEntities: (text) => {
      const entities = {
        dates: extractDates(text),
        numbers: extractNumbers(text),
        people: extractPeople(text),
        rooms: extractRooms(text),
        locations: extractLocations(text),
        preferences: extractPreferences(text),
        timeframes: extractTimeframes(text)
      };
      
      return entities;
    },
    
    analyzeSentiment: (text) => {
      const positiveWords = ['bom', 'ótimo', 'excelente', 'gosto', 'adoro', 'fantástico', 'maravilhoso', 'perfeito', 'incrível', 'espetacular', 'sim', 'claro', 'certamente'];
      const negativeWords = ['mau', 'ruim', 'péssimo', 'não gosto', 'odeio', 'terrível', 'horrível', 'decepcionante', 'não', 'nunca', 'jamais'];
      
      const tokens = nlp.tokenize(text);
      let positiveScore = 0;
      let negativeScore = 0;
      
      tokens.forEach(token => {
        if (positiveWords.some(word => token.includes(word))) {
          positiveScore++;
        }
        if (negativeWords.some(word => token.includes(word))) {
          negativeScore++;
        }
      });
      
      if (positiveScore > negativeScore) return 'positive';
      if (negativeScore > positiveScore) return 'negative';
      return 'neutral';
    }
  };

  // Base de conhecimento expandida
  const knowledgeBase = {
    greeting: [
      "Olá! Bem-vindo à Quinta Flores. Como posso ajudar hoje?",
      "Bem-vindo(a) à Quinta Flores! Sou o assistente virtual e estou aqui para tornar a sua estadia inesquecível. Em que posso ajudar?",
      "Olá! É um prazer recebê-lo(a) na Quinta Flores. Como posso tornar a sua experiência mais agradável?",
      "Bem-vindo(a)! Sou o assistente da Quinta Flores, o seu refúgio em Ponte de Lima. Como posso ser útil?"
    ],
    goodbye: [
      "Até breve! Obrigado pelo contacto e esperamos recebê-lo em breve na Quinta Flores. Tenha um excelente dia!",
      "Foi um prazer ajudar! Se precisar de mais informações, estamos sempre disponíveis. Esperamos vê-lo em breve!",
      "Até à próxima! Não hesite em contactar-nos para qualquer esclarecimento adicional. A Quinta Flores aguarda a sua visita!",
      "Adeus e obrigado pela sua preferência! Estamos ansiosos para recebê-lo na nossa quinta!"
    ],
    thanks: [
      "De nada! Estamos aqui para tornar a sua estadia inesquecível.",
      "É um prazer poder ajudar! Alguma outra questão em que possa ser útil?",
      "Sempre às ordens! A sua satisfação é a nossa prioridade.",
      "O prazer é meu! Estamos comprometidos em proporcionar a melhor experiência possível na Quinta Flores."
    ],
    rooms: {
      info: "A Quinta Flores dispõe de 3 acomodações charmosas: uma suite elegante com jacuzzi privativo ideal para casais, e dois quartos espaçosos para famílias com até 4 pessoas cada, que partilham uma casa de banho moderna e completa.",
      suite: "A nossa suite é um refúgio de 21.8m² com cama king-size, decoração elegante e rústica, casa de banho privativa com jacuzzi e uma varanda com vista privilegiada para o jardim e piscina. Perfeita para momentos românticos e relaxantes.",
      quarto2: "O Quarto 2 é espaçoso com 16.2m², duas camas de casal confortáveis, decoração rústica com toques modernos e partilha casa de banho com o Quarto 3. É ideal para famílias ou pequenos grupos que buscam conforto e autenticidade.",
      quarto3: "O Quarto 3 oferece 16.2m², duas camas de casal, amplo espaço para relaxar e partilha casa de banho com o Quarto 2. Decorado com elementos tradicionais minhotos, é perfeito para grupos que viajam juntos e apreciam a cultura local.",
      amenidades: "Todos os quartos incluem: roupas de cama e toalhas de alta qualidade, climatização, Wi-Fi gratuito, espaço de armazenamento, espelhos de corpo inteiro e fácil acesso às áreas comuns."
    },
    disponibilidade: {
      geral: "Para verificar disponibilidade específica para as suas datas, utilize o [formulário de reserva](#reservas) no nosso site ou entre em contacto direto connosco pelo telefone +351 919 241 169. Teremos todo o gosto em verificar as opções disponíveis para as suas datas preferidas.",
      verao: "Os meses de verão (junho a setembro) são muito procurados, especialmente aos fins de semana e durante eventos locais como as Feiras Novas. Recomendamos reservar com pelo menos 2-3 meses de antecedência para garantir disponibilidade.",
      inverno: "Na época baixa (novembro a março, exceto festas de fim de ano) temos geralmente boa disponibilidade e oferecemos condições especiais para estadias longas superiores a 5 noites. Consulte-nos para conhecer as nossas promoções sazonais.",
      feriados: "Durante feriados prolongados, festas locais e datas comemorativas como Páscoa, Natal e Passagem de Ano, a disponibilidade é limitada. Recomendamos reservar com 3-4 meses de antecedência.",
      politica: "A nossa política de reserva é flexível, mas para garantir o melhor quarto e as melhores tarifas, recomendamos sempre fazer a reserva com antecedência. Respondemos a pedidos de disponibilidade em até 24 horas."
    },
    precos: {
      geral: "Os preços variam conforme a época do ano, tipo de quarto e duração da estadia, mas normalmente a diária é a partir de 120€ por noite. Para orçamentos personalizados, contacte-nos diretamente pelo telefone +351 919 241 169 ou pelo email quinta.flores2019@gmail.com.",
      suite: "A nossa suite com jacuzzi tem preços a partir de 140€ por noite para duas pessoas, dependendo da época. Durante o verão e feriados, o valor pode variar entre 160€ e 180€.",
      quartos: "Os quartos familiares têm preços a partir de 120€ por noite para até 2 pessoas, com acréscimo de 30€ por pessoa adicional. Crianças até 5 anos podem ficar gratuitamente utilizando a cama existente.",
      descontos: "Oferecemos descontos de 10% para estadias de 5 a 7 noites e 15% para estadias superiores a 7 noites. Também temos tarifas especiais para reservas antecipadas feitas com mais de 90 dias de antecedência.",
      epocas: "Trabalhamos com três épocas: Baixa (novembro a março, exceto festas), Média (abril, maio, outubro) e Alta (junho a setembro, feriados e eventos especiais). Cada época tem preços diferenciados."
    },
    politicas: {
      checkin: "O check-in é realizado a partir das 11h até às 20h. Check-ins tardios (após as 20h) são possíveis mediante aviso prévio. Nossa equipe estará aguardando para recebê-lo e apresentar a propriedade.",
      checkout: "O check-out deve ser feito até às 15h00. Podemos guardar as suas bagagens se precisar sair mais tarde, permitindo que aproveite o seu último dia com tranquilidade.",
      cancelamento: "Nossa política de cancelamento é flexível: cancelamentos gratuitos até 10 dias antes da data de chegada. Entre 9 e 5 dias, será cobrado 50% do valor da reserva. Com menos de 5 dias ou no-show, será cobrado o valor total.",
      animais: "Aceitamos animais de pequeno porte (até 10kg) ou cães-guia mediante comunicação prévia. É aplicada uma taxa adicional de limpeza de 15€ por estadia.",
      criancas: "Crianças são bem-vindas na Quinta Flores. Crianças até 5 anos podem ficar gratuitamente utilizando as camas existentes. Podemos disponibilizar espaço para berços trazidos pelos hóspedes.",
      limpeza: "A limpeza é realizada a cada 3 dias para estadias mais longas. Limpezas adicionais podem ser solicitadas mediante pagamento de taxa extra de 25€.",
      barulho: "Solicitamos aos nossos hóspedes que respeitem o horário de silêncio entre as 23h e as 8h para garantir uma experiência tranquila a todos.",
      visitas: "Visitas de não-hóspedes devem ser comunicadas previamente à administração e não podem pernoitar sem registro e pagamento adicional."
    },
    localizacao: {
      geral: "A Quinta Flores está localizada em Calheiros, Ponte de Lima, a apenas 5 minutos de carro do centro histórico da vila mais antiga de Portugal. Estamos inseridos num vale verdejante com vista para a Serra d'Arga, oferecendo tranquilidade rural com proximidade a todas as comodidades.",
      acesso: "Somos facilmente acessíveis pela A3, saída Ponte de Lima, com estacionamento gratuito na propriedade. O Aeroporto do Porto fica a aproximadamente 45 minutos de carro, e oferecemos serviço de transfer mediante reserva prévia.",
      proximidades: "Na região, encontrará o centro histórico de Ponte de Lima, o rio Lima com suas praias fluviais, o Ecovia do Lima para caminhadas e ciclismo, trilhos pedestres na Serra d'Arga, e inúmeros locais de interesse cultural e gastronómico.",
      pontos_interesse: "A menos de 30 minutos de carro: Viana do Castelo, Ponte da Barca, Arcos de Valdevez, e as praias do litoral norte. A menos de uma hora: Porto, Braga, Gerês e fronteira com Espanha (Tui/Valença).",
      coordenadas: "Nossas coordenadas GPS são: 41.7892° N, 8.5838° W. Enviamos instruções detalhadas após a confirmação da reserva."
    },
    comodidades: {
      geral: "Oferecemos Wi-Fi gratuito em toda a propriedade, piscina exterior sazonal, estacionamento privado e gratuito, área de lazer com jogos tradicionais, churrasqueira, e jardins exuberantes com áreas de descanso.",
      piscina: "A piscina está disponível de maio a outubro (dependendo das condições climatéricas) com espreguiçadeiras, guarda-sóis e toalhas à disposição. Mede 8x4m com profundidade variável entre 1,20m e 1,80m.",
      jardins: "Os nossos jardins têm mais de 5000m² com árvores centenárias, flores sazonais e áreas de descanso. Inclui uma pequena horta biológica, pomar com frutas da época e um espaço zen para meditação e yoga ao ar livre.",
      internet: "Wi-Fi de alta velocidade gratuito em toda a propriedade, incluindo áreas exteriores. Temos também uma Smart TV nas áreas comuns com Netflix e outros serviços de streaming.",
      estacionamento: "Estacionamento privativo gratuito dentro da propriedade, com capacidade para até 8 veículos, com área coberta e ao ar livre.",
      lazer: "Área de lazer com mesa de ping-pong, matraquilhos, jogos de tabuleiro, pequena biblioteca com livros em vários idiomas e espaço para crianças."
    },
    atividades: {
      quinta: "Na quinta, pode desfrutar da piscina, relaxar nos jardins, colher frutas sazonais (quando disponíveis), participar em workshops gastronómicos ocasionais, praticar yoga ao ar livre, ou simplesmente desfrutar do pôr-do-sol com uma taça de vinho verde local.",
      regiao: "A região oferece passeios de barco no rio Lima, trilhos pedestres na Serra d'Arga, praias fluviais, percursos históricos pela vila, enoturismo com visitas a quintas produtoras de vinho verde, e festas tradicionais ao longo do ano.",
      gastronomia: "Recomendamos vivamente explorar a rica gastronomia minhota nos restaurantes locais. Especialidades incluem o arroz de sarrabulho, bacalhau à moda de Braga, papas de sarrabulho, e doces conventuais. Podemos fazer reservas nos melhores restaurantes para nossos hóspedes.",
      cultural: "Visite o centro histórico de Ponte de Lima com sua ponte romana, igreja matriz, Torres de São Paulo e da Cadeia, Torre da Porta Nova, e o Paço do Marquês. A região tem um rico património histórico e festivais culturais regulares.",
      natureza: "Explore os trilhos da Serra d'Arga, os Passadiços do Rio Lima, a Ecovia do Lima para caminhadas ou ciclismo, ou visite a Lagoa de Bertiandos e São Pedro de Arcos, um espaço natural protegido ideal para observação de aves."
    },
    eventos: {
      quinta: "A Quinta Flores é um espaço ideal para pequenos eventos como aniversários, reuniões familiares ou retiros empresariais para até 30 pessoas em formato cocktail ou 20 pessoas sentadas. Dispomos de serviço de catering mediante reserva.",
      casamentos: "Podemos acomodar mini-weddings e elopements para até 30 convidados. Trabalhamos com parceiros locais para oferecer pacotes personalizados incluindo celebrante, fotógrafo, flores e catering.",
      corporativo: "Oferecemos pacotes para pequenos retiros corporativos e team building, com atividades personalizadas como wine tastings, workshops gastronómicos ou yoga. Temos capacidade para sessões de trabalho para até 15 pessoas.",
      festas: "Ponte de Lima tem um rico calendário de eventos tradicionais, como a Vaca das Cordas (maio/junho), as Feiras Novas (setembro), Feiras Medievais e festivais gastronómicos ao longo do ano."
    },
    transportes: {
      carro: "Recomendamos o uso de carro para maior flexibilidade na exploração da região. Disponibilizamos estacionamento gratuito e seguro na propriedade e podemos sugerir roteiros personalizados.",
      publico: "A estação de autocarros de Ponte de Lima fica a 5 minutos de carro. Há serviços regulares para Viana do Castelo, Braga e Porto. Podemos providenciar transporte da estação mediante solicitação prévia.",
      taxi: "Trabalhamos com serviço de táxi local de confiança e podemos agendar para nossos hóspedes. O custo aproximado para o centro de Ponte de Lima é de 8-10€.",
      bicicleta: "Temos algumas bicicletas disponíveis para uso gratuito dos hóspedes, mediante reserva. A região tem excelentes percursos cicláveis, incluindo a Ecovia do Lima.",
      transfer: "Oferecemos serviço de transfer do/para o Aeroporto do Porto mediante reserva prévia. O custo é de 60€ por trajeto para até 4 pessoas."
    },
    reservas: {
      online: "Você pode fazer sua reserva diretamente pelo nosso site [Quinta Flores Reservas](#reservas), por email ou telefone. Reservas diretas recebem vantagens exclusivas como check-in antecipado/late check-out (mediante disponibilidade) e um brinde de boas-vindas.",
      contacto: "Para reservas diretas conosco, ligue para +351 919 241 169 ou envie email para quinta.flores2019@gmail.com. Respondemos em até 24 horas.",
      grupo: "Para reservas de grupo (mais de 6 pessoas), entre em contacto diretamente conosco para negociação de condições especiais. Podemos oferecer a quinta em exclusividade para grupos que reservem todos os quartos.",
      pagamento: "Aceitamos pagamento por transferência bancária (IBAN disponível após pré-reserva), MB Way, cartão de crédito (Visa, Mastercard) e dinheiro. É solicitado um pré-pagamento de 30% para garantir a reserva.",
      garantia: "Para confirmar sua reserva, solicitamos um pré-pagamento de 30% do valor total. O valor remanescente pode ser pago até 7 dias antes da chegada ou no check-in."
    },
    experiencias: {
      gastronomia: "Podemos organizar experiências gastronómicas como provas de vinho verde em adegas locais, workshops de culinária tradicional portuguesa, ou um almoço exclusivo com um chef local que prepara os pratos na nossa quinta.",
      cultural: "Oferecemos roteiros culturais personalizados para explorar o património histórico da região, aulas de artesanato tradicional, ou participação em festividades locais com guia privado.",
      natureza: "Para os amantes da natureza, organizamos caminhadas guiadas na Serra d'Arga, passeios de barco no rio Lima, observação de aves na Lagoa de Bertiandos, ou passeios a cavalo por trilhos rurais.",
      bem_estar: "Proporcionamos experiências de bem-estar como yoga no jardim, massagens por agendamento, banhos de floresta guiados, ou sessões de meditação ao pôr-do-sol."
    },
    acessibilidade: {
      geral: "A Quinta Flores está parcialmente adaptada para pessoas com mobilidade reduzida. Temos rampa de acesso à entrada principal e ao espaço comum do piso térreo.",
      limitacoes: "Infelizmente, devido à natureza histórica do edifício, nem todos os espaços são completamente acessíveis. Os quartos estão localizados no primeiro andar, acessíveis apenas por escadas.",
      solucoes: "Estamos comprometidos em proporcionar a melhor experiência possível. Informe-nos sobre necessidades específicas e faremos o possível para acomodá-las, incluindo assistência pessoal quando necessário."
    },
    temporada: {
      primavera: "A primavera (março a maio) traz flores coloridas aos nossos jardins, temperaturas amenas e festas tradicionais como a Páscoa e a Vaca das Cordas. É ideal para caminhadas e explorar a natureza.",
      verao: "O verão (junho a setembro) oferece dias quentes perfeitos para aproveitar a piscina e as praias fluviais. É a época de festas tradicionais como as Feiras Novas e vários festivais gastronómicos.",
      outono: "O outono (outubro a novembro) é época de vindimas, cogumelos e folhagem colorida. As temperaturas são agradáveis e a região fica menos movimentada, ideal para um retiro tranquilo.",
      inverno: "O inverno (dezembro a fevereiro) é a época de lareira acesa, gastronomia reconfortante e festas de fim de ano. Oferecemos descontos especiais para estadias longas nesta época."
    },
    sustentabilidade: {
      praticas: "Estamos comprometidos com práticas sustentáveis: separação de resíduos, compostagem, redução de plásticos de uso único, uso de produtos de limpeza ecológicos e apoio a produtores locais.",
      agua: "Implementamos sistemas de economia de água, incluindo recolha de água da chuva para rega dos jardins e sensores de presença nas torneiras das áreas comuns.",
      energia: "Utilizamos iluminação LED de baixo consumo, painéis solares para aquecimento de água e sensores de presença em áreas comuns para reduzir o consumo energético.",
      local: "Promovemos a economia local recomendando restaurantes, artesãos e produtores da região. Nossa pequena loja tem produtos artesanais exclusivamente de produtores locais."
    },
    desporto: {
      golf: "Existem 3 campos de golfe num raio de 30km: Ponte de Lima Golf Course (12km), Estela Golf Club (25km) e Oporto Golf Club (30km). Podemos arranjar green fees com desconto para nossos hóspedes.",
      ciclismo: "A região é perfeita para ciclismo em estrada e BTT, com percursos para todos os níveis. Temos bicicletas disponíveis e podemos sugerir roteiros ou organizar saídas guiadas.",
      caminhadas: "Diversos trilhos marcados na Serra d'Arga, Ecovia do Lima e trilhos do Caminho de Santiago passam próximos à quinta. Disponibilizamos mapas e podemos organizar caminhadas guiadas.",
      agua: "No rio Lima (5km) pode praticar stand-up paddle, canoagem e pesca desportiva. No oceano Atlântico (30km) há excelentes condições para surf, bodyboard e kitesurfing."
    },
    covid: {
      medidas: "Seguimos todas as recomendações da DGS: intensificação da higienização, disponibilização de álcool gel em vários pontos, arejamento regular dos espaços e formação da equipa.",
      checkin: "Oferecemos check-in contactless para quem preferir e espaçamento entre reservas para garantir higienização completa.",
      garantia: "Temos uma política de cancelamento flexível em caso de restrições de viagem relacionadas com a COVID-19, permitindo reagendamento sem custos."
    },
    unknown: [
      "Desculpe, não compreendi completamente. Pode reformular a sua pergunta ou especificar melhor o que procura?",
      "Hmm, não tenho certeza se entendi corretamente. Pode perguntar de outra forma?",
      "Peço desculpa, mas não consegui entender. Posso ajudar com informações sobre quartos, preços, disponibilidade, atividades ou localização.",
      "Essa questão é um pouco confusa para mim. Poderia detalhar melhor o que gostaria de saber sobre a Quinta Flores?"
    ]
  };

  // Faqs expandidas
  const faqs = [
    {
      question: "Qual é o horário de check-in e check-out?",
      answer: "O check-in é a partir das 11h e o check-out até às 15h. Flexibilidade mediante disponibilidade e aviso prévio. Para check-ins tardios (após 20h), por favor informe-nos com antecedência."
    },
    {
      question: "A piscina é aquecida?",
      answer: "A piscina não é aquecida, mas durante os meses de verão (junho a setembro) a temperatura da água é bastante agradável, normalmente entre 24-28°C. A piscina está disponível de maio a outubro, dependendo das condições climatéricas."
    },
    {
      question: "Posso levar o meu animal de estimação?",
      answer: "Sim, aceitamos animais de pequeno porte (até 10kg) ou cães-guia mediante comunicação prévia. É aplicada uma taxa adicional de limpeza de 15€ por estadia. Disponibilizamos comedouros e solicitamos que os animais não fiquem sozinhos no quarto."
    },
    {
      question: "Têm berços disponíveis?",
      answer: "Não disponibilizamos berços, mas você pode trazer o seu próprio berço pois os quartos são espaçosos e podem acomodá-los confortavelmente. Crianças até 5 anos podem ficar gratuitamente utilizando as camas existentes."
    },
    {
      question: "Como posso chegar à Quinta Flores do Aeroporto do Porto?",
      answer: "O Aeroporto do Porto fica a aproximadamente 45 minutos de carro. Você pode alugar um veículo (recomendado para explorar a região), apanhar um táxi (custo aproximado de 60-70€), ou solicitar nosso serviço de transfer (60€ por trajeto para até 4 pessoas)."
    },
    {
      question: "O pequeno-almoço está incluído?",
      answer: "Não oferecemos serviço de pequeno-almoço. Cada alojamento tem uma cozinha equipada para que possa preparar suas refeições. Recomendamos visitar as padarias e cafés locais para uma experiência autêntica ou podemos providenciar um cesto de pequeno-almoço mediante solicitação (custo adicional)."
    },
    {
      question: "Têm opções para refeições?",
      answer: "Não servimos refeições, mas cada alojamento dispõe de cozinha totalmente equipada para preparar suas próprias refeições. Temos uma lista de excelentes restaurantes locais e podemos fazer reservas para nossos hóspedes. Em ocasiões especiais, podemos organizar serviço de catering ou chef privado (mediante reserva)."
    },
    {
      question: "Como posso fazer uma reserva diretamente com a Quinta?",
      answer: "Você pode reservar diretamente pelo nosso site, por telefone (+351 919 241 169) ou por email (quinta.