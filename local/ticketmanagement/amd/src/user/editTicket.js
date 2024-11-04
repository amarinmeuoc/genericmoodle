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
        formClass: "\\local_ticketmanagement\\form\\TicketFormPopupStudent",
        args: {num_ticket: ticketId},
        modalConfig: {title: `Ticket details: #${ticketId}`},
        returnFocus:e.target
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
        
    });

    modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                
    });
    modalForm.show();
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
