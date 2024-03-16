<?php
function kutt_it_add_admin_styles() {
    global $pagenow;
    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'kutt-it-main') {
    echo '<style>
    .kutt-it-wrap {
        max-width: 1200px;
        margin: 20px auto;
        display: flex;
        flex-wrap: wrap;
        gap: 20px; /* Espaçamento entre as colunas */
    }
    .kutt-it-section {
        flex: 1;
        min-width: 300px; /* Mínimo para as colunas encolherem */
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .kutt-it-wrap h2, .kutt-it-wrap h3 {
        color: #333;
        margin-bottom: 20px;
    }
    .kutt-it-wrap input[type="text"], .kutt-it-wrap input[type="submit"], .kutt-it-wrap select {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .kutt-it-wrap input[type="submit"] {
        background-color: #0073aa;
        color: white;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }
    .kutt-it-wrap input[type="submit"]:hover {
        background-color: #005a8c;
    }
    .wp-list-table {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
    }
    .wp-list-table th, .wp-list-table td {
        text-align: left;
        padding: 12px 15px;
    }
    .wp-list-table tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .wp-list-table th {
        background-color: #f1f1f1;
        color: #333;
        font-weight: bold;
    }
    </style>';
}
}


// Função para encurtar uma URL usando a API Kutt.it
function kutt_it_shorten_url($data) {
    $api_url = get_option('kutt_it_api_url') . '/links';
    $api_key = get_option('kutt_it_api_key');

    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'X-API-KEY' => $api_key,
            'Content-Type' => 'application/json'
        ),
        'body' => json_encode($data),
        'method' => 'POST',
        'data_format' => 'body'
    ));

    if (is_wp_error($response)) {
        return $response->get_error_message();
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['link'] ?? 'Erro: Não foi possível encurtar a URL.';
    }
}

// Função para recuperar URLs encurtadas
function kutt_it_get_shortened_urls() {
    $api_url = get_option('kutt_it_api_url') . '/links';
    $api_key = get_option('kutt_it_api_key');

    $response = wp_remote_get($api_url, array(
        'headers' => array('X-API-KEY' => $api_key)
    ));

    if (is_wp_error($response)) {
        return $response->get_error_message();
    } else {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        return $body['data'] ?? 'Erro: Não foi possível recuperar as URLs.';
    }
}

// Função para renderizar a página de encurtamento de URL com opções UTM integradas




function kutt_it_shorten_page() {
    $output = '<div class="kutt-it-wrap">';

    // Coluna de Encurtamento de URL
    $output .= '<div class="kutt-it-section">';
    $output .= '<h2>Encurtar URL</h2>';
    $output .= '<form action="" method="post">';
    $output .= '<label for="kutt_url">URL Original:</label>';
    $output .= '<input type="text" name="kutt_url"><br>';
    $output .= '<label for="kutt_custom">URL Personalizada (opcional):</label>';
    $output .= '<input type="text" name="kutt_custom"><br>';
    $output .= '<label for="kutt_description">Descrição do Link (opcional):</label>';
    $output .= '<input type="text" name="kutt_description"><br>';
    $output .= '<input type="submit" name="shorten_url" value="Encurtar URL" class="button button-primary">';
    $output .= '</div>';


    // Campos UTM opcionais
    $output .= '<div class="kutt-it-section">';
    $output .= '<h3>Parâmetros UTM (opcional)</h3>';
    $output .= '<label for="utm_source">UTM Source:</label>';
    $output .= '<input type="text" name="utm_source"><br>';
    $output .= '<label for="utm_medium">UTM Medium:</label>';
    $output .= '<input type="text" name="utm_medium"><br>';
    $output .= '<label for="utm_campaign">UTM Campaign:</label>';
    $output .= '<input type="text" name="utm_campaign"><br>';
    $output .= '<label for="utm_term">UTM Term (opcional):</label>';
    $output .= '<input type="text" name="utm_term"><br>';
    $output .= '<label for="utm_content">UTM Content (opcional):</label>';
    $output .= '<input type="text" name="utm_content"><br>';
    $output .= '</form>';
    $output .= '</div>';
    $output .= '</div>'; // Fecha .kutt-it-wrap

    // Lógica para construir e encurtar a URL com parâmetros UTM se necessário
    if (isset($_POST['shorten_url'])) {
        $base_url = sanitize_text_field($_POST['kutt_url']);
        
// Inicialize um array para os parâmetros UTM
$utm_params = [];

// Verifique cada chave UTM antes de usá-la
$utm_keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
foreach ($utm_keys as $key) {
    if (!empty($_POST[$key])) {
        $utm_params[$key] = sanitize_text_field($_POST[$key]);
    }
}

$built_url = add_query_arg(array_filter($utm_params), $base_url);
        
        $url_data = [
            'target' => $built_url,
            'description' => isset($_POST['kutt_description']) ? sanitize_text_field($_POST['kutt_description']) : ''
        ];
        
        if (!empty($_POST['kutt_custom'])) {
            $url_data['customurl'] = sanitize_text_field($_POST['kutt_custom']);
        }
    
        $shortened_url = kutt_it_shorten_url($url_data);
        $output .= '<p>URL Final: <a href="' . esc_url($shortened_url) . '" target="_blank">' . esc_html($shortened_url) . '</a></p>';
    }
    

    // Exibir URLs encurtadas (ajuste conforme necessário)
    $output .= '<div class="kutt-it-section">';
    $output .= kutt_it_display_shortened_urls();
    $output .= '</div>';

    echo $output;
}

