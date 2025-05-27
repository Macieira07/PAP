    document.addEventListener('DOMContentLoaded', function() {
    // Verificar o idioma salvo no localStorage ou usar o padrão (português)
    const savedLanguage = localStorage.getItem('language') || 'pt';
    changeLanguage(savedLanguage);

    // Ativar o botão do idioma atual
    const languageButtons = document.querySelectorAll('.language-btn');
    languageButtons.forEach(button => {
        if (button.getAttribute('data-lang') === savedLanguage) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
});

function changeLanguage(language) {
    // Salvar o idioma selecionado no localStorage
    localStorage.setItem('language', language);

    // Atualizar a classe active nos botões de idioma
    document.querySelectorAll('.language-btn').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-lang') === language) {
            btn.classList.add('active');
        }
    });

    // Traduzir todo o conteúdo da página
    translatePage(language);
}

function translatePage(language) {
    // Objeto com todas as traduções
    const translations = {
        'en': {
            // Hero Section
            'hero__title': 'Discover More About Our Accommodation and the Region',
            'hero__subtitle': 'Explore the charms of our space and be surprised by Ponte de Lima',
            'hero__cta': 'Book Now',

            // About Section
            'section-title-about': 'Our Story',
            'section-subtitle-about': 'Discover the history and values that make Quinta Flores a special destination in Ponte de Lima',
            'Nossa História': 'Our History',
            'A Quinta Flores nasceu do sonho de criar um espaço que combinasse o charme rústico do Minho com o conforto moderno. Localizada em uma das regiões mais pitorescas de Portugal, nossa propriedade preserva a arquitetura tradicional portuguesa enquanto oferece todas as comodidades contemporâneas que você espera.': 
            'Quinta Flores was born from the dream of creating a space that combines the rustic charm of Minho with modern comfort. Located in one of the most picturesque regions of Portugal, our property preserves traditional Portuguese architecture while offering all the contemporary amenities you expect.',
            'Nossa Missão': 'Our Mission',
            'Proporcionamos aos nossos hóspedes uma experiência autêntica do norte de Portugal, combinando hospitalidade calorosa com serviços de qualidade. Queremos que cada visitante se sinta em casa e leve consigo memórias inesquecíveis que durarão para sempre.':
            'We provide our guests with an authentic experience of northern Portugal, combining warm hospitality with quality services. We want every visitor to feel at home and take with them unforgettable memories that will last forever.',
            'Nossos Valores': 'Our Values',
            'Sustentabilidade, autenticidade e hospitalidade são os pilares que guiam nossa operação. Valorizamos as tradições locais e nos esforçamos para preservar o ambiente natural que nos rodeia, garantindo que as futuras gerações também possam desfrutar dessa beleza.':
            'Sustainability, authenticity and hospitality are the pillars that guide our operation. We value local traditions and strive to preserve the natural environment around us, ensuring that future generations can also enjoy this beauty.',

            // Accommodation Section
            'section-title-accommodation': 'Our Accommodations',
            'section-subtitle-accommodation': 'Comfort and charm in every detail, providing an unforgettable stay',
            'Quartos': 'Rooms',
            'Áreas Comuns': 'Common Areas',
            'Suítes Especiais': 'Special Suites',
            'Quartos espaçosos com vista para o jardim': 'Spacious rooms with garden view',
            'Ar condicionado e aquecimento': 'Air conditioning and heating',
            'Casa de banho privativa com amenities de qualidade': 'Private bathroom with quality amenities',
            'Wi-Fi de alta velocidade': 'High-speed Wi-Fi',
            'Decoração tradicional portuguesa com toques contemporâneos': 'Traditional Portuguese decoration with contemporary touches',
            'Piscina ao ar livre com espreguiçadeiras': 'Outdoor pool with sun loungers',
            'Jardins exuberantes para relaxar': 'Lush gardens to relax',
            'Área de churrasco totalmente equipada': 'Fully equipped barbecue area',
            'Espaço para camping': 'Camping space',
            'Estacionamento privado': 'Private parking',
            'Terraço panorâmico com vista para as montanhas': 'Panoramic terrace with mountain views',
            'Suítes familiares espaçosas': 'Spacious family suites',
            'Unidades com cozinha equipada': 'Units with equipped kitchen',
            'Decoração de luxo nas suítes': 'Luxury decoration in the suites',
            'Opções de acomodação para grupos': 'Group accommodation options',

            // Activities Section
            'section-title-activities': 'Experiences & Activities',
            'section-subtitle-activities': 'Discover the best of the region with our carefully selected experiences',
            'Experiência Gastronômica': 'Gastronomic Experience',
            'Desfrute de uma experiência gastronómica única no coração do Minho, com a oportunidade de saborear pratos tradicionais da região. Participe em workshops culinários e degustações de produtos típicos, incluindo os reconhecidos vinhos verdes, que irão enriquecer a sua estadia com os sabores autênticos do nosso território':
            'Enjoy a unique gastronomic experience in the heart of Minho, with the opportunity to taste traditional regional dishes. Participate in culinary workshops and tastings of typical products, including the renowned green wines, which will enrich your stay with the authentic flavors of our territory.',
            'Trilhas e Natureza': 'Trails and Nature',
            'Explore as belas trilhas da região e conheça as maravilhas naturais do Minho. Descubra detalhes sobre a fauna, flora e a rica paisagem local, enquanto passeia por caminhos tranquilos e desfruta da serenidade da natureza.':
            'Explore the beautiful trails of the region and discover the natural wonders of Minho. Learn about the fauna, flora and rich local landscape as you walk along peaceful paths and enjoy the serenity of nature.',
            'Passeios Culturais': 'Cultural Tours',
            'Descubra os monumentos históricos, as festas tradicionais e as feiras de artesanato que celebram a rica cultura de Ponte de Lima, oferecendo-lhe uma verdadeira imersão nas tradições locais e no património da região.':
            'Discover the historical monuments, traditional festivals and craft fairs that celebrate the rich culture of Ponte de Lima, offering you a true immersion in local traditions and the heritage of the region.',
            'Explore mais': 'Explore more',

            // Gallery Section
            'section-title-gallery': 'Our Gallery',
            'section-subtitle-gallery': 'Images that capture the essence and beauty of Quinta Flores',
            'Entrada Principal': 'Main Entrance',
            'Vista Panorâmica': 'Panoramic View',
            'Área de Churrasco': 'Barbecue Area',
            'Jardins Floridos': 'Flowering Gardens',
            'Piscina Exterior': 'Outdoor Pool',
            'Entrada Acolhedora para os Quartos': 'Welcoming Entrance to the Rooms',
            'Suite': 'Suite',
            'Casa de Banho (Suite)': 'Bathroom (Suite)',
            'Sala de Estar Aconchegante': 'Cozy Living Room',
            'Cozinha Totalmente Equipada': 'Fully Equipped Kitchen',
            'Espaço Ajardinado com Esculturas': 'Landscaped Space with Sculptures',
            'Pôr-do-Sol na Quinta': 'Sunset at the Farm',

            // Testimonials Section
            'section-title-testimonials': 'What Our Guests Say',
            'section-subtitle-testimonials': 'Authentic experiences shared by those who have stayed at Quinta Flores',
            'Uma experiência incrível! A hospitalidade da equipe da Quinta Flores é incomparável. Os quartos são espaçosos e confortáveis, e as áreas comuns são perfeitas para relaxar. Voltaremos com certeza!':
            'An incredible experience! The hospitality of the Quinta Flores team is unmatched. The rooms are spacious and comfortable, and the common areas are perfect for relaxing. We will definitely be back!',

            // FAQ Section
            'section-title-faq': 'Frequently Asked Questions',
            'section-subtitle-faq': 'Answers to the most common questions about Quinta Flores',
            'O que acontece em caso de danos na propriedade ou problemas durante a estadia?': 
            'What happens in case of damage to the property or problems during the stay?',
            'Em caso de danos à propriedade durante a sua estadia, solicitamos que nos informe imediatamente para que possamos tomar as devidas providências. Dependendo da gravidade do dano, poderá ser cobrada uma taxa adicional para reparação. Caso haja qualquer problema durante a sua estadia, a nossa equipa está disponível 24 horas por dia para garantir que tudo seja resolvido rapidamente e com a máxima eficiência.':
            'In case of damage to the property during your stay, we ask that you inform us immediately so we can take appropriate action. Depending on the severity of the damage, an additional repair fee may be charged. If there are any problems during your stay, our team is available 24 hours a day to ensure everything is resolved quickly and efficiently.',
            'Qual é a política de cancelamento?': 'What is the cancellation policy?',
            'Para cancelar a sua reserva, é necessário ligar para o número +351 912 418 976 com 10 dias de antecedência. Caso a anulação seja feita dentro de um prazo inferior, será cobrado 50% do valor da reserva. Pedimos que esteja atento às condições e prazos para evitar custos adicionais.':
            'To cancel your reservation, you must call +351 912 418 976 at least 10 days in advance. If cancellation is made within a shorter period, 50% of the reservation value will be charged. Please be aware of the conditions and deadlines to avoid additional costs.',
            'Aceitam animais de estimação?': 'Do you accept pets?',
            'Aceitamos animais de porte pequeno apenas. Para garantir a sua estadia confortável e sem imprevistos, recomendamos que entre em contacto conosco com antecedência para confirmar a disponibilidade e as condições específicas para o seu animal.':
            'We only accept small pets. To ensure your comfortable stay without surprises, we recommend contacting us in advance to confirm availability and specific conditions for your pet.',

            // Location Section
            'section-title-location': 'Privileged Location',
            'section-subtitle-location': 'Discover why our location is perfect for exploring the best of Northern Portugal',
            'Pontos de Interesse Próximos': 'Nearby Points of Interest',
            'Como Chegar': 'How to Get Here',
            'Centro histórico de Ponte de Lima (5 min)': 'Historic center of Ponte de Lima (5 min)',
            'Praia fluvial (10 min)': 'River beach (10 min)',
            'Ecovia do Lima para caminhadas e ciclismo': 'Ecovia do Lima for hiking and cycling',
            'Área de Paisagem Protegida das Lagoas': 'Protected Landscape Area of Lagoas',
            'Festival Internacional de Jardins': 'International Garden Festival',
            '45 minutos do Aeroporto do Porto': '45 minutes from Porto Airport',
            '30 minutos de Viana do Castelo': '30 minutes from Viana do Castelo',
            '20 minutos de Braga': '20 minutes from Braga',
            'Coordenadas GPS disponíveis': 'GPS coordinates available',
            'Fácil acesso pela autoestrada A3': 'Easy access via A3 highway',

            // Contact Section
            'section-title-contact': 'Contact Us',
            'section-subtitle-contact': 'We are always available to help and answer your questions',
            'Informações de Contato': 'Contact Information',
            'Check-in: 15:00': 'Check-in: 3:00 PM',
            'Check-out: até 11:00': 'Check-out: until 11:00 AM',
            'Nome': 'Name',
            'Seu nome completo': 'Your full name',
            'Email': 'Email',
            'seuemail@gmail.com': 'youremail@gmail.com',
            'Assunto': 'Subject',
            'Resumo do assunto': 'Subject summary',
            'Mensagem': 'Message',
            'Escreva a sua mensagem aqui': 'Write your message here',
            'Enviar Mensagem': 'Send Message',
            'Enviando...': 'Sending...',

            // Chatbot
            'Assistente Virtual da Quinta Flores': 'Quinta Flores Virtual Assistant',
            'Olá! Bem-vindo à Quinta Flores. Como posso ajudá-lo hoje?': 'Hello! Welcome to Quinta Flores. How can I help you today?',
            'Digite sua mensagem...': 'Type your message...',
            'Reservas': 'Bookings',
            'Acomodações': 'Accommodations',
            'Serviços': 'Services',
            'Localização': 'Location',
            'Atividades': 'Activities',
            'Preços': 'Prices',
            'Quinta Flores - Sentindo-se em casa, no coração do Minho': 'Quinta Flores - Feeling at home in the heart of Minho',

            // Footer
            'A Quinta Flores oferece uma experiência única de alojamento local em Ponte de Lima, combinando tradição minhota com conforto moderno.':
            'Quinta Flores offers a unique local accommodation experience in Ponte de Lima, combining Minho tradition with modern comfort.',
            'Links Rápidos': 'Quick Links',
            '🏠 Início': '🏠 Home',
            'ℹ️ Acomodações': 'ℹ️ Accommodations',
            '🛋️ Experiências': '🛋️ Experiences',
            '🖼️ Galeria': '🖼️ Gallery',
            '⭐ Avaliações': '⭐ Reviews',
            '✉️ Contacto': '✉️ Contact',
            'Descobrir a Região': 'Discover the Region',
            'Trilhos e Natureza': 'Trails and Nature',
            'Sabores do Minho': 'Flavors of Minho',
            'Contactos': 'Contacts',
            'Contactar via WhatsApp': 'Contact via WhatsApp',
            'Ver no Google Maps': 'View on Google Maps',
            'Segue-nos no Instagram': 'Follow us on Instagram',
            'Copyright © 2025 QUINTA FLORES. Todos os direitos reservados.': 'Copyright © 2025 QUINTA FLORES. All rights reserved.'
        },
        'es': {
            // Hero Section
            'hero__title': 'Descubre Más Sobre Nuestro Alojamiento y la Región',
            'hero__subtitle': 'Explora los encantos de nuestro espacio y déjate sorprender por Ponte de Lima',
            'hero__cta': 'Reservar Ahora',

            // About Section
            'section-title-about': 'Nuestra Historia',
            'section-subtitle-about': 'Descubre la historia y los valores que hacen de Quinta Flores un destino especial en Ponte de Lima',
            'Nossa História': 'Nuestra Historia',
            'A Quinta Flores nasceu do sonho de criar um espaço que combinasse o charme rústico do Minho com o conforto moderno. Localizada em uma das regiões mais pitorescas de Portugal, nossa propriedade preserva a arquitetura tradicional portuguesa enquanto oferece todas as comodidades contemporâneas que você espera.':
            'Quinta Flores nació del sueño de crear un espacio que combinara el encanto rústico de Minho con la comodidad moderna. Ubicada en una de las regiones más pintorescas de Portugal, nuestra propiedad preserva la arquitectura tradicional portuguesa mientras ofrece todas las comodidades contemporáneas que esperas.',
            'Nossa Missão': 'Nuestra Misión',
            'Proporcionamos aos nossos hóspedes uma experiência autêntica do norte de Portugal, combinando hospitalidade calorosa com serviços de qualidade. Queremos que cada visitante se sinta em casa e leve consigo memórias inesquecíveis que durarão para sempre.':
            'Brindamos a nuestros huéspedes una experiencia auténtica del norte de Portugal, combinando hospitalidad cálida con servicios de calidad. Queremos que cada visitante se sienta como en casa y se lleve recuerdos inolvidables que durarán para siempre.',
            'Nossos Valores': 'Nuestros Valores',
            'Sustentabilidade, autenticidade e hospitalidade são os pilares que guiam nossa operação. Valorizamos as tradições locais e nos esforçamos para preservar o ambiente natural que nos rodeia, garantindo que as futuras gerações também possam desfrutar dessa beleza.':
            'Sostenibilidad, autenticidad y hospitalidad son los pilares que guían nuestra operación. Valoramos las tradiciones locales y nos esforzamos por preservar el entorno natural que nos rodea, asegurando que las futuras generaciones también puedan disfrutar de esta belleza.',

            // Accommodation Section
            'section-title-accommodation': 'Nuestros Alojamientos',
            'section-subtitle-accommodation': 'Comodidad y encanto en cada detalle, proporcionando una estancia inolvidable',
            'Quartos': 'Habitaciones',
            'Áreas Comuns': 'Áreas Comunes',
            'Suítes Especiais': 'Suites Especiales',
            'Quartos espaçosos com vista para o jardim': 'Habitaciones espaciosas con vista al jardín',
            'Ar condicionado e aquecimento': 'Aire acondicionado y calefacción',
            'Casa de banho privativa com amenities de qualidade': 'Baño privado con amenities de calidad',
            'Wi-Fi de alta velocidade': 'Wi-Fi de alta velocidad',
            'Decoração tradicional portuguesa com toques contemporâneos': 'Decoración tradicional portuguesa con toques contemporáneos',
            'Piscina ao ar livre com espreguiçadeiras': 'Piscina al aire libre con tumbonas',
            'Jardins exuberantes para relaxar': 'Jardines exuberantes para relajarse',
            'Área de churrasco totalmente equipada': 'Área de barbacoa totalmente equipada',
            'Espaço para camping': 'Espacio para acampar',
            'Estacionamento privado': 'Aparcamiento privado',
            'Terraço panorâmico com vista para as montanhas': 'Terraza panorámica con vistas a las montañas',
            'Suítes familiares espaçosas': 'Suites familiares espaciosas',
            'Unidades com cozinha equipada': 'Unidades con cocina equipada',
            'Decoração de luxo nas suítes': 'Decoración de lujo en las suites',
            'Opções de acomodação para grupos': 'Opciones de alojamiento para grupos',

            // Activities Section
            'section-title-activities': 'Experiencias & Actividades',
            'section-subtitle-activities': 'Descubre lo mejor de la región con nuestras experiencias cuidadosamente seleccionadas',
            'Experiência Gastronômica': 'Experiencia Gastronómica',
            'Desfrute de uma experiência gastronómica única no coração do Minho, com a oportunidade de saborear pratos tradicionais da região. Participe em workshops culinários e degustações de produtos típicos, incluindo os reconhecidos vinhos verdes, que irão enriquecer a sua estadia com os sabores autênticos do nosso território':
            'Disfrute de una experiencia gastronómica única en el corazón de Minho, con la oportunidad de saborear platos tradicionales de la región. Participe en talleres culinarios y degustaciones de productos típicos, incluidos los reconocidos vinos verdes, que enriquecerán su estancia con los sabores auténticos de nuestro territorio.',
            'Trilhas e Natureza': 'Senderismo y Naturaleza',
            'Explore as belas trilhas da região e conheça as maravilhas naturais do Minho. Descubra detalhes sobre a fauna, flora e a rica paisagem local, enquanto passeia por caminhos tranquilos e desfruta da serenidade da natureza.':
            'Explore los hermosos senderos de la región y conozca las maravillas naturales de Minho. Descubra detalles sobre la fauna, flora y el rico paisaje local mientras pasea por caminos tranquilos y disfruta de la serenidad de la naturaleza.',
            'Passeios Culturais': 'Visitas Culturales',
            'Descubra os monumentos históricos, as festas tradicionais e as feiras de artesanato que celebram a rica cultura de Ponte de Lima, oferecendo-lhe uma verdadeira imersão nas tradições locais e no património da região.':
            'Descubra los monumentos históricos, las fiestas tradicionales y las ferias de artesanía que celebran la rica cultura de Ponte de Lima, ofreciéndole una verdadera inmersión en las tradiciones locales y el patrimonio de la región.',
            'Explore mais': 'Explorar más',

            // Gallery Section
            'section-title-gallery': 'Nuestra Galería',
            'section-subtitle-gallery': 'Imágenes que capturan la esencia y belleza de Quinta Flores',
            'Entrada Principal': 'Entrada Principal',
            'Vista Panorâmica': 'Vista Panorámica',
            'Área de Churrasco': 'Área de Barbacoa',
            'Jardins Floridos': 'Jardines Floridos',
            'Piscina Exterior': 'Piscina Exterior',
            'Entrada Acolhedora para os Quartos': 'Entrada Acogedora a las Habitaciones',
            'Suite': 'Suite',
            'Casa de Banho (Suite)': 'Baño (Suite)',
            'Sala de Estar Aconchegante': 'Acogedor Salón',
            'Cozinha Totalmente Equipada': 'Cocina Totalmente Equipada',
            'Espaço Ajardinado com Esculturas': 'Espacio Ajardinado con Esculturas',
            'Pôr-do-Sol na Quinta': 'Atardecer en la Finca',

            // Testimonials Section
            'section-title-testimonials': 'Lo Que Dicen Nuestros Huéspedes',
            'section-subtitle-testimonials': 'Experiencias auténticas compartidas por quienes ya se han alojado en Quinta Flores',
            'Uma experiência incrível! A hospitalidade da equipe da Quinta Flores é incomparável. Os quartos são espaçosos e confortáveis, e as áreas comuns são perfeitas para relaxar. Voltaremos com certeza!':
            '¡Una experiencia increíble! La hospitalidad del equipo de Quinta Flores es incomparable. Las habitaciones son espaciosas y cómodas, y las áreas comunes son perfectas para relajarse. ¡Volveremos sin duda!',

            // FAQ Section
            'section-title-faq': 'Preguntas Frecuentes',
            'section-subtitle-faq': 'Respuestas a las dudas más comunes sobre Quinta Flores',
            'O que acontece em caso de danos na propriedade ou problemas durante a estadia?':
            '¿Qué sucede en caso de daños en la propiedad o problemas durante la estancia?',
            'Em caso de danos à propriedade durante a sua estadia, solicitamos que nos informe imediatamente para que possamos tomar as devidas providências. Dependendo da gravidade do dano, poderá ser cobrada uma taxa adicional para reparação. Caso haja qualquer problema durante a sua estadia, a nossa equipa está disponível 24 horas por dia para garantir que tudo seja resolvido rapidamente e com a máxima eficiência.':
            'En caso de daños a la propiedad durante su estancia, le pedimos que nos informe inmediatamente para que podamos tomar las medidas adecuadas. Dependiendo de la gravedad del daño, se podrá cobrar una tarifa adicional por reparación. Si hay algún problema durante su estancia, nuestro equipo está disponible las 24 horas del día para garantizar que todo se resuelva rápida y eficientemente.',
            'Qual é a política de cancelamento?': '¿Cuál es la política de cancelación?',
            'Para cancelar a sua reserva, é necessário ligar para o número +351 912 418 976 com 10 dias de antecedência. Caso a anulação seja feita dentro de um prazo inferior, será cobrado 50% do valor da reserva. Pedimos que esteja atento às condições e prazos para evitar custos adicionais.':
            'Para cancelar su reserva, debe llamar al número +351 912 418 976 con 10 días de antelación. Si la cancelación se realiza en un plazo menor, se cobrará el 50% del valor de la reserva. Le pedimos que esté atento a las condiciones y plazos para evitar costos adicionales.',
            'Aceitam animais de estimação?': '¿Aceptan mascotas?',
            'Aceitamos animais de porte pequeno apenas. Para garantir a sua estadia confortável e sem imprevistos, recomendamos que entre em contacto conosco com antecedência para confirmar a disponibilidade e as condições específicas para o seu animal.':
            'Aceptamos solo mascotas pequeñas. Para garantizar una estancia cómoda y sin sorpresas, le recomendamos que nos contacte con antelación para confirmar la disponibilidad y las condiciones específicas para su mascota.',

            // Location Section
            'section-title-location': 'Ubicación Privilegiada',
            'section-subtitle-location': 'Descubre por qué nuestra ubicación es perfecta para explorar lo mejor del norte de Portugal',
            'Pontos de Interesse Próximos': 'Puntos de Interés Cercanos',
            'Como Chegar': 'Cómo Llegar',
            'Centro histórico de Ponte de Lima (5 min)': 'Centro histórico de Ponte de Lima (5 min)',
            'Praia fluvial (10 min)': 'Playa fluvial (10 min)',
            'Ecovia do Lima para caminhadas e ciclismo': 'Ecovia do Lima para senderismo y ciclismo',
            'Área de Paisagem Protegida das Lagoas': 'Área de Paisaje Protegido de las Lagunas',
            'Festival Internacional de Jardins': 'Festival Internacional de Jardines',
            '45 minutos do Aeroporto do Porto': '45 minutos del Aeropuerto de Oporto',
            '30 minutos de Viana do Castelo': '30 minutos de Viana do Castelo',
            '20 minutos de Braga': '20 minutos de Braga',
            'Coordenadas GPS disponíveis': 'Coordenadas GPS disponibles',
            'Fácil acesso pela autoestrada A3': 'Fácil acceso por la autopista A3',

            // Contact Section
            'section-title-contact': 'Contacto',
            'section-subtitle-contact': 'Siempre estamos disponibles para ayudar y responder a sus preguntas',
            'Informações de Contato': 'Información de Contacto',
            'Check-in: 15:00': 'Check-in: 15:00',
            'Check-out: até 11:00': 'Check-out: hasta 11:00',
            'Nome': 'Nombre',
            'Seu nome completo': 'Su nombre completo',
            'Email': 'Correo electrónico',
            'seuemail@gmail.com': 'suemail@gmail.com',
            'Assunto': 'Asunto',
            'Resumo do assunto': 'Resumen del asunto',
            'Mensagem': 'Mensaje',
            'Escreva a sua mensagem aqui': 'Escriba su mensaje aquí',
            'Enviar Mensagem': 'Enviar Mensaje',
            'Enviando...': 'Enviando...',

            // Chatbot
            'Assistente Virtual da Quinta Flores': 'Asistente Virtual de Quinta Flores',
            'Olá! Bem-vindo à Quinta Flores. Como posso ajudá-lo hoje?': '¡Hola! Bienvenido a Quinta Flores. ¿Cómo puedo ayudarte hoy?',
            'Digite sua mensagem...': 'Escriba su mensaje...',
            'Reservas': 'Reservas',
            'Acomodações': 'Alojamientos',
            'Serviços': 'Servicios',
            'Localização': 'Ubicación',
            'Atividades': 'Actividades',
            'Preços': 'Precios',
            'Quinta Flores - Sentindo-se em casa, no coração do Minho': 'Quinta Flores - Sintiéndose en casa en el corazón de Minho',

            // Footer
            'A Quinta Flores oferece uma experiência única de alojamento local em Ponte de Lima, combinando tradição minhota com conforto moderno.':
            'Quinta Flores ofrece una experiencia única de alojamiento local en Ponte de Lima, combinando la tradición de Minho con la comodidad moderna.',
            'Links Rápidos': 'Enlaces Rápidos',
            '🏠 Início': '🏠 Inicio',
            'ℹ️ Acomodações': 'ℹ️ Alojamientos',
            '🛋️ Experiências': '🛋️ Experiencias',
            '🖼️ Galeria': '🖼️ Galería',
            '⭐ Avaliações': '⭐ Valoraciones',
            '✉️ Contacto': '✉️ Contacto',
            'Descobrir a Região': 'Descubrir la Región',
            'Trilhos e Natureza': 'Senderismo y Naturaleza',
            'Sabores do Minho': 'Sabores de Minho',
            'Contactos': 'Contactos',
            'Contactar via WhatsApp': 'Contactar por WhatsApp',
            'Ver no Google Maps': 'Ver en Google Maps',
            'Segue-nos no Instagram': 'Síguenos en Instagram',
            'Copyright © 2025 QUINTA FLORES. Todos os direitos reservados.': 'Copyright © 2025 QUINTA FLORES. Todos los derechos reservados.'
        },
        'fr': {
            // Hero Section
            'hero__title': 'Découvrez Plus Sur Notre Hébergement et la Région',
            'hero__subtitle': 'Explorez les charmes de notre espace et laissez-vous surprendre par Ponte de Lima',
            'hero__cta': 'Réserver Maintenant',

            // About Section
            'section-title-about': 'Notre Histoire',
            'section-subtitle-about': 'Découvrez l\'histoire et les valeurs qui font de Quinta Flores une destination spéciale à Ponte de Lima',
            'Nossa História': 'Notre Histoire',
            'A Quinta Flores nasceu do sonho de criar um espaço que combinasse o charme rústico do Minho com o conforto moderno. Localizada em uma das regiões mais pitorescas de Portugal, nossa propriedade preserva a arquitetura tradicional portuguesa enquanto oferece todas as comodidades contemporâneas que você espera.':
            'Quinta Flores est née du rêve de créer un espace alliant le charme rustique du Minho au confort moderne. Située dans l\'une des régions les plus pittoresques du Portugal, notre propriété préserve l\'architecture traditionnelle portugaise tout en offrant tout le confort contemporain que vous attendez.',
            'Nossa Missão': 'Notre Mission',
            'Proporcionamos aos nossos hóspedes uma experiência autêntica do norte de Portugal, combinando hospitalidade calorosa com serviços de qualidade. Queremos que cada visitante se sinta em casa e leve consigo memórias inesquecíveis que durarão para sempre.':
            'Nous offrons à nos clients une expérience authentique du nord du Portugal, alliant hospitalité chaleureuse et services de qualité. Nous voulons que chaque visiteur se sente comme chez lui et reparte avec des souvenirs inoubliables qui dureront toujours.',
            'Nossos Valores': 'Nos Valeurs',
            'Sustentabilidade, autenticidade e hospitalidade são os pilares que guiam nossa operação. Valorizamos as tradições locais e nos esforçamos para preservar o ambiente natural que nos rodeia, garantindo que as futuras gerações também possam desfrutar dessa beleza.':
            'Durabilité, authenticité et hospitalité sont les piliers qui guident notre fonctionnement. Nous valorisons les traditions locales et nous efforçons de préserver l\'environnement naturel qui nous entoure, en veillant à ce que les générations futures puissent également profiter de cette beauté.',

            // Accommodation Section
            'section-title-accommodation': 'Nos Hébergements',
            'section-subtitle-accommodation': 'Confort et charme dans chaque détail, pour un séjour inoubliable',
            'Quartos': 'Chambres',
            'Áreas Comuns': 'Espaces Communs',
            'Suítes Especiais': 'Suites Spéciales',
            'Quartos espaçosos com vista para o jardim': 'Chambres spacieuses avec vue sur le jardin',
            'Ar condicionado e aquecimento': 'Climatisation et chauffage',
            'Casa de banho privativa com amenities de qualidade': 'Salle de bain privée avec produits de qualité',
            'Wi-Fi de alta velocidade': 'Wi-Fi haut débit',
            'Decoração tradicional portuguesa com toques contemporâneos': 'Décoration traditionnelle portugaise avec touches contemporaines',
            'Piscina ao ar livre com espreguiçadeiras': 'Piscine extérieure avec transats',
            'Jardins exuberantes para relaxar': 'Jardins luxuriants pour se détendre',
            'Área de churrasco totalmente equipada': 'Zone barbecue entièrement équipée',
            'Espaço para camping': 'Espace pour camping',
            'Estacionamento privado': 'Parking privé',
            'Terraço panorâmico com vista para as montanhas': 'Terrasse panoramique avec vue sur les montagnes',
            'Suítes familiares espaçosas': 'Suites familiales spacieuses',
            'Unidades com cozinha equipada': 'Unités avec cuisine équipée',
            'Decoração de luxo nas suítes': 'Décoration de luxe dans les suites',
            'Opções de acomodação para grupos': 'Options d\'hébergement pour groupes',

            // Activities Section
            'section-title-activities': 'Expériences & Activités',
            'section-subtitle-activities': 'Découvrez le meilleur de la région avec nos expériences soigneusement sélectionnées',
            'Experiência Gastronômica': 'Expérience Gastronomique',
            'Desfrute de uma experiência gastronómica única no coração do Minho, com a oportunidade de saborear pratos tradicionais da região. Participe em workshops culinários e degustações de produtos típicos, incluindo os reconhecidos vinhos verdes, que irão enriquecer a sua estadia com os sabores autênticos do nosso território':
            'Profitez d\'une expérience gastronomique unique au cœur du Minho, avec l\'opportunité de déguster des plats traditionnels de la région. Participez à des ateliers culinaires et à