import {exception as displayException} from 'core/notification'; 
import Templates from 'core/templates';
import Notification from 'core/notification';
import ModalForm from 'core_form/modalform';
import ModalFactory from 'core/modal';


const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;

export const init =() => {
    
    const selectedUser=document.querySelector('#id_userlist').value;

    const boSelect=document.querySelector('#id_bosubmit');

    boSelect.addEventListener('click',()=>{
        const selectedUser=document.querySelector('#id_userlist').value;
        if (selectedUser==='')
            Notification.addNotification({message:'Error: No trainee selected. Please select an availabe trainee.',type:'error'});
        else {
            loadFamiliyfromUserId(selectedUser,token,url);
        }
    })

    if (selectedUser===''){
        Notification.addNotification({message:'Error: No trainee selected. Please select an availabe trainee.',type:'error'});
        return;
    } else {
        window.console.log('loading...');
        loadFamiliyfromUserId(selectedUser,token,url);
    }
}

const loadFamiliyfromUserId=(userid,token,url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_get_family_members');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][userid]',userid);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerLoadFamilyMembers(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerLoadFamilyMembers=(xhr)=>{
    if (xhr.readyState === 4 && xhr.status === 200) {
        if (xhr.response) {
            const response = JSON.parse(xhr.response);
            loadFamiliyTemplate(response);
            window.console.log(response);
        }
    }
}

const loadFamiliyTemplate=(response)=>{
    
        Templates.renderForPromise('local_ticketmanagement/family-userlabel',response)
        .then(({html,js})=>{
        const content=document.querySelector('#etiqueta');
        content.innerHTML='';
          Templates.appendNodeContents(content,html,js);
        })
        .catch((error)=>displayException(error));

        //Render the choosen mustache template by Javascript
        Templates.renderForPromise('local_ticketmanagement/tr_family',response)
        .then(({html,js})=>{
        const content=document.querySelector('#tablebody');
        content.innerHTML='';
          Templates.appendNodeContents(content,html,js);

          const boedit=document.querySelectorAll('.edit');

          boedit.forEach(elem=>{
              
              elem.addEventListener('click',(e)=>{
                  
                  const id=e.target.dataset.id;
                  
                  showEditFamilyPopup(id);
              })
          })

          const boremove=document.querySelectorAll('.remove');

          boremove.forEach(elem=>{
              
            elem.addEventListener('click',(e)=>{
                const id=e.target.dataset.id;
                showRemoveFamilyPopup(id);
            })
        })

        })
        .catch((error)=>displayException(error));
     
}

function showRemoveFamilyPopup(id) {
    const modalContent = `
        
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar este miembro de la familia?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-action="cancel">Cancelar</button>
            <button type="button" class="btn btn-danger" data-action="confirm">Aceptar</button>
        </div>
    `;

    ModalFactory.create({
        title: 'Confirmar Eliminación',
        body: modalContent,
        size: 'modal-md'
    }).then(modal => {
        window.console.log(modal);
        // Manejar el clic en Aceptar
        modal.getRoot()[0].querySelector('[data-action="confirm"]').onclick = function() {
            removeFamilyMember(id); // Llama a la función para eliminar el miembro
            modal.hide(); // Cierra el modal
        };

        // Manejar el clic en Cancelar
        modal.getRoot()[0].querySelector('[data-action="cancel"]').onclick = function() {
            modal.hide(); // Solo cierra el modal
        };

        
        modal.show(); // Muestra el modal
    });
}

const removeFamilyMember=(id)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_remove_family_members');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][id]',id);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerRemoveFamilyMember(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerRemoveFamilyMember=(xhr)=>{
    if (xhr.readyState === 4 && xhr.status === 200) {
        if (xhr.response) {
            const id = JSON.parse(xhr.response);
            if (id) {
                //Se borra el elemento afectado
                document.querySelector(`#id_${id}`).remove()
            }
        }
    }
}

const showEditFamilyPopup=(id)=>{
    const modalForm=new ModalForm({
        formClass: "\\local_ticketmanagement\\form\\EditFamiliarFormPopup",
        args: {id: id},
        modalConfig: {title: `Edit Family member`},
        returnFocus:e.target
    });

    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
        //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
        const id=e.detail.id;
        const relationship=e.detail.selrelationship;
        const firstname=e.detail.firstname;
        const lastname=e.detail.lastname;
        editFamiliar(id,relationship,firstname,lastname,token,url);
    });

    modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
        //Changing the text of the dynamic button
        //e.target.querySelector("button[data-action='save']").textContent="Send Email"
        
        }
    );
    
    modalForm.show();
}

const editFamiliar=(id,relationship,firstname,lastname,token,url)=>{
    let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_edit_family_members');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][id]',id);
    formData.append('params[0][relationship]',relationship);
    formData.append('params[0][firstname]',firstname);
    formData.append('params[0][lastname]',lastname);
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerEditFamiliar(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerEditFamiliar=(xhr)=>{
    if (xhr.readyState === 4 && xhr.status === 200) {
        if (xhr.response) {
            const response = JSON.parse(xhr.response);
            if (response) {
                window.console.log(response);
                editFamiliarToTemplate(response);
            }
        }
    }
}

const editFamiliarToTemplate=(response)=>{
    const id=response.listadoFamily.id;
    Templates.renderForPromise('local_ticketmanagement/relationship_family',response.listadoFamily)
        .then(({html,js})=>{
            const relationship=document.querySelector(`#id_${id} > .relationship`);
            relationship.innerHTML=html;
            //Templates.appendNodeContents(relationship,html,js);
          
        })
        .catch((error)=>displayException(error));

        Templates.renderForPromise('local_ticketmanagement/firstname_family',response.listadoFamily)
        .then(({html,js})=>{
            const firstname=document.querySelector(`#id_${id} > .firstname`);
            firstname.innerHTML=html;
            //Templates.appendNodeContents(firstname,html,js);
          
        })
        .catch((error)=>displayException(error));

        Templates.renderForPromise('local_ticketmanagement/lastname_family',response.listadoFamily)
        .then(({html,js})=>{
            const lastname=document.querySelector(`#id_${id} > .lastname`);
            lastname.innerHTML=html;
            //Templates.appendNodeContents(lastname,html,js);
          
        })
        .catch((error)=>displayException(error));
  }