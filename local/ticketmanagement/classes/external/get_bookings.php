<?php
namespace local_ticketmanagement\external;

require_once($CFG->dirroot.'/user/profile/lib.php'); 
require_once($CFG->libdir.'/externallib.php');

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use context_system;

class get_bookings extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Get all internal bookings with room occupancy status and user profile fields
     * @return array
     */
    public static function execute() {
    global $DB, $CFG;
    
    // Validar contexto y capacidades
    $context = \context_system::instance();
    self::validate_context($context);
    require_capability('local/ticketmanagement:viewbookings', $context);

    // Obtener todos los registros internos
    $sql = "SELECT * 
            FROM {ticketmanagement_address} 
            WHERE type = 'Internal' 
            ORDER BY userid, address, house, floor, number";
    $records = $DB->get_records_sql($sql);

    // Definir habitaciones de forma más eficiente
    $roomDefinitions = [
        'floor' => [
            'name' => 'Carraca -Cuatro Torres-',
            'rooms' => [
                0 => range(101, 122),    // Planta 0: habitaciones 101-122
                1 => range(201, 230),    // Planta 1: habitaciones 201-230
                2 => range(301, 338)     // Planta 2: habitaciones 301-338
            ]
        ],
        'house' => [
            'name' => 'Carraca -Houses-',
            'rooms' => [
                '0' => array_merge(range(111, 124)),       // Casa 0
                '1' => array_merge(range(211, 214), range(221, 224)), // Casa 1
                '2' => array_merge(range(311, 313), range(321, 323)), // Casa 2
                '3' => array_merge(range(411, 414), range(421, 424)), // Casa 3
                '4' => array_merge(range(511, 514), range(521, 524)), // Casa 4
                '5' => array_merge(range(611, 614), range(621, 624)), // Casa 5
                '6' => array_merge(range(711, 714), range(721, 724)), // Casa 6
                '7' => array_merge(range(811, 814), range(821, 824)), // Casa 7
                '8' => array_merge(range(911, 914), range(921, 924))  // Casa 8
            ]
        ]
    ];

    // Obtener todos los números de habitación
    $allRooms = [];
    foreach ($roomDefinitions['floor']['rooms'] as $floorRooms) {
        $allRooms = array_merge($allRooms, $floorRooms);
    }
    foreach ($roomDefinitions['house']['rooms'] as $houseRooms) {
        $allRooms = array_merge($allRooms, $houseRooms);
    }
    $allRooms = array_unique($allRooms);
    sort($allRooms);

    // Obtener userids únicos
    $userIds = array_unique(array_column($records, 'userid'));

    // Consulta optimizada para datos de usuario
    list($sql, $params) = $DB->get_in_or_equal($userIds);
    $users = $DB->get_records_sql("
        SELECT u.id, u.username, u.firstname, u.lastname, u.email,
               MAX(IF(uf.shortname='billid', ui.data, '')) as billid,
               MAX(IF(uf.shortname='group', ui.data, '')) as groupname,
               MAX(IF(uf.shortname='customer', ui.data, '')) as customer
        FROM {user} u
        LEFT JOIN {user_info_data} ui ON ui.userid = u.id
        LEFT JOIN {user_info_field} uf ON uf.id = ui.fieldid
        WHERE u.id $sql AND u.suspended = 0
        GROUP BY u.id, u.username, u.firstname, u.lastname, u.email
    ", $params);

    // Preparar estructura de resultados
    $result = [
        'rooms' => $allRooms,
        'room_types' => $roomDefinitions,
        'users' => []
    ];

    foreach ($userIds as $userId) {
        $userRooms = array_fill_keys($allRooms, false);
        $userDetails = $users[$userId] ?? null;

        foreach ($records as $record) {
            if ($record->userid == $userId && 
                (empty($record->departure_date) || $record->departure_date == 0)) {
                
                if ($record->address === $roomDefinitions['floor']['name']) {
                    // Es una habitación de planta
                    if (isset($roomDefinitions['floor']['rooms'][$record->floor]) && 
                        in_array($record->number, $roomDefinitions['floor']['rooms'][$record->floor])) {
                        $userRooms[$record->number] = true;
                    }
                } elseif ($record->address === $roomDefinitions['house']['name']) {
                    // Es una casa
                    $houseKey = (string)$record->house;
                    if (isset($roomDefinitions['house']['rooms'][$houseKey]) && 
                        in_array($record->number, $roomDefinitions['house']['rooms'][$houseKey])) {
                        $userRooms[$record->number] = true;
                    }
                }
            }
        }

        $result['users'][] = [
            'userid' => $userId,
            'firstname' => $userDetails->firstname ?? '',
            'lastname' => $userDetails->lastname ?? '',
            'email' => $userDetails->email ?? '',
            'customer' => $userDetails->customer ?? '',
            'group' => $userDetails->groupname ?? '',
            'billid' => $userDetails->billid ?? '',
            'rooms' => $userRooms
        ];
    }

    return $result;
}

/**
 * Returns description of method result value
 * @return external_description
 */
public static function execute_returns() {
    return new external_single_structure([
        'rooms' => new external_multiple_structure(
            new external_value(PARAM_INT, 'Room number')
        ),
        'room_types' => new external_single_structure([
            'floor' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'Floor rooms name'),
                'rooms' => new external_multiple_structure(
                    new external_multiple_structure(
                        new external_value(PARAM_INT, 'Room number'),
                    'Rooms by floor (index = floor number)',
                    VALUE_OPTIONAL
                ))
            ], 'Floor rooms definition', VALUE_OPTIONAL),
            'house' => new external_single_structure([
                'name' => new external_value(PARAM_TEXT, 'House rooms name'),
                'rooms' => new external_multiple_structure(
                    new external_multiple_structure(
                        new external_value(PARAM_INT, 'Room number'),
                    'Rooms by house (index = house number)',
                    VALUE_OPTIONAL
                ))
            ], 'House rooms definition', VALUE_OPTIONAL)
        ], 'Room types definition', VALUE_OPTIONAL),
        'users' => new external_multiple_structure(
            new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'firstname' => new external_value(PARAM_TEXT, 'First name', VALUE_OPTIONAL),
                'lastname' => new external_value(PARAM_TEXT, 'Last name', VALUE_OPTIONAL),
                'email' => new external_value(PARAM_EMAIL, 'Email', VALUE_OPTIONAL),
                'customer' => new external_value(PARAM_TEXT, 'Customer', VALUE_OPTIONAL),
                'group' => new external_value(PARAM_TEXT, 'Group', VALUE_OPTIONAL),
                'billid' => new external_value(PARAM_TEXT, 'Bill ID', VALUE_OPTIONAL),
                'rooms' => new external_single_structure(
                        // Dynamic room structure - you may want to define specific rooms here
                        // or use a different approach if you have many rooms
                        [ 
                            '101'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'102'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'103'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'104'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'105'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'106'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'107'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'108'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'109'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'110'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'111'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'112'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'113'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'114'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'115'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'116'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'117'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'118'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'119'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'120'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'121'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'122'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'123'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'124'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'125'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'201'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'202'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'203'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'204'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'205'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'206'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'207'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'208'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'209'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'210'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'211'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'212'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'213'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'214'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'215'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'216'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'217'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'218'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'219'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'220'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'221'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'222'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'223'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'224'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'225'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'226'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'227'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'228'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'229'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'230'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'301'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'302'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'303'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'304'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'305'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'306'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'307'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'308'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'309'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'310'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'311'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'312'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'313'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'314'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'315'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'316'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'317'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'318'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'319'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'320'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'321'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'322'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'323'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'324'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'325'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'326'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'327'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'328'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'329'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'330'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'331'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'332'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'333'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'334'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'335'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'336'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'337'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'338'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'411'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'412'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'413'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'414'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'415'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'416'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'417'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'418'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'419'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'420'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'421'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'422'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'423'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'424'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'511'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'512'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'513'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'514'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'515'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'516'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'517'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'518'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'519'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'520'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'521'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'522'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'523'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'524'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'611'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'612'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'613'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'614'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'615'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'616'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'617'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'618'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'619'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'620'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'621'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'622'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'623'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'624'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'711'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'712'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'713'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'714'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'715'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'716'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'717'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'718'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'719'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'720'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'721'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'722'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'723'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'724'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'811'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'812'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'813'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'814'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'815'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'816'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'817'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'818'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'819'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'820'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'821'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'822'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'823'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'824'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'911'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'912'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'913'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'914'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'915'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'916'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'917'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'918'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'919'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'920'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'921'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'922'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'923'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),
'924'  => new external_value(PARAM_BOOL, 'Room 102 status', VALUE_OPTIONAL),


                        ],
                        'Room occupancy status',
                        VALUE_OPTIONAL
                    )
            ], 'User information with room occupancy')
        )
    ]);
}
}