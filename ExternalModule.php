<?php
/**
 * @file
 * Provides ExternalModule class for REDCap Web Services.
 */

namespace REDCapWebServices\ExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * ExternalModule class for REDCap Web Services.
 */
class ExternalModule extends AbstractExternalModule {

    /**
     * @inheritdoc.
     */
    function hook_every_page_top($project_id) {
        if (strpos(PAGE, 'ExternalModules/manager/control_center.php') !== false) {
            $this->setJsSettings(array('modulePrefix' => $this->PREFIX));
            $this->includeJs('js/config.js');
        }
    }

    /**
     * Formats settings into a hierarchical key-value pair array.
     *
     * @param int $project_id
     *   Enter a project ID to get project settings.
     *   Leave blank to get system settings.
     *
     * @return array $formmated
     *   The formatted settings.
     */
    function getFormattedSettings($project_id = null) {
        $config = $this->getConfig();

        if ($project_id) {
            $type = 'project';
            $settings = ExternalModules::getProjectSettingsAsArray($this->PREFIX, $project_id);
        }
        else {
            $type = 'system';
            $settings = ExternalModules::getSystemSettingsAsArray($this->PREFIX);
        }

        $formatted = array();
        foreach ($config[$type . '-settings'] as $field) {
            $key = $field['key'];

            if ($field['type'] == 'sub_settings') {
                // Handling sub settings.
                $formatted[$key] = array();

                if ($field['repeatable']) {
                    // Handling repeating sub settings.
                    foreach (array_keys($settings[$key]['value']) as $delta) {
                        foreach ($field['sub_settings'] as $sub_setting) {
                            $sub_key = $sub_setting['key'];
                            $formatted[$key][$delta][$sub_key] = $settings[$sub_key]['value'][$delta];
                        }
                    }
                }
                else {
                    foreach ($field['sub_settings'] as $sub_setting) {
                        $sub_key = $sub_setting['key'];
                        $formatted[$key][$sub_key] = reset($settings[$sub_key]['value']);
                    }
                }
            }
            else {
                $formatted[$key] = $settings[$key]['value'];
            }
        }

        return $formatted;
    }

    /**
     * Prints an error JSON on the screen.
     *
     * @param string $msg
     *   The error message.
     */
    function returnErrorResponse($msg) {
        echo json_encode(array('success' => false, 'error_msg' => $msg));
        exit;
    }

    /**
     * Includes a local JS file.
     *
     * @param string $path
     *   The relative path to the js file.
     */
    protected function includeJs($path) {
        echo '<script src="' . $this->getUrl($path) . '"></script>';
    }

    /**
     * Sets JS settings.
     *
     * @param mixed $settings
     *   The settings to be available on JS.
     */
    protected function setJsSettings($settings) {
        echo '<script>REDCapWebServices = ' . json_encode($settings) . ';</script>';
    }
}
