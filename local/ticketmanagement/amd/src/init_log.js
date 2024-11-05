import {exception as displayException} from 'core/notification'; 
import Templates from 'core/templates';

export const loadTemplate =() => {
      //definicion de url
  const url=M.cfg.wwwroot+'/webservice/rest/server.php';

  areElementsLoaded('#id_project,#id_vessel,#id_userlist,#id_category, #id_subcategory,input[name="token"],#startdate,#enddate').then((elements) => {
    //Se obtienen los valores de los campos necesarios
    const token = document.querySelector('input[name="token"]').value;
    const orderby = document.querySelector('input[name="orderby"]').value;
    const order = document.querySelector('input[name="order"]').value;
    const page= document.querySelector('input[name="page"]').value;
    
    const dates=getFirstAndLastDayOfCurrentWeek();
    const firstDayOfWeek=truncateDateToDay(dates.firstDayOfWeek);
    const lastDayOfWeek=truncateDateToDay(dates.lastDayOfWeek);
    
    const startdate= document.querySelector('#startdate');
    const enddate= document.querySelector('#enddate');

    startdate.value=dates.firstDayOfWeek.toISOString().slice(0, 10);
    enddate.value=dates.lastDayOfWeek.toISOString().slice(0, 10);
    const activePage=1;
    
    const bosearchdate=document.querySelector('#id_bosearchdate');
    const bosearchbyid=document.querySelector('#id_bosearchbyid');

    bosearchdate.addEventListener('click',()=>{
      const newPage=document.querySelector('input[name="page"]');
      newPage.value=1;
      const newStartdate=new Date(Date.parse(startdate.value));
      const newEnddate=new Date(Date.parse(enddate.value));
      const newStartdateUnix=truncateDateToDay(newStartdate);
      const newEnddateUnix=truncateDateToDay(newEnddate);
      
      requestDataToServer(1,newStartdateUnix, newEnddateUnix, order, orderby, newPage.value, token, url);
    })

    bosearchbyid.addEventListener('click',()=>{
      const newPage=document.querySelector('input[name="page"]');
      newPage.value=1;
      const ticketNumber=document.querySelector('#id_tesearch').value.trim();
      
      
      requestDataToServerbyTicket(1,ticketNumber, order, orderby, newPage.value, token, url);
    })

    //Carga de los datos por defecto
    requestDataToServer(activePage,firstDayOfWeek, lastDayOfWeek, order, orderby, page, token, url);  

  });
}

const requestDataToServerbyTicket=(activePage,ticketId, order,orderby,page,token,url)=>{
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_get_ticket_byId');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][order]',order);
    formData.append('params[0][orderby]',orderby);
    formData.append('params[0][page]',page);
    formData.append('params[0][ticketId]',ticketId);
    formData.append('params[0][activePage]',activePage);
    
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerGetTicketById(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}


const requestDataToServer=(activePage,firstDayOfWeek, lastDayOfWeek, order,orderby,page,token,url)=>{
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_get_tickets');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][order]',order);
    formData.append('params[0][orderby]',orderby);
    formData.append('params[0][page]',page);
    formData.append('params[0][startdate]',firstDayOfWeek);
    formData.append('params[0][enddate]',lastDayOfWeek);
    formData.append('params[0][activePage]',activePage);
    
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerGetTickets(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const truncateDateToDay=(date) =>{
  // Create a new Date object with the year, month, and date from the input date.
  const truncatedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

  // Convert the truncated date to Unix time (milliseconds since the Unix epoch).
  const unixTime = truncatedDate.getTime() / 1000; // Divide by 1000 to get seconds

  return unixTime;
}


const getFirstAndLastDayOfCurrentWeek=()=> {
  const today = new Date();
  const dayOfWeek = today.getDay(); // 0 (Sunday) to 6 (Saturday)

  // Calculate the first day of the week (Monday)
  const firstDayOfWeek = new Date(today);
  firstDayOfWeek.setDate(today.getDate() - dayOfWeek );

  // Calculate the last day of the week (Sunday)
  const lastDayOfWeek = new Date(firstDayOfWeek);
  lastDayOfWeek.setDate(lastDayOfWeek.getDate() + 6);

  return { firstDayOfWeek, lastDayOfWeek };
}


const reqHandlerGetTickets=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
      let response=JSON.parse(xhr.response);
      response.pages=truncateArrayWithActiveMiddle(response.pages,8);
      window.console.log(response);
      
        loadTemplatefromResponse(response);
        
    }
 }
}

