
export const init=(XLSX, filesaver,blobutil)=>{
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    const boexport=document.querySelector('#id_exportExcelFamily');
    boexport.addEventListener('click',(e)=>{
        exportToExcel(e,XLSX,filesaver,blobutil, url);
        
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil, url)=>{
    
    const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const groupid = document.querySelector('#id_vessel').value;
        const customerid= document.querySelector('#id_project').value;
        const billid=document.querySelector('#tebillid').value;
        const nombre=document.querySelector('#tenombre').value;
        const apellidos=document.querySelector('#teapellidos').value;
                
        const obj={
          activePage:1,
          order:order,
          orderby:orderby,
          page:newPage.value,
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

    const service= 'local_ticketmanagement_get_list_families';

    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', service);
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',obj.customerid);
    formData.append('params[0][groupid]',obj.groupid);
    formData.append('params[0][order]',obj.order);
    formData.append('params[0][orderby]',obj.orderby);
    formData.append('params[0][page]',obj.page);
    formData.append('params[0][activePage]',obj.activePage);
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
        //self.showLoader(event);
        }

    xhr.onprogress = (event)=>{
        //self.onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        //self.hideLoader(event);
    }

    xhr.onerror = ()=> {
        //self.rejectAnswer(xhr);
    }

}

const onLoadFunction=(myXhr)=>{
   
    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        window.console.log(res);
        createExcelFromJSON(res,'familyReport');
        
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
    
    if (res.family_list && res.family_list.length>0){
        // Generar títulos basados en las claves del primer objeto
        const titles = Object.keys(res.family_list[0]);
        listado.push(titles);

        // Convertir cada objeto a un array de valores y añadirlo al listado
        const usersArray = res.family_list.map(user => [
            user.id,
            user.vessel,
            user.billid,
            user.firstname,
            user.lastname,
            user.email,
            user.family_role,
            user.family_firstname,
            user.family_lastname,
            user.family_nie,
            formatUnixToDateTime(user.family_birthdate),
            user.family_adeslas,
            user.family_phone1,
            user.family_email,
            formatUnixToDateTime(user.family_arrival),
            formatUnixToDateTime(user.family_departure),
            user.family_notes
        ]);
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
        Title: "List of familiars",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year, month - 1, dateFile) // Ajuste del mes a base 0
    };

    // Añadir una hoja de Excel con el nombre "FamilyReport"
    wb.SheetNames.push("FamilyReport");
    const ws = XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets["FamilyReport"] = ws;

    // Generar y descargar el archivo
    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
    const nameFile = `FamilyReport-${dateFile}.${month}.${year}-${hour}.${min}.xlsx`;
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
        date.setDate(date.getDate() + 1); // Sumar un día
    }
    return date;
};

