<?php
$translations = [
    'pt' => [
        'page_title' => 'Gastronomia de Ponte de Lima | Quinta Flores',
        'hero_title' => 'Gastronomia de Ponte de Lima',
        'hero_subtitle' => 'Capital do Sarrabulho e berço dos sabores autênticos do Alto Minho',
        'section_title' => 'Tesouros Gastronômicos Limianos',
        'section_subtitle' => 'Descubra os pratos que fazem de Ponte de Lima a capital gastronômica do Alto Minho',
        'wine_title' => 'Vinhos Verdes do Vale do Lima',
        'wine_content' => 'Ponte de Lima é coração da sub-região vitivinícola do Vale do Lima...',
        // Adicione todas as outras traduções para português
    ],
    'en' => [
        'page_title' => 'Gastronomy of Ponte de Lima | Quinta Flores',
        'hero_title' => 'Gastronomy of Ponte de Lima',
        'hero_subtitle' => 'Capital of Sarrabulho and cradle of authentic Alto Minho flavors',
        'section_title' => 'Gastronomic Treasures of Ponte de Lima',
        'section_subtitle' => 'Discover the dishes that make Ponte de Lima the gastronomic capital of Alto Minho',
        'wine_title' => 'Vinho Verde from Lima Valley',
        'wine_content' => 'Ponte de Lima is the heart of the Lima Valley wine sub-region...',
        // Adicione todas as outras traduções para inglês
    ],
    'es' => [
        'page_title' => 'Gastronomía de Ponte de Lima | Quinta Flores',
        'hero_title' => 'Gastronomía de Ponte de Lima',
        'hero_subtitle' => 'Capital del Sarrabulho y cuna de los sabores auténticos del Alto Minho',
        'section_title' => 'Tesoros Gastronómicos de Ponte de Lima',
        'section_subtitle' => 'Descubra los platos que hacen de Ponte de Lima la capital gastronómica del Alto Minho',
        'wine_title' => 'Vinos Verdes del Valle del Lima',
        'wine_content' => 'Ponte de Lima es el corazón de la subregión vitivinícola del Valle del Lima...',
        // Adicione todas as outras traduções para espanhol
    ],
    'fr' => [
        'page_title' => 'Gastronomie de Ponte de Lima | Quinta Flores',
        'hero_title' => 'Gastronomie de Ponte de Lima',
        'hero_subtitle' => 'Capitale du Sarrabulho et berceau des saveurs authentiques de l\'Alto Minho',
        'section_title' => 'Trésors Gastronomiques de Ponte de Lima',
        'section_subtitle' => 'Découvrez les plats qui font de Ponte de Lima la capitale gastronomique de l\'Alto Minho',
        'wine_title' => 'Vins Verts de la Vallée du Lima',
        'wine_content' => 'Ponte de Lima est au cœur de la sous-région viticole de la Vallée du Lima...',
        // Adicione todas as outras traduções para francês
    ]
];

// Idioma padrão
$default_lang = 'pt';

// Verifica se há um idioma selecionado
$current_lang = isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], $translations) ? $_COOKIE['lang'] : $default_lang;

// Função para obter tradução
function t($key) {
    global $current_lang, $translations;
    return $translations[$current_lang][$key] ?? $translations['pt'][$key] ?? $key;
}
?>