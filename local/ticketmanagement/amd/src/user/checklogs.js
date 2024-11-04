import {exception as displayException} from 'core/notification';  
import Templates from 'core/templates';
import Notification from 'core/notification';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal';

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
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_load_actions');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][ticketid]',ticketId);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerLoadActions(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerLoadActions=(xhr)=>{
    if (xhr.readyState === 4 && xhr.status === 200) {
        if (xhr.response) {
            const response = JSON.parse(xhr.response);
            loadActionsTemplate(response);
            window.console.log(response);
        }
    }
}

const loadActionsTemplate=(response)=>{
    const modalContent = `
      <div class="modal-body">
        <p>This is the list of actions ordered by date</p>
    </div>
    <div class="table-responsive" style="max-height:300px">
        <table class="generaltable table-sm">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Task</th>
                    <th>Done by</th>
                </tr>
            </thead>
            <tbody>
                ` + 
                response.map(action => {
                    
                    return `
                        <tr>
                            <td>${action.dateaction}</td>
                            <td>${action.action}</td>
                            <td>${action.user}</td>
                        </tr>
                    `;
                }).join('') + // Unir todas las filas generadas
                `</tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-action="confirm">Accept</button>
    </div>`;

    ModalFactory.create({
        title: 'Actions history',
        body: modalContent,
        size: 'modal-xl'
    }).then(modal => {
        window.console.log(modal);
        // Manejar el clic en Aceptar
        modal.getRoot()[0].querySelector('[data-action="confirm"]').onclick = function() {
            
            modal.hide(); // Cierra el modal
        };
        modal.show(); // Muestra el modal
    });
}