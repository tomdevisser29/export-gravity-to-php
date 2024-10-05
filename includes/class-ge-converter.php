<?php

/**
 * This class is used to convert forms to PHP code.
 * 
 * @package GravityExporter
 */
class GE_Converter {
    /**
     * Convert fields to PHP code.
     */
    public function convert_fields($fields) {
        $php_code = '';

        if (! empty($fields)) {
            $php_code .= "'fields' => array(\n";

            // Add fields to the generated PHP code, including all options
            foreach ($fields as $field) {
                $php_code .= "array(\n";
                $php_code .= "'type' => '" . addslashes($field->type) . "',\n";
                $php_code .= "'label' => __('" . addslashes($field->label) . "', '%textdomain%'),\n";
                $php_code .= "'id' => " . intval($field->id) . ",\n";
                $php_code .= "'isRequired' => " . ($field->isRequired ? 'true' : 'false') . ",\n";
                $php_code .= "'placeholder' => __('" . addslashes($field->placeholder) . "', '%textdomain%'),\n";
                $php_code .= "'defaultValue' => __('" . addslashes($field->defaultValue) . "', '%textdomain%'),\n";
                $php_code .= "'description' => __('" . addslashes($field->description) . "', '%textdomain%'),\n";
                $php_code .= "'adminLabel' => __('" . addslashes($field->adminLabel) . "', '%textdomain%'),\n";

                // Email Field Type
                if ($field->type == 'email' && isset($field->emailConfirmEnabled) && $field->emailConfirmEnabled) {
                    $php_code .= "'emailConfirmEnabled' => true,\n";
                }

                // Consent Field Type
                if ($field->type == 'consent') {
                    $php_code .= "'checkboxLabel' => __('" . addslashes($field->checkboxLabel) . "', '%textdomain%'),\n"; // Consent checkbox label
                }

                // Add choices if the field has options (like for checkboxes, radios)
                if (isset($field->choices) && ! empty($field->choices)) {
                    $php_code .= "'choices' => array(\n";
                    foreach ($field->choices as $choice) {
                        $php_code .= "array('text' => __('" . addslashes($choice['text']) . "', '%textdomain%'), 'value' => '" . addslashes($choice['value']) . "', 'isSelected' => " . ($choice['isSelected'] ? 'true' : 'false') . "),\n";
                    }
                    $php_code .= "),\n";
                }

                // Handle multi-input fields (like checkboxes, name, address)
                if (isset($field->inputs) && ! empty($field->inputs)) {
                    $php_code .= "'inputs' => array(\n";
                    foreach ($field->inputs as $input) {
                        $php_code .= "array('id' => '" . addslashes($input['id']) . "', 'label' => __('" . addslashes($input['label']) . "', '%textdomain%')),\n";
                    }
                    $php_code .= "),\n";
                }

                // Add conditional logic, if applicable
                if (isset($field->conditionalLogic)) {
                    $php_code .= "'conditionalLogic' => " . var_export($field->conditionalLogic, true) . ",\n";
                }

                $php_code .= "),\n";
            }

            $php_code .= "),\n";
        }

        return $php_code;
    }

    /**
     * Convert notifications to PHP code.
     */
    public function convert_notifications($notifications) {
        $php_code = '';

        if (! empty($notifications)) {
            $php_code .= "'notifications' => array(\n";

            foreach ($notifications as $notification) {
                $php_code .= "array(\n";

                if (! empty($notification['name'])) {
                    $php_code .= "'name' => __('" . addslashes($notification['name']) . "', '%textdomain%'),\n";
                }

                if (! empty($notification['to'])) {
                    $php_code .= "'to' => '" . addslashes($notification['to']) . "',\n";
                }

                if (! empty($notification['subject'])) {
                    $php_code .= "'subject' => __('" . addslashes($notification['subject']) . "', '%textdomain%'),\n";
                }

                if (! empty($notification['message'])) {
                    $php_code .= "'message' => __('" . addslashes($notification['message']) . "', '%textdomain%'),\n";
                }

                $php_code .= "),\n";
            }
            $php_code .= "),\n";
        }

        return $php_code;
    }

    /**
     * Convert confirmations to PHP code.
     */
    public function convert_confirmations($confirmations) {
        $php_code = '';

        if (! empty($confirmations)) {
            $php_code .= "'confirmations' => array(\n";
            foreach ($confirmations as $confirmation) {
                $php_code .= "array(\n";
                $php_code .= "'name' => __('" . addslashes($confirmation['name']) . "', '%textdomain%'),\n";
                $php_code .= "'type' => '" . addslashes($confirmation['type']) . "',\n";
                $php_code .= "'message' => __('" . addslashes($confirmation['message']) . "', '%textdomain%'),\n";
                $php_code .= "),\n";
            }
            $php_code .= "),\n";
        }

        return $php_code;
    }

    /**
     * Convert the submit button to PHP.
     */
    public function convert_submit_button($button) {
        $php_code = '';

        if (!empty($button)) {
            $php_code = "'button' => array(\n";

            if (! empty($button['text'])) {
                $php_code .= "'text' => __('" . addslashes($button['text']) . "', '%textdomain%'),\n";
            }

            if (! empty($button['conditionalLogic'])) {
                $php_code .= "'conditionalLogic' => " . (isset($button['conditionalLogic']) ? var_export($button['conditionalLogic'], true) : 'null') . ",\n";
            }

            $php_code .= "),\n";
        }

        return $php_code;
    }
}
