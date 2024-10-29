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
 * @package     local_ticketmanagement
 * @category    admin
 * @copyright   2020 Your Name <email@example.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();



if ($hassiteconfig) {
    global $DB;

    //Si la categoria 'localpluginsticketmanagement' ya está creada
    if (!$ADMIN->locate('localpluginsticketmanagement')) {
        //Crea una nueva categoría en la administración para el bloque
        $ADMIN->add('localplugins', new admin_category('localpluginsticketmanagement', new lang_string('pluginname', 'local_ticketmanagement')));
    }
    
     //Crea una nueva página de configuración dentro de la categoria creada.
     
     $urlcategory=new moodle_url('/local/ticketmanagement/admin/create_category.php',[]);
     $settingspagecategory = new admin_externalpage('local_ticketmanagement_category', new lang_string('createcategory','local_ticketmanagement'),$urlcategory);
     
     $urlsubcategory=new moodle_url('/local/ticketmanagement/admin/create_subcategory.php',[]);
     $settingspagesubcategory = new admin_externalpage('local_ticketmanagement_subcategory', new lang_string('createsubcategory','local_ticketmanagement'),$urlsubcategory);
     
     $urluploadfamily=new moodle_url('/local/ticketmanagement/admin/upload_family.php',[]);
     $settingspageuploadfamily = new admin_externalpage('local_ticketmanagement_uploadfamily', new lang_string('uploadfamilydetails','local_ticketmanagement'),$urluploadfamily);
     

    //$ADMIN->add('blocksettingitp', $settingitp);

    
    $ADMIN->add('localpluginsticketmanagement', $settingspagecategory);
    $ADMIN->add('localpluginsticketmanagement', $settingspagesubcategory);
    $ADMIN->add('localpluginsticketmanagement', $settingspageuploadfamily);
    
    

    
    
}