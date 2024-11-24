define([
    'core_form/modalform'
],function(ModalForm){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    
    const init =() => {
        
        const bomodifyuserlist=document.querySelectorAll('.modify-user');

        bomodifyuserlist.forEach((bomodifyuser)=>{
            bomodifyuser.addEventListener('click',(e)=>{
                    showUserFormPopup(e);
            })
        })
    }

    const showUserFormPopup= (e)=>{
        
        e.stopPropagation();
        const userid=e.target.closest('.modify-user').dataset.userid;
        const formpopup="UserFormPopup";
        
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
            updateUser(e.detail);
    
        });

        modalForm.addEventListener(modalForm.events.LOADED, (e) => {
            
            // Obtener el formulario modal después de que se ha cargado
            const formElement = e.target;
               
        });
        modalForm.show();
    }

    const updateUser=(data)=>{
        const fila = document.getElementById(data.userid);
        const personalemail = fila.querySelector('td:nth-child(6)');
        personalemail.textContent=data.personalemail;
        const phone1 = fila.querySelector('td:nth-child(7)');
        phone1.textContent=data.phone1;
        const phone2 = fila.querySelector('td:nth-child(8)');
        phone2.textContent=data.phone2;
        const address = fila.querySelector('td:nth-child(9)');
        address.textContent=data.address;
        const city = fila.querySelector('td:nth-child(10)');
        city.textContent=data.city;
            
    }

    return {
        init:init
    }
})