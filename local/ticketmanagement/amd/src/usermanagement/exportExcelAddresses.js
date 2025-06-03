
export const init=(XLSX, filesaver,blobutil)=>{
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    const boexport=document.querySelector('#id_exportExcelAddresses');
    boexport.addEventListener('click',(e)=>{
        exportToExcel(e,XLSX,filesaver,blobutil, url); 
        
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil, url)=>{
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const groupid = document.querySelector('#id_vessel').value;
        const customerid= document.querySelector('#id_project').value;
        const billid=document.querySelector('#tebillid').value;
        const nombre=document.querySelector('#tenombre').value;
        const apellidos=document.querySelector('#teapellidos').value;
                
        const obj={
          
          order:order,
          orderby:orderby,
          
          groupid:groupid,
          customerid:customerid,
          billid:billid,
          nombre:nombre,
          apellidos:apellidos
        }

    prepareDataToSend(obj, url,token);
}

const prepareDataToSend=(obj, url,token)=>{
    let xhr = new XMLHttpRequest();

    const service= 'local_ticketmanagement_get_list_addresses_excel';

    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', service);
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',obj.customerid);
    formData.append('params[0][groupid]',obj.groupid);
    formData.append('params[0][order]',obj.order);
    formData.append('params[0][orderby]',obj.orderby);
    
    if ('billid' in obj){
      formData.append('params[0][billid]',obj.billid);
    }
    if ('nombre' in obj){
      formData.append('params[0][firstname]',obj.nombre);
    }
    if ('apellidos' in obj){
      formData.append('params[0][lastname]',obj.apellidos);
    }
    
    
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
        
        const boexport=document.querySelector('#id_exportExcelAddresses');

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
        
        const boexport=document.querySelector('#id_exportExcelAddresses');

        if (boexport)
            boexport.disabled=false;
    }

    xhr.onerror = ()=> {
        window.console.error('Network Error: Unable to send the request.');

        // Opcional: Restablecer el estado de la interfaz
        const loader = document.querySelector('.excel_loader');
        loader.classList.remove('show');
        loader.classList.add('hide');

        const boexport = document.querySelector('#id_exportExcelAddresses');
        if (boexport) boexport.disabled = false;
    }

}

const onLoadFunction=(myXhr)=>{
   
    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        
        createExcelFromJSON(res,'addressesReport');
        
    }
}

const onProgressFunction=(event) =>{
    console.log(`Uploaded ${event.loaded} of ${event.total}`);
    const loader=document.querySelector('.loader');
    loader.classList.remove('.hide');
    loader.classList.add('.show');
}

const createExcelFromJSON = (res, op) => {
    let listado = [];
    
    if (res.address_list && res.address_list.length>0){
        // Generar títulos basados en las claves del primer objeto
        const titles = Object.keys(res.address_list[0]);
        listado.push(titles);

        // Convertir cada objeto a un array de valores y añadirlo al listado
        const usersArray = res.address_list.map(user => {
            // Hacer una copia del usuario para no modificar el original
            const modifiedUser = { ...user };
            // Aplicar reglas condicionales
            if (modifiedUser.type === 'Internal') {
                if (modifiedUser.address === 'Carraca -Cuatro Torres-') {
                    modifiedUser.floor += 1;
                    modifiedUser.house = '';
                } else if (modifiedUser.address === 'Carraca -Houses-') {
                    modifiedUser.floor = '';
                    modifiedUser.house += 1;
                }
            } else {
                modifiedUser.floor='';
                modifiedUser.house='';
            }

            return [
                modifiedUser.id,
                modifiedUser.vessel,
                modifiedUser.billid,
                modifiedUser.firstname,
                modifiedUser.lastname,
                modifiedUser.email,
                modifiedUser.type,
                modifiedUser.address,
                modifiedUser.house,
                modifiedUser.floor,
                modifiedUser.block,
                modifiedUser.door,
                modifiedUser.number,
                modifiedUser.town,
                formatUnixToDateTime(modifiedUser.entry_date),
                formatUnixToDateTime(modifiedUser.departure_date),
            ]
        });
        listado = listado.concat(usersArray);
    }
    

    // Crear un nuevo libro de Excel
    const wb = XLSX.utils.book_new();
    const dr = new Date();
    const dateFile = dr.getDate();
    const month = dr.getMonth() + 1;
    const year = dr.getFullYear();
    const min = dr.getMinutes();
    const hour = dr.getHours();

    // Configuración de propiedades del archivo
    wb.Props = {
        Title: "List of addresses",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year, month - 1, dateFile) // Ajuste del mes a base 0
    };

    // Añadir una hoja de Excel con el nombre "AddressReport"
    wb.SheetNames.push("AddressReport");
    const ws = XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets["AddressReport"] = ws;

    // Generar y descargar el archivo
    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
    const nameFile = `AddressReport-${dateFile}.${month}.${year}-${hour}.${min}.xlsx`;
    saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), nameFile);
};

// Función auxiliar para convertir la cadena de datos binarios a ArrayBuffer
const s2ab = (s) => {
    const buf = new ArrayBuffer(s.length);
    const view = new Uint8Array(buf);
    for (let i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
};

const formatUnixToDateTime = (unixTimestamp) => {
    let date='';
    if(unixTimestamp!==0){
        date = new Date(unixTimestamp * 1000); // Convertir de segundos a milisegundos
        date.setDate(date.getDate()); 
    }
    return date;
};