// Função separada para exibir URLs encurtadas
function kutt_it_display_shortened_urls() {
    $output = '<h3>URLs Encurtadas:</h3>';
    $shortened_urls = kutt_it_get_shortened_urls();
    if (is_array($shortened_urls)) {
        $output .= '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Original URL</th><th>Encurtada URL</th><th>Descrição</th><th>Ações</th></tr></thead><tbody>';
        foreach ($shortened_urls as $url) {
            $output .= '<tr>';
            $output .= '<td>' . esc_html($url['target']) . '</td>';
            $output .= '<td><a href="' . esc_url($url['link']) . '" target="_blank">' . esc_html($url['link']) . '</a></td>';
            $output .= '<td>' . esc_html($url['description'] ?? 'Sem descrição') . '</td>';
            // Adicionando nonce ao link de deleção
            $delete_nonce = wp_create_nonce('kutt_it_delete_url_' . $url['id']);
            $delete_link = add_query_arg(['action' => 'delete', 'url_id' => $url['id'], '_wpnonce' => $delete_nonce], admin_url('admin.php?page=kutt-it-main'));
            $output .= '<td><a href="'. esc_url($delete_link) .'" onclick="return confirm(\'Tem certeza que deseja deletar esta URL?\')">Deletar</a></td>';

            $output .= '</tr>';
        }
        $output .= '</tbody></table>';
    } else {
        $output .= '<p>' . esc_html($shortened_urls) . '</p>';
    }
    return $output;
}

// Verificando ação de deleção e nonce
if (isset($_GET['action'], $_GET['url_id'], $_GET['_wpnonce']) && $_GET['action'] == 'delete' && current_user_can('manage_options')) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'kutt_it_delete_url_' . $_GET['url_id'])) {
        kutt_it_delete_url(sanitize_text_field($_GET['url_id']));
    } else {
        // Tratamento em caso de falha na verificação do nonce
        wp_die('Ação não permitida ou sessão expirada.');
    }
}

// Função kutt_it_delete_url com redirecionamento ajustado
function kutt_it_delete_url($id) {
    $api_url = get_option('kutt_it_api_url') . "/links/$id";
    $api_key = get_option('kutt_it_api_key');

    $response = wp_remote_request($api_url, array(
        'method'    => 'DELETE',
        'headers'   => array('X-API-KEY' => $api_key),
        'data_format' => 'body'
    ));

    if (is_wp_error($response)) {
        // Tratamento do erro
        $error_message = $response->get_error_message();
        add_action('admin_notices', function() use ($error_message) { 
            echo "<div class='notice notice-error'><p>Error: $error_message</p></div>"; 
        });
    } else {
        // Redirecionar para a página do plugin após deleção bem-sucedida
        wp_redirect(admin_url('admin.php?page=kutt-it-main'));
        exit;
    }
}


// Verifica se há alguma ação de deletar sendo solicitada
if (isset($_GET['delete']) && current_user_can('manage_options')) {
    kutt_it_delete_url(sanitize_text_field($_GET['delete']));
}
add_action('admin_head', 'kutt_it_add_admin_styles');
