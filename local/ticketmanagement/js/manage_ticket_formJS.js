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
      let role='student'
      if (e.target.options[e.target.selectedIndex].textContent==='PCO'){
        role='observer';
      } else if (e.target.options[e.target.selectedIndex].textContent==='UTE'){
        role='controller';
      }
      
          
      
      
      updateListofUsers(customerid, vesselid, role, token);
    });

    const selcategory=document.querySelector("#id_category");
    selcategory.addEventListener('change',(e)=>{
      const categoryid=e.target.value;
      updateSubcategory(categoryid,token);

    });

    const selUserlist=document.querySelector('#id_userlist');

    selUserlist.addEventListener('change',(e)=>{
      const userid=e.target.value;
      
      updateFamilyMembersSelectBox(userid);
    })

})

const updateFamilyMembersSelectBox=(userid)=>{
  const gestorid=document.querySelector('input[name="gestorid"]').value;
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_get_family_members');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][userid]',userid);
    formData.append('params[0][gestorid]',gestorid);

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerLoadFamilyMembers(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerLoadFamilyMembers=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
      const response=JSON.parse(xhr.response);
      if (response.listadoFamily){
        const listadoFamily=response.listadoFamily;
        
        const selFamily=document.querySelector('#id_familiar');
        let optionsHTML="";
        if (listadoFamily.length>0){
          selFamily.innerHTML="";
        } else {
          optionsHTML=`<option value="0">No family registered for this user</option>`;
        }
        
        listadoFamily.forEach(elem=>{
          optionsHTML += `<option value="${elem.id}">${elem.name}, ${elem.lastname}</option>`;
        });
        selFamily.innerHTML=optionsHTML;
      }
      

      
    }
  }
}

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
        //Es observer porque el primer grupo por defecto es la pco
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
          if (typeof selUserlist.options[selUserlist.selectedIndex]!=='undefined'){
            const userid=selUserlist.options[selUserlist.selectedIndex].value;
            updateFamilyMembersSelectBox(userid);
          }
          
    }
}
}



const updateSubcategory= (categoryid,token)=>{
  const role=document.querySelector('input[name="role"]').value;
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_load_subcategories');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][categoryid]',categoryid);
    formData.append('params[0][role]',role);

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerLoadSubcategories(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerLoadSubcategories=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
        const response=JSON.parse(xhr.response);
        const selsubcategory=document.querySelector('#id_subcategory');
        if (response){
          selsubcategory.innerHTML='';
          const optionsSubcategories = response;
          

          optionsHTML='';
          optionsSubcategories.forEach(optionData=>{
                optionsHTML += `<option value="${optionData.id}">${optionData.subcategory}</option>`;
          })
          selsubcategory.innerHTML = optionsHTML;
        }
        
    }
  }
}


