
export const init=(XLSX, filesaver,blobutil)=>{
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    const boexport=document.querySelector('#id_getBookings');
    boexport.addEventListener('click',(e)=>{
        exportToExcel(e,XLSX,filesaver,blobutil, url); 
        
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil, url)=>{
        

    prepareDataToSend(url,token);
}

const prepareDataToSend=(url,token)=>{
    let xhr = new XMLHttpRequest();

    const service= 'local_ticketmanagement_get_bookings';

    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', service);
    formData.append('moodlewsrestformat', 'json');   
    
    xhr.open('POST',url,true);
    
    setTimeout(()=>{
        xhr.send(formData);
    },100);

    xhr.onload = (ev)=> {
        onLoadFunction(xhr);
    }

    xhr.onloadstart=(event)=>{
        const loader=document.querySelector('.excel_loader');
        loader.classList.remove('hide');
        loader.classList.add('show');
        
        const boexport=document.querySelector('#id_getBookings');

        if (boexport)
            boexport.disabled=true;
        
    }

    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        const loader=document.querySelector('.excel_loader');
        
        loader.classList.remove('show');
        loader.classList.add('hide');
        
        const boexport=document.querySelector('#id_getBookings');

        if (boexport)
            boexport.disabled=false;
    }

    xhr.onerror = ()=> {
        window.console.error('Network Error: Unable to send the request.');

        // Opcional: Restablecer el estado de la interfaz
        const loader = document.querySelector('.excel_loader');
        loader.classList.remove('show');
        loader.classList.add('hide');

        const boexport = document.querySelector('#id_getBookings');
        if (boexport) boexport.disabled = false;
    }

}

const onLoadFunction=(myXhr)=>{
   
    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        
        generateEnhancedOccupancyExcel(res);
        
    }
}

