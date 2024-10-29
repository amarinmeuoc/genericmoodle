const url=M.cfg.wwwroot+'/webservice/rest/server.php';
document.addEventListener('DOMContentLoaded',()=>{
  //Se obtiene datos a enviar
  const selcustomer=document.querySelector('#id_selcustomer');
  const token=document.querySelector('input[name="token"]').value;
  
  if (selcustomer!==null){
    selcustomer.addEventListener('change',(e)=>{
      requestGroup(e.target.value, token, url);
    });
  } 

});


const requestGroup=(customerid, token, url)=>{
    const xhr=new XMLHttpRequest();
    
    xhr.open('POST',url,true);
    
    const formData=new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','report_partialplan_get_group_list');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][customerid]',customerid);
    
    xhr.send(formData);
    xhr.onload = (event) =>{
        onLoadFunction(xhr);
        };
      xhr.onprogress = (event)=>{
        onProgressFunction(event);
      } 
      xhr.onerror = function() {
        window.console.log("Solicitud fallida");
      };

}

const onLoadFunction=(myXhr)=>{
    if (myXhr.readyState=== 4 && myXhr. status === 200){
        const res=JSON.parse(myXhr.response);
        window.console.log(res);
        const group_list_selector=document.querySelector('#id_selgroup');
        group_list_selector.innerHTML='';
        res.map((elem)=>{
          
            group_list_selector.innerHTML+='<option value="'+elem.id+'">'+elem.name+'</option>';
        })
        
          group_list_selector.innerHTML = '<option value="0">All groups</option>' + group_list_selector.innerHTML;
     
    }
}

const onProgressFunction= (event)=>{
    if (event.lengthComputable) {
      window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
    } else {
      window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
    }
}