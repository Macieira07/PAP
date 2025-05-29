<?php
require_once '../conexao.php';
require_once 'header.php';

// Buscar dados do usuário
$query = "SELECT * FROM hospedes WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>
<link rel="stylesheet" href="../index/chatbot.css">
<div class="profile-container">
    <h1><i class="fas fa-user"></i> Meu Perfil</h1>
    
    <div class="profile-section">
        <h2><i class="fas fa-id-card"></i> Informações Pessoais</h2>
        <div class="profile-info">
            <div class="info-item">
                <span class="info-label">Nome Completo:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_nome']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Telefone:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_telefone']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_documento_ident']) ?></span>
            </div>
            <button id="editProfileBtn" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Editar Perfil
            </button>
        </div>
    </div>

    <!-- Formulário de edição (inicialmente oculto) -->
    <div id="editProfileForm" class="profile-edit-form" style="display:none;">
        <h2><i class="fas fa-edit"></i> Editar Perfil</h2>
        <form action="atualizar_perfil.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_nome']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_telefone']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="documento">Documento de Identificação</label>
                <input type="text" id="documento" name="documento" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_documento_ident']) ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="button" id="cancelEditBtn" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <div class="profile-section">
        <h2><i class="fas fa-lock"></i> Segurança</h2>
        <div class="security-actions">
            <a href="alterar_senha.php" class="btn btn-secondary">
                <i class="fas fa-key"></i> Alterar Senha
            </a>
        </div>
    </div>
    
    <div class="profile-section">
        <h2><i class="fas fa-history"></i> Histórico Recente</h2>
        <div class="recent-activity">
            <!-- Aqui você pode mostrar as últimas atividades -->
            <div class="activity-item">
                <i class="fas fa-calendar-check"></i>
                <span>Último login: 
  <?php
    if (!empty($usuario['H_ultimo_login'])) {
        echo date('d/m/Y H:i', strtotime($usuario['H_ultimo_login']));
    } else {
        echo "Sem dados de último login";
    }
  ?>
</span>
    
            </div>
        </div>
        
