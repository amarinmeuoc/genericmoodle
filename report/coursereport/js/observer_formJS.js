const url=M.cfg.wwwroot+'/webservice/rest/server.php';

document.addEventListener('DOMContentLoaded',()=>{
  const customerSel=document.querySelector('#id_selCustomer');
  const groupSel=document.querySelector('#id_grouptrainee');
  const token=document.querySelector('input[name="token"]').value;

  groupSel.addEventListener('change',()=>{
    const selListTrainees=document.querySelector('#id_list_trainees');
    const token=document.querySelector('input[name="token"]').value;
    updateListTrainees(selListTrainees,token);
    
  });
  
});


const requestgrouplistfrom= (customerid,token) => {
    const xhr=new XMLHttpRequest();
    xhr.open('POST',url,true);
    
    const formData=new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','report_coursereport_get_group_list');
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
        const groupSel=document.querySelector('#id_grouptrainee');
        groupSel.innerHTML='';
        res.map((elem)=>{
            groupSel.innerHTML+='<option value="'+elem.id+'">'+elem.name+'</option>';
        })
        groupSel.dispatchEvent(new Event('change'));

        //Actualizamos trainees y lista de cursos
        const selListTrainees=document.querySelector('#id_list_trainees');
        const selListCourses=document.querySelector('#id_list_courses');
        const token=document.querySelector('input[name="token"]').value;
        updateListTrainees(selListTrainees,token);
        updateListCourses(selListCourses,token);

    }
}

const onProgressFunction= (event)=>{
    if (event.lengthComputable) {
      window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
    } else {
      window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
    }
}

const removeOptions=(selectElement) =>{
    var i, L = selectElement.options.length - 1;
    for(i = L; i >= 0; i--) {
       selectElement.remove(i);
    }
 }

 const updateListTrainees=(listTrainees,token)=>{
  //Obtener el id del grupo seleccionado
   const groupSel=document.querySelector('#id_grouptrainee');
   const selectedGroupId=(groupSel.value==='')?0:groupSel.value;
  
   let xhr = new XMLHttpRequest();
  xhr.open('POST',url,true);
  const formData= new FormData();
  formData.append('wstoken',token);
  formData.append('wsfunction','report_coursereport_get_trainee_list');
  formData.append('moodlewsrestformat','json');
  formData.append('params[0][groupid]',selectedGroupId);
  xhr.send(formData);
  xhr.onload = ()=>{
      reloadTraineeList(xhr,listTrainees)
  };

  xhr.onprogress = (event)=>{
      onProgressFunction(event,listTrainees);
    } 
  
  xhr.onerror = function() {
      window.console.log("Solicitud fallida");
  };
}

const reloadTraineeList=(xhr,listTrainees)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
      if (xhr.response){
          const list_trainees=JSON.parse(xhr.response);
          
          removeOptions(listTrainees);

          list_trainees.map(obj=>{
              const opt=document.createElement('option');
              opt.value=obj.billid;
              opt.textContent=obj.groupname+"_"+obj.billid+" "+obj.firstname+", "+obj.lastname;
              listTrainees.appendChild(opt);
          });

          //Seleccionar el primer elemento de la lista
          const selTrainees=document.querySelector('#id_list_trainees');
          selTrainees.selectedIndex=0;

          let activeSpan = document.querySelector('#fitem_id_list_trainees>div>div>span[role="option"]');
          if (activeSpan!==null && list_trainees.length>0){
              activeSpan.setAttribute('data-value',(list_trainees.length===0)?'':list_trainees[0].billid);
              const span=document.createElement('span');
              span.setAttribute('aria-hidden',true);
              span.textContent="× "
              activeSpan.innerHTML="";
              activeSpan.appendChild(span);
              activeSpan.innerHTML+= list_trainees[0].groupname+"_"+list_trainees[0].billid+" "+list_trainees[0].firstname+", "+list_trainees[0].lastname;
          } else {
            //Asegurarse de que el select no tenga elementos seleccionados
            selTrainees.selectedIndex=-1;
          }
          
      } 
  }
}

const updateListCourses=(listCourses,token)=>{
  let xhr = new XMLHttpRequest();
  
  const customerSel=document.querySelector('#id_selCustomer');
  const selectedCustomerId=customerSel.value;
  xhr.open('POST',url,true);
  const formData= new FormData();
  formData.append('wstoken',token);
  formData.append('wsfunction','report_coursereport_get_course_list');
  formData.append('moodlewsrestformat','json');
  formData.append('params[0][customerid]',selectedCustomerId);
  xhr.send(formData);
  xhr.onload = ()=>{
      reloadCourseList(xhr,listCourses)
  };

  xhr.onprogress = (event)=>{
      onProgressFunction(event,listCourses);
    } 
  
  xhr.onerror = function() {
      window.console.log("Solicitud fallida");
  };
}


const reloadCourseList=(xhr,listCourses)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
      if (xhr.response){
          const list_courses=JSON.parse(xhr.response);
          
          removeOptions(listCourses);

          list_courses.map(obj=>{
              const opt=document.createElement('option');
              opt.value=obj.shortname;
              opt.textContent=obj.shortname+"_"+obj.fullname;
              listCourses.appendChild(opt);
          })

          let activeSpan = document.querySelector('#fitem_id_list_courses>div>div>span[role="option"]');
          if (activeSpan!==null && list_courses.length>0){
              activeSpan.setAttribute('data-value',(list_courses.length===0)?'':list_courses[0].shortname);
              const span=document.createElement('span');
              span.setAttribute('aria-hidden',true);
              span.textContent="× "
              activeSpan.innerHTML="";
              activeSpan.appendChild(span);
              activeSpan.innerHTML+= list_courses[0].shortname+"_"+list_courses[0].fullname;
          } else {
            //Asegurarse de que el select no tenga elementos seleccionados
            listCourses.selectedIndex=-1;
          }
          

          
      } 
  }
}

