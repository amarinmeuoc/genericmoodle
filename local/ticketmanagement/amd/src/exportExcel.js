
export const init=(XLSX, filesaver,blobutil)=>{
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    const boexport=document.querySelector('#id_exportExcel');
    boexport.addEventListener('click',(e)=>{
        exportToExcel(e,XLSX,filesaver,blobutil, url);
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil, url)=>{
    
    const startdatetxt=document.querySelector('#startdate');
    // Convierte el valor de la fecha a un objeto Date
    const startdateValue = new Date(startdatetxt.value);

    // Obtén el valor en formato Unix (milisegundos desde 1970) y conviértelo a segundos
    const startdate_unixTimestamp = Math.floor(startdateValue.getTime() / 1000);

    const enddatetxt=document.querySelector('#enddate');
    // Convierte el valor de la fecha a un objeto Date
    const enddateValue = new Date(enddatetxt.value);

    // Obtén el valor en formato Unix (milisegundos desde 1970) y conviértelo a segundos
    const enddate_unixTimestamp = Math.floor(enddateValue.getTime() / 1000);
    
    const data={
        startdate:startdate_unixTimestamp,
        enddate:enddate_unixTimestamp,
    }
    prepareDataToSend(data, url,token);
}

const prepareDataToSend=(data, url,token)=>{
    const xhr=new XMLHttpRequest();
    
    xhr.open('POST',url,true);
 
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','local_ticketmanagement_get_tickets_excel');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][startdate]',data.startdate);
    formData.append('params[0][enddate]',data.enddate);
    
    
    setTimeout(()=>{
        xhr.send(formData);
    },100);
    
    xhr.onload=(event)=>{
        onLoadFunction(xhr);
    }

    xhr.onloadstart=(event)=>{
        //showLoader(event);
    }

    xhr.onprogress = (event)=>{
        //onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        //hideLoader(event);
    }
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
    const showLoader=(event)=>{
        const loader=document.querySelector('.loader');
        const table=document.querySelector('.generaltable');
        loader.classList.remove('hide');
        loader.classList.add('show');
        table.classList.add('hide');
      
      }
      
      const hideLoader=(event)=>{
        const loader=document.querySelector('.loader');
        const table=document.querySelector('.generaltable');
        loader.classList.remove('show');
        loader.classList.add('hide');
        table.classList.remove('hide');
      }

}

const onLoadFunction=(myXhr)=>{
    /*const loader=document.querySelector('.loader');
    loader.classList.add('.hide');
    loader.classList.remove('.show');
*/
    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        createExcelFromJSON(res,'ticketReport');
        
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
    
    // Generar títulos basados en las claves del primer objeto
    const titles = Object.keys(res.listadoTickets[0]);
    listado.push(titles);

    // Convertir cada objeto a un array de valores y añadirlo al listado
    const ticketsArray = res.listadoTickets.map(ticket => [
        ticket.ticketnumber,
        ticket.username,
        ticket.familyissue,
        formatUnixToDateTime(ticket.date),
        ticket.state,
        ticket.description,
        ticket.priority,
        ticket.assigned
    ]);
    listado = listado.concat(ticketsArray);

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
        Title: "List of tickets",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year, month - 1, dateFile) // Ajuste del mes a base 0
    };

    // Añadir una hoja de Excel con el nombre "TicketsReport"
    wb.SheetNames.push("TicketsReport");
    const ws = XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets["TicketsReport"] = ws;

    // Generar y descargar el archivo
    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
    const nameFile = `TicketsReport-${dateFile}.${month}.${year}-${hour}.${min}.xlsx`;
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
    const date = new Date(unixTimestamp * 1000); // Convertir de segundos a milisegundos
    

    return date;
};