<div class="chatbot-container">
    <div class="chatbot-button" id="chatbotButton">
        <i class="fa-solid fa-comment-dots"></i>
    </div>
    <div class="chatbot-box" id="chatbotBox">
        <div class="chatbot-header">
            <div class="chatbot-title">
                <img src="assets/logos/logotipo1.png" alt="Quinta Flores" class="chatbot-logo">
                <span>Assistente Virtual da Quinta Flores</span>
            </div>
            <button class="chatbot-close" id="chatbotClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="message bot-message">
                <img src="assets/logos/logotipo1.png" alt="Bot" class="message-avatar">
                <div class="message-content">
                    <p>Olá! Bem-vindo à Quinta Flores. Como posso ajudá-lo hoje?</p>
                </div>
            </div>
        </div>
        <div class="chatbot-input-container">
            <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Digite sua mensagem...">
            <button id="chatbotSend" class="chatbot-send">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
        <div class="chatbot-suggestions">
            <button class="suggestion-button">
                <i class="fa-solid fa-calendar-check"></i> Reservas
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-bed"></i> Acomodações
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-bell-concierge"></i> Serviços
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-map-location-dot"></i> Localização
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-person-hiking"></i> Atividades
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-euro-sign"></i> Preços
            </button>
        </div>
        <div class="chatbot-footer">
            <span>Quinta Flores - ChatBot</span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatbotButton = document.getElementById('chatbotButton');
        const chatbotBox = document.getElementById('chatbotBox');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotInput = document.getElementById('chatbotInput');
        const chatbotSend = document.getElementById('chatbotSend');
        const chatbotMessages = document.getElementById('chatbotMessages');
        const suggestionButtons = document.querySelectorAll('.suggestion-button');

        // Mostrar chatbot box
        chatbotButton.addEventListener('click', function() {
            chatbotBox.style.display = 'flex';
            chatbotButton.style.display = 'none';
        });

        // Fechar chatbot box
        chatbotClose.addEventListener('click', function() {
            chatbotBox.style.display = 'none';
            chatbotButton.style.display = 'flex';
        });

        // Enviar mensagem ao pressionar Enter
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Enviar mensagem ao clicar no botão
        chatbotSend.addEventListener('click', sendMessage);

        // Botões de sugestão
        suggestionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.textContent.trim();
                chatbotInput.value = text;
                sendMessage();
            });
        });

        // Respostas do chatbot
        const responses = {
            saudacao: "Bem-vindo à Quinta Flores. Em que podemos ser úteis?",
            agradecimento: "Obrigado pelo seu contacto. Ficamos ao dispor para qualquer questão relacionada com a Quinta Flores ou com a região de Ponte de Lima. Desejamos-lhe uma excelente estadia connosco.",
            despedida: "Agradecemos o seu contacto. Esperamos ter o prazer de o receber brevemente na Quinta Flores. Votos de um excelente dia.",
            reservas: {
                geral: "Para efetuar uma reserva na Quinta Flores, dispõe das seguintes opções:\n\n• Utilize o botão 'Reservar Agora' disponível no topo da página\n• Contacte-nos através do número: +351 912 418 976\n• Ou visite-nos presencialmente mediante agendamento.",
                cancelamento: "A nossa política de cancelamento é flexível:\n\n• Cancelamentos até 7 dias antes da data de chegada – reembolso total\n• Cancelamentos entre 3 e 7 dias – taxa de 30%\n• Cancelamentos com menos de 3 dias – taxa de 50% do valor total da reserva.",
                alteracao: "As alterações à reserva estão sujeitas a disponibilidade. Recomendamos que entre em contacto connosco com a maior antecedência possível para verificarmos as alternativas disponíveis.",
                disponibilidade: "Para consultar a disponibilidade para datas específicas, utilize o formulário na página inicial ou contacte-nos diretamente.",
                antecedencia: "Durante a época alta (junho a setembro) e em períodos festivos, recomendamos que efetue a sua reserva com 1 a 2 meses de antecedência."
            },
            acomodacoes: {
                casaprincipal: "A Casa Principal é nossa maior acomodação com 3 quartos com 5 camas de casal, 3 casas de banho, sala de estar espaçosa, cozinha completa e varanda com vista para os jardins.",
                geral: "Oferecemos acomodações confortáveis e bem equipadas. A Casa Principal comporta até 10 pessoas com todos os confortos necessários para uma estadia perfeita."
            },
            precos: {
                geral: "A Quinta Flores apresenta um valor fixo de 120€ por noite, com capacidade máxima até 10 pessoas. Para eventos ou ocasiões especiais com número superior de participantes, solicitamos que entre em contacto connosco previamente."
            },
            servicos: {
                geral: "A Quinta Flores disponibiliza diversos serviços pensados para proporcionar uma estadia confortável e memorável:\n\n• Receção disponível das 08h00 às 22h00\n• Piscina exterior com zona de solário\n• Estacionamento privativo gratuito\n• Jardins e zonas de lazer",
                piscina: "A nossa piscina exterior encontra-se acessível diariamente. Dispõe de zona de solário com espreguiçadeiras e toalhas disponibilizadas gratuitamente aos hóspedes.",
                wifi: "Disponibilizamos Wi-Fi gratuito de alta velocidade em toda a propriedade, incluindo nas zonas exteriores. A palavra-passe será fornecida no momento do check-in.",
                limpeza: "O serviço de limpeza é sempre feito antes e depois da estadia. Caso pretenda limpeza diária, poderá ser solicitado por um valor adicional de 15€ por dia.",
                recepcao: "A receção está disponível entre as 08h00 e as 22h00. Para chegadas fora deste horário, temos ao dispor um sistema de check-in automatizado, mediante pedido prévio."
            },
            localizacao: {
                geral: "A Quinta Flores está situada a cerca de 3 km do centro histórico de Ponte de Lima, oferecendo um ambiente calmo e campestre com fácil acesso às principais atrações da região.",
                como_chegar: "Como chegar à Quinta Flores:\n\n• De carro: pela A3, tome a saída para Ponte de Lima e siga em direção a Arcozelo. Após aproximadamente 2,5 km, encontrará sinalização com a nossa identificação à direita.",
                arredores: "Nas proximidades da Quinta Flores poderá explorar vinícolas de Vinho Verde, percursos pedestres, atividades no Rio Lima e restaurantes típicos da gastronomia minhota.",
                estacionamento: "Disponibilizamos estacionamento privado e gratuito dentro da propriedade, com capacidade para todos os nossos hóspedes."
            },
            atividades: {
                geral: "A região do Minho oferece inúmeras atividades: passeios de bicicleta, degustação de vinhos, caminhadas, passeios a cavalo, canoagem no Rio Lima e visitas culturais. Se tiver interesse pode ver no nosso site mais atividades que pode fazer perto da Quinta Flores.",
                cicloturismo: "Dispomos ainda de várias rotas para descobrir as paisagens únicas da região.",
                gastronomia: "O Minho é famoso por sua gastronomia. Recomendamos restaurantes autênticos nas proximidades.",
                criancas: "Para famílias com crianças, recomendamos: caça ao tesouro em nossos jardins, visita ao parque aventura, piqueniques à beira-rio e passeios de barco no Rio Lima."
            },
            fallback: "Sou um assistente virtual da Quinta Flores. Peço desculpa, mas não consegui compreender corretamente a sua pergunta. Poderia reformulá-la ou especificar melhor, por favor?"
        };

        // Função principal para enviar mensagem
        function sendMessage() {
            const message = chatbotInput.value.trim();
            if (message === '') return;

            // Adicionar mensagem do usuário
            addMessage(message, 'user');
            chatbotInput.value = '';

            // Simular digitação do bot
            showTypingIndicator();

            // Processar resposta com um pequeno delay
            setTimeout(() => {
                removeTypingIndicator();
                const response = getResponse(message);
                addMessage(response, 'bot');
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 1000 + Math.random() * 1000);
        }

        // Função para adicionar mensagem à conversa
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            
            let avatar;
            if (sender === 'bot') {
                avatar = document.createElement('img');
                avatar.src = 'assets/logos/logotipo1.png';
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
            contentDiv.innerHTML = formatMessageText(text);

            if (sender === 'user') {
                messageDiv.appendChild(contentDiv);
                messageDiv.appendChild(avatar);
            } else {
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(contentDiv);
            }

            chatbotMessages.appendChild(messageDiv);
        }

        // Função para formatar o texto da mensagem
        function formatMessageText(text) {
            return text.replace(/\n/g, '<br>');
        }

        // Função para mostrar indicador de digitação
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot-message typing-message';
            
            const avatar = document.createElement('img');
            avatar.src = 'assets/logos/logotipo1.png';
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

        // Função para determinar a resposta adequada (versão corrigida)
        function getResponse(message) {
            const lowercaseMessage = message.toLowerCase();
            
            // 1. Verificar despedidas (mais específico)
            if (/(adeus|tchau|até logo|até mais|até breve|goodbye|bye|até à próxima|ate a proxima)/i.test(lowercaseMessage)) {
                return responses.despedida;
            }
            
            // 2. Verificar agradecimentos explícitos
            if (/(obrigado|obrigada|agradecido|agradecida|thanks|thank you|grato|grata|muito obrigado|muito obrigada)\b/i.test(lowercaseMessage)) {
                return responses.agradecimento;
            }
            
            // 3. Verificar saudações
            if (/(olá|ola|oi|bom dia|boa tarde|boa noite|hello|hi|hey|saudações|saudacoes)\b/i.test(lowercaseMessage)) {
                return responses.saudacao;
            }
            
            // 4. Verificar reservas
            if (/(reserva|reservar|booking|alugar|disponibilidade|marcar|fazer reserva|agendar|quero reservar)\b/i.test(lowercaseMessage)) {
                if (/(cancelar|cancelamento|anular|desmarcar|cancelada|cancelar reserva)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.cancelamento;
                } else if (/(alterar|alteração|mudar|modificar|trocar|alterar reserva)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.alteracao;
                } else if (/(disponível|disponibilidade|tem vaga|vagas|datas livres|datas disponíveis)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.disponibilidade;
                } else if (/(antecedência|antecedencia|com antecedência|quando reservar|prazo para reservar|tempo antes)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.antecedencia;
                } else {
                    return responses.reservas.geral;
                }
            }
            
            // 5. Verificar acomodações
            if (/(acomodação|acomodacoes|quarto|quartos|casa|alojamento|hospedagem|suite|suíte)\b/i.test(lowercaseMessage)) {
                if (/(casa principal|principal|casa mãe|principal casa)\b/i.test(lowercaseMessage)) {
                    return responses.acomodacoes.casaprincipal;
                } else {
                    return responses.acomodacoes.geral;
                }
            }
            
            // 6. Verificar preços
            if (/(preço|preco|preços|precos|valor|valores|custo|quanto custa|tarifa|taxa|preço por noite)\b/i.test(lowercaseMessage)) {
                return responses.precos.geral;
            }
            
            // 7. Verificar serviços
            if (/(serviço|servico|facilidade|comodidade|serviços|comodidades|infraestrutura)\b/i.test(lowercaseMessage)) {
                if (/(piscina|nadar|piscinas|área de lazer aquática)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.piscina;
                } else if (/(wifi|internet|wi-fi|rede|conexão|conexao)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.wifi;
                } else if (/(limpeza|arrumação|arrumacao|faxina|serviço de limpeza)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.limpeza;
                } else if (/(recepção|recepcao|atendimento|balcão|front desk)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.recepcao;
                } else {
                    return responses.servicos.geral;
                }
            }
            
            // 8. Verificar localização
            if (/(localização|localizacao|endereço|endereco|onde fica|como chegar|morada|situação|direção|direcao)\b/i.test(lowercaseMessage)) {
                if (/(como chegar|chegar|direções|direcoes|rota|caminho|instruções|instrucoes|acesso)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.como_chegar;
                } else if (/(arredores|proximidade|perto|próximo|proximo|vizinhança|vizinhanca|área|região|regiao)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.arredores;
                } else if (/(estacionamento|parque|carro|vaga|garagem|parking)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.estacionamento;
                } else {
                    return responses.localizacao.geral;
                }
            }
            
            // 9. Verificar atividades
            if (/(atividade|atividades|fazer|lazer|passeio|passeios|entretenimento|diversão|diversao|programa)\b/i.test(lowercaseMessage)) {
                if (/(bicicleta|bike|cicloturismo|bicicletas|ciclismo|andar de bicicleta)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.cicloturismo;
                } else if (/(comida|gastronomia|comer|restaurante|culinária|culinaria|prato|refeição|refeicao)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.gastronomia;
                } else if (/(criança|criancas|família|familia|kids|crianças|famílias|familias|filhos|filha|filho)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.criancas;
                } else {
                    return responses.atividades.geral;
                }
            }
            
            // 10. Se nenhuma das condições acima for atendida
            return responses.fallback;
        }
    });
</script>
    </div>
</div>

<script>
document.getElementById('editProfileBtn').addEventListener('click', function() {
    document.querySelector('.profile-info').style.display = 'none';
    document.getElementById('editProfileForm').style.display = 'block';
});

document.getElementById('cancelEditBtn').addEventListener('click', function() {
    document.querySelector('.profile-info').style.display = 'block';
    document.getElementById('editProfileForm').style.display = 'none';
});
</script>

<?php require_once 'footer.php'; ?>