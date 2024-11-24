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
      role=(vesselid==="0")?"observer":"student";
      
      
      updateListofUsers(customerid, vesselid, role, token);
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
        
        customerid=selproject.options[selproject.selectedIndex].value;
        vesselid=selVessel.options[selVessel.selectedIndex].value;
        role="observer";
        
        
        updateListofUsers(customerid, vesselid, role, token);
    }
  }
}

const updateListofUsers=(customerid, vesselid, role, token)=>{
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_get_list_trainees');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',customerid);
    formData.append('params[0][role]',role);
    formData.append('params[0][groupid]',vesselid);
  

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerGetListTrainees(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerGetListTrainees=(xhr)=>{
  if (xhr.response){
    const response=JSON.parse(xhr.response);
    const selUserlist=document.querySelector('#id_userlist');
    if (response){
      selUserlist.innerHTML='';
      const optionsUsers = response;

      optionsHTML='';
      
      optionsUsers.forEach(optionData=>{
            optionsHTML += `<option value="${optionData.id}">${optionData.groupname}_${optionData.billid} ${optionData.firstname}, ${optionData.lastname}</option>`;
      })
      selUserlist.innerHTML = optionsHTML;

      let activeSpan = document.querySelector('#fitem_id_userlist>div>div>span[role="option"]');
          if (activeSpan!==null && optionsUsers.length>0){
              activeSpan.setAttribute('data-value',(optionsUsers.length===0)?'':optionsUsers[0].billid);
              const span=document.createElement('span');
              span.setAttribute('aria-hidden',true);
              span.textContent="× "
              activeSpan.innerHTML="";
              activeSpan.appendChild(span);
              activeSpan.innerHTML+= optionsUsers[0].groupname+"_"+optionsUsers[0].billid+" "+optionsUsers[0].firstname+", "+optionsUsers[0].lastname;
          } else {
            //Asegurarse de que el select no tenga elementos seleccionados
            const padre=document.querySelector('#fitem_id_userlist .felement .form-autocomplete-selection');
            padre.innerHTML='';
            const newSpan=document.createElement('span');
            if (optionsUsers[0]){
              const span=document.createElement('span');
              span.setAttribute('aria-hidden',true);
              span.textContent="× "
              newSpan.innerHTML="";
              newSpan.dataset.value=optionsUsers[0].id;
              newSpan.setAttribute('data-active-selection',true);
              newSpan.setAttribute('role','option');
              newSpan.setAttribute('aria-selected',true);
              newSpan.style.fontSize='100%';
              newSpan.appendChild(span);
              newSpan.classList.add('badge','bg-secondary','text-dark','m-1');
              newSpan.innerHTML+= optionsUsers[0].groupname+"_"+optionsUsers[0].billid+" "+optionsUsers[0].firstname+", "+optionsUsers[0].lastname;
            } else {
              newSpan.innerHTML="No user selected";
            }
            
            padre.appendChild(newSpan);
            selUserlist.selectedIndex=-1;
          }

    }
}
}

