import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {get_string as getString} from 'core/str';

const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;
let eventoCat="";
let eventoSubCat="";
let eventoFile="";
let eventoPriority="";
export const init =() => {
    
    const tickets=document.querySelectorAll('.tickets');

    tickets.forEach((node)=>{
        node.addEventListener('click',(e)=>{
                showTicketFormPopup(e);
        })
    })
}

const showTicketFormPopup=(e)=>{
    e.stopPropagation();
    const ticketId=e.target.textContent.trim();
    
    const modalForm=new ModalForm({
        formClass: "\\local_ticketmanagement\\form\\TicketFormPopup",
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


        updateTicket(e.detail,token,url);
    });

    modalForm.addEventListener(modalForm.events.LOADED, (e) => {
        // Obtener el formulario modal después de que se ha cargado
        const formElement = e.target;
    
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

            const closebox=formElement.querySelector("input[type='checkbox'][name='close']");
            const cancelledbox=formElement.querySelector("input[type='checkbox'][name='cancelled']");
            const bosave=formElement.querySelector(".btn-primary");
            
            if (closebox.checked){
              bosave.disabled=true;
              closebox.disabled=true;
            }


            if (cancelledbox.checked){
              bosave.disabled=true;
              cancelledbox.disabled=true;
            }


            
        }).catch((error) => {
            window.console.error('Error al cargar los elementos select:', error);
        });
    });
    modalForm.show();
}

const updateTicket = (obj,token,url)=>{
    let xhr = new XMLHttpRequest();
      
      //Se prepara el objeto a enviar
      const formData= new FormData();
      formData.append('wstoken',token);
      formData.append('wsfunction', 'local_ticketmanagement_edit_ticket');
      formData.append('moodlewsrestformat', 'json');
      formData.append('params[0][ticketid]',obj.ticketid);
      formData.append('params[0][fileid]',obj.attachments);
      formData.append('params[0][cancelled]',obj.cancelled);
      formData.append('params[0][state]',obj.hiddenstate);
      formData.append('params[0][priority]',obj.priority);
      formData.append('params[0][closed]',obj.close);
      formData.append('params[0][category]',obj.subcategory);
      formData.append('params[0][eventoCat]',eventoCat);
      formData.append('params[0][eventoSubCat]',eventoSubCat);
      formData.append('params[0][eventoPriority]',eventoPriority);
  
      xhr.open('POST',url,true);
      xhr.send(formData);
  
      xhr.onload = (ev)=> {
          reqHandlerUpdateTicket(xhr);
      }
  
      xhr.onerror = ()=> {
          rejectAnswer(xhr);
      }
  
}

const updateSubcategory= (categoryid,subcategorySelect, token)=>{
    let xhr = new XMLHttpRequest();
      
      //Se prepara el objeto a enviar
      const formData= new FormData();
      formData.append('wstoken',token);
      formData.append('wsfunction', 'local_ticketmanagement_load_subcategories');
      formData.append('moodlewsrestformat', 'json');
      formData.append('params[0][categoryid]',categoryid);
  
      xhr.open('POST',url,true);
      xhr.send(formData);
  
      xhr.onload = (ev)=> {
          reqHandlerLoadSubcategories(xhr, subcategorySelect);
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
    window.console.log("edicion...");
    if (ticket.state==='Closed' || ticket.state==='Cancelled'){
        fila.classList.add("cerrado");
        const boAssigment=fila.querySelector('.assignbtn');
        boAssigment.addEventListener("click", (event) => {
            event.stopPropagation(); // Detener la propagación del evento
            event.preventDefault();  // Prevenir cualquier acción predeterminada
        });
        // Opcional: cambiar el cursor a "not-allowed" para indicar que no es clickeable
        boAssigment.style.cursor = "not-allowed";
    }

  }

  const reqHandlerLoadSubcategories=(xhr,subcategorySelect)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
      if (xhr.response){
          const response=JSON.parse(xhr.response);
          const selsubcategory=subcategorySelect;
          if (response){
            selsubcategory.innerHTML='';
            const optionsSubcategories = response;
            
  
            optionsHTML='';
            optionsSubcategories.forEach(optionData=>{
                  optionsHTML += `<option value="${optionData.id}">${optionData.subcategory}</option>`;
            })
            selsubcategory.innerHTML = optionsHTML;
          }
          
      }
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
