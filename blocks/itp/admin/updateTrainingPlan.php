<?php

// This file is part of Moodle - http://moodle.org/
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
 * MOODLE VERSION INFORMATION
 *
 * This file defines the current version of the local_createcustomer plugin code being used.
 * This is compared against the values stored in the database to determine
 * whether upgrades should be performed (see lib/db/*.php)
 *
 * @package    local_createcustomer
 * @copyright  2024 Alberto Marín Mendoza (http://myhappycoding.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 require_once('../../../config.php');
 require_once($CFG->libdir.'/adminlib.php');
 
 global $PAGE;

 require_login();

 // Configura la página para que se mantenga en el contexto de administración.
 admin_externalpage_setup('block_itp_updateTrainingPlan');

 //Url del sitio
 $pageurl = $PAGE->url;

 //Parametros para recuperar el estado de una operación 
 $type=optional_param('type','',PARAM_TEXT);
 $message=optional_param('message','',PARAM_TEXT);
 

 $context=context_system::instance();
 if (!has_capability('moodle/site:config',$context)) {
    echo $OUTPUT->header();
    $message=get_string('error','block_itp');
    \core\notification::error($message);
    echo $OUTPUT->footer();
    return;
 }
 
 //Formulario
 $mform=new \block_itp\form\updateTrainingPlanform();
 
 echo $OUTPUT->header();

 echo $OUTPUT->heading(get_string('updateTrainingPlan', 'block_itp'));

 $toform='';

 // Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // If there is a cancel element on the form, and it was pressed,
    // then the `is_cancelled()` function will return true.
    // You can handle the cancel operation here.
} else if ($fromform = $mform->get_data()) {
    global $DB;

    $customerid=$fromform->tecustomer;

    // Ahora debería capturar correctamente el valor de `tegroup`
    $groupid = isset($fromform->tegroup) ? intval($fromform->tegroup) : 0;

    $separator=$fromform->selectdelimiter;
    $csv_content=$mform->get_file_content('csv_file');

    $lines = explode(PHP_EOL, $csv_content);
    $header = null;
    $data = [];

    $inserted_count = 0; 

    $params[]=array('type' => 'ok', 'message' => 'Todo bien.');

    $cont=2;

    //Antes de borrar la tabla, se verifica si el archivo CSV está vacío
    if ($lines[0]==="" || empty($lines)) {
        $params[]=array(
            'type' => 'error',
            'message' => "Operación cancelada: El archivo CSV está vacío."
        );
        $url = new \moodle_url($pageurl, $params[1]);
        redirect($url);
    }
    
    //Antes de insertar en la base de datos, se borra el cliente seleccionado
    if ($groupid===0)
        $result=$DB->delete_records('trainingplan', array('customerid'=>$customerid));
    else {
        $result=$DB->delete_records('trainingplan', array('customerid'=>$customerid, 'groupid'=>$groupid));
    }
    

    foreach ($lines as $line) {

        // Descarta las líneas en blanco y clientes diferentes al seleccionado
        if (empty(trim($line))) {
            continue;
            $cont++;
        }

        // Usar el separador seleccionado
        $line = str_getcsv($line, $separator);

        // Validar el número de columnas
        if (count($line) !== 10) {
            
            $params[]=array(
                'type' => 'error',
                'message' => "Operación cancelada: Formato de archivo erroneo: Utima fila procesada: '$cont'"
            );
            break;
        }

        if (!$header) {
            $header = $line; // Asume que la primera fila es el encabezado
        } else {
            $data = array_combine($header, $line);
            
            //Se eliminan BOM de las claves
            $data = removeBomKeys($data);

            // Convertir $groupid y $data['group'] a enteros
            $groupid = intval($groupid);
            $data['group'] = intval($data['group']);

            //Descarta las lineas que no correspondan al grupo seleccionado
            if ($groupid!==0 && $data['group']!==$groupid) {
                continue;
                $cont++;
            }

            // Verificar si `customerid` no es nulo o no coincide con el cliente seleccionado.
            if (empty($data['customer']) || $data['customer']!==$customerid) {
                
                
                var_dump($data['customer']);
            die;
                $cont++;
                continue;
            }

            // Verificar si el grupo existe o no es nulo
            if (empty($data['group']) || !grupoExiste($customerid,$data['group'])) {
                
                $params[]=array(
                    'type' => 'error',
                    'message' => "Operación cancelada: Asegurate de que el campo groupid exista y no sea nulo. Error en linea: {$cont}. Valor del campo: group: {$data['group']}"
                );
                $cont++;
                break;
            }

            //Verificar que el campo wbs no sea nulo y mayor que 40 caracteres
            if (empty($data['wbs']) || strlen($data['wbs'])>40) {
                
                $params[]=array(
                    'type' => 'error',
                    'message' => "Operación cancelada: Asegurate de que el campo wbs no sea nulo y tenga menos de 40 caracteres. Error en linea: {$cont}. Valor del campo: wbs: {$data['wbs']}"
                );
                $cont++;
                continue;
            }

            // Procesar las fechas
            $startdate = \DateTime::createFromFormat('d/m/Y', $data['startdate']);
            $enddate = \DateTime::createFromFormat('d/m/Y', $data['enddate']);

            if ($startdate === false || $enddate === false) {
                
                // Manejar el error de fecha aquí, como omitir la línea o lanzar una excepción
                $params[]=array(
                    'type' => 'error',
                    'message' => "Operación cancelada: Formato de fecha incorrecto en el archivo CSV. Error en linea: {$cont}. Valor del campo: startdate: {$data['startdate']} && enddate: {$data['enddate']}"
                );
                $cont++;
                continue;
            }

            // Mapear los datos del csv a los campos de la base de datos
            $record = new stdClass();
            $record->customerid = $data['customer'];
            $record->groupid = $data['group'];
            $record->wbs = $data['wbs'];
            $record->course = $data['course'];
            $record->startdate = $startdate->getTimestamp();
            $record->enddate = $enddate->getTimestamp();
            $record->num_trainees = $data['num_trainees'];
            $record->trainees = $data['trainees'];
            $record->location = $data['location'];
            $record->provider = $data['provider'];
            $record->lastupdate = time();

            // Insertar el registro en la base de datos.
            $DB->insert_record('trainingplan', $record);

            $cont++;
            $inserted_count++;
        }
    }
    
    // Si hay errores, que me muestre el primer error de la lista
    if (count($params)>1){
        $params[1]['message'].= ". Se han insertado $inserted_count registros en la base de datos.";
        $url = new \moodle_url($pageurl, $params[1]);
    } else {
        if ($inserted_count===0){
            $params[0]['message']= "Warning: Se han insertado $inserted_count registros en la base de datos. Ten en cuenta que se han borrado todos los registros relativos al cliente y grupo seleccionados. Revise el archivo CSV y verifique el el clienteid coincide con el cliente que tiene seleccionado en la lista superior.";
            $params[0]['type']="error";
        } else {
            $params[0]['message']= "Operación exitosa: Se han insertado $inserted_count registros en la base de datos.";
            
        }
        
        $url = new \moodle_url($pageurl, $params[0]);
    }
    
    redirect($url);

} else {
    //Configurar datos predeterminados y mostrar el formulario
    $mform->set_data($toform);
}

$mform->display();

if ($type=='ok'){
    \core\notification::success($message);
 }
 if ($type=='error'){
    \core\notification::error($message);
 }

 echo $OUTPUT->footer();

 function grupoExiste($customerid, $groupid){
    global $DB;
    $result=$DB->record_exists('grouptrainee', ['customer'=>$customerid, 'id'=>$groupid]);
    return $result;
 }

 function removeBomKeys(array $data): array {
    $cleanData = [];
    foreach ($data as $key => $value) {
        // Eliminar el BOM de la clave
        $cleanKey = preg_replace('/^\xEF\xBB\xBF/', '', $key);
        $cleanData[$cleanKey] = $value;
    }
    return $cleanData;
}