const onProgressFunction=(event) =>{
    console.log(`Uploaded ${event.loaded} of ${event.total}`);
    const loader=document.querySelector('.loader');
    loader.classList.remove('.hide');
    loader.classList.add('.show');
}
/*
const generateDetailedOccupancyExcel = (response) => {
    // Prepare the data array for Excel
    let excelData = [];
    
    // Create headers - first the user info columns
    const headers = [
        'User ID',
        'First Name',
        'Last Name',
        'Email',
        'Customer',
        'Group',
        'Bill ID'
    ];
    
    // Define room types (from your PHP code)
    const roomTypes = {
        floorRooms: [
            [101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122],
            [201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230],
            [301,302,303,304,305,306,307,308,309,310,311,312,313,314,315,316,317,318,319,320,321,322,323,324,325,326,327,328,329,330,331,332,333,334,335,336,337,338]
        ],
        houseRooms: [
            [111,112,113,114,115,116,117,118,119,120,121,122,123,124],
            [211,212,213,214,221,222,213,224],
            [311,312,313,321,322,323],
            [411,412,413,414,421,422,423,424],
            [511,512,513,514,521,522,523,524],
            [611,612,613,614,621,622,623,624],
            [711,712,713,714,721,722,723,724],
            [811,812,813,814,821,822,823,824],
            [911,912,913,914,921,922,923,924]
        ]
    };
    
    // Create a flat array of all rooms with their type
    const allRoomsWithType = [];
    
    // Add floor rooms with type
    roomTypes.floorRooms.forEach((floor, floorIndex) => {
        floor.forEach(room => {
            allRoomsWithType.push({
                number: room,
                type: 'Floor',
                floor: floorIndex
            });
        });
    });
    
    // Add house rooms with type
    roomTypes.houseRooms.forEach((house, houseIndex) => {
        house.forEach(room => {
            allRoomsWithType.push({
                number: room,
                type: 'House',
                house: houseIndex
            });
        });
    });
    
    // Sort rooms by number
    allRoomsWithType.sort((a, b) => a.number - b.number);
    
    // Add room headers with type information
    allRoomsWithType.forEach(room => {
        if (room.type === 'Floor') {
            headers.push(`F${room.floor}-${room.number}`);
        } else {
            headers.push(`H${room.house}-${room.number}`);
        }
    });
    
    excelData.push(headers);
    
    // Process each user
    response.users.forEach(user => {
        // Start with user information
        const row = [
            user.userid,
            user.firstname,
            user.lastname,
            user.email,
            user.customer,
            user.group,
            user.billid
        ];
        
        // Add occupancy status for each room
        allRoomsWithType.forEach(room => {
            const status = user.rooms[room.number] ? 'Occupied' : 'Free';
            row.push(status);
        });
        
        excelData.push(row);
    });
    
    // Create workbook
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    
    // Freeze the first row (headers) and first 7 columns (user info)
    ws['!freeze'] = { xSplit: 7, ySplit: 1, topLeftCell: 'H2' };
    
    // Add conditional formatting for occupied/free rooms
    const range = XLSX.utils.decode_range(ws['!ref']);
    
    // Format for occupied rooms (red background)
    for (let row = 1; row <= range.e.r; row++) {
        for (let col = 7; col <= range.e.c; col++) { // Start from column H (7)
            const cellAddress = XLSX.utils.encode_cell({r: row, c: col});
            if (ws[cellAddress]?.v === 'Occupied') {
                ws[cellAddress].s = {
                    fill: { patternType: 'solid', fgColor: { rgb: 'FFCCCC' } },
                    font: { color: { rgb: '990000' }, bold: true }
                };
            } else if (ws[cellAddress]?.v === 'Free') {
                ws[cellAddress].s = {
                    fill: { patternType: 'solid', fgColor: { rgb: 'CCFFCC' } },
                    font: { color: { rgb: '006600' } }
                };
            }
        }
    }
    
    // Set column widths
    ws['!cols'] = [
        { width: 10 }, // User ID
        { width: 15 }, // First Name
        { width: 15 }, // Last Name
        { width: 25 }, // Email
        { width: 15 }, // Customer
        { width: 15 }, // Group
        { width: 15 }, // Bill ID
        // Room columns will auto-size
    ];
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, "Detailed Occupancy");
    
    // Generate and download the file
    const now = new Date();
    const dateStr = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')}`;
    const timeStr = `${now.getHours().toString().padStart(2, '0')}-${now.getMinutes().toString().padStart(2, '0')}`;
    XLSX.writeFile(wb, `Room_Occupancy_${dateStr}_${timeStr}.xlsx`);
};
*/
const generateEnhancedOccupancyExcel = (response) => {
    const wb = XLSX.utils.book_new();
    const now = new Date();
    const dateStr = `${now.getDate().toString().padStart(2, '0')}-${(now.getMonth()+1).toString().padStart(2, '0')}-${now.getFullYear()}`;
    
    // Crear hoja para habitaciones de planta
    createRoomTypeSheet(wb, response, 'floor', 'Plantas');
    
    // Crear hoja para habitaciones de casas
    createRoomTypeSheet(wb, response, 'house', 'Casas');
    
    // Crear hoja resumen
    createSummarySheet(wb, response);
    
    // Descargar el archivo
    XLSX.writeFile(wb, `Informe_Ocupacion_${dateStr}.xlsx`);
};

