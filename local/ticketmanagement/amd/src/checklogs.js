import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {get_string as getString} from 'core/str';

const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;
export const init =() => {
    
    const logs=document.querySelectorAll('.logs');

    logs.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            showTicketActions(e);
        })
    })
}

const showTicketActions=(e)=>{
    e.stopPropagation();
    const ticketId=e.target.dataset.ticketid;
    const modalForm=new ModalForm({
        formClass: "\\local_ticketmanagement\\form\\ActionsFormPopup",
        args: {num_ticket: ticketId},
        modalConfig: {title: `Ticket details: #${ticketId}`},
        returnFocus:e.target
    });

    modalForm.show();


    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
        //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
    });

    // Listen for the modal LOADED event
    modalForm.addEventListener(modalForm.events.LOADED, (e) => {
        // Get the button after the modal is fully loaded
        // Get the modal element after it is loaded
        const formElement=e.target;

        areElementsLoaded('button[name="boExcel"]', formElement).then((elements) => {
            
            const boexport=formElement.querySelector('button[name="boExcel"]');
            boexport.addEventListener('click',(e)=>{
                const ticketDiv=formElement.querySelector('div[data-name="ticketid"]');
                const ticketid=ticketDiv.textContent.trim();
                loadActions(ticketid,url,token);
            })
        }).catch((error) => {
            window.console.error('Error al cargar los elementos select:', error);
        });
    });
}

const loadActions=(ticketid, url,token)=>{
    const xhr=new XMLHttpRequest();
    
    xhr.open('POST',url,true);
 
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','local_ticketmanagement_load_actions');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][ticketid]',ticketid);
       
    
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
        createExcelFromJSON(res,'logsReport');
        window.console.log(res);
        
    }
}

const createExcelFromJSON = (res, op) => {
    let listado = [];
    
    // Generar títulos basados en las claves del primer objeto
    const titles = Object.keys(res[0]);
    listado.push(titles);

    // Convertir cada objeto a un array de valores y añadirlo al listado
    const actionArray = res.map(action => [
        action.id,
        action.action,
        parseDate(action.dateaction),
        action.user,
        action.ticketid,
    ]);
    listado = listado.concat(actionArray);

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
        Title: "List of actions",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year, month - 1, dateFile) // Ajuste del mes a base 0
    };

    // Añadir una hoja de Excel con el nombre "TicketsReport"
    wb.SheetNames.push("LogsReport");
    const ws = XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets["LogsReport"] = ws;

    // Generar y descargar el archivo
    const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
    const nameFile = `LogsReport-${listado[1].ticketid}.xlsx`;
    saveAs(new Blob([s2ab(wbout)], { type: "application/octet-stream" }), nameFile);
};

// Función auxiliar para convertir la cadena de datos binarios a ArrayBuffer
const s2ab = (s) => {
    const buf = new ArrayBuffer(s.length);
    const view = new Uint8Array(buf);
    for (let i = 0; i != s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
};

const parseDate = (str) => {
    const partesFecha = str.split(" "); // Separamos la fecha y la hora
    const partesFechaHora = partesFecha[0].split("-"); // Separamos el día, mes y año
    const partesHora = partesFecha[1].split(":"); // Separamos la hora y los minutos

    const fecha = new Date(
        partesFechaHora[2], // Año
        partesFechaHora[1] - 1, // Mes (0-based)
        partesFechaHora[0], // Día
        partesHora[0], // Hora
        partesHora[1] // Minutos
    );
    return fecha;
    

    return date;
};


  const areElementsLoaded = (selector, parentElement = document) => {
    return new Promise((resolve) => {
        const checkElements = () => {
            const elements = parentElement.querySelectorAll(selector);
            if (elements.length > 0 && Array.from(elements).every(elem => elem !== null)) {
                resolve(elements);
            } else {
                requestAnimationFrame(checkElements);
            }
        };
        checkElements();
    });
};