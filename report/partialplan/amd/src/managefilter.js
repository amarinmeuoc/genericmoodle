import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';

export const init = () => {
    
    const sdlink=document.querySelector('#sd-link');
    const edlink=document.querySelector('#ed-link');

    

    sdlink.addEventListener('click',(e)=>{
        e.preventDefault();
        const customerid=(document.querySelector('#id_selcustomer')!==null)?document.querySelector("#id_selcustomer").value:document.querySelector('input[name="selcustomer"').value;
        const order=document.querySelector('input[name="order"]');
        const orderby=document.querySelector('input[name="orderby"]');
        const day=document.querySelector('#id_assesstimefinish_day').value;
        const month=document.querySelector('#id_assesstimefinish_month').value;
        const year=document.querySelector('#id_assesstimefinish_year').value;
        const group=document.querySelector('#id_selgroup').value;
        const billid=document.querySelector('#id_tebillid').value;
        const date=new Date(year,month-1,day);
        const unixtime=date.getTime()/1000;
        
        orderby.value='startdate';
        // Alternar el valor de order
        order.value = order.value === '1' ? '0' : '1';

        
        
        requestResultFromServer(customerid,group,billid,unixtime,order.value,orderby.value);
    });

    edlink.addEventListener('click',(e)=>{
        e.preventDefault();
        const customerid=(document.querySelector('#id_selcustomer')!==null)?document.querySelector("#id_selcustomer").value:document.querySelector('input[name="selcustomer"').value;
        const order=document.querySelector('input[name="order"]');
        const orderby=document.querySelector('input[name="orderby"]');
        const day=document.querySelector('#id_assesstimefinish_day').value;
        const month=document.querySelector('#id_assesstimefinish_month').value;
        const year=document.querySelector('#id_assesstimefinish_year').value;
        const group=document.querySelector('#id_selgroup').value;
        const billid=document.querySelector('#id_tebillid').value;
        const date=new Date(year,month-1,day);
        const unixtime=date.getTime()/1000;
        orderby.value='enddate';
        order.value = order.value === '1' ? '0' : '1';
        
        
        requestResultFromServer(customerid,group,billid,unixtime,order.value,orderby.value);
    });
    
}

const requestResultFromServer=(customerid,group,billid,unixtime,order,orderby)=>{
    let xhr=new XMLHttpRequest();
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('input[name="token"]').value;
    xhr.open('POST',url,true);
    const formData=new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','report_partialplan_get_training_plan');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][customerid]',customerid);
    formData.append('params[0][groupid]',parseInt(group));
    formData.append('params[0][billid]',billid);
    formData.append('params[0][unixtime]',unixtime);
    formData.append('params[0][order]',order);
    formData.append('params[0][orderby]',orderby);
    
    xhr.send(formData);
    xhr.onload = (event) =>{
        onLoadFunction(xhr,group,token);
        };
    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
}

const onLoadFunction=(myXhr,selectedGroup,token)=>{
    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        
        showTemplateAssessment(res);
    }
}

const onProgressFunction= (event)=>{
    if (event.lengthComputable) {
      window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
    } else {
      window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
    }
}

function showTemplateAssessment(response){
    
    const content=document.querySelector('#content');
    content.innerHTML='';
    const responseData = response[0];
    
    Templates.renderForPromise('report_partialplan/content_con-ajax', responseData).then(({html, js}) => {
        const content = document.querySelector('#content');
        content.innerHTML = '';

        Templates.appendNodeContents(content,html,js);
    }).catch((error)=>displayException(error));
    
    
  }