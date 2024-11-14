
define(['core_form/modalform','local_ticketmanagement/funciones_comunes'],function(ModalForm,funcionesComunes){
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    let eventoCat="";
    let eventoSubCat="";
    let eventoFile="";
    let eventoPriority="";
    const init =() => {
    
        const tickets=document.querySelectorAll('.tickets');
    
        tickets.forEach((node)=>{
            node.addEventListener('click',(e)=>{
                    funcionesComunes.showTicketFormPopup(e,'student');
            })
        })
    }
    return {
        init:init
    }
});


 