define([
    'core_form/modalform',
    'local_ticketmanagement/funciones_comunes',
],function(ModalForm,funcionesComunes){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    
    const init =() => {
        
        const bomodifyuserlist=document.querySelectorAll('.add-file-user');

        bomodifyuserlist.forEach((bomodifyuser)=>{
            bomodifyuser.addEventListener('click',(e)=>{
                
                    showUserFormFilesPopup(e);
            })
        })
    }

    const showUserFormFilesPopup= (e)=>{
        window.console.log('addFiles.js loaded');
        e.stopPropagation();
        const tr = e.target.closest('tr');
        const userid=tr.id;
        
        // Accede al tercer <td> dentro del <tr>
        const email = tr.querySelector('td:nth-child(3)').textContent;
        const nombre= tr.querySelector('td:nth-child(4)').textContent;
        const apellidos=tr.querySelector('td:nth-child(5)').textContent;

        const formpopup="UserFormFilesPopup";
        
        const modalForm=new ModalForm({
            formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
            args: {userid: userid},
            modalConfig: {title: `Files attached to: ${nombre}, ${apellidos}`},
            returnFocus:e.target
        });

        modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
            //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
            const formElement=e.target;
            
            
          
    
        });

        modalForm.addEventListener(modalForm.events.LOADED, (e) => {
            const formElement = e.target;
            
        });
        modalForm.show();
    }

    

    return {
        init:init
    }
})
