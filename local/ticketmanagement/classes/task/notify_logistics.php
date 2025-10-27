<?php
namespace local_ticketmanagement\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;
use core_user;
use context_system;

class notify_logistics extends scheduled_task {

    public function get_name() {
        return get_string('task_notify_logistics', 'local_ticketmanagement');
    }

    public function execute() {
        global $DB;

        $now = time();
        $limit = strtotime('+14 days', $now);

        // Campos personalizados
        $fields = ['departure_date', 'billid', 'group'];
        list($insql, $params) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);
        $fieldrecords = $DB->get_records_select('user_info_field', "shortname $insql", $params);
        if (empty($fieldrecords)) {
            mtrace("Campos personalizados no encontrados.");
            return true;
        }

        $fieldids = [];
        foreach ($fieldrecords as $f) {
            $fieldids[$f->shortname] = $f->id;
        }

        // Traemos todos los usuarios con departure_date definido
        $sql = "SELECT u.id, u.firstname, u.lastname, u.email, d.data AS departure_date
                  FROM {user_info_data} d
                  JOIN {user} u ON u.id = d.userid
                 WHERE d.fieldid = :departurefield AND d.data <> ''";
        $params = ['departurefield' => $fieldids['departure_date']];
        $users = $DB->get_records_sql($sql, $params);

        if (empty($users)) {
            mtrace("No hay usuarios con departure_date definido.");
            return true;
        }

        // Obtener usuarios con rol 'logistic'
        $systemcontext = context_system::instance();
        $role = $DB->get_record('role', ['shortname' => 'logistic']);
        if (!$role) {
            mtrace("Rol 'logistic' no encontrado.");
            return false;
        }

        $logistics = get_role_users($role->id, $systemcontext, true, 'u.*');

        foreach ($users as $user) {
            // Convertir la fecha del campo de perfil a timestamp
            $departuretime = is_numeric($user->departure_date)
                ? (int)$user->departure_date
                : strtotime($user->departure_date);

            if (!$departuretime) continue; // No válida

            // Comprobar si está dentro del rango de los próximos 14 días
            if ($departuretime < $now || $departuretime > $limit) continue;

            // Verificar si ya fue notificado
            $exists = $DB->record_exists('local_ticketmanagement_departurealert', [
                'userid' => $user->id,
                'departuredate' => $departuretime,
            ]);
            if ($exists) continue;

            // Obtener otros campos personalizados
            $userfields = [];
            foreach (['billid', 'group'] as $shortname) {
                if (!isset($fieldids[$shortname])) continue;
                $data = $DB->get_field('user_info_data', 'data', [
                    'userid' => $user->id,
                    'fieldid' => $fieldids[$shortname],
                ]);
                $userfields[$shortname] = $data ?? '-';
            }

            // Notificar
            $this->notify_logistics($logistics, $user, $userfields, $departuretime);

            // Registrar notificación enviada
            $record = (object)[
                'userid' => $user->id,
                'departuredate' => $departuretime,
                'timecreated' => $now,
            ];
            $DB->insert_record('local_ticketmanagement_departurealert', $record);
        }

        return true;
    }

    private function notify_logistics($logistics, $user, $fields, $departuretime) {
        global $CFG;
        require_once($CFG->dirroot . '/message/lib.php');

        $daysleft = ceil(($departuretime - time()) / DAYSECS);
        if ($daysleft < 0) $daysleft = 0;

        $subject = "Notice: {$user->firstname} {$user->lastname}'s upcoming departure, ({$daysleft} days ramaining)";
        $message = "The user {$user->firstname} {$user->lastname} has a scheduled departure date set for " .
                   date('Y-m-d', $departuretime) . ".\n\n" .
                   "Details:\n" .
                   " - Bill ID: {$fields['billid']}\n" .
                   " - Group: {$fields['group']}\n\n" .
                   "Please, proceed to deactivate his associated services.";

        foreach ($logistics as $logistic) {
            $logistic = \core_user::get_user($logistic->id);
            $eventdata = new \core\message\message();
            $eventdata->component = 'local_ticketmanagement';
            $eventdata->name = 'departure_notification';
            $eventdata->userfrom = \core_user::get_noreply_user();
            $eventdata->userto = $logistic;
            $eventdata->subject = $subject;
            $eventdata->fullmessage = $message;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = nl2br($message);
            $eventdata->smallmessage = "Salida próxima: {$user->firstname} {$user->lastname}";
            $eventdata->notification = 1;
            message_send($eventdata);
        }
    }
}
