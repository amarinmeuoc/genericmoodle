define([
    'core/ajax',
    'core/toast',
    'core_form/modalform',
    'local_ticketmanagement/funciones_comunes',
],function(ajax,addToast,ModalForm,funcionesComunes){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    
    const init =() => {
        
        const boaddcars=document.querySelectorAll('.modify-car');

        boaddcars.forEach((boaddcar)=>{
            boaddcar.addEventListener('click',(e)=>{
                    showCarFormPopup(e);
            })
        });

        
    }

   
    

    const showCarFormPopup= (e)=>{
        
        e.stopPropagation();
        const tr = e.target.closest('tr');
        const userid=e.target.closest('.modify-car').dataset.userid;
        
        // Accede al tercer <td> dentro del <tr>
        const email = tr.querySelector('td:nth-child(3)').textContent;
        const nombre= tr.querySelector('td:nth-child(4)').textContent;
        const apellidos=tr.querySelector('td:nth-child(5)').textContent;

        const formpopup="CarFormPopup";
        
        
        const modalForm=new ModalForm({
            formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
            args: {userid: userid},
            modalConfig: {title: `User details: ${nombre}, ${apellidos}`},
            returnFocus:e.target
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
            //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
            const formElement=e.target;
            //Se actualiza
            if (e.detail===null)
                addToast.add(`No car added.`);
            else
                addToast.add(`Car ${e.detail.brand}, ${e.detail.model} has been added succesfully.`);
                
        });

        modalForm.addEventListener(modalForm.events.LOADED, (e) => {
            
            // Obtener el formulario modal después de que se ha cargado
            const formElement = e.target;
            funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-car"]', formElement).then((elements) => {
                    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
                    const token=document.querySelector('input[name="token"]').value;
                    formElement.querySelectorAll('.edit-car').forEach(button => {
                        button.addEventListener('click', (e) => {
                            const carId = e.target.dataset.id; // ID del miembro de la familia a editar
                            const famId="#car_"+carId;
                            const brand = formElement.querySelector(famId+' input[name="tecarbrand"]').value;
                            const model = formElement.querySelector(famId+' input[name="tecarmodel"]').value;
                            
                            
                            editCar(carId,model,brand,token,url);
                        });
                    });
                    formElement.querySelectorAll('.remove-car').forEach(button => {
                        button.addEventListener('click', (e) => {
                            const carId = e.target.dataset.id; // ID del miembro de la familia a editar
                            
                            
                            removeCar(carId,token,url);
                        });
                    });

                    const boviewcar=document.querySelectorAll('.view-car');

                    boviewcar.forEach((boview)=>{
                        boview.addEventListener('click',(e)=>{
                            showViewCarFormPopup(e);
                        })
                    })

                    const bofinescar=document.querySelectorAll('.fine-car');

                    bofinescar.forEach((bofine)=>{
                        bofine.addEventListener('click',(e)=>{
                            showFineFormPopup(e);
                        })
                    })
            });   
        });

        const showFineFormPopup = (e) => {
            e.stopPropagation();
            const userid=e.target.dataset.userid;
            const carid=e.target.dataset.id;
            
            const getUsername= function(userid) {
                return ajax.call([{
                    methodname: 'core_user_get_users_by_field',
                    args: { field: 'id', values: [userid] }
                }])[0].then(function(users) {
                    return users[0].firstname +", "+ users[0].lastname;
                });
            };

            // First get the username, THEN create the modal
            getUsername(userid).then(function(username) {
                const formpopup = "FineFormPopup";
                const modalForm = new ModalForm({
                    formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
                    args: {userid: userid, carid: carid},
                    modalConfig: {title: `List of fines of: #${username}`}, // Use the resolved username
                    returnFocus: e.target
                });

                modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                });
        
                modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                    const formElement = e.target;
                    funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-fine"]', formElement).then((elements) => {
                        const url = M.cfg.wwwroot + '/webservice/rest/server.php';
                        const token = document.querySelector('input[name="token"]').value;
                
                        // Function to handle status change
                        const handleStatusChange = (fineId) => {
                            const statusSelect = formElement.querySelector(fineId + ' select[name="selstatus"]');
                            const reminderCheckbox = formElement.querySelector(fineId + ' input[name="reminder"]');
                            
                            if (statusSelect.value !== 'pending') {
                                reminderCheckbox.disabled = true;
                                reminderCheckbox.checked = false;
                            } else {
                                reminderCheckbox.disabled = false;
                            }
                        };
                
                        formElement.querySelectorAll('.edit-fine').forEach(button => {
                            const id = button.dataset.id;
                            const fineId = "#fine_" + id;
                            
                            // Initial setup
                            handleStatusChange(fineId);
                            
                            // Add change listener to status select
                            formElement.querySelector(fineId + ' select[name="selstatus"]').addEventListener('change', () => {
                                handleStatusChange(fineId);
                            });
                
                            button.addEventListener('click', (e) => {
                                const ticketId = formElement.querySelector(fineId + ' input[name="tefineticket"]').value;
                                const status = formElement.querySelector(fineId + ' select[name="selstatus"]').value;
                                const reminder = formElement.querySelector(fineId + ' input[name="reminder"]').checked;
                                editFine(id, ticketId, status, reminder, token, url);
                            });
                        });
                
                        formElement.querySelectorAll('.remove-fine').forEach(button => {
                            button.addEventListener('click', (e) => {
                                const id = e.target.dataset.id;
                                removeFine(id, token, url);
                            });
                        });
                    });
                });
                
                // Show the modal after it's configured
                modalForm.show();
            }).catch(function(error) {
                console.error('Failed to load username:', error);
                // Fallback if username fails to load
                const modalForm = new ModalForm({
                    formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
                    args: {userid: userid, carid: carid},
                    modalConfig: {title: `List of fines`}, // Fallback title
                    returnFocus: e.target
                });
                modalForm.show();
            }); 
        }

        const editFine = (id,ticketId,status,reminder,token,url)=>{
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_edit_fine');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][id]',id);
            formData.append('params[0][reminder]',(reminder)?1:0);
            formData.append('params[0][ticketId]',ticketId);
            formData.append('params[0][status]',status);
            
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                reqHandlerEditFine(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        }

        const reqHandlerEditFine=(xhr)=>{
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.response) {
                    const response = JSON.parse(xhr.response);
                    if (response) {
                        
                        addToast.add(`Fine updated.`);

                        // Find the wrapper and override z-index
                        const toastWrapper = document.querySelector('.toast-wrapper');
                        if (toastWrapper) {
                            toastWrapper.style.zIndex = '10000';
                        }
                    
                    }
                }
            }
        }

        const removeFine=(id,token,url)=>{
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_remove_fine');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][id]',id);
            
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                reqHandlerRemoveFine(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }

        }

        const reqHandlerRemoveFine=(xhr)=>{
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.response) {
                    const response = JSON.parse(xhr.response);
                    const fineId="#fine_"+response;
                    document.querySelector(fineId).remove();
                    addToast.add(`The selected fine has been removed.`, {
                        attributes: {
                            style: 'background-color: #ff0000 !important; color: white;' // Red bg, white text
                        }
                    });
                    
                        // Find the wrapper and override z-index
                        const toastWrapper = document.querySelector('.toast-wrapper');
                        if (toastWrapper) {
                            toastWrapper.style.zIndex = '10000';
                        }
                }
            }
            
        }

        const showViewCarFormPopup= (e)=>{
        
            e.stopPropagation();
            const carid=e.target.dataset.id;
            const formpopup="ViewCarFormPopup";
            
            const modalForm=new ModalForm({
                formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
                args: {carid: carid},
                modalConfig: {title: `Details of the car with ID: #${carid}`},
                returnFocus:e.target
            });
    
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
                const record=document.querySelector('#car_form tr#car_'+e.detail.data.id);
                const brand=e.detail.data.brand;

                const tebrand=record.querySelector('input[name="tecarbrand"]');
                tebrand.value=brand;
    
                const model=e.detail.data.model;
                const temodel=record.querySelector('input[name="tecarmodel"]');
                temodel.value=model;

                const delivery_date = e.detail.data.delivery_date; // Could be Unix timestamp or ISO string
                const refund_date = e.detail.data.refund_date;

                // Robust date formatter that handles multiple input types
                const formatDate = (dateValue) => {
                    if (!dateValue) return 'N/A';
                    
                    let date;
                    
                    // Handle Unix timestamp (seconds since epoch)
                    if (/^\d+$/.test(dateValue)) {
                        date = new Date(dateValue * 1000);
                    }
                    // Handle ISO string or other formats
                    else {
                        date = new Date(dateValue);
                    }
                    
                    // Check if date is valid
                    if (isNaN(date.getTime())) return 'Invalid date';
                    
                    return new Intl.DateTimeFormat(navigator.language || 'en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                    }).format(date);
                };

                // Apply formatting
                const tedelivery_date = record.querySelector('td:nth-child(3)');
                tedelivery_date.textContent = formatDate(delivery_date);

                const terefund_date = record.querySelector('td:nth-child(4)');
                
                if (refund_date!=='0')
                    terefund_date.textContent = formatDate(refund_date);
                else 
                    terefund_date.textContent='';
            });
    
            modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                
                // Obtener el formulario modal después de que se ha cargado
                const formElement = e.target;
                funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-car"]', formElement).then((elements) => {
                        const url=M.cfg.wwwroot+'/webservice/rest/server.php';
                        const token=document.querySelector('input[name="token"]').value;
                        
                });
     
                   
            });
    
            modalForm.show();
        }

        const removeCar=(id,token,url)=>{
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_remove_car');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][id]',id);
            
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                reqHandlerRemoveCar(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        }

        const editCar=(id,model,brand,token,url)=>{
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_edit_car');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][id]',id);
            
            formData.append('params[0][model]',model);
            formData.append('params[0][brand]',brand);
            
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                reqHandlerEditCar(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        }

        const reqHandlerRemoveCar=(xhr)=>{
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.response) {
                    const response = JSON.parse(xhr.response);
                    const carId="#car_"+response;
                    document.querySelector(carId).remove();
                    addToast.add(`The selected car has been removed.`, {
                        attributes: {
                            style: 'background-color: #ff0000 !important; color: white;' // Red bg, white text
                        }
                    });
                    

                        // Find the wrapper and override z-index
                        const toastWrapper = document.querySelector('.toast-wrapper');
                        if (toastWrapper) {
                            toastWrapper.style.zIndex = '10000';
                        }
                }
            }
            
        }

        const reqHandlerEditCar=(xhr)=>{
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.response) {
                    const response = JSON.parse(xhr.response);
                    if (response) {
                        
                        addToast.add(`Record updated.`);

                        // Find the wrapper and override z-index
                        const toastWrapper = document.querySelector('.toast-wrapper');
                        if (toastWrapper) {
                            toastWrapper.style.zIndex = '10000';
                        }
                    
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




