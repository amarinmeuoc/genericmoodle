const url=M.cfg.wwwroot+'/webservice/rest/server.php';
export const init = () => {
    //customer id
    const customerid=(document.querySelector('#id_selcustomer')!==null)?document.querySelector("#id_selcustomer").value:document.querySelector('input[name="selcustomer"').value;
    let trainee_list=document.querySelectorAll('.bodownload');
    trainee_list.forEach((node)=>{
        node.addEventListener('click',(e)=>{
            downloadcsv(e,customerid,url);
            
        });
    })
    
}

const downloadcsv = (e,customerid,url)=>{
    e.preventDefault();
    const element=e.target.closest('a');
    let list_of_trainees=element.getAttribute('data-id');
    list_of_trainees=list_of_trainees.split(",");
    list_of_trainees=list_of_trainees.reduce((arr,elem)=>{
        const aux= elem.split("_");
        //Si aux.length<2 entonces devuelve return [...arr,{group:'None',billid:aux[1]}];
        if (aux.length<2)
            elem=[...arr,{group:'None',billid:aux[1]}];
        else
            elem=[...arr,{group:aux[0],billid:aux[1]}];
        return elem;
    },[]);
    
    const xhr=new XMLHttpRequest();
    xhr.open('POST',url,true);
    const formData= new FormData();
    const token=document.querySelector('input[name="token"]').value;
    formData.append('wstoken',token);
    formData.append('wsfunction','report_partialplan_get_trainees');
    formData.append('moodlewsrestformat','json');
    
    for (let i = 0; i < list_of_trainees.length; i++) {
        const trainee = list_of_trainees[i];
        
        // Verificar si las propiedades existen y no son undefined
        const hasBillid = trainee.billid !== undefined && trainee.billid !== 'undefined';
        const hasGroup = trainee.group !== undefined && trainee.group !== 'undefined';
    
        if (hasBillid || hasGroup) {
            // Utilizar trim() solo si los valores son válidos
            const group = hasGroup ? trainee.group.trim() : '';
            const billid = hasBillid ? trainee.billid.trim() : '';
    
            formData.append(`params[${i}][customerid]`, parseInt(customerid));
            formData.append(`params[${i}][group]`, group);
            formData.append(`params[${i}][billid]`, billid);
        }
    }
    
            
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

      //Prepare the excel for to be downloaded
      createExcelFromJSON(res);
  }
  }

const onProgressFunction= (event)=>{
    if (event.lengthComputable) {
      window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
    } else {
      window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
    }
}

const createExcelFromJSON=(res)=>{
  
    if (res.length>0){
        const titles=Object.keys(res[0]);
        res=res.map(elem=>{
            return Object.values(elem);
        })
        res.unshift(titles);
    }
    
    let csvContent='';
    res.forEach(row=>{
        csvContent+=row.join(';')+'\n';
    })
    const blob = new Blob(['\uFEFF' +csvContent], { type: 'text/csv;charset=utf-16;' });
    
    const objUrl = URL.createObjectURL(blob);
    const dr=new Date();
    const dateFile=dr.getDate();
    const month=dr.getMonth()+1
    const year=dr.getFullYear();
    const nameFile='Trainee_list_loader_'+dateFile+'.'+month+'.'+year+'.csv';
    const link = document.createElement('a');
    link.setAttribute('href', objUrl);
    link.setAttribute('download', nameFile);
    
    link.click();
    return;
    

    
   
}

