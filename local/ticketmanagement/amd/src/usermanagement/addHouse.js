define([
    'core/ajax',
    'core/toast',
    'core_form/modalform',
    'local_ticketmanagement/funciones_comunes',
],function(ajax,addToast,ModalForm,funcionesComunes){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    
    const init =() => {
        
        const boaddhouses=document.querySelectorAll('.modify-house');

        boaddhouses.forEach((boaddhouse)=>{
            boaddhouse.addEventListener('click',(e)=>{
                    showHouseFormPopup(e);
            })
        });
    }

    const showHouseFormPopup= (e)=>{
        
        e.stopPropagation();
        const tr = e.target.closest('tr');
        const userid=e.target.closest('.modify-house').dataset.userid;
        
        // Accede al tercer <td> dentro del <tr>
        const email = tr.querySelector('td:nth-child(3)').textContent;
        const nombre= tr.querySelector('td:nth-child(4)').textContent;
        const apellidos=tr.querySelector('td:nth-child(5)').textContent;

        const formpopup="HouseFormPopup";
        
        
        const modalForm=new ModalForm({
            formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
            args: {userid: userid},
            modalConfig: {title: `User details: ${nombre}, ${apellidos}`},
            returnFocus:e.target
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
            //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
            const formElement=e.target;

             if (e.detail.numero === null || e.detail.numero === 0) {
                // Check addresses only if numero is invalid
                const isExternalEmpty = typeof e.detail.external_address === 'undefined' || e.detail.external_address === '';
                const isInternalEmpty = typeof e.detail.internal_address === 'undefined' || e.detail.internal_address === '';
                
                if (isExternalEmpty || isInternalEmpty) {
                    addToast.add(`No house added.`);
                } else {
                    // Both addresses exist and are not empty, but numero is invalid
                    addToast.add(`No house added.`);
                }
            } else {
                // numero is valid (not null or 0) - check addresses
                
                let isValid = (e.detail.status==='success')?true:false;
                
                if (isValid) {
                    addToast.add(e.detail.message);
                } else {
                    addToast.add(e.detail.message);
                }
            }
                
        });

        modalForm.addEventListener(modalForm.events.LOADED, (e) => {
            
            // Obtener el formulario modal después de que se ha cargado
            const formElement = e.target;
            funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-house"]', formElement).then((elements) => {
                    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
                    const token=document.querySelector('input[name="token"]').value;
                   
                    formElement.querySelectorAll('.remove-house').forEach(button => {
                        button.addEventListener('click', (e) => {
                            const houseId = e.target.dataset.id; // ID del miembro de la house a remove
                            removeHouse(houseId,token,url);
                        });
                    });

                    const boviewhouse=document.querySelectorAll('.view-house');

                    boviewhouse.forEach((boview)=>{
                        boview.addEventListener('click',(e)=>{
                            showViewHouseFormPopup(e);
                        })
                    })

                   
            });   
        });

  

        const showViewHouseFormPopup= (e)=>{
        
            e.stopPropagation();
            const houseid=e.target.dataset.id;
            const formpopup="ViewHouseFormPopup";
            
            const modalForm=new ModalForm({
                formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
                args: {houseid: houseid},
                modalConfig: {title: `Details of the house with ID: #${houseid}`},
                returnFocus:e.target
            });
    
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                if (e.detail.data){
                    const record=document.querySelector('#house_form tr#house_'+e.detail.data.id);
                    const type=record.querySelector('td:nth-child(1)>span');
                    type.textContent=e.detail.data.type;

                    const address=record.querySelector('td:nth-child(2)>span');
                    address.textContent=e.detail.data.address;

                    const entry_date = e.detail.data.entry_date; // Could be Unix timestamp or ISO string
                    const departure_date = e.detail.data.departure_date;

                    // Robust date formatter that handles multiple input types
                    const formatDate = (dateValue) => {
                        if (!dateValue) return '-';
                        
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
                    tedelivery_date.textContent = formatDate(entry_date);

                    const terefund_date = record.querySelector('td:nth-child(4)');
                    
                    if (departure_date!=='0')
                        terefund_date.textContent = formatDate(departure_date);
                    else 
                        terefund_date.textContent='';
                } else {
                    addToast.add(`The selected address hasnt been saved. Check all data and connection`, {
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
            });
    
            modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                
                // Obtener el formulario modal después de que se ha cargado
                const formElement = e.target;
                funcionesComunes.areElementsLoaded('input[name="token"],button[class="edit-house"]', formElement).then((elements) => {
                        const url=M.cfg.wwwroot+'/webservice/rest/server.php';
                        const token=document.querySelector('input[name="token"]').value;
                        
                });
     
                   
            });
    
            modalForm.show();
        }

        const removeHouse=(id,token,url)=>{
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_remove_house');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][id]',id);
            
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                reqHandlerRemoveHouse(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        }

        

        const reqHandlerRemoveHouse=(xhr)=>{
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.response) {
                    const response = JSON.parse(xhr.response);
                    const houseId="#house_"+response;
                    document.querySelector(houseId).remove();
                    addToast.add(`The selected address has been removed.`, {
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

        

        modalForm.show();
    }

    

    return {
        init:init
    }
})




