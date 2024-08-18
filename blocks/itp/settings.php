<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds admin settings for the plugin.
 *
 * @package     block_itp
 * @category    admin
 * @copyright   2020 Your Name <email@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $DB, $PAGE;

    //Crea una nueva categoría en la administración para el bloque
    $ADMIN->add('blocksettings', new admin_category('blocksettingitp', new lang_string('pluginname', 'block_itp')));

     //Crea una nueva página de configuración dentro de la categoria creada.
     $settingspageupload = new admin_settingpage('block_itp_upload', new lang_string('upload', 'block_itp'));

     //Comprueba si estamos mostrando todas las configuraciónes (arbol completo)
    if ($ADMIN->fulltree) {
        $list_of_customers = $DB->get_records('customer', [], 'name ASC', 'id, name');

        // Convertir el array de objetos en un array asociativo
        $customers_options = [];
        foreach ($list_of_customers as $customer) {
            $customers_options[$customer->id] = $customer->name;
        }

        //Si es 0, no hay proyectos seleccionados
        $customers_options[0]='No project selected';

        // Crear el select en la página de configuración
        $settingspageupload->add(new admin_setting_configselect('block_itp/selectcustomer',
            get_string('selectcustomer', 'block_itp'), get_string('selectcustomer_help', 'block_itp'),
             '0', $customers_options));
        
        $settingspageupload->add(new admin_setting_configstoredfile('block_itp/uploadfile',
            get_string('uploadfile', 'block_itp'), get_string('uploadfile_help', 'block_itp'),''));

        // Add radio options for CSV delimiter
        $delimiter_options = array(
            ',' => get_string('comma', 'block_itp'),
            "\t" => get_string('tab', 'block_itp'),
            ';' => get_string('semicolon', 'block_itp'),
            ':' => get_string('colon', 'block_itp'),
        );
        $settingspageupload->add(new admin_setting_configselect('block_itp/selectdelimiter',
            get_string('csvdelimiter', 'block_itp'), get_string('csvdelimiter_help', 'block_itp'),';',
            $delimiter_options));

        // Add options for sending email or not
        $email_options = array(
            'No' => get_string('No', 'block_itp'),
            'Yes' => get_string('Yes', 'block_itp'),
        );
        $settingspageupload->add(new admin_setting_configselect('block_itp/selectitpemail',
            get_string('selectitpemail', 'block_itp'), get_string('selectitpemail_help', 'block_itp'),'No',
            $email_options));

        $settingspageupload->add(new admin_setting_confightmleditor(
            'block_itp/tinymce_field',
            get_string('tinymce_field_label', 'block_itp'), // Título del campo
            get_string('tinymce_field_desc', 'block_itp'),  // Descripción del campo
            '',  // Valor por defecto
            PARAM_RAW,  // Tipo de parámetro, ya que aceptará contenido HTML
            '60',  // Ancho del textarea (en caracteres)
            '15'   // Alto del textarea (en líneas)
        ));

        
    }


    $ADMIN->add('blocksettingitp', $settingspageupload);


    // Procesa el archivo CSV al guardar la configuración
    if (optional_param('savechanges', false, PARAM_BOOL)) {
        // Obtén el archivo subido
        $context = context_system::instance();
        $itemid = 0; // Para usar con los archivos subidos
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'block_itp', 'uploadfile', $itemid, 'itemid, filepath, filename', false);

        foreach ($files as $file) {
            // Lee el contenido del archivo CSV
            $content = $file->get_content();
            $rows = explode("\n", $content);
            $delimiter = get_config('block_itp', 'selectdelimiter'); // Obtén el delimitador seleccionado

            foreach ($rows as $row) {
                $data = str_getcsv($row, $delimiter);
                if (!empty($data)) {
                    // Inserta los datos en la tabla de la base de datos
                    $recordtoinsert=new \stdClass();
                    $recordtoinsert->customerid=intval($data[0]);
                    $recordtoinsert->groupid=intval($data[1]);
                    $recordtoinsert->billid=$data[2];
                    $recordtoinsert->email=$data[3];
                    $recordtoinsert->startdate=$startdate->getTimestamp();
                    $recordtoinsert->enddate=$enddate->getTimestamp();
                    $recordtoinsert->course=$data[6];
                    $recordtoinsert->name=$data[7];
                    $recordtoinsert->duration=$data[8];
                    $recordtoinsert->location=$data[9];
                    $recordtoinsert->classroom=$data[10];
                    $recordtoinsert->schedule=$data[11];
                    $recordtoinsert->lastupdate=time();
                    // Agrega más campos según la estructura de tu tabla
                    $DB->insert_record('block_itp_csvdata', $recordtoinsert);
                }
            }
        }
    }
}