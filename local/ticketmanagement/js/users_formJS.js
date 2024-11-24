const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;

document.addEventListener('DOMContentLoaded',()=>{
    const selproject=document.querySelector('#id_project');
    
    selproject.addEventListener('change',(e)=>{
      const customerid=e.target.value;
      updateVessel(customerid,token);
    });

    const selvessel=document.querySelector('#id_vessel');
    selvessel.addEventListener('change',(e)=>{
      customerid=selproject.options[selproject.selectedIndex].value;
      vesselid=e.target.options[e.target.selectedIndex].value;
      const role=(vesselid==="0")?"observer":"student";
      
    });

    
})

const updateVessel= (customerid,token)=>{
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_load_groups');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',customerid);

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerLoadGroups(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerLoadGroups=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
        const response=JSON.parse(xhr.response);
        const selVessel=document.querySelector('#id_vessel');
        if (response){
          selVessel.innerHTML='';
          const optionsVessel = response;

          optionsHTML='';
          optionsVessel.forEach(optionData=>{
                optionsHTML += `<option value="${optionData.id}">${optionData.name}</option>`;
          })
          selVessel.innerHTML = optionsHTML;
        }
        const selproject=document.querySelector('#id_project');
        if (selproject.selectedIndex===-1){
          selproject.innerHTML='<option>No project registered yet</option>'
          customerid=-1
        } else {
          customerid=selproject.options[selproject.selectedIndex].value;
        }
        

        if (selVessel.selectedIndex===-1){
          selVessel.innerHTML='<option>No Vessel registered yet</option>'
          vesselid=-1;
        } else {
          vesselid=selVessel.options[selVessel.selectedIndex].value;
        }

    }
  }
}



