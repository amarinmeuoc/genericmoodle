<?php
namespace local_ticketmanagement\external;

require_once($CFG->dirroot.'/user/profile/lib.php'); 

use \core_external\external_function_parameters as external_function_parameters;
use \core_external\external_multiple_structure as external_multiple_structure;
use \core_external\external_single_structure as external_single_structure;
use \core_external\external_value as external_value;

class get_list_families extends \core_external\external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'params'=>new external_multiple_structure(
                new external_single_structure([
                    'groupid'=>new external_value(PARAM_TEXT,'Group name'),
                    'customerid'=>new external_value(PARAM_TEXT,'Customer shortname'),
                    'order' => new external_value(PARAM_INT, 'order'),
                    'orderby' => new external_value(PARAM_TEXT, 'orderby: name, lastname, email...'),
                    'page' => new external_value(PARAM_INT, 'Page number'),
                    'activePage' => new external_value(PARAM_INT, 'Active page'),

                    // Parámetros opcionales
                    'billid' => new external_value(PARAM_TEXT, 'Bill ID', VALUE_OPTIONAL),
                    'firstname' => new external_value(PARAM_TEXT, 'First name', VALUE_OPTIONAL),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name', VALUE_OPTIONAL),
                ])
            ) 
        ]);
    }

        /**
     * Order ITP
     * @param array A list of params for sorting the ITP (with keys orderby and order)
     * @return array A ITP Row
     */
    public static function execute($params) {
        global $DB;
    
        // Validate parameters and extract them
        $request = self::validate_parameters(self::execute_parameters(), ['params' => $params]);
        $groupid = $request['params'][0]['groupid'];
        $customerid = $request['params'][0]['customerid'];
        $order=($request['params'][0]['order']===1)?'ASC':'DESC'; //Si order es igual a 1 ordenamiento ascendente, sino descendente
        $orderby=$request['params'][0]['orderby'];
        $page=$request['params'][0]['page'];
        $activePage=$request['params'][0]['activePage'];
        $perpage=50;
        $offset=($page-1)*$perpage;

        // Parámetros opcionales
        $billid = $request['params'][0]['billid'] ?? null;
        $firstname = $request['params'][0]['firstname'] ?? null;
        $lastname = $request['params'][0]['lastname'] ?? null;
            
        // Security checks
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('webservice/rest:use', $context);

        $customer=$DB->get_field('customer','shortname',['id'=>$customerid]);
    
        if ($groupid!=='0')
            $groupname=$DB->get_field('grouptrainee','name',['id'=>$groupid]);
        else if ( $groupid==='0'){
            $groupname='PCO';
        }

        $sql='SELECT u.id, username, firstname, lastname, email, phone1, phone2, address, city,
                MAX(if (uf.shortname="billid",ui.data,"")) as billid,
                MAX(if (uf.shortname="group",ui.data,"")) as groupname,
                MAX(if (uf.shortname="customer",ui.data,"")) as customer,
                MAX(if (uf.shortname="personalemail",ui.data,"")) as personalemail,
                MAX(if (uf.shortname="notes",ui.data,"")) as notes,
                MAX(if (uf.shortname="insurance_card_number",ui.data,"")) as insurance_card_number,
                MAX(if (uf.shortname="birthdate",ui.data,"")) as birthdate,
                MAX(if (uf.shortname="shoesize",ui.data,"")) as shoesize,
                MAX(if (uf.shortname="overallsize",ui.data,"")) as overallsize,
                MAX(if (uf.shortname="arrival_date",ui.data,"")) as arrival_date,
                MAX(if (uf.shortname="departure_date",ui.data,"")) as departure_date
                FROM mdl_user AS u
                INNER JOIN mdl_user_info_data AS ui ON ui.userid=u.id
                INNER JOIN mdl_user_info_field AS uf ON uf.id=ui.fieldid
                WHERE u.suspended=0
                GROUP by id,username,firstname, lastname
                HAVING customer=:customer AND groupname=:groupname';
        
        $params_array=['customer'=>$customer, 'groupname'=>$groupname];

        // Agregar filtros opcionales
        if (!empty($billid)) {
            $sql .= ' AND billid LIKE :billid';
            $params_array['billid'] = "%$billid%";
        }
        if (!empty($firstname)) {
            $sql .= ' AND firstname LIKE :firstname';
            $params_array['firstname'] = "%$firstname%";
        }
        if (!empty($lastname)) {
            $sql .= ' AND lastname LIKE :lastname';
            $params_array['lastname'] = "%$lastname%";
        }

        
        //Listado total de usuarios sin paginación
        $sqlTotal=$sql. " ORDER BY $orderby $order";

        // Listado de usuarios ordenado y con paginación
        $sql .= " ORDER BY $orderby $order LIMIT $perpage OFFSET $offset";
        

         // Obtener el número total de usuarios
        $total_records = $DB->get_records_sql($sqlTotal, $params_array);
        $num_total_records = count($total_records);

        // Obtener los usuarios filtrados
        $trainee_query = $DB->get_records_sql($sql, $params_array);

        // Calcular el número total de páginas
        $num_pages = intval(ceil($num_total_records / $perpage));
        $num_pages = max($num_pages, 1);

        $num_records = count($trainee_query) + $offset;

        $pages=[];
        for ($i = 1; $i <= $num_pages; $i++) {
            $pages[] = (object)[
                'page' => $i,
                'active' => ($i === $activePage) // Set the first page as active
            ];
        }
        
        $total_family_list=[];
        foreach ($trainee_query as $user) {
            $family_members=$DB->get_records('family',['userid'=>$user->id]);
            $familiar_list=[];
            foreach ($family_members as $member){
                $familiar_list[]=[
                    'id'=>$user->id,
                    'vessel'=>$user->groupname,
                    'billid'=>$user->billid,
                    'firstname'=>$user->firstname,
                    'lastname'=>$user->lastname,
                    'email'=>$user->email,
                    'family_role'=>$member?->relationship ?? '',
                    'family_firstname'=>$member?->name ?? '',
                    'family_lastname'=>$member?->lastname ?? '',
                    'family_nie'=>$member?->nie ?? '',
                    'family_birthdate'=>$member?->birthdate ?? 0,
                    'family_adeslas'=>$member?->adeslas ?? '',
                    'family_phone1'=>$member?->phone1 ?? '',
                    'family_email'=>$member?->email ?? '',
                    'family_arrival'=>$member?->arrival ?? 0,
                    'family_departure'=>$member?->departure ?? 0,
                    'family_notes'=>$member?->notes ?? '',
                ];
            };
            $total_family_list = array_merge($total_family_list, $familiar_list);
        }
        
        $users=[
            'family_list'=>$total_family_list,
            'orderbyid'=>$orderby==='id',
            'orderbyvessel'=>$orderby==='groupname',
            'orderbybillid'=>$orderby==='billid',
            'orderbyemail'=>$orderby==='email',
            'orderbyfirstname'=>$orderby==='firstname',
            'orderbylastname'=>$orderby==='lastname',
            'order'=>($order==='ASC')?1:0,
            'hidecontrolonsinglepage'=>false,
            'activepagenumber'=>$activePage,
            'barsize'=>'small',
            'num_total_records'=>$num_total_records,
            'num_pages'=>$num_pages,
            'num_records'=>$num_records,
            'pages'=>$pages,
            'previous' => [
                'page' => max(1, $page - 1), // Si es menor a 1, devolver siempre 1
                'url' => '' // URL vacía o generada
            ],
            'next' => [
                'page' => min($num_pages, $page + 1),    // Límite superior en 10
                'url' => '' // URL vacía o generada
            ],
            'first' => [
                'page' => 1,
                'url' => '' // URL vacía o generada
            ],
            'last' => [
                'page' => $num_pages, // Asumiendo que 10 es el número máximo de páginas
                'url' => '' // URL vacía o generada
            ],
        ];

        return $users;

    }

    

    public static function execute_returns() {
        //Devuelve un array de objetos
        return new external_single_structure(
            array(
                'family_list' => new external_multiple_structure(
                    new external_single_structure([
                        'id' => new external_value(PARAM_INT, 'User ID'),
                        'vessel' => new external_value(PARAM_TEXT, 'Groupname or vessel'),
                        'billid' => new external_value(PARAM_TEXT, 'User Billid'),
                        'firstname' => new external_value(PARAM_TEXT, 'First name'),
                        'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                        'email' => new external_value(PARAM_TEXT, 'Email'),
                        'family_role'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_firstname'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_lastname'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_nie'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_birthdate'=> new external_value(PARAM_INT, 'Last name'),
                        'family_adeslas'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_phone1'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_email'=> new external_value(PARAM_TEXT, 'Last name'),
                        'family_arrival'=> new external_value(PARAM_INT, 'Last name'),
                        'family_departure'=> new external_value(PARAM_INT, 'Last name'),
                        'family_notes'=> new external_value(PARAM_TEXT, 'Last name'),
                    ])
                ),
                'orderbyid' => new external_value(PARAM_BOOL, 'ordenados por id'),
                'orderbyvessel' => new external_value(PARAM_BOOL, 'Ordenados por groupname'),
                'orderbybillid' => new external_value(PARAM_BOOL, 'ordenados por billid'),
                'orderbyemail' => new external_value(PARAM_BOOL, 'ordenados por email'),
                'orderbyfirstname' => new external_value(PARAM_BOOL, 'Ordenados por phone1'),
                'orderbylastname' => new external_value(PARAM_BOOL, 'Ordenados por phone2'),
                
                'order'=> new external_value(PARAM_INT, 'Indica si es orden ascendente o descendente'),
                'hidecontrolonsinglepage' => new external_value(PARAM_BOOL, 'Control para ocultar la navegación en una sola página'),
                'activepagenumber' => new external_value(PARAM_INT, 'Número de página actual'),
                'barsize' => new external_value(PARAM_TEXT, 'Tamaño de la barra de navegación'),
                'num_total_records' => new external_value(PARAM_INT, 'Número total de registros'),
                'num_pages' => new external_value(PARAM_INT, 'Número total de registros para la pagina seleccionada'),
                'num_records' => new external_value(PARAM_INT, 'Número total de registros para la pagina seleccionada'),
                // Nueva estructura 'pages'
                'pages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'page' => new external_value(PARAM_INT, 'Número de página'),
                            'active' => new external_value(PARAM_BOOL, 'Indica si la página está activa')
                        )
                    )
                ),
                'previous' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la página anterior'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la página anterior'),
                    )
                ),
                'next' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la página siguiente'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la página siguiente'),
                    )
                ),
                'first' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la primera página'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la primera página'),
                    )
                ),
                'last' => new external_single_structure(
                    array(
                        'page' => new external_value(PARAM_INT, 'Número de la última página'),
                        'url' => new external_value(PARAM_TEXT, 'URL para la última página'),
                    )
                ),
            )
        );
    }
}