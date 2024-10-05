<?php

/**
 * This file adds the submenu page to the backend with the Gravity Forms page as its parent.
 * 
 * @package GravityExporter
 */

/**
 * Register the submenu page.
 */
function ge_menu_page() {
    add_submenu_page(
        'gf_edit_forms',
        __('Gravity Exporter', 'ge'),
        __('Gravity Exporter', 'ge'),
        'manage_options',
        'gravity-exporter',
        'ge_menu_page_markup',
    );
}
add_action('admin_menu', 'ge_menu_page', 20);

/**
 * The markup for the submenu page.
 */
function ge_menu_page_markup() {
    if (! class_exists('GFAPI')) {
        echo '<div class="error"><p>' . __('Gravity Forms is not activated. Please install and activate Gravity Forms to use this plugin.', 'ge') . '</p></div>';
        return;
    }

    if (isset($_POST['gf_form_id']) && ! empty($_POST['gf_form_id'])) {
        $form_id = intval($_POST['gf_form_id']);
        $form = GFAPI::get_form($form_id);

        $generator = new GE_Generator($form);

        if (! $form) {
            echo '<div class="error"><p>' . __('Form not found.', 'ge') . '</p></div>';
        } else {
            // Display the PHP code for the selected form
            echo '<h2>' . __('PHP Code for Form:', 'ge') . ' ' . esc_html($form['title']) . '</h2>';
            echo '<div class="code-wrapper">';
            echo '<button class="copy-button button-primary" onclick="copyToClipboard()">' . __('Copy the code', 'ge') . '</button>';
            echo '<code id="php-code">' . esc_html($generator->generate_php_code_for_form()) . '</code>';
            echo '</div>';
        }
    } else {
        // Display form selection
        echo '<h2>' . __('Gravity Exporter', 'ge') . '</h2>';
        echo '<form method="post">';
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="gf_form_id">' . __('Form', 'ge') . '</label></th>';
        echo '<td>';

        $forms = GFAPI::get_forms();
        if (!empty($forms)) {
            echo '<select name="gf_form_id" id="gf_form_id">';
            foreach ($forms as $form) {
                echo '<option value="' . esc_attr($form['id']) . '">' . esc_html($form['title']) . '</option>';
            }
            echo '</select>';
            echo '<p class="description">' . __('Please select a form from the dropdown to generate the corresponding PHP code for that form.', 'ge') . '</p>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            echo '<p class="submit">';
            echo '<input type="submit" class="button-primary" value="' . __('Generate PHP Code', 'ge') . '">';
            echo '</p>';
        } else {
            echo '<p>' . __('No forms found.', 'ge') . '</p>';
        }

        echo '</form>';
    }
}

/**
 * Add custom styles for the code display.
 */
function ge_php_exporter_styles() {
    echo '
    <style>
        .code-wrapper {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            overflow-x: auto;
            font-family: monospace;
            white-space: pre;
            border-radius: 4px;
            margin-right: 20px;
            position: relative;
        }
        .code-wrapper code {
            background: transparent;
            font-family: monospace;
            font-size: 14px;
        }
        .code-wrapper .copy-button {
            position: absolute;
            right: 1rem;
            top: 1rem;
        }
    </style>
    ';
}
add_action('admin_head', 'ge_php_exporter_styles');

// Add the JavaScript for copying the code to clipboard
function ge_php_exporter_scripts() {
    echo '
    <script type="text/javascript">
        function copyToClipboard() {
            const code = document.getElementById("php-code").innerText;
            const tempInput = document.createElement("textarea");
            tempInput.value = code;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            alert("' . __('Code copied to your clipboard!', 'ge') .  '");
        }
    </script>
    ';
}
add_action('admin_footer', 'ge_php_exporter_scripts');
