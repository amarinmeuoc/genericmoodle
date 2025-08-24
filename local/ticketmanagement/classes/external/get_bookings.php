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
    $sql = "
        SELECT a.*
        FROM {ticketmanagement_address} a
        INNER JOIN (
            SELECT userid, MAX(id) AS maxid
            FROM {ticketmanagement_address}
            WHERE (departure_date IS NULL OR departure_date = 0) AND type = 'Internal'
            GROUP BY userid
        ) latest ON a.userid = latest.userid AND a.id = latest.maxid
        WHERE (a.departure_date IS NULL OR a.departure_date = 0) AND a.type = 'Internal'
        ORDER BY a.userid";
    $records = $DB->get_records_sql($sql);

    // Si no hay registros, devolver estructura vacía
    if (empty($records)) {
        return [
            'rooms' => [],
            'room_types' => [
                'floor' => [
                    'name' => 'Carraca -Cuatro Torres-',
                    'rooms' => []
                ],
                'house' => [
                    'name' => 'Carraca -Houses-',
                    'rooms' => []
                ]
            ],
            'users' => []
        ];
    }

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
        foreach ($floorRooms as $room) {
            $allRooms[] = 'floor_' . $room;
        }
    }
    foreach ($roomDefinitions['house']['rooms'] as $houseRooms) {
        foreach ($houseRooms as $room) {
            $allRooms[] = 'house_' . $room;
        }
    }
    $allRooms = array_unique($allRooms);
    sort($allRooms);


    // Obtener userids únicos
    $userIds = array_unique(array_column($records, 'userid'));

    // Verificar nuevamente si hay usuarios (por si acaso)
    if (empty($userIds)) {
        return [
            'rooms' => [],
            'room_types' => $roomDefinitions,
            'users' => []
        ];
    }

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
                    if (isset($roomDefinitions['floor']['rooms'][$record->floor]) && 
                        in_array($record->number, $roomDefinitions['floor']['rooms'][$record->floor])) {
                        $roomKey = 'floor_' . $record->number;
                        $userRooms[$roomKey] = true;
                    }
                } elseif ($record->address === $roomDefinitions['house']['name']) {
                    $houseKey = (string)$record->house;
                    if (isset($roomDefinitions['house']['rooms'][$houseKey]) && 
                        in_array($record->number, $roomDefinitions['house']['rooms'][$houseKey])) {
                        $roomKey = 'house_' . $record->number;
                        $userRooms[$roomKey] = true;
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
            new external_value(PARAM_TEXT, 'Room number')
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
                        
                        [ 
                            'floor_101' => new external_value(PARAM_BOOL, 'Room 101 in floor', VALUE_OPTIONAL),
                            'floor_102' => new external_value(PARAM_BOOL, 'Room 102 in floor', VALUE_OPTIONAL),
                            'floor_103' => new external_value(PARAM_BOOL, 'Room 103 in floor', VALUE_OPTIONAL),
                            'floor_104' => new external_value(PARAM_BOOL, 'Room 104 in floor', VALUE_OPTIONAL),
                            'floor_105' => new external_value(PARAM_BOOL, 'Room 105 in floor', VALUE_OPTIONAL),
                            'floor_106' => new external_value(PARAM_BOOL, 'Room 106 in floor', VALUE_OPTIONAL),
                            'floor_107' => new external_value(PARAM_BOOL, 'Room 107 in floor', VALUE_OPTIONAL),
                            'floor_108' => new external_value(PARAM_BOOL, 'Room 108 in floor', VALUE_OPTIONAL),
                            'floor_109' => new external_value(PARAM_BOOL, 'Room 109 in floor', VALUE_OPTIONAL),
                            'floor_110' => new external_value(PARAM_BOOL, 'Room 110 in floor', VALUE_OPTIONAL),
                            'floor_111' => new external_value(PARAM_BOOL, 'Room 111 in floor', VALUE_OPTIONAL),
                            'floor_112' => new external_value(PARAM_BOOL, 'Room 112 in floor', VALUE_OPTIONAL),
                            'floor_113' => new external_value(PARAM_BOOL, 'Room 113 in floor', VALUE_OPTIONAL),
                            'floor_114' => new external_value(PARAM_BOOL, 'Room 114 in floor', VALUE_OPTIONAL),
                            'floor_115' => new external_value(PARAM_BOOL, 'Room 115 in floor', VALUE_OPTIONAL),
                            'floor_116' => new external_value(PARAM_BOOL, 'Room 116 in floor', VALUE_OPTIONAL),
                            'floor_117' => new external_value(PARAM_BOOL, 'Room 117 in floor', VALUE_OPTIONAL),
                            'floor_118' => new external_value(PARAM_BOOL, 'Room 118 in floor', VALUE_OPTIONAL),
                            'floor_119' => new external_value(PARAM_BOOL, 'Room 119 in floor', VALUE_OPTIONAL),
                            'floor_120' => new external_value(PARAM_BOOL, 'Room 120 in floor', VALUE_OPTIONAL),
                            'floor_121' => new external_value(PARAM_BOOL, 'Room 121 in floor', VALUE_OPTIONAL),
                            'floor_122' => new external_value(PARAM_BOOL, 'Room 122 in floor', VALUE_OPTIONAL),

                            'floor_201' => new external_value(PARAM_BOOL, 'Room 201 in floor', VALUE_OPTIONAL),
                            'floor_202' => new external_value(PARAM_BOOL, 'Room 202 in floor', VALUE_OPTIONAL),
                            'floor_203' => new external_value(PARAM_BOOL, 'Room 203 in floor', VALUE_OPTIONAL),
                            'floor_204' => new external_value(PARAM_BOOL, 'Room 204 in floor', VALUE_OPTIONAL),
                            'floor_205' => new external_value(PARAM_BOOL, 'Room 205 in floor', VALUE_OPTIONAL),
                            'floor_206' => new external_value(PARAM_BOOL, 'Room 206 in floor', VALUE_OPTIONAL),
                            'floor_207' => new external_value(PARAM_BOOL, 'Room 207 in floor', VALUE_OPTIONAL),
                            'floor_208' => new external_value(PARAM_BOOL, 'Room 208 in floor', VALUE_OPTIONAL),
                            'floor_209' => new external_value(PARAM_BOOL, 'Room 209 in floor', VALUE_OPTIONAL),
                            'floor_210' => new external_value(PARAM_BOOL, 'Room 210 in floor', VALUE_OPTIONAL),
                            'floor_211' => new external_value(PARAM_BOOL, 'Room 211 in floor', VALUE_OPTIONAL),
                            'floor_212' => new external_value(PARAM_BOOL, 'Room 212 in floor', VALUE_OPTIONAL),
                            'floor_213' => new external_value(PARAM_BOOL, 'Room 213 in floor', VALUE_OPTIONAL),
                            'floor_214' => new external_value(PARAM_BOOL, 'Room 214 in floor', VALUE_OPTIONAL),
                            'floor_215' => new external_value(PARAM_BOOL, 'Room 215 in floor', VALUE_OPTIONAL),
                            'floor_216' => new external_value(PARAM_BOOL, 'Room 216 in floor', VALUE_OPTIONAL),
                            'floor_217' => new external_value(PARAM_BOOL, 'Room 217 in floor', VALUE_OPTIONAL),
                            'floor_218' => new external_value(PARAM_BOOL, 'Room 218 in floor', VALUE_OPTIONAL),
                            'floor_219' => new external_value(PARAM_BOOL, 'Room 219 in floor', VALUE_OPTIONAL),
                            'floor_220' => new external_value(PARAM_BOOL, 'Room 220 in floor', VALUE_OPTIONAL),
                            'floor_221' => new external_value(PARAM_BOOL, 'Room 221 in floor', VALUE_OPTIONAL),
                            'floor_222' => new external_value(PARAM_BOOL, 'Room 222 in floor', VALUE_OPTIONAL),
                            'floor_223' => new external_value(PARAM_BOOL, 'Room 223 in floor', VALUE_OPTIONAL),
                            'floor_224' => new external_value(PARAM_BOOL, 'Room 224 in floor', VALUE_OPTIONAL),
                            'floor_225' => new external_value(PARAM_BOOL, 'Room 225 in floor', VALUE_OPTIONAL),
                            'floor_226' => new external_value(PARAM_BOOL, 'Room 226 in floor', VALUE_OPTIONAL),
                            'floor_227' => new external_value(PARAM_BOOL, 'Room 227 in floor', VALUE_OPTIONAL),
                            'floor_228' => new external_value(PARAM_BOOL, 'Room 228 in floor', VALUE_OPTIONAL),
                            'floor_229' => new external_value(PARAM_BOOL, 'Room 229 in floor', VALUE_OPTIONAL),
                            'floor_230' => new external_value(PARAM_BOOL, 'Room 230 in floor', VALUE_OPTIONAL),
                            
                            'floor_301' => new external_value(PARAM_BOOL, 'Room 301 in floor', VALUE_OPTIONAL),
                            'floor_302' => new external_value(PARAM_BOOL, 'Room 302 in floor', VALUE_OPTIONAL),
                            'floor_303' => new external_value(PARAM_BOOL, 'Room 303 in floor', VALUE_OPTIONAL),
                            'floor_304' => new external_value(PARAM_BOOL, 'Room 304 in floor', VALUE_OPTIONAL),
                            'floor_305' => new external_value(PARAM_BOOL, 'Room 305 in floor', VALUE_OPTIONAL),
                            'floor_306' => new external_value(PARAM_BOOL, 'Room 306 in floor', VALUE_OPTIONAL),
                            'floor_307' => new external_value(PARAM_BOOL, 'Room 307 in floor', VALUE_OPTIONAL),
                            'floor_308' => new external_value(PARAM_BOOL, 'Room 308 in floor', VALUE_OPTIONAL),
                            'floor_309' => new external_value(PARAM_BOOL, 'Room 309 in floor', VALUE_OPTIONAL),
                            'floor_310' => new external_value(PARAM_BOOL, 'Room 310 in floor', VALUE_OPTIONAL),
                            'floor_311' => new external_value(PARAM_BOOL, 'Room 311 in floor', VALUE_OPTIONAL),
                            'floor_312' => new external_value(PARAM_BOOL, 'Room 312 in floor', VALUE_OPTIONAL),
                            'floor_313' => new external_value(PARAM_BOOL, 'Room 313 in floor', VALUE_OPTIONAL),
                            'floor_314' => new external_value(PARAM_BOOL, 'Room 314 in floor', VALUE_OPTIONAL),
                            'floor_315' => new external_value(PARAM_BOOL, 'Room 315 in floor', VALUE_OPTIONAL),
                            'floor_316' => new external_value(PARAM_BOOL, 'Room 316 in floor', VALUE_OPTIONAL),
                            'floor_317' => new external_value(PARAM_BOOL, 'Room 317 in floor', VALUE_OPTIONAL),
                            'floor_318' => new external_value(PARAM_BOOL, 'Room 318 in floor', VALUE_OPTIONAL),
                            'floor_319' => new external_value(PARAM_BOOL, 'Room 319 in floor', VALUE_OPTIONAL),
                            'floor_320' => new external_value(PARAM_BOOL, 'Room 320 in floor', VALUE_OPTIONAL),
                            'floor_321' => new external_value(PARAM_BOOL, 'Room 321 in floor', VALUE_OPTIONAL),
                            'floor_322' => new external_value(PARAM_BOOL, 'Room 322 in floor', VALUE_OPTIONAL),
                            'floor_323' => new external_value(PARAM_BOOL, 'Room 323 in floor', VALUE_OPTIONAL),
                            'floor_324' => new external_value(PARAM_BOOL, 'Room 324 in floor', VALUE_OPTIONAL),
                            'floor_325' => new external_value(PARAM_BOOL, 'Room 325 in floor', VALUE_OPTIONAL),
                            'floor_326' => new external_value(PARAM_BOOL, 'Room 326 in floor', VALUE_OPTIONAL),
                            'floor_327' => new external_value(PARAM_BOOL, 'Room 327 in floor', VALUE_OPTIONAL),
                            'floor_328' => new external_value(PARAM_BOOL, 'Room 328 in floor', VALUE_OPTIONAL),
                            'floor_329' => new external_value(PARAM_BOOL, 'Room 329 in floor', VALUE_OPTIONAL),
                            'floor_330' => new external_value(PARAM_BOOL, 'Room 330 in floor', VALUE_OPTIONAL),
                            'floor_331' => new external_value(PARAM_BOOL, 'Room 331 in floor', VALUE_OPTIONAL),
                            'floor_332' => new external_value(PARAM_BOOL, 'Room 332 in floor', VALUE_OPTIONAL),
                            'floor_333' => new external_value(PARAM_BOOL, 'Room 333 in floor', VALUE_OPTIONAL),
                            'floor_334' => new external_value(PARAM_BOOL, 'Room 334 in floor', VALUE_OPTIONAL),
                            'floor_335' => new external_value(PARAM_BOOL, 'Room 335 in floor', VALUE_OPTIONAL),
                            'floor_336' => new external_value(PARAM_BOOL, 'Room 336 in floor', VALUE_OPTIONAL),
                            'floor_337' => new external_value(PARAM_BOOL, 'Room 337 in floor', VALUE_OPTIONAL),
                            'floor_338' => new external_value(PARAM_BOOL, 'Room 338 in floor', VALUE_OPTIONAL),
                            
                            'house_111' => new external_value(PARAM_BOOL, 'Room 111 in house', VALUE_OPTIONAL),
                            'house_112' => new external_value(PARAM_BOOL, 'Room 112 in house', VALUE_OPTIONAL),
                            'house_113' => new external_value(PARAM_BOOL, 'Room 113 in house', VALUE_OPTIONAL),
                            'house_114' => new external_value(PARAM_BOOL, 'Room 114 in house', VALUE_OPTIONAL),
                            
                            'house_121' => new external_value(PARAM_BOOL, 'Room 121 in house', VALUE_OPTIONAL),
                            'house_122' => new external_value(PARAM_BOOL, 'Room 122 in house', VALUE_OPTIONAL),
                            'house_123' => new external_value(PARAM_BOOL, 'Room 123 in house', VALUE_OPTIONAL),
                            'house_124' => new external_value(PARAM_BOOL, 'Room 124 in house', VALUE_OPTIONAL),
                            
                            'house_211' => new external_value(PARAM_BOOL, 'Room 211 in house', VALUE_OPTIONAL),
                            'house_212' => new external_value(PARAM_BOOL, 'Room 212 in house', VALUE_OPTIONAL),
                            'house_213' => new external_value(PARAM_BOOL, 'Room 213 in house', VALUE_OPTIONAL),
                            'house_214' => new external_value(PARAM_BOOL, 'Room 214 in house', VALUE_OPTIONAL),
                            'house_221' => new external_value(PARAM_BOOL, 'Room 221 in house', VALUE_OPTIONAL),
                            'house_222' => new external_value(PARAM_BOOL, 'Room 222 in house', VALUE_OPTIONAL),
                            'house_223' => new external_value(PARAM_BOOL, 'Room 223 in house', VALUE_OPTIONAL),
                            'house_224' => new external_value(PARAM_BOOL, 'Room 224 in house', VALUE_OPTIONAL),
                            
                            'house_311' => new external_value(PARAM_BOOL, 'Room 311 in house', VALUE_OPTIONAL),
                            'house_312' => new external_value(PARAM_BOOL, 'Room 312 in house', VALUE_OPTIONAL),
                            'house_313' => new external_value(PARAM_BOOL, 'Room 313 in house', VALUE_OPTIONAL),
                            'house_321' => new external_value(PARAM_BOOL, 'Room 321 in house', VALUE_OPTIONAL),
                            'house_322' => new external_value(PARAM_BOOL, 'Room 322 in house', VALUE_OPTIONAL),
                            'house_323' => new external_value(PARAM_BOOL, 'Room 323 in house', VALUE_OPTIONAL),
                            'house_411' => new external_value(PARAM_BOOL, 'Room 411 in house', VALUE_OPTIONAL),
                            'house_412' => new external_value(PARAM_BOOL, 'Room 412 in house', VALUE_OPTIONAL),
                            'house_413' => new external_value(PARAM_BOOL, 'Room 413 in house', VALUE_OPTIONAL),
                            'house_414' => new external_value(PARAM_BOOL, 'Room 414 in house', VALUE_OPTIONAL),
                            'house_421' => new external_value(PARAM_BOOL, 'Room 421 in house', VALUE_OPTIONAL),
                            'house_422' => new external_value(PARAM_BOOL, 'Room 422 in house', VALUE_OPTIONAL),
                            'house_423' => new external_value(PARAM_BOOL, 'Room 423 in house', VALUE_OPTIONAL),
                            'house_424' => new external_value(PARAM_BOOL, 'Room 424 in house', VALUE_OPTIONAL),
                            
                            'house_511' => new external_value(PARAM_BOOL, 'Room 511 in house', VALUE_OPTIONAL),
                            'house_512' => new external_value(PARAM_BOOL, 'Room 512 in house', VALUE_OPTIONAL),
                            'house_513' => new external_value(PARAM_BOOL, 'Room 513 in house', VALUE_OPTIONAL),
                            'house_514' => new external_value(PARAM_BOOL, 'Room 514 in house', VALUE_OPTIONAL),
                            'house_521' => new external_value(PARAM_BOOL, 'Room 521 in house', VALUE_OPTIONAL),
                            'house_522' => new external_value(PARAM_BOOL, 'Room 522 in house', VALUE_OPTIONAL),
                            'house_523' => new external_value(PARAM_BOOL, 'Room 523 in house', VALUE_OPTIONAL),
                            'house_524' => new external_value(PARAM_BOOL, 'Room 524 in house', VALUE_OPTIONAL),
                            
                            'house_611' => new external_value(PARAM_BOOL, 'Room 611 in house', VALUE_OPTIONAL),
                            'house_612' => new external_value(PARAM_BOOL, 'Room 612 in house', VALUE_OPTIONAL),
                            'house_613' => new external_value(PARAM_BOOL, 'Room 613 in house', VALUE_OPTIONAL),
                            'house_614' => new external_value(PARAM_BOOL, 'Room 614 in house', VALUE_OPTIONAL),
                            'house_621' => new external_value(PARAM_BOOL, 'Room 621 in house', VALUE_OPTIONAL),
                            'house_622' => new external_value(PARAM_BOOL, 'Room 622 in house', VALUE_OPTIONAL),
                            'house_623' => new external_value(PARAM_BOOL, 'Room 623 in house', VALUE_OPTIONAL),
                            'house_624' => new external_value(PARAM_BOOL, 'Room 624 in house', VALUE_OPTIONAL),
                            'house_711' => new external_value(PARAM_BOOL, 'Room 711 in house', VALUE_OPTIONAL),
                            'house_712' => new external_value(PARAM_BOOL, 'Room 712 in house', VALUE_OPTIONAL),
                            'house_713' => new external_value(PARAM_BOOL, 'Room 713 in house', VALUE_OPTIONAL),
                            'house_714' => new external_value(PARAM_BOOL, 'Room 714 in house', VALUE_OPTIONAL),
                            'house_721' => new external_value(PARAM_BOOL, 'Room 721 in house', VALUE_OPTIONAL),
                            'house_722' => new external_value(PARAM_BOOL, 'Room 722 in house', VALUE_OPTIONAL),
                            'house_723' => new external_value(PARAM_BOOL, 'Room 723 in house', VALUE_OPTIONAL),
                            'house_724' => new external_value(PARAM_BOOL, 'Room 724 in house', VALUE_OPTIONAL),
                            
                            'house_811' => new external_value(PARAM_BOOL, 'Room 811 in house', VALUE_OPTIONAL),
                            'house_812' => new external_value(PARAM_BOOL, 'Room 812 in house', VALUE_OPTIONAL),
                            'house_813' => new external_value(PARAM_BOOL, 'Room 813 in house', VALUE_OPTIONAL),
                            'house_814' => new external_value(PARAM_BOOL, 'Room 814 in house', VALUE_OPTIONAL),
                            'house_821' => new external_value(PARAM_BOOL, 'Room 821 in house', VALUE_OPTIONAL),
                            'house_822' => new external_value(PARAM_BOOL, 'Room 822 in house', VALUE_OPTIONAL),
                            'house_823' => new external_value(PARAM_BOOL, 'Room 823 in house', VALUE_OPTIONAL),
                            'house_824' => new external_value(PARAM_BOOL, 'Room 824 in house', VALUE_OPTIONAL),
                            'house_911' => new external_value(PARAM_BOOL, 'Room 911 in house', VALUE_OPTIONAL),
                            'house_912' => new external_value(PARAM_BOOL, 'Room 912 in house', VALUE_OPTIONAL),
                            'house_913' => new external_value(PARAM_BOOL, 'Room 913 in house', VALUE_OPTIONAL),
                            'house_914' => new external_value(PARAM_BOOL, 'Room 914 in house', VALUE_OPTIONAL),
                            'house_921' => new external_value(PARAM_BOOL, 'Room 921 in house', VALUE_OPTIONAL),
                            'house_922' => new external_value(PARAM_BOOL, 'Room 922 in house', VALUE_OPTIONAL),
                            'house_923' => new external_value(PARAM_BOOL, 'Room 923 in house', VALUE_OPTIONAL),
                            'house_924' => new external_value(PARAM_BOOL, 'Room 924 in house', VALUE_OPTIONAL),
                        ],
                        'Room occupancy status',
                        VALUE_OPTIONAL
                    )
            ], 'User information with room occupancy')
        )
    ]);
}
}