const reqHandlerGetTicketById=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
        const response=JSON.parse(xhr.response);
        loadTemplateSingleTicketfromResponse(response);
        window.console.log("response");
    }
 }
}

const loadTemplateSingleTicketfromResponse=(response)=>{
  //Render the choosen mustache template by Javascript
  Templates.renderForPromise('local_ticketmanagement/content_log-ajax',response)
  .then(({html,js})=>{
  const content=document.querySelector('#content');
  content.innerHTML='';
    Templates.appendNodeContents(content,html,js);

    const fila=document.querySelector('tbody>tr');
    if (typeof response.listadoTickets[0]!=='undefined')
      if (response.listadoTickets[0].state==='Closed' || response.listadoTickets[0].state==='Cancelled')
        fila.classList.add('cerrado');
    
    //Ahora que se ha cargado la plantilla, se puede añadir el evento a los enlaces de ordenación
    const orderlinks=document.querySelectorAll('.orderby');
    orderlinks.forEach((link)=>{
      link.addEventListener('click',(ev)=>{
        ev.preventDefault();

        //Se obtienen los valores de los campos necesarios
        const token = document.querySelector('input[name="token"]').value;
        const page= document.querySelector('input[name="page"]');

        //Obtenemos el ordenamiento y el campo de orden actuales
        const orderby = document.querySelector('input[name="orderby"]');
        const order = document.querySelector('input[name="order"]');

        //Si el campo de ordenamiento es el mismo que el actual, se cambia el orden
        if (ev.target.dataset.activo==='activo'){
          if (order.value==='1'){
            ev.target.dataset.order=0;
            order.value=0;
          } else {
            ev.target.dataset.order=1;
            order.value=1;
          }
        } else {
          orderlinks.forEach((link)=>{
            link.dataset.activo='';
          });
          ev.target.dataset.activo='activo';
          orderby.value=ev.target.dataset.orderby;
          ev.target.dataset.order=1;
          order.value=1;
        }

        

        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const ticketNumber=document.querySelector('#id_tesearch').value.trim();
      
      
      requestDataToServerbyTicket(1,ticketNumber, parseInt(order.value), orderby.value, newPage.value, token, url);
        
        
      });
    });

    const pages=document.querySelectorAll('.page-link');
    pages.forEach((page)=>{
      page.addEventListener('click',(ev)=>{
        ev.preventDefault();
        ev.stopPropagation();
        const token = document.querySelector('input[name="token"]');
        const page= document.querySelector('input[name="page"]');

        //Se obtiene el elemento padre del elemento clicado
        const padre=ev.currentTarget.parentElement;
        
        if (padre.dataset.control==='first' || padre.dataset.control==='last' || padre.dataset.control==='previous' || padre.dataset.control==='next'){
          //Si es la primera página se coge el valor de arial-label
          page.value=ev.currentTarget.getAttribute('aria-label');
        } else {
          page.value=ev.currentTarget.textContent.trim();
        }
        
        //Obtenemos el ordenamiento y el campo de orden actuales
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const activePage=1;
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const ticketNumber=document.querySelector('#id_tesearch').value.trim();
      
      
      requestDataToServerbyTicket(1,ticketNumber, parseInt(order), orderby, newPage.value, token.value, url);
      });
    });

  })
  .catch((error)=>displayException(error));
}

