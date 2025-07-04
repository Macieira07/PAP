<?php
// i18n.php
class I18n {
    private static $language = 'pt';
    private static $translations = [];
    public static function init() {
        if (!isset($_SESSION)) {
            session_start();
        } 
        if (isset($_SESSION['language'])) {
            self::$language = $_SESSION['language'];
        } elseif (isset($_COOKIE['language'])) {
            self::$language = $_COOKIE['language'];
        }
        // Carrega as traduções para o idioma selecionado
        self::loadTranslations();
    }
    private static function loadTranslations() {
        $langFile = __DIR__ . '/lang/' . self::$language . '.php';
        if (file_exists($langFile)) {
            self::$translations = include $langFile;
        } else {
            // Fallback para português se o arquivo de idioma não existir
            self::$translations = include __DIR__ . 'lang/pt.php';
        }
    }
    public static function setLanguage($lang) {
        self::$language = $lang;
        $_SESSION['language'] = $lang;
        setcookie('language', $lang, time() + (86400 * 30), "/"); // 30 dias
        self::loadTranslations();
    }
    public static function get($key, $default = '') {
        return self::$translations[$key] ?? $default;
    }
    public static function getCurrentLanguage() {
        return self::$language;
    }
}
// Inicializa o sistema de internacionalização
I18n::init();
?>