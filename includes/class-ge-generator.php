<?php

/**
 * This class is used to generate the PHP code, using the converter.
 * 
 * @package GravityExporter
 */
class GE_Generator {
    public $form;
    private $converter;

    /**
     * Create a new Converter, and save the form you need to generate code for.
     */
    public function __construct($form) {
        $this->form = $form;
        $this->converter = new GE_Converter();
    }

    /**
     * Function to generate PHP code from the Gravity Form object
     */
    public function generate_php_code_for_form() {
        $php_code = "\$form = array(\n";

        if (! empty($this->form['title'])) {
            $php_code .= "'title' => __('" . addslashes($this->form['title']) . "', '%textdomain%'),\n";
        }

        if (! empty($this->form['description'])) {
            $php_code .= "'description' => __('" . addslashes($this->form['description']) . "', '%textdomain%'),\n";
        }

        // Process Fields
        if (! empty($this->form['fields'])) {
            $php_code .= $this->converter->convert_fields($this->form['fields']);
        }

        // Process Submit Button
        if (! empty($this->form['button'])) {
            $php_code .= $this->converter->convert_submit_button($this->form['button']);
        }

        // Process Notifications
        if (! empty($this->form['notifications'])) {
            $php_code .= $this->converter->convert_notifications($this->form['notifications']);
        }

        // Process Confirmations
        if (! empty($this->form['confirmations'])) {
            $php_code .= $this->converter->convert_confirmations($this->form['confirmations']);
        }

        $php_code .= ");\n\n";
        $php_code .= "GFAPI::add_form(\$form);\n";

        return $this->apply_indentation($php_code);
    }

    /**
     * Format the PHP code by indenting lines when finding parentheses or braces.
     */
    private function apply_indentation($code, $indentation = 2) {
        $lines = explode("\n", $code);
        $indented_code = '';
        $indent_level = 0;

        foreach ($lines as $line) {
            // Check for closing parentheses or braces to decrease indent level
            if (preg_match('/^\s*[\)\}]/', $line)) {
                $indent_level--;
            }

            // Apply indentation
            $indented_code .= str_repeat(' ', $indentation * $indent_level) . $line . "\n";

            // Check for opening parentheses or braces to increase indent level
            if (preg_match('/[\(\{]\s*$/', $line)) {
                $indent_level++;
            }
        }

        return rtrim($indented_code);
    }
}
