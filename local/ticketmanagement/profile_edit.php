<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/outputcomponents.php');

global $DB, $PAGE, $USER, $OUTPUT;

// Verifica que el usuario tenga el permiso requerido.
require_login();
if (!has_capability('local/ticketmanagement:edituserprofile', context_system::instance())) {
    throw new moodle_exception('nopermissions', 'error', '', 'local/ticketmanagement:edituserprofile');
}

// Obtén el ID del usuario a editar desde el parámetro URL.
$userid = required_param('userid', PARAM_INT);

// Verifica que el usuario existe.
if (!$user = $DB->get_record('user', array('id' => $userid))) {
    throw new moodle_exception('invaliduserid', 'error');
}

// Define la URL de la página.
$PAGE->set_url('/local/ticketmanagement/profile_edit.php', array('userid' => $userid));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('edituserprofile', 'local_ticketmanagement'));
$PAGE->set_heading(get_string('edituserprofile', 'local_ticketmanagement'));

// Verifica si el formulario fue enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtén los datos enviados por el formulario.
    $customer = required_param('customer', PARAM_TEXT);
    $group = required_param('group', PARAM_TEXT);

    // Actualiza los campos personalizados en la base de datos.

    // Actualiza el campo 'customer'.
    $customer_field = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => get_field_id_by_shortname('customer')));
    if ($customer_field) {
        $customer_field->data = $customer;
        $DB->update_record('user_info_data', $customer_field);
    } else {
        // Si no existe el registro, lo creamos.
        $DB->insert_record('user_info_data', (object)[
            'userid' => $userid,
            'fieldid' => get_field_id_by_shortname('customer'),
            'data' => $customer,
        ]);
    }

    // Actualiza el campo 'group'.
    $group_field = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => get_field_id_by_shortname('group')));
    if ($group_field) {
        $group_field->data = $group;
        $DB->update_record('user_info_data', $group_field);
    } else {
        // Si no existe el registro, lo creamos.
        $DB->insert_record('user_info_data', (object)[
            'userid' => $userid,
            'fieldid' => get_field_id_by_shortname('group'),
            'data' => $group,
        ]);
    }

    redirect(new moodle_url('/local/ticketmanagement/profile_edit.php', ['userid' => $userid]), get_string('profilesaved', 'local_ticketmanagement'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Obtén los valores actuales de los campos personalizados.
$customer_field = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => get_field_id_by_shortname('customer')));
$group_field = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => get_field_id_by_shortname('group')));

// Renderiza la página.
echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('edituserprofile', 'local_ticketmanagement'));

// Formulario sencillo para editar los campos personalizados.
echo html_writer::start_tag('form', array('method' => 'post'));

echo html_writer::tag('label', get_string('profilefield_customer', 'local_ticketmanagement'), array('for' => 'customer'));
echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'customer', 'value' => $customer_field->data));

echo html_writer::empty_tag('br');

echo html_writer::tag('label', get_string('profilefield_group', 'local_ticketmanagement'), array('for' => 'group'));
echo html_writer::empty_tag('input', array('type' => 'text', 'name' => 'group', 'value' => $group_field->data));

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('savechanges', 'local_ticketmanagement')));

echo html_writer::end_tag('form');

echo $OUTPUT->footer();

/**
 * Obtiene el ID de un campo personalizado a partir de su shortname.
 */
function get_field_id_by_shortname($shortname) {
    global $DB;
    $field = $DB->get_record('user_info_field', array('shortname' => $shortname));
    return $field ? $field->id : null;
}
