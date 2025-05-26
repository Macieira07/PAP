    // Chatbot Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const chatbotButton = document.getElementById('chatbotButton');
            const chatbotBox = document.getElementById('chatbotBox');
            const chatbotClose = document.getElementById('chatbotClose');
            const chatbotInput = document.getElementById('chatbotInput');
            const chatbotSend = document.getElementById('chatbotSend');
            const chatbotMessages = document.getElementById('chatbotMessages');
            const suggestionButtons = document.querySelectorAll('.suggestion-button');


            
            // Adicione após a declaração de chatbotMessages
    const feedbackButtons = document.createElement('div');
    feedbackButtons.className = 'message-feedback';
    feedbackButtons.innerHTML = '<button class="feedback-btn positive"><i class="ri-thumb-up-line"></i></button><button class="feedback-btn negative"><i class="ri-thumb-down-line"></i></button>';

    // Na função addMessage, adicione após contentDiv
    if (sender === 'bot') {
    const feedbackClone = feedbackButtons.cloneNode(true);
    contentDiv.appendChild(feedbackClone);
    
    // Adicione event listeners para os botões de feedback
    feedbackClone.querySelectorAll('.feedback-btn').forEach(btn => {
        btn.addEventListener('click', function() {
        this.classList.add('active');
        // Aqui você pode adicionar código para enviar o feedback
        });
    });
    }
            // Banco de dados de respostas para o chatbot
            const responses = {
                // Saudações e despedidas
                saudacao: "Bem-vindo! Como posso ser útil?",
                agradecimento: "É um prazer poder ajudar! Se surgir mais alguma dúvida sobre a Quinta Flores ou sobre a região de Ponte de Lima, estou à disposição. Desejamos uma estadia inesquecível!",
                despedida: "Obrigado por entrar em contato conosco! Esperamos recebê-lo em breve para uma estadia inesquecível no nosso alojamento. Tenha um ótimo dia!", 
                // Respostas para categorias principais
                reservas: {
                    geral: "Para fazer uma reserva na Quinta Flores, você tem várias opções:\n\n• Use o botão 'Reservar Agora' no topo da página\n• Entre em contato pelo telefone: +351 912 418 976\n Ou até precensialmente.",
                    cancelamento: "Nossa política de cancelamento é flexível. Cancelamentos feitos até 7 dias antes da data de chegada recebem reembolso total. Para cancelamentos entre 3-7 dias, há uma taxa de 30%. Cancelamentos com menos de 10 dias de antecedência estão sujeitos a uma taxa de 50% do valor total. Não comparecimentos não têm direito a reembolso.",
                    alteracao: "Alterações de reserva são possíveis mediante disponibilidade. Por favor, entre em contato conosco o quanto antes para verificarmos as possibilidades de mudança de data ou acomodação. Não cobramos taxa para alterações feitas com pelo menos 5 dias de antecedência.",
                    disponibilidade: "Para verificar a disponibilidade exata para suas datas, recomendamos utilizar o formúlario da pagina inicial ou entrar em contato diretamente conosco. Nossos períodos de maior ocupação são entre junho e setembro e durante feriados importantes.",
                    antecedencia: "Recomendamos reservar com pelo menos 1-2 meses de antecedência durante a alta temporada (junho a setembro) e para feriados. Para o resto do ano, 2-3 semanas de antecedência normalmente é suficiente, mas sempre dependendo da disponibilidade."
                },
                acomodacoes: {
                    casaprincipal: "A Casa Principal é nossa maior acomodação com 3 quartos, 2 banheiros, sala de estar espaçosa, cozinha completa e varanda com vista para os jardins. Comporta até 6 pessoas e é ideal para famílias ou grupos. Inclui acesso à piscina, jardins e todas as áreas comuns da propriedade.",
                },
                precos: {
                    geral: "A Quinta Flores tem um preço fixo de 120€ por noite, com capacidade máxima até 10 pessoas. Se fazer uma festa ou algo que terá mais pessoas entre em contacto conosco."
                },
                servicos: {
                    geral: "A Quinta Flores oferece uma variedade de serviços para tornar sua estadia inesquecível:\n\n• Recepção disponível das 8h às 22h\n• Piscina exterior\n• Estacionamento privado\n• Jardins e áreas de lazer\nHá algum serviço específico sobre o qual gostaria de saber mais?",
                    piscina: " A nossa piscina exterior está sempre aberta Possui área de solário com espreguiçadeiras, toalhas disponíveis gratuitamente para os hóspedes.",
                    wifi: "Oferecemos Wi-Fi gratuito de alta velocidade em todas as áreas da propriedade, incluindo jardins e piscina. A senha é fornecida no momento do check-in.",
                    limpeza: "O serviço de limpeza padrão é realizado a cada 3 dias para estadias longas. Para serviço diário, há uma taxa adicional de €15 por dia. Troca de toalhas e lençois de cama sempre disponível mediante solicitação.",
                    recepcao: "Nossa recepção funciona das 8h às 22h. Para chegadas fora deste horário, oferecemos sistema de check-in automatizado. Estamos sempre disponíveis por telefone para emergências 24 horas."
                },
                localizacao: {
                    geral: "A Quinta Flores está localizada nos arredores de Ponte de Lima, a cerca de 3 km do centro histórico. Nossa localização privilegiada oferece tranquilidade no campo com fácil acesso às atrações da região:\n\n• 3 km do centro histórico de Ponte de Lima\n• 25 km de Viana do Castelo\n• 30 km de Braga\n• 70 km do Aeroporto do Porto\n• 35 km da fronteira com Espanha (Galiza)\n\nPodemos fornecer coordenadas GPS exatas para facilitar sua chegada.",
                    como_chegar: "Para chegar à Quinta Flores:\n\nDe carro: Pela A3, saia em Ponte de Lima e siga as indicações para Arcozelo. Após 2,5 km, verá nossa placa à direita.\n\nDe transporte público: Chegue até Ponte de Lima de autocarro e solicite nosso serviço de transfer (taxa adicional) ou táxi (aproximadamente €7-10).\n\nDo Aeroporto do Porto: Oferecemos serviço de transfer privado (€75 para até 4 pessoas) ou você pode alugar um carro (recomendado para explorar a região).",
                    arredores: "Nos arredores da Quinta Flores você encontrará:\n\n• Vinículos de Vinho Verde (3-15 km)\n• Trilhas para caminhada e ciclismo (algumas começam na propriedade)\n• Rio Lima a 1,5 km para atividades aquáticas\n• Restaurantes regionais (1-3 km)\n• Pequeno mercado local (1 km)\n• Farmácia (2 km)\n• Centro histórico com todos os serviços (3 km)",
                    estacionamento: "Oferecemos estacionamento privativo gratuito dentro da propriedade, com espaço para todos os hóspedes. O estacionamento é monitorado por câmeras de segurança e bem iluminado à noite."
                },
                atividades: {
                    geral: "A região do Minho oferece inúmeras atividades para todos os gostos:\n\n• Passeios de bicicleta pela Ecovia do Lima\n• Degustação de vinhos em quintas produtoras de Vinho Verde\n• Caminhadas em trilhas históricas\n• Passeios a cavalo\n• Canoagem no Rio Lima\n• Visitas culturais a aldeias históricas\n• Festivais e festas tradicionais\n• Gastronomia regional\n\nNa recepção, podemos ajudar a organizar qualquer uma destas atividades.",
                    cicloturismo: " Dispomos ainda de várias rotas para descobrir as paisagens únicas da região ",
                    gastronomia: "O Minho é famoso por sua gastronomia. Recomendamos restaurantes autênticos nas proximidades.",
                    criancas: "Para famílias com crianças, recomendamos: caça ao tesouro em nossos jardins, visita ao parque aventura nas proximidades, piqueniques à beira-rio, passeios de barco no Rio Lima e visitas a quintas pedagógicas. Também temos jogos de tabuleiro e livros disponíveis para uso gratuito."
                },
                // Respostas para temas específicos
                historia_propriedade: "A Quinta Flores tem uma história rica que remonta ao século XVIII, quando era uma propriedade agrícola produtora de vinho e azeite. A casa principal ainda preserva elementos originais como a adega de pedra e a fonte que dá nome à Casa da Fonte. Durante a restauração, tivemos o cuidado de preservar o caráter histórico enquanto introduzimos confortos modernos.",
                animais: "Aceitamos animais de estimação de pequeno e médio porte  Solicitamos que os animais não sejam deixados sozinhos nas acomodações",
                grupos: "A Quinta Flores é ideal para grupos de até 10 pessoas, utilizando todas as nossas acomodações. Oferecemos descontos especiais para reservas de grupo e podemos organizar atividades exclusivas como workshops gastronômicos, degustações de vinho ou eventos familiares. Para grupos maiores, trabalhamos com propriedades parceiras nas proximidades.",
                casamentos: "Realizamos pequenos eventos como casamentos íntimos, aniversários especiais e reuniões familiares . Nossos jardins e áreas exteriores oferecem um cenário romântico e autêntico. Trabalhamos com fornecedores locais para catering, decoração e outros serviços. Entre em contacto conosco para mais informações e preços.",
                clima: "A região do Minho tem um clima ameno, influenciado pelo Atlântico. Os verões são quentes mas não excessivamente (20-30°C), e os invernos são suaves (5-15°C) com alguma precipitação. A primavera traz flores exuberantes e o outono cores deslumbrantes às vinhas e florestas.",
                // Fallback (resposta padrão quando não entende a pergunta)
                fallback: "Peço desculpa, mas não tenho certeza se compreendi corretamente sua pergunta. Posso ajudar com informações sobre nossas acomodações, serviços, reservas, localização, atividades na região ou preços. Poderia reformular sua pergunta?"
            };
            // Função para mostrar a chatbot box
            chatbotButton.addEventListener('click', function() {
                chatbotBox.style.display = 'flex';
                chatbotButton.style.display = 'none';
            });
            // Função para fechar a chatbot box
            chatbotClose.addEventListener('click', function() {
                chatbotBox.style.display = 'none';
                chatbotButton.style.display = 'flex';
            });
            // Função para enviar mensagem ao pressionar Enter
            chatbotInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
            // Função para enviar mensagem ao clicar no botão
            chatbotSend.addEventListener('click', sendMessage);
            // Função para lidar com os botões de sugestão
            suggestionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const text = this.textContent;
                    chatbotInput.value = text;
                    sendMessage();
                });
            });
            // Função principal para enviar mensagem
            function sendMessage() {
                const message = chatbotInput.value.trim();
                if (message === '') return;
                // Adicionar mensagem do usuário
                addMessage(message, 'user');
                chatbotInput.value = '';
                // Simular digitação do bot
                showTypingIndicator();
                // Processar resposta com um pequeno delay para simular o pensamento
                setTimeout(() => {
                    // Remover indicador de digitação
                    removeTypingIndicator();
                    // Gerar e mostrar resposta
                    const response = getResponse(message);
                    addMessage(response, 'bot');
                    // Scroll para a última mensagem
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                }, 1000 + Math.random() * 1000); // Delay variável entre 1-2 segundos
            }
            // Função para adicionar mensagem à conversa
            function addMessage(text, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${sender}-message`;
                
                let avatar;
                if (sender === 'bot') {
                    avatar = document.createElement('img');
                    avatar.src = '../assets/logos/logotipo1.png';
                    avatar.alt = 'Bot';
                    avatar.className = 'message-avatar';
                } else {
                    avatar = document.createElement('div');
                    avatar.className = 'message-avatar';
                    avatar.style.backgroundColor = '#8CB58E';
                    avatar.style.display = 'flex';
                    avatar.style.justifyContent = 'center';
                    avatar.style.alignItems = 'center';
                    avatar.style.color = 'white';
                    avatar.style.fontWeight = 'bold';
                    avatar.textContent = 'EU';
                }
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                // Processar quebras de linha e links
                const formattedText = formatMessageText(text);
                contentDiv.appendChild(formattedText);
                if (sender === 'user') {
                    messageDiv.appendChild(contentDiv);
                    messageDiv.appendChild(avatar);
                } else {
                    messageDiv.appendChild(avatar);
                    messageDiv.appendChild(contentDiv);
                }
                chatbotMessages.appendChild(messageDiv);
            }
            // Função para formatar o texto da mensagem (quebras de linha e links)
            function formatMessageText(text) {
                const container = document.createElement('div');
                // Dividir por quebras de linha
                const lines = text.split('\n');
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i];
                    // Verificar se a linha contém um link
                    const linkRegex = /(https?:\/\/[^\s]+)/g;
                    const parts = line.split(linkRegex);
                    
                    for (let j = 0; j < parts.length; j++) {
                        if (linkRegex.test(parts[j])) {
                            const link = document.createElement('a');
                            link.href = parts[j];
                            link.textContent = parts[j];
                            link.target = '_blank';
                            container.appendChild(link);
                        } else if (parts[j] !== '') {
                            container.appendChild(document.createTextNode(parts[j]));
                        }
                    }
                    // Adicionar quebra de linha, exceto para a última linha
                    if (i < lines.length - 1) {
                        container.appendChild(document.createElement('br'));
                    }
                }
                return container;
            }
            // Função para mostrar indicador de digitação
            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.className = 'message bot-message typing-message';
                const avatar = document.createElement('img');
                avatar.src = '../assets/logos/logotipo1.png';
                avatar.alt = 'Bot';
                avatar.className = 'message-avatar';
                const typingIndicator = document.createElement('div');
                typingIndicator.className = 'typing-indicator';
                for (let i = 0; i < 3; i++) {
                    const dot = document.createElement('span');
                    typingIndicator.appendChild(dot);
                }
                typingDiv.appendChild(avatar);
                typingDiv.appendChild(typingIndicator);
                chatbotMessages.appendChild(typingDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
            // Função para remover indicador de digitação
            function removeTypingIndicator() {
                const typingMessage = document.querySelector('.typing-message');
                if (typingMessage) {
                    typingMessage.remove();
                }
            }
            // Função para determinar a resposta adequada
            function getResponse(message) {
                // Converter para minúsculas para facilitar a comparação
                const lowercaseMessage = message.toLowerCase();
                
                // Verificar saudações
                if (containsAny(lowercaseMessage, ['olá', 'ola', 'oi', 'bom dia', 'boa tarde', 'boa noite', 'hello', 'hi'])) {
                    return responses.saudacao;
                }
                // Verificar despedidas
                if (containsAny(lowercaseMessage, ['adeus', 'tchau', 'até logo', 'até mais', 'até breve', 'goodbye'])) {
                    return responses.despedida;
                }
                // Verificar agradecimentos
                if (containsAny(lowercaseMessage, ['obrigado', 'obrigada', 'agradecido', 'agradecida', 'thanks', 'thank you'])) {
                    return responses.agradecimento;
                }
                // Verificar categorias principais
                // Reservas
                if (containsAny(lowercaseMessage, ['reserva', 'reservar', 'booking', 'alugar', 'disponibilidade'])) {
                    if (containsAny(lowercaseMessage, ['cancelar', 'cancelamento', 'desistir'])) {
                        return responses.reservas.cancelamento;
                    } else if (containsAny(lowercaseMessage, ['alterar', 'alteração', 'mudar', 'modificar'])) {
                        return responses.reservas.alteracao;
                    } else if (containsAny(lowercaseMessage, ['disponível', 'disponibilidade', 'livre', 'data'])) {
                        return responses.reservas.disponibilidade;
                    } else if (containsAny(lowercaseMessage, ['antecedência', 'antecedencia', 'quanto tempo', 'com quanto tempo'])) {
                        return responses.reservas.antecedencia;
                    } else {
                        return responses.reservas.geral;
                    }
                }
                // Acomodações
                if (containsAny(lowercaseMessage, ['acomodação', 'acomodacoes', 'quarto', 'quartos', 'casa', 'apartamento', 'estúdio', 'suite', 'dormir', 'hospedagem'])) {
                    if (containsAny(lowercaseMessage, ['casa principal', 'principal'])) {
                        return responses.acomodacoes.casaprincipal;
                    } else {
                        return responses.acomodacoes.geral;
                    }
                }
                // Preços
                if (containsAny(lowercaseMessage, ['preço', 'preco', 'preços', 'precos', 'valor', 'valores', 'custo', 'quanto custa', 'tarifa'])) {
                    if (containsAny(lowercaseMessage, ['promoção', 'promocao', 'desconto', 'oferta', 'especial'])) {
                        return responses.precos.promocoes;
                    } else if (containsAny(lowercaseMessage, ['extra', 'adicional', 'serviço', 'servico'])) {
                        return responses.precos.extras;
                    } else {
                        return responses.precos.geral;
                    }
                }
                // Serviços
                if (containsAny(lowercaseMessage, ['serviço', 'servico', 'facilidade', 'comodidade', 'amenidade'])) {
                    if (containsAny(lowercaseMessage, ['piscina', 'nadar', 'banho'])) {
                        return responses.servicos.piscina;
                    } else if (containsAny(lowercaseMessage, ['wifi', 'internet', 'wi-fi', 'wireless'])) {
                        return responses.servicos.wifi;
                    } else if (containsAny(lowercaseMessage, ['limpeza', 'arrumação', 'arrumacao', 'limpar'])) {
                        return responses.servicos.limpeza;
                    } else if (containsAny(lowercaseMessage, ['pequeno almoço', 'pequeno-almoço', 'café da manhã', 'cafe da manha', 'breakfast'])) {
                        return responses.servicos.pequeno_almoco;
                    } else if (containsAny(lowercaseMessage, ['recepção', 'recepcao', 'atendimento', 'check-in', 'check-out'])) {
                        return responses.servicos.recepcao;
                    } else {
                        return responses.servicos.geral;
                    }
                }
                // Localização
                if (containsAny(lowercaseMessage, ['localização', 'localizacao', 'endereço', 'endereco', 'onde fica', 'como chegar', 'direção', 'direcao'])) {
                    if (containsAny(lowercaseMessage, ['como chegar', 'chegar', 'direções', 'direcoes', 'caminho'])) {
                        return responses.localizacao.como_chegar;
                    } else if (containsAny(lowercaseMessage, ['arredores', 'proximidade', 'perto', 'ao redor'])) {
                        return responses.localizacao.arredores;
                    } else if (containsAny(lowercaseMessage, ['estacionamento', 'parque', 'carro', 'estacionar', 'parking'])) {
                        return responses.localizacao.estacionamento;
                    } else {
                        return responses.localizacao.geral;
                    }
                }
                // Atividades
                if (containsAny(lowercaseMessage, ['atividade', 'atividades', 'fazer', 'lazer', 'passeio', 'passeios', 'visitar', 'tour'])) {
                    if (containsAny(lowercaseMessage, ['bicicleta', 'bike', 'cicloturismo', 'ciclismo', 'pedalar'])) {
                        return responses.atividades.cicloturismo;
                    } else if (containsAny(lowercaseMessage, ['vinho', 'vinhos', 'vinho verde', 'adega', 'degustação', 'degustacao'])) {
                        return responses.atividades.vinhos;
                    } else if (containsAny(lowercaseMessage, ['comida', 'gastronomia', 'comer', 'restaurante', 'culinária', 'culinaria'])) {
                        return responses.atividades.gastronomia;
                    } else if (containsAny(lowercaseMessage, ['cultura', 'histórico', 'historico', 'monumento', 'museu', 'igreja'])) {
                        return responses.atividades.cultura;
                    } else if (containsAny(lowercaseMessage, ['criança', 'criancas', 'família', 'familia', 'filho', 'filhos', 'filha', 'filhas', 'bebê', 'bebe'])) {
                        return responses.atividades.criancas;
                    } else {
                        return responses.atividades.geral;
                    }
                }
                // Temas específicos
                if (containsAny(lowercaseMessage, ['quem são', 'quem sao', 'sobre vocês', 'sobre voces', 'equipe', 'proprietários', 'proprietarios', 'história da quinta', 'historia da quinta'])) {
                    return responses.quem_somos;
                }
                if (containsAny(lowercaseMessage, ['sustentável', 'sustentavel', 'sustentabilidade', 'ecológico', 'ecologico', 'ambiente', 'ambiental', 'eco'])) {
                    return responses.sustentabilidade;
                }
                if (containsAny(lowercaseMessage, ['história da propriedade', 'historia da propriedade', 'quando foi construída', 'quando foi construida', 'antiguidade', 'antiga', 'século', 'seculo'])) {
                    return responses.historia_propriedade;
                }
                if (containsAny(lowercaseMessage, ['animal', 'animais', 'pet', 'pets', 'cachorro', 'gato', 'cão', 'cao'])) {
                    return responses.animais;
                }
                if (containsAny(lowercaseMessage, ['grupo', 'grupos', 'várias pessoas', 'varias pessoas', 'família grande', 'familia grande', 'amigos', 'turma'])) {
                    return responses.grupos;
                }
                if (containsAny(lowercaseMessage, ['casamento', 'casar', 'evento', 'festa', 'celebração', 'celebracao', 'comemoracao', 'comemoração'])) {
                    return responses.casamentos;
                }
                if (containsAny(lowercaseMessage, ['clima', 'tempo', 'temperatura', 'chove', 'chuva', 'sol', 'quente', 'frio', 'estação', 'estacao', 'melhor época', 'melhor epoca'])) {
                    return responses.clima;
                }
                // Se nenhuma correspondência for encontrada, use a resposta padrão
                return responses.fallback;
            }
            // Função auxiliar para verificar se uma mensagem contém qualquer uma das palavras-chave
            function containsAny(message, keywords) {
                return keywords.some(keyword => message.includes(keyword));
            }
        });
