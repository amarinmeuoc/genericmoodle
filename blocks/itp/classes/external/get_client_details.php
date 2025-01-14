<?php
namespace block_itp\external;

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_client_details extends \core_external\external_api {

    /**
     * Define parameters for the function.
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'shortname' => new external_value(PARAM_TEXT, 'Shortname of the customer', VALUE_REQUIRED),
        ]);
    }

    /**
     * Main function to fetch client details.
     * 
     * @param string $shortname The shortname of the client.
     * @return array Array containing client details.
     */
    public static function execute($shortname) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), ['shortname' => $shortname]);

        // Security checks.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        // Fetch the client details from the database.
        $client = $DB->get_record('customer', ['shortname' => strtoupper(trim($params['shortname']))], '*', MUST_EXIST);
        
        // Get file storage and prepare file URLs.
        $fs = get_file_storage();
        $filefields = ['logo', 'background', 'image'];
        $fileurls = [];

        foreach ($filefields as $field) {
            if (!empty($client->{$field})) {
                // Obtener el nombre del archivo desde la base de datos.
                $file_record = $DB->get_record('files', [
                    'id' => $client->{$field}, // ID del archivo almacenado en customer (268, 270, 272, etc.)
                ]);
                if ($file_record) {
                    $file = $fs->get_file(
                        $file_record->contextid,
                        $file_record->component,
                        $file_record->filearea,
                        $file_record->itemid,
                        $file_record->filepath,
                        $file_record->filename
                    );
                
                    if ($file) {
                        $fileurls[$field] = \moodle_url::make_pluginfile_url(
                            $file->get_contextid(),
                            $file->get_component(),
                            $file->get_filearea(),
                            $file->get_itemid(),
                            $file->get_filepath(),
                            $file->get_filename(),
                            false
                        )->out(false);
                    }
                } else {
                    $fileurls[$field] = null; // File not found.
                }
            } else {
                $fileurls[$field] = null; // Field is empty.
            }
        }

        // Return client details.
        return [
            'shortname' => $client->shortname,
            'name' => $client->name,
            'logo_url' => $fileurls['logo'] ?? '',
            'background_url' => $fileurls['background'] ?? '',
            'image_url' => $fileurls['image'] ?? ''
        ];
    }

    /**
     * Define return values for the function.
     */
    public static function execute_returns() {
        return new external_single_structure([
            'shortname' => new external_value(PARAM_TEXT, 'Shortname of the customer'),
            'name' => new external_value(PARAM_TEXT, 'Full name of the customer'),
            'logo_url' => new external_value(PARAM_URL, 'URL for the logo', VALUE_OPTIONAL),
            'background_url' => new external_value(PARAM_URL, 'URL for the background', VALUE_OPTIONAL),
            'image_url' => new external_value(PARAM_URL, 'URL for the image', VALUE_OPTIONAL),
        ]);
    }
}
