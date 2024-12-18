const url=M.cfg.wwwroot+'/webservice/rest/server.php';
const token=document.querySelector('input[name="token"]').value;

document.addEventListener('DOMContentLoaded',()=>{
    
    const selcategory=document.querySelector("#id_category");
    selcategory.addEventListener('change',(e)=>{
      const categoryid=e.target.value;
      updateSubcategory(categoryid,token);
    });
})


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
