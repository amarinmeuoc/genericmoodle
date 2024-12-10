import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {get_string as getString} from 'core/str';

export const init =() => { 
    

    // Seleccionar todas las filas con la clase `.cerrado`
    document.querySelectorAll("tr.cerrado .assignbtn").forEach(link => {
        // Añadir un manejador de eventos para el clic que evita que el evento se propague
        link.addEventListener("click", (event) => {
            
            event.stopPropagation(); // Detener la propagación del evento
            event.preventDefault();  // Prevenir cualquier acción predeterminada
        });
        // Opcional: cambiar el cursor a "not-allowed" para indicar que no es clickeable
        link.style.cursor = "not-allowed";
    });

    const assignbtn=document.querySelectorAll('tr:not(.cerrado) .assignbtn');

    assignbtn.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            const fila=e.target.closest('tr');
            if (!fila.classList.contains('cerrado')){
                showAssigmentFormPopup(e);
            } else {
                e.stopPropagation();
                e.preventDefault();
            }
        })
    })
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
        
        const tr=document.querySelector(`td a.assignbtn[data-ticketid="${ticket.id}"]`).closest('tr'); 
        //Se borra la clase amarillo, ya que se asigna un gestor
        tr.classList.remove('yellow');
        const td=document.querySelector(`td a.assignbtn[data-ticketid="${ticket.id}"]`).parentElement;
        const link=document.querySelector(`a.assignbtn[data-ticketid="${ticket.id}"]`)
        const span = link.nextElementSibling; 
        span.textContent=ticket.user;
        const state=tr.querySelector("td:nth-child(6)");
        state.textContent=ticket.state;
    });

    modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
        //Changing the text of the dynamic button
        //e.target.querySelector("button[data-action='save']").textContent="Send Email"
        
        }
    );
    
    modalForm.show();

}