const loadTemplatefromResponse=(response)=>{
  //Render the choosen mustache template by Javascript
  Templates.renderForPromise('local_ticketmanagement/content_log-ajax',response)
  .then(({html,js})=>{
  const content=document.querySelector('#content');
  content.innerHTML='';
    Templates.appendNodeContents(content,html,js);
    
    //Ahora que se ha cargado la plantilla, se puede añadir el evento a los enlaces de ordenación
    const orderlinks=document.querySelectorAll('.orderby');
    orderlinks.forEach((link)=>{
      link.addEventListener('click',(ev)=>{
        ev.preventDefault();

        //Se obtienen los valores de los campos necesarios
        const token = document.querySelector('input[name="token"]').value;
        const page= document.querySelector('input[name="page"]');

        //Obtenemos el ordenamiento y el campo de orden actuales
        const orderby = document.querySelector('input[name="orderby"]');
        const order = document.querySelector('input[name="order"]');

        //Si el campo de ordenamiento es el mismo que el actual, se cambia el orden
        if (ev.target.dataset.activo==='activo'){
          if (order.value==='1'){
            ev.target.dataset.order=0;
            order.value=0;
          } else {
            ev.target.dataset.order=1;
            order.value=1;
          }
        } else {
          orderlinks.forEach((link)=>{
            link.dataset.activo='';
          });
          ev.target.dataset.activo='activo';
          orderby.value=ev.target.dataset.orderby;
          ev.target.dataset.order=1;
          order.value=1;
        }

        const activePage=document.querySelector('li.page-item.active>a').textContent.trim();

        const startdate= document.querySelector('#startdate');
        const enddate= document.querySelector('#enddate');

        const startdateUnixFormat=truncateDateToDay(new Date(startdate.value));
        const enddateUnixFormat=truncateDateToDay(new Date(enddate.value));
        
        requestDataToServer(activePage,startdateUnixFormat, enddateUnixFormat, parseInt(order.value),orderby.value,page.value,token,url);
      });
    });

    const pages=document.querySelectorAll('.page-link');
    pages.forEach((page)=>{
      page.addEventListener('click',(ev)=>{
        ev.preventDefault();
        ev.stopPropagation();
        const token = document.querySelector('input[name="token"]');
        const page= document.querySelector('input[name="page"]');

        //Se obtiene el elemento padre del elemento clicado
        const padre=ev.currentTarget.parentElement;
        
        if (padre.dataset.control==='first' || padre.dataset.control==='last' || padre.dataset.control==='previous' || padre.dataset.control==='next'){
          //Si es la primera página se coge el valor de arial-label
          page.value=ev.currentTarget.getAttribute('aria-label');
        } else {
          page.value=ev.currentTarget.textContent.trim();
        }
        
        //Obtenemos el ordenamiento y el campo de orden actuales
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const activePage=parseInt(page.value);
        const startdate= document.querySelector('#startdate');
        const enddate= document.querySelector('#enddate');

        const startdateUnixFormat=truncateDateToDay(new Date(startdate.value));
        const enddateUnixFormat=truncateDateToDay(new Date(enddate.value));

        requestDataToServer(activePage,startdateUnixFormat, enddateUnixFormat,parseInt(order), orderby, parseInt(page.value), token.value, url);
      });
    });

    
    

  })
  .catch((error)=>displayException(error));
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

  function truncateArrayWithActiveMiddle(arr, maxLength) {
    const activeIndex = arr.indexOf(arr.find(item => item.active)); // Combine find and indexOf
  
    // Handle cases where there's no active element or less than maxLength elements
    if (activeIndex === -1 || arr.length <= maxLength) {
      return arr;
    }
  
    // Similar logic to calculate before and after lengths
    const halfLength = Math.floor(maxLength / 2);
    const beforeLength = Math.min(halfLength, activeIndex);
    const afterLength = Math.min(halfLength, arr.length - activeIndex - 1);
  
    // Use a loop to iterate and build the truncated array
    const truncatedArray = [];
    for (let i = activeIndex - beforeLength; i <= activeIndex + afterLength; i++) {
      if (i >= 0 && i < arr.length) { // Ensure we stay within array bounds
        truncatedArray.push(arr[i]);
      }
    }
  
    return truncatedArray;
  }