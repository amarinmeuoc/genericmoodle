import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
const url=M.cfg.wwwroot+'/webservice/rest/server.php';

export const init = () => {
    //Aseguramos que el token esté cargado y el selector de cliente tambien
    areElementsLoaded('#filterformid input, #filterformid select').then((elements) => {
        //Se obtienen los datos a enviar
        const token=document.querySelector('input[name="token"]').value;
        const orderby = document.querySelector('input[name="orderby"]');
        const order = document.querySelector('input[name="order"]');
        const day=document.querySelector('#id_assesstimefinish_day').value;
        const month=document.querySelector('#id_assesstimefinish_month').value;
        const year=document.querySelector('#id_assesstimefinish_year').value;
        const customerid=document.querySelector('#id_selcustomer').value;
        const groupid=document.querySelector('#id_selgroup').value;; // El 0 representa todos los grupos
        const billid=document.querySelector('#id_tebillid').value;
        //Obtener la fecha en formato unix a partir de day month y year con el formato: d-m-Y
        const date=new Date(year,month-1,day);
        const unixtime=date.getTime()/1000;
        requestResultFromServer(url,token, orderby.value, order.value, unixtime, customerid, groupid, billid);

        //Se añade el evento para el botón de filtrar
        const bofilter=document.querySelector('#id_bosubmit');
        bofilter.addEventListener('click',()=>{
            //Al volver a filtrar se reinincia el orden y la dirección
            orderby.value='startdate';
            order.value=1;
            const groupid=document.querySelector('#id_selgroup').value;; // El 0 representa todos los grupos
            const billid=document.querySelector('#id_tebillid').value;
            const day=document.querySelector('#id_assesstimefinish_day').value;
            const month=document.querySelector('#id_assesstimefinish_month').value;
            const year=document.querySelector('#id_assesstimefinish_year').value;
            const customerid=document.querySelector('#id_selcustomer').value;
            const date=new Date(year,month-1,day);
            const unixtime=date.getTime()/1000;
            requestResultFromServer(url,token, orderby.value, order.value, unixtime, customerid, groupid, billid);
        });
    });

}

// Función para realizar la petición al servidor
const requestResultFromServer = (url, token, orderby, order, unixtime, customerid, groupid, billid) => {
    const data = {
        wstoken: token,
        wsfunction: 'report_partialplan_get_training_plan',
        moodlewsrestformat: 'json',
        'params[0][orderby]': orderby,
        'params[0][order]': order,
        'params[0][unixtime]': parseInt(unixtime),
        'params[0][customerid]': customerid,
        'params[0][groupid]': groupid, 
        'params[0][billid]': billid
    };

    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(data),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    })
    .then(response => response.json())
    .then(response => {
        if (response.exception) {
            displayException(response);
        } else {
            const responseData = response[0];
            
            Templates.renderForPromise('report_partialplan/content_con-ajax', responseData).then(({html, js}) => {
                const content = document.querySelector('#content');
                content.innerHTML = '';

                Templates.appendNodeContents(content,html,js);
            });

        }
    })
    .catch((error) => {
        displayException(error);
        window.console.log('Error:', error);
    });
}

// Función para verificar que todos los elementos seleccionados estén cargados
const areElementsLoaded = (selector) => {
    return new Promise((resolve) => {
        const checkElements = () => {
            const elements = document.querySelectorAll(selector);
            if (elements.length > 0 && Array.from(elements).every(elem => elem !== null)) {
                resolve(elements);
            } else {
                requestAnimationFrame(checkElements);
            }
        };
        checkElements();
    });
  };