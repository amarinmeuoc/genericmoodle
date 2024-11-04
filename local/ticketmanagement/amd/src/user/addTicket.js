import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {get_string as getString} from 'core/str';
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';


const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;
let eventoCat="";
let eventoSubCat="";

let eventoPriority="";

export const init =() => {
    const bonewticket=document.querySelector("#id_bocreate");
    bonewticket.addEventListener('click',(e)=>{
        e.preventDefault();
        e.stopImmediatePropagation();
        
        const trainee=document.querySelector('input[type="hidden"][name="user"]').value;
        const category=(document.querySelector('#id_category').value)?document.querySelector('#id_category').value:0;
        const subcategory=(document.querySelector('#id_subcategory').value)?document.querySelector('#id_subcategory').value:0;
        
        
        const familyissue=document.querySelector('#id_familyissue').value;
        const description=document.querySelector('textarea[name="description[text]"]').value;
        
        let familiar=trainee;
        if (familyissue==='yes'){
          if (document.querySelector('#id_familiar').value!=='')
            familiar=document.querySelector('#id_familiar').value;
        }
        const newTicket={
            
            traineeid:trainee,
            categoryid:category,
            subcategoryid:subcategory,
            state:"Open",
            priority:"Medium",
            description:description,
            
            familiarid:familiar
        };

        if (category!==0 && subcategory!==0)
          createNewTicket(newTicket);
        else{
          Notification.addNotification({message:'Error: No categories have been defined yet. No ticket has been inserted',type:'error'});
        }

    })
}

const createNewTicket=(newTicket)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_add_ticket');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][subcategoryid]',newTicket.subcategoryid);
    formData.append('params[0][traineeid]',newTicket.traineeid);
    formData.append('params[0][state]',newTicket.state);
    formData.append('params[0][priority]',newTicket.priority);
    formData.append('params[0][description]',newTicket.description);
    
    formData.append('params[0][familiarid]',newTicket.familiarid);

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerNewTicket(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerNewTicket = (xhr) => {
  if (xhr.readyState === 4 && xhr.status === 200) {
      if (xhr.response) {
          const response = JSON.parse(xhr.response);

          if (response) {
              window.console.log(response);
              addToast('Ticket created successfully: ' + response.id);

              // Actualiza el número de tickets y las páginas
              let numRecords = parseInt(document.querySelector('#num_records').textContent.trim()) + 1;
              let numPages = Math.ceil(numRecords / 25); // Cálculo de las páginas necesarias
              let currentPage = numPages;

              // Actualiza la lista de páginas en el paginador
              const pageList = [];
              let pagePrevious = Math.max(1, currentPage - 1);
              let pageNext = currentPage + 1;
              if (numPages===1){
                pageNext=1;
              }
              
              
              for (let i = 1; i <= numPages; i++) {
                  pageList.push({
                      page: i,
                      active: i === currentPage
                  });
              }

              const formattedResponse = {
                  listadoTickets: [{
                      ticketnumber: response.id,
                      username: response.username,
                      familyissue: response.familyissue,
                      state: response.state,
                      priority: response.priority,
                      assigned: 'Waiting to be assigned.',
                      date: response.dateticket,
                      description: response.description,
                      familyissue: response.familyissue
                  }],
                  num_records: numRecords,
                  num_total_records: numRecords,
                  num_pages: numPages,
                  pages: pageList,
                  previous: [{
                      page: pagePrevious,
                      url: '#'
                  }],
                  next: [{
                      page: pageNext,
                      url: '#'
                  }],
                  first: [{
                      page: 1,
                      url: '#'
                  }],
                  last: [{
                      page: numPages,
                      url: '#'
                  }],
              };

              addTickettoTemplate(formattedResponse);
          } else {
              addToast('Something went wrong. No ticket created');
          }
      }
  }
};


function addTickettoTemplate(response){
    //Render the choosen mustache template by Javascript
    Templates.renderForPromise('local_ticketmanagement/tr_st-ajax',response)
    .then(({html,js})=>{
      const content=document.querySelector('#tablebody');
      const numRecords = document.querySelectorAll('.tickets').length;
      
      // Si el número total de registros supera los 25, limpiar la tabla para una nueva página
      if (numRecords >= 25) {
        content.innerHTML = '';  // Limpiar solo cuando se supere el límite de 25 registros
      }

      Templates.appendNodeContents(content,html,js);
      const newTicket=document.querySelectorAll('.tickets')[document.querySelectorAll('.tickets').length-1];
      newTicket.addEventListener('click',(e)=>{
        
          showTicketFormPopup(e);
        
      });

      const logs=document.querySelectorAll('.logs')[document.querySelectorAll('.logs').length-1];

    
        logs.addEventListener('click',(e)=>{
          const filaPadre=e.target.closest('tr');
          
            showTicketActions(e);
          
        })
   


      
      //En caso de que se superen 25 tickets por página, se crea una nueva página y añade el registro ahí


      Templates.renderForPromise('local_ticketmanagement/pagebar_log-ajax',response)
      .then(({html,js})=>{
        const content=document.querySelector('#pagination');
        content.innerHTML='';
        Templates.appendNodeContents(content,html,js);


        const pages=document.querySelectorAll('.page-link');
        pages.forEach((page)=>{
          page.addEventListener('click',(ev)=>{
            window.console.log('adleante');
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
        
      }).catch((error)=>displayException(error));
    }).catch((error)=>displayException(error));
  }

  const truncateDateToDay=(date) =>{
    // Create a new Date object with the year, month, and date from the input date.
    const truncatedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  
    // Convert the truncated date to Unix time (milliseconds since the Unix epoch).
    const unixTime = truncatedDate.getTime() / 1000; // Divide by 1000 to get seconds
  
    return unixTime;
  }

  const requestDataToServer=(activePage,firstDayOfWeek, lastDayOfWeek, order,orderby,page,token,url)=>{
    let xhr = new XMLHttpRequest();
      
      //Se prepara el objeto a enviar
      const formData= new FormData();
      formData.append('wstoken',token);
      formData.append('wsfunction', 'local_ticketmanagement_get_tickets_byUserId');
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

  const reqHandlerGetTickets=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
      if (xhr.response){
          const response=JSON.parse(xhr.response);
          loadTemplatefromResponse(response);
          
      }
   }
  }
  
  const loadTemplatefromResponse=(response)=>{
    //Render the choosen mustache template by Javascript
    Templates.renderForPromise('local_ticketmanagement/content_user-ajax',response)
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

  const showTicketFormPopup=(e)=>{
    e.stopPropagation();
    const ticketId=e.target.textContent.trim();
    
    const modalForm=new ModalForm({
        formClass: "\\local_ticketmanagement\\form\\TicketFormPopupStudent",
        args: {num_ticket: ticketId},
        modalConfig: {title: `Ticket details: #${ticketId}`},
        returnFocus:e.target
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
         //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
         const formElement=e.target;
         const subcategoryValue = formElement.querySelector('select[name="subcategory"]')?.value;
         window.console.log(e.detail);
         if (subcategoryValue) {
             e.detail.subcategory = subcategoryValue;
         }
 
 
         //updateTicket(e.detail,token,url);
    });

    modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
        // Obtener el formulario modal después de que se ha cargado
        const formElement = e.target;
        window.console.log("disabled...");
        // Usa la función areElementsLoaded para esperar hasta que los selectores estén cargados en el DOM
        areElementsLoaded('select[name="category"], select[name="subcategory"]', formElement).then((elements) => {

            // Una vez que los selectores están disponibles, los seleccionamos
            const categorySelect = formElement.querySelector('select[name="category"]');
            const subcategorySelect = formElement.querySelector('select[name="subcategory"]');
            const priority=formElement.querySelector('select[name="priority"]');
            priority.addEventListener('change',(e)=>{
              eventoPriority=e.target.value
            })
    
            // Asegúrate de que ambos selectores existen
            if (categorySelect && subcategorySelect) {
              // Añadir un listener para cuando cambie la categoría seleccionada
              categorySelect.addEventListener('change', (event) => {
                  const selectedCategory = event.target.value;
                  window.console.log(`Categoría seleccionada: ${selectedCategory}`);
                  eventoCat=event.target.value;
                  // Lógica para actualizar las opciones del selector de subcategorías
                  updateSubcategory(selectedCategory, subcategorySelect,token);
              });
              subcategorySelect.addEventListener('change',(e)=>{
                eventoSubCat=e.target.value;
            })
          } else {
              window.console.error('Los selectores de categoría y subcategoría no están disponibles.');
          }


            
        }).catch((error) => {
            window.console.error('Error al cargar los elementos select:', error);
        });
        
        }
    );
    
    modalForm.show();

}

const showAssigmentFormPopup=(e)=>{
  e.stopPropagation();
  const fila=e.target.closest('tr');
  const ticket=fila.querySelector('.tickets');
  const ticketId=ticket.textContent;
  const modalForm=new ModalForm({
      formClass: "\\local_ticketmanagement\\form\\AssignmentFormPopup",
      args: {num_ticket: ticketId},
      modalConfig: {title: `Ticket details: #${ticketId}`},
      returnFocus:e.target
  });

  modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
      //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
        
      const ticket=e.detail.ticket;
      const td=document.querySelector(`td a.assignbtn[data-ticketid="${ticket.id}"]`).parentElement;
      const link=document.querySelector(`a.assignbtn[data-ticketid="${ticket.id}"]`)
      const span = link.nextElementSibling; 
      span.textContent=ticket.user;
      const state=document.querySelector(`td a.assignbtn[data-ticketid="${ticket.id}"]`).parentElement.parentElement.children[4];
      state.textContent=ticket.state;
  });

  modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
      //Changing the text of the dynamic button
      //e.target.querySelector("button[data-action='save']").textContent="Send Email"
      
      }
  );
  
  modalForm.show();

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
      
      areElementsLoaded('#boactionid').then((elements)=>{
          const addActionBtn = (elements.length>0)?elements[0]:null;
          
          if (addActionBtn) {
              // Add an event listener to the button
              addActionBtn.addEventListener('click', function() {
                  console.log('Add Action button clicked!');
              });
          }

      })
  });
}

const updateTicket = (obj,token,url)=>{
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_edit_ticket_byUser');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][ticketid]',obj.ticketid);
    formData.append('params[0][fileid]',obj.attachments);
    formData.append('params[0][userid]',obj.userid);

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerUpdateTicket(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }

}

const reqHandlerUpdateTicket=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
      if (xhr.response){
          const response=JSON.parse(xhr.response);
          
          if (response){
            window.console.log(response.ticket);
            const ticket=response.ticket;
            updateTemplate(ticket);
          }
          
      }
    }
}

const updateTemplate=(ticket)=>{
  const fila = document.querySelector(`#${ticket.ticketid}`);
  const state = fila.querySelector('td:nth-child(5)');
  const priority = fila.querySelector('td:nth-child(7)');
  state.textContent=ticket.state;
  priority.textContent=ticket.priority;
  
    if (ticket.state==='Closed' || ticket.state==='Cancelled'){
        fila.classList.add("cerrado");
        
        // Opcional: cambiar el cursor a "not-allowed" para indicar que no es clickeable
        boAssigment.style.cursor = "not-allowed";
    }

}

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