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
        });

        
    }

    const showViewFamilyFormPopup= (e)=>{
        
        e.stopPropagation();
        const familiarid=e.target.dataset.id;;
        const formpopup="ViewFamilyFormPopup";
        
        const modalForm=new ModalForm({
            formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
            args: {userid: familiarid},
            modalConfig: {title: `User details: #${familiarid}`},
            returnFocus:e.target
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
            //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
            const formElement=document.querySelector('#family_form');
            const role=e.detail.data.relationship;
            const selrol=formElement.querySelector('select[name="selrole"]');
            selrol.value=role;

            const name=e.detail.data.name;
            const tefaname=formElement.querySelector('input[name="tefaname"]');
            tefaname.value=name;

            const lastname=e.detail.data.lastname;
            const tefalastname=formElement.querySelector('input[name="tefalastname"]');
            tefalastname.value=lastname;
            
    
        });

        modalForm.addEventListener(modalForm.events.LOADED, (e) => {
            
            // Obtener el formulario modal después de que se ha cargado
            const formElement = e.target;
            funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-family"]', formElement).then((elements) => {
                    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
                    const token=document.querySelector('input[name="token"]').value;
                    
            });
 
               
        });



        modalForm.show();
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
                            const famId="#family_"+familyId;
                            const firstname = formElement.querySelector(famId+' input[name="tefaname"]').value;
                            const lastname = formElement.querySelector(famId+' input[name="tefalastname"]').value
                            const relationship = formElement.querySelector(famId+' select').value;
                            editFamiliar(familyId,relationship,firstname,lastname,token,url);
                        });
                    });

                    const boviewfamiliar=document.querySelectorAll('.view-family');

                    boviewfamiliar.forEach((boviewfamily)=>{
                        boviewfamily.addEventListener('click',(e)=>{
                            window.console.log(e.target.dataset.id);
                            
                            showViewFamilyFormPopup(e);
                        })
                    })
            });
 
               
        });

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
                        //editFamiliarToTemplate(response);
                    }
                }
            }
        }

        modalForm.show();
    }

    

    return {
        init:init
    }
})