const createRoomTypeSheet = (wb, response, roomType, sheetName) => {
    const roomDef = response.room_types[roomType];
    const rooms = [];
    
    // Preparar encabezados
    const headers = [
        'ID User', 'Name', 'Lastname', 'Email', 
        'Project', 'Group', 'Billid'
    ];
    
    // Procesar las habitaciones de este tipo
    Object.entries(roomDef.rooms).forEach(([key, roomNumbers]) => {
        const incrementedKey = Number(key) + 1;
        roomNumbers.forEach(room => {
            const roomLabel = roomType === 'floor' 
                ? `F${incrementedKey}-${room}` 
                : `H${incrementedKey}-${room}`;
            headers.push(roomLabel);
            rooms.push(room);
        });
    });
    
    const excelData = [headers];
    
    // Procesar cada usuario
    response.users.forEach((user) => {
        const row = [
            user.userid, user.firstname, user.lastname, 
            user.email, user.customer, user.group, user.billid
        ];
        
        rooms.forEach((room) => {
            // Check for floor and house keys
            const floorKey = `floor_${room}`;
            const houseKey = `house_${room}`;
            
            // Prioritize floor if both exist (or vice versa)
            const isOccupied = 
                (user.rooms[floorKey] && roomType === 'floor') || 
                (user.rooms[houseKey] && roomType === 'house' && !user.rooms[floorKey]);
            
            row.push(isOccupied ? "OCCUPIED" : "");
        });
        
        excelData.push(row);
    });
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    
    // Aplicar formato condicional (same as before)
    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let row = 1; row <= range.e.r; row++) {
        for (let col = 7; col <= range.e.c; col++) {
            const cellAddress = XLSX.utils.encode_cell({r: row, c: col});
            if (ws[cellAddress]?.v === 'OCCUPIED') {
                ws[cellAddress].s = {
                    fill: { patternType: 'solid', fgColor: { rgb: 'FFCCCC' } },
                    font: { color: { rgb: '990000' }, bold: true }
                };
            } else {
                ws[cellAddress].s = {
                    fill: { patternType: 'solid', fgColor: { rgb: 'E0FFE0' } }
                };
            }
        }
    }
    
    // Congelar primera fila y columnas de usuario
    ws['!freeze'] = { xSplit: 7, ySplit: 1, topLeftCell: 'H2' };
    
    // Añadir hoja al libro
    XLSX.utils.book_append_sheet(wb, ws, sheetName);
};

const createSummarySheet = (wb, response) => {
    const headers = [
        'ID User', 'Name', 'Lastname', 'Email',
        'Project', 'Group', 'Billid', 'Accommodation type',
        'Location', 'Number', 'Status'
    ];
    
    const excelData = [headers];
    
    response.users.forEach(user => {
        // Buscar habitaciones ocupadas
        Object.entries(user.rooms).forEach(([roomNumber, isOccupied]) => {
            type=roomNumber.split('_')[0];
            roomNumber = parseInt(roomNumber.match(/\d+/)[0]);

            if (isOccupied) {
                const roomInfo = getRoomInfo(type, response.room_types, roomNumber);
                
                excelData.push([
                    user.userid,
                    user.firstname,
                    user.lastname,
                    user.email,
                    user.customer,
                    user.group,
                    user.billid,
                    roomInfo.type === 'floor' ? 'Floor' : 'House',
                    roomInfo.type === 'floor' ? `Floor ${roomInfo.key}` : `House ${roomInfo.key}`,
                    roomNumber,
                    'OCCUPIED'
                ]);
            }
        });
    });
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    
    // Añadir filtros
    ws['!autofilter'] = { ref: XLSX.utils.encode_range({
        s: { r: 0, c: 0 },
        e: { r: excelData.length, c: headers.length - 1 }
    })};
    
    XLSX.utils.book_append_sheet(wb, ws, 'Summary');
};

// Función auxiliar para obtener información de la habitación
const getRoomInfo = (type, roomDefinitions, roomNumber) => {
    if (type==='floor'){
        // Buscar en plantas
        for (const [floor, rooms] of Object.entries(roomDefinitions.floor.rooms)) {
            if (rooms.includes(roomNumber)) {
                return { type: 'floor', key: floor+1 };
            }
        }
    }
    
    if (type==='house'){
        // Buscar en casas
        for (const [house, rooms] of Object.entries(roomDefinitions.house.rooms)) {
            if (rooms.includes(roomNumber)) {
                return { type: 'house', key: house+1 };
            }
        }
    }
    
    
    return { type: 'unknown', key: '' };
};