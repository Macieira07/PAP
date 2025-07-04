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
            self::setLanguage($_SESSION['language']);
        } elseif (isset($_COOKIE['language'])) {
            self::setLanguage($_COOKIE['language']);
        } else {
            self::setLanguage('pt');
        }
    }
    private static function loadTranslations() {
        // Detecta o nome da página (sem .php)
        $page = basename($_SERVER['SCRIPT_NAME'], '.php');
        $langFile = __DIR__ . '/index/lang/' . self::$language . "_{$page}.php";
        if (file_exists($langFile)) {
            self::$translations = include $langFile;
        } else {
            // Fallback para português se o arquivo de idioma não existir
            $fallback = __DIR__ . '/index/lang/pt_' . $page . '.php';
            if (file_exists($fallback)) {
                self::$translations = include $fallback;
            } else {
                self::$translations = [];
            }
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
