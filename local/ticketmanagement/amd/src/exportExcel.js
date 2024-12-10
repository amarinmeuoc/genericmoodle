
export const init=(XLSX, filesaver,blobutil)=>{
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    const boexport=document.querySelector('#id_exportExcel');
    const boexportActions=document.querySelector('#id_exportExcelActions');
    boexport.addEventListener('click',(e)=>{
        exportToExcel(e,XLSX,filesaver,blobutil, url);
        
    });
    boexportActions.addEventListener('click',(e)=>{
        const startdate=document.querySelector('#startdate').value;
        const startdateValue = new Date(startdate);

        // Obtén el valor en formato Unix (milisegundos desde 1970) y conviértelo a segundos
        const startdate_unixTimestamp = Math.floor(startdateValue.getTime() / 1000);

        const enddate=document.querySelector('#enddate').value;

        // Convierte el valor de la fecha a un objeto Date
        const enddateValue = new Date(enddate);

        // Obtén el valor en formato Unix (milisegundos desde 1970) y conviértelo a segundos
        const enddate_unixTimestamp = Math.floor(enddateValue.getTime() / 1000);

        const selstate=document.querySelector('#id_state').value;
        const gestorvalue=document.querySelector('#id_logistic').value;

        exportToExcelActions(startdate_unixTimestamp,enddate_unixTimestamp,selstate,gestorvalue,token,url);
    });
}

const exportToExcelActions=(startdate,enddate,state,gestor,token,url)=>{
    const xhr=new XMLHttpRequest();
    
    xhr.open('POST',url,true);
 
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','local_ticketmanagement_get_ticket_actions_excel');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][startdate]',startdate);
    formData.append('params[0][enddate]',enddate);
    formData.append('params[0][state]',state);
    formData.append('params[0][gestor]',gestor);
    
    
    setTimeout(()=>{
        xhr.send(formData);
    },100);
    
    xhr.onload=(event)=>{
        onLoadFunctionActions(xhr);
    }

    xhr.onloadstart=(event)=>{
        showLoader(event);
    }

    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        hideLoader(event);
    }
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
    const showLoader=(event)=>{
        const loader=document.querySelector('.excel_loader');
        loader.classList.remove('hide');
        loader.classList.add('show');
        const boExcel=document.querySelector('#id_exportExcel').disabled=true;
      }
      
      const hideLoader=(event)=>{
        const loader=document.querySelector('.excel_loader');
        loader.classList.remove('show');
        loader.classList.add('hide');
        const boExcel=document.querySelector('#id_exportExcel').disabled=false;
        
      }

}

const onLoadFunctionActions=(myXhr)=>{
    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        
        createExcelFromJSON(res.listofactions,'actionReport');
        
        
    }
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

    const selstate=document.querySelector('#id_state').value;
    const gestorvalue=document.querySelector('#id_logistic').value;
    
    const data={
        startdate:startdate_unixTimestamp,
        enddate:enddate_unixTimestamp,
        state:selstate,
        gestor:gestorvalue
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
    formData.append('params[0][state]',data.state);
    formData.append('params[0][gestor]',data.gestor);
    
    
    setTimeout(()=>{
        xhr.send(formData);
    },100);
    
    xhr.onload=(event)=>{
        onLoadFunction(xhr);
    }

    xhr.onloadstart=(event)=>{
        showLoader(event);
    }

    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        hideLoader(event);
    }
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
    const showLoader=(event)=>{
        const loader=document.querySelector('.excel_loader');
        loader.classList.remove('hide');
        loader.classList.add('show');
        const boExcel=document.querySelector('#id_exportExcel').disabled=true;
      }
      
      const hideLoader=(event)=>{
        const loader=document.querySelector('.excel_loader');
        loader.classList.remove('show');
        loader.classList.add('hide');
        const boExcel=document.querySelector('#id_exportExcel').disabled=false;
        
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
    
    if (op==='ticketReport'){
        // Generar títulos basados en las claves del primer objeto
        const titles = Object.keys(res.listadoTickets[0]);
        listado.push(titles);

        // Convertir cada objeto a un array de valores y añadirlo al listado
        const ticketsArray = res.listadoTickets.map(ticket => [
            ticket.ticketnumber,
            ticket.category,
            ticket.subcategory,
            ticket.customer,
            ticket.vessel,
            ticket.billid,
            ((/webservice/i).test(ticket.username))?'no user':ticket.username,
            ticket.familyissue,
            ticket.familiarname,
            ticket.familiar_role,
            formatUnixToDateTime(ticket.date),
            formatUnixToDateTime(ticket.lastdate),
            ticket.state,
            ticket.description,
            ticket.priority,
            ticket.label,
            ticket.assigned
        ]);
        listado = listado.concat(ticketsArray);
    } else if (op==='actionReport') {
        window.console.log("hola");
        // Generar títulos basados en las claves del primer objeto
        const titles = Object.keys(res[0]);
        listado.push(titles);

        // Convertir cada objeto a un array de valores y añadirlo al listado
        const actionArray = res.map(action => [
            action.action,
            action.internal,
            formatUnixToDateTime(action.dateaction),
            action.user,
            action.ticketid,
        ]);
        listado = listado.concat(actionArray);
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
        Title: (op==='ticketReport')?"List of tickets":'actionReport',
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year, month - 1, dateFile) // Ajuste del mes a base 0
    };

    // Añadir una hoja de Excel con el nombre "TicketsReport"
    const pageTitle=(op==='ticketReport')?'TicketsReport':'ActionsReport';
    wb.SheetNames.push(pageTitle);
    const ws = XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets[pageTitle] = ws;

    // Generar y descargar el archivo
    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
    const nameFile = `${pageTitle}-${dateFile}.${month}.${year}-${hour}.${min}.xlsx`;
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


