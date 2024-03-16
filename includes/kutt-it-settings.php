<?php

function kutt_it_register_settings() {
    register_setting('kutt_it_options_group', 'kutt_it_api_url');
    register_setting('kutt_it_options_group', 'kutt_it_api_key');
}
add_action('admin_init', 'kutt_it_register_settings');

// Função para verificar a conexão com a API
function kutt_it_check_api_connection() {
    $api_url = get_option('kutt_it_api_url') . '/health';
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return false;
    } else {
        $body = wp_remote_retrieve_body($response);
        return $body === 'OK';
    }
}

function kutt_it_options_page() {
    $api_connected = kutt_it_check_api_connection();
?>
    <div>
    <h2>Configurações do Kutt.it</h2>
    <form method="post" action="options.php">
    <?php settings_fields('kutt_it_options_group'); ?>
    <table>
    <tr valign="top">
    <th scope="row"><label for="kutt_it_api_url">URL da API</label></th>
    <td><input type="text" id="kutt_it_api_url" name="kutt_it_api_url" value="<?php echo get_option('kutt_it_api_url'); ?>" /></td>
    </tr>
    <tr valign="top">
    <th scope="row"><label for="kutt_it_api_key">API Key</label></th>
    <td><input type="text" id="kutt_it_api_key" name="kutt_it_api_key" value="<?php echo get_option('kutt_it_api_key'); ?>" /></td>
    </tr>
    </table>
    <p>Status da API: <?php echo $api_connected ? '<strong>Conectado</strong>' : '<strong>Desconectado</strong>'; ?></p>
    <?php submit_button(); ?>
    </form>
    </div>
<?php
}
?>
