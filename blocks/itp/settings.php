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
    global $DB;

    //Si la categoria 'blocksettingitp' ya está creada
    if (!$ADMIN->locate('blocksettingitp')) {
        //Crea una nueva categoría en la administración para el bloque
        $ADMIN->add('blocksettings', new admin_category('blocksettingitp2', new lang_string('pluginname', 'block_itp')));
    }
    
     //Crea una nueva página de configuración dentro de la categoria creada.
     $urlupdateItp=new moodle_url('/blocks/itp/admin/updateitp.php',[]);
     $settingspageupdateitp = new admin_externalpage('block_itp_upload', new lang_string('upload','block_itp'),$urlupdateItp);
     
     //$settingspageupload = new admin_settingpage('block_itp_upload', new lang_string('upload', 'block_itp'));

     //Se crea una  página externa de configuración para la creación de clientes.
     $urlcreatecustomer=new moodle_url('/blocks/itp/admin/createcustomer.php',[]);
     $settingspagecreatecustomer = new admin_externalpage('block_itp_createcustomer', new lang_string('createcustomer','block_itp'),$urlcreatecustomer);
     
     //Crea una nueva página de configuración para la creación de grupos
     $urlcreategroup=new moodle_url('/blocks/itp/admin/creategroup.php',[]);
     $settingspagecreategroups = new admin_externalpage('block_itp_creategroup', new lang_string('creategroup','block_itp'),$urlcreategroup);

     //Crea una nueva página de configuración para la creación de grupos
     $urlupdateTrainingPlan=new moodle_url('/blocks/itp/admin/updateTrainingPlan.php',[]);
     $settingspageupdateTrainingPlan = new admin_externalpage('block_itp_updateTrainingPlan', new lang_string('updateTrainingPlan','block_itp'),$urlupdateTrainingPlan);



    //$ADMIN->add('blocksettingitp', $settingitp);

    $ADMIN->add('blocksettingitp2', $settingspagecreatecustomer);
    
    $ADMIN->add('blocksettingitp2', $settingspagecreategroups);
    
    $ADMIN->add('blocksettingitp2', $settingspageupdateitp);

    $ADMIN->add('blocksettingitp2', $settingspageupdateTrainingPlan);
    // Incluye el archivo JavaScript
    //$PAGE->requires->js_call_amd('block_itp/settings', 'init');

    
    
}