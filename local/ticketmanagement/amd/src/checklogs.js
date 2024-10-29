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
        
    });
}



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