<?php
/**
 * Plugin Name: Encurtador de Links
 * Description: Integração com a API Kutt.it para encurtamento de URLs.
 * Version: 1.0
 * Author: Alan Frigo
 */

// Não permitir acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Hook para ativação do plugin
function kutt_it_activate() {
    add_option('kutt_it_api_url', '');
    add_option('kutt_it_api_key', '');
}
register_activation_hook(__FILE__, 'kutt_it_activate');

// Hook para desativação do plugin
function kutt_it_deactivate() {
    delete_option('kutt_it_api_url');
    delete_option('kutt_it_api_key');
}
register_deactivation_hook(__FILE__, 'kutt_it_deactivate');

// Inicializar o plugin e incluir arquivos
function kutt_it_init() {
    include_once(plugin_dir_path(__FILE__) . 'includes/kutt-it-settings.php');
    include_once(plugin_dir_path(__FILE__) . 'includes/kutt-it-functions.php');
    add_action('admin_menu', 'kutt_it_setup_menu');
    add_action('admin_init', 'kutt_it_register_settings');
}
add_action('plugins_loaded', 'kutt_it_init');

// Função para configurar o menu
function kutt_it_setup_menu() {
    // Adiciona o item de menu principal
    add_menu_page('Encurtador de Links', 'Encurtador de Links' , 'manage_options', 'kutt-it-main', 'kutt_it_shorten_page','dashicons-admin-links');

    // Adiciona submenu para configurações
    add_submenu_page('kutt-it-main', 'Configurações', 'Configurações', 'manage_options', 'kutt-it-settings', 'kutt_it_options_page');

}
?>
