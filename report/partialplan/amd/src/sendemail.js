import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {add as addToast} from 'core/toast';
import {get_string as getString} from 'core/str';

export const init = () => {
    //customer id
    const customerid=(document.querySelector('#id_selcustomer')!==null)?document.querySelector("#id_selcustomer").value:document.querySelector('input[name="selcustomer"').value;
    let trainee_list=document.querySelectorAll('.boemail');
    trainee_list.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            traineesFunctionEvent(e,customerid);
        });
    })
}

const addNotification = msg => {
    addToast(msg);
};

const traineesFunctionEvent=(e,customerid)=>{
    e.stopPropagation(); //To avoid the event bubbles up further up the DOM tree, stopping it at the button element
    const element=e.target.closest('button');
    const modalForm= new ModalForm({
        formClass: "\\report_partialplan\\form\\Modal_Form",
        args: {list_trainees: element.getAttribute('data-id'),customer:customerid},
        modalConfig: {title: "Send email to..."},
        returnFocus: element
    });
    
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
        const xhr=new XMLHttpRequest();
        const url='./send_email.php';
        xhr.open('POST',url,true);
        const formData= new FormData();
        
        formData.append('messagebody',e.detail.teeditor.text);
        formData.append('to',e.detail.emailhidden);
        formData.append('subject',e.detail.tesubject);
       
        xhr.send(formData);
        xhr.onload = (event) =>{
            onLoadFunction(xhr);
        };
        xhr.onprogress = (event)=>{
            onProgressFunction(event);
        } 
        xhr.onerror = function() {
            addNotification('Something went wrong. Please contact with the admin');
        };
    });

    const onProgressFunction=(event)=> {
        if (event.lengthComputable) {
          window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
        } else {
          window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
        }
      };
      
      const onLoadFunction=(myxhr)=>{
        if (myxhr.readyState=== 4 && myxhr.status === 200){
          const res=JSON.parse(myxhr.response);
            if (res.result)
                addNotification('The email has been sent successfully');
            else 
                addNotification('Something went wrong. Please contact with the admin');
        }
      }
    
    modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
        //Changing the text of the dynamic button
        e.target.querySelector("button[data-action='save']").textContent="Send Email"
        
        }
    );
    
    modalForm.show();
}