import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {get_string as getString} from 'core/str';

export const init =() => {
    window.console.log("hello!!!!!!");
    const logs=document.querySelectorAll('.logs');

    logs.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            showTicketFormPopup(e);
        })
    })
}

const showTicketFormPopup=(e)=>{
    e.stopPropagation();
    const ticketId=e.target.dataset.ticketid;
    const modalForm=new ModalForm({
        formClass: "\\local_ticketmanagement\\form\\TicketFormPopup",
        args: {num_ticket: ticketId},
        modalConfig: {title: `Ticket details: #${ticketId}`},
        returnFocus:e.target
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
        //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
    });

    modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
        //Changing the text of the dynamic button
        //e.target.querySelector("button[data-action='save']").textContent="Send Email"
        
        }
    );
    
    modalForm.show();

}