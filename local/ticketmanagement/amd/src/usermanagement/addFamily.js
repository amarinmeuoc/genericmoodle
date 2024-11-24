define([
    'core_form/modalform',
    'local_ticketmanagement/funciones_comunes'
],function(ModalForm,funcionesComunes){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    
    const init =() => {
        
        const boaddfamiliar=document.querySelectorAll('.modify-family');

        boaddfamiliar.forEach((boaddfamily)=>{
            boaddfamily.addEventListener('click',(e)=>{
                    showFamilyFormPopup(e);
            })
        })
    }

    const showFamilyFormPopup= (e)=>{
        
        e.stopPropagation();
        const userid=e.target.closest('.modify-family').dataset.userid;
        const formpopup="FamilyFormPopup";
        
        const modalForm=new ModalForm({
            formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
            args: {userid: userid},
            modalConfig: {title: `User details: #${userid}`},
            returnFocus:e.target
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
            //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
            const formElement=e.target;
            //Se actualiza
            window.console.log(e.detail);
            
    
        });

        modalForm.addEventListener(modalForm.events.LOADED, (e) => {
            
            // Obtener el formulario modal después de que se ha cargado
            const formElement = e.target;
            funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-family"]', formElement).then((elements) => {
                    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
                    const token=document.querySelector('input[name="token"]').value;
                    formElement.querySelectorAll('.edit-family').forEach(button => {
                        button.addEventListener('click', (e) => {
                            const familyId = e.target.dataset.id; // ID del miembro de la familia a editar
                            const firstname = prompt('Enter first name of the family member:');
                            const lastname = prompt('Enter last name of the family member:');
                            const relationship = prompt('Enter relationship to the trainee:');
                            window.console.log(token);
                            
                            if (firstname && lastname && relationship) {
                                                    
                                // Crear y configurar la solicitud XMLHttpRequest
                                let xhr = new XMLHttpRequest();

                                const service= 'edit_family_members';

                                //Se prepara el objeto a enviar
                                const formData= new FormData();
                                formData.append('wstoken',token);
                                formData.append('wsfunction', service);
                                formData.append('moodlewsrestformat', 'json');
                                formData.append('params[0][id]',familyId);
                                formData.append('params[0][firstname]',firstname);
                                formData.append('params[0][lastname]',lastname);
                                formData.append('params[0][relationship]',relationship);

                                xhr.open('POST',url,true);
                    
                                // Manejar la respuesta
                                xhr.onreadystatechange = function() {
                                    if (xhr.readyState === 4) { // La solicitud ha finalizado
                                        if (xhr.status === 200) { // HTTP 200 OK
                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                if (response.errorcode) {
                                                    alert(`Error: ${response.message}`);
                                                } else {
                                                    const family = response.listadoFamily;
                                                    alert(`Family member updated successfully:
                                                        ID: ${family.id}
                                                        Name: ${family.name}
                                                        Last Name: ${family.lastname}
                                                        Relationship: ${family.relationship}`);
                                                    location.reload(); // Actualizar la tabla
                                                }
                                            } catch (err) {
                                                console.error('Error parsing response:', err);
                                                alert('An unexpected error occurred.');
                                            }
                                        } else {
                                            console.error('Request failed:', xhr.status, xhr.statusText);
                                            alert('Failed to update the family member.');
                                        }
                                    }
                                };
                    
                                // Enviar la solicitud
                                xhr.send(url);
                            } else {
                                alert('All fields are required.');
                            }
                        });
                    });
                });
 
               
        });
        modalForm.show();
    }

    

    return {
        init:init
    }
})




