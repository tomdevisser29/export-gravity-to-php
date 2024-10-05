<?php
add_action('admin_menu', 'grav_menu_page', 20);
function grav_menu_page() {
    add_submenu_page(
        'gf_edit_forms',
        'Gravity Exporter',
        'Gravity Exporter',
        'manage_options',
        'gravity-exporter',
        'grav_menu_page_markup',
    );
}

function grav_menu_page_markup() {
    // Check if Gravity Forms is active
    if (! class_exists('GFForms')) {
        echo '<div class="error"><p>Gravity Forms is not activated. Please install and activate Gravity Forms to use this plugin.</p></div>';
        return;
    }

    // Check if a form has been selected
    if (isset($_POST['gf_form_id']) && ! empty($_POST['gf_form_id'])) {
        $form_id = intval($_POST['gf_form_id']);
        $form = GFAPI::get_form($form_id);

        if (! $form) {
            echo '<div class="error"><p>Form not found.</p></div>';
        } else {
            // Display the PHP code for the selected form
            echo '<h2>PHP Code for Form: ' . esc_html($form['title']) . '</h2>';
            echo '<div class="code-wrapper"><code>' . esc_html(generate_php_code_for_form($form)) . '</code></div>';
        }
    } else {
        // Display form selection
        echo '<h2>Select a Form to Export as PHP Code</h2>';
        echo '<form method="post">';
        echo '<label for="gf_form_id">Choose a form:</label><br>';

        $forms = GFAPI::get_forms();
        if (! empty($forms)) {
            echo '<select name="gf_form_id" id="gf_form_id">';
            foreach ($forms as $form) {
                echo '<option value="' . esc_attr($form['id']) . '">' . esc_html($form['title']) . '</option>';
            }
            echo '</select>';
            echo '<br><br>';
            echo '<input type="submit" class="button-primary" value="Generate PHP Code">';
        } else {
            echo '<p>No forms found.</p>';
        }

        echo '</form>';
    }
}

// Add custom styles for the code display
add_action('admin_head', 'gf_php_exporter_styles');
function gf_php_exporter_styles() {
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
        }
        .code-wrapper code {
            background: transparent;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
    ';
}

// Function to generate PHP code from the Gravity Form object
function generate_php_code_for_form($form) {
    $php_code  = "<?php\n";
    $php_code .= "add_action('gform_loaded', 'create_custom_form', 5);\n";
    $php_code .= "function create_custom_form() {\n";
    $php_code .= "  if (class_exists('GFForms')) {\n";
    $php_code .= "    \$form = array(\n";
    $php_code .= "      'title' => '" . esc_js($form['title']) . "',\n";
    $php_code .= "      'description' => '" . esc_js($form['description']) . "',\n";
    $php_code .= "      'fields' => array(\n";

    // Add fields to the generated PHP code, including all options
    foreach ($form['fields'] as $field) {
        $php_code .= "        array(\n";
        $php_code .= "          'type' => '" . esc_js($field->type) . "',\n";
        $php_code .= "          'label' => '" . esc_js($field->label) . "',\n";
        $php_code .= "          'id' => " . intval($field->id) . ",\n";
        $php_code .= "          'isRequired' => " . ($field->isRequired ? 'true' : 'false') . ",\n";
        $php_code .= "          'placeholder' => '" . esc_js($field->placeholder) . "',\n";
        $php_code .= "          'defaultValue' => '" . esc_js($field->defaultValue) . "',\n";
        $php_code .= "          'description' => '" . esc_js($field->description) . "',\n";
        $php_code .= "          'adminLabel' => '" . esc_js($field->adminLabel) . "',\n";

        // Handle specific settings for the email field
        if ($field->type == 'email' && isset($field->emailConfirmEnabled) && $field->emailConfirmEnabled) {
            $php_code .= "          'emailConfirmEnabled' => true,\n";
        }

        // Handle consent fields with checkbox label
        if ($field->type == 'consent') {
            $php_code .= "          'checkboxLabel' => '" . esc_js($field->checkboxLabel) . "',\n"; // Consent checkbox label
        }

        // Add choices if the field has options (like for checkboxes, radios)
        if (isset($field->choices) && ! empty($field->choices)) {
            $php_code .= "          'choices' => array(\n";
            foreach ($field->choices as $choice) {
                $php_code .= "            array('text' => '" . esc_js($choice['text']) . "', 'value' => '" . esc_js($choice['value']) . "', 'isSelected' => " . ($choice['isSelected'] ? 'true' : 'false') . "),\n";
            }
            $php_code .= "          ),\n";
        }

        // Handle multi-input fields (like checkboxes, name, address)
        if (isset($field->inputs) && ! empty($field->inputs)) {
            $php_code .= "          'inputs' => array(\n";
            foreach ($field->inputs as $input) {
                $php_code .= "            array('id' => '" . esc_js($input['id']) . "', 'label' => '" . esc_js($input['label']) . "'),\n";
            }
            $php_code .= "          ),\n";
        }

        // Add conditional logic, if applicable
        if (isset($field->conditionalLogic)) {
            $php_code .= "          'conditionalLogic' => " . var_export($field->conditionalLogic, true) . ",\n";
        }

        $php_code .= "        ),\n";
    }

    $php_code .= "      ),\n";

    // Add submit button
    $php_code .= "      'button' => array(\n";
    $php_code .= "        'text' => '" . esc_js($form['button']['text']) . "',\n";
    $php_code .= "        'conditionalLogic' => " . (isset($form['button']['conditionalLogic']) ? var_export($form['button']['conditionalLogic'], true) : 'null') . ",\n";
    $php_code .= "      ),\n";

    // Add notifications to the generated PHP code
    if (isset($form['notifications']) && ! empty($form['notifications'])) {
        $php_code .= "      'notifications' => array(\n";
        foreach ($form['notifications'] as $notification) {
            $php_code .= "        array(\n";
            $php_code .= "          'name' => '" . esc_js($notification['name']) . "',\n";
            $php_code .= "          'to' => '" . esc_js($notification['to']) . "',\n";
            $php_code .= "          'subject' => '" . esc_js($notification['subject']) . "',\n";
            $php_code .= "          'message' => '" . esc_js($notification['message']) . "',\n";
            $php_code .= "        ),\n";
        }
        $php_code .= "      ),\n";
    }

    // Add confirmations to the generated PHP code
    if (isset($form['confirmations']) && ! empty($form['confirmations'])) {
        $php_code .= "      'confirmations' => array(\n";
        foreach ($form['confirmations'] as $confirmation) {
            $php_code .= "        array(\n";
            $php_code .= "          'name' => '" . esc_js($confirmation['name']) . "',\n";
            $php_code .= "          'type' => '" . esc_js($confirmation['type']) . "',\n";
            $php_code .= "          'message' => '" . esc_js($confirmation['message']) . "',\n";
            $php_code .= "        ),\n";
        }
        $php_code .= "      ),\n";
    }

    $php_code .= "    );\n";
    $php_code .= "    GFAPI::add_form(\$form);\n";
    $php_code .= "  }\n";
    $php_code .= "}";

    return $php_code;
}
