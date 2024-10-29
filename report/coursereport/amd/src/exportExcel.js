
export const init=(XLSX, filesaver,blobutil)=>{
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';

    const boexport=document.querySelector('#idexport');
    boexport.addEventListener('click',(e)=>{
        exportToExcel(e,XLSX,filesaver,blobutil, url);
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil, url)=>{
    const token=document.querySelector('input[name="token"]').value;
    const startdate_day=document.querySelector('#id_startdate_day');
    const startdate_month=document.querySelector('#id_startdate_month');
    const startdate_year=document.querySelector('#id_startdate_year');
    const startdate=Math.floor(new Date(startdate_year.value+'.'+startdate_month.value+'.'+startdate_day.value).getTime()/1000);
    const data={
        orderby:'startdate',
        customerid:(document.querySelector('#id_selCustomer'))?document.querySelector('#id_selCustomer').value:document.querySelector('input[name="selCustomer"]').value,
        groupid:document.querySelector('#id_grouptrainee').value,
        billid:document.querySelector('#id_list_trainees').value,
        wbs:document.querySelector('#id_list_courses').value,
        startdate:startdate,
        queryType:document.querySelector('input[type="radio"][name="status"]:checked').value,
        token:token
    }
    prepareDataToSend(data, url);
}

const prepareDataToSend=(data, url)=>{
    const xhr=new XMLHttpRequest();
    
    xhr.open('POST',url,true);
 
    const formData= new FormData();
    formData.append('wstoken',data.token);
    formData.append('wsfunction','report_coursereport_get_assessment');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][customerid]',data.customerid);
    formData.append('params[0][order]',1);
    formData.append('params[0][orderby]',data.orderby);
    formData.append('params[0][groupid]',data.groupid);
    formData.append('params[0][billid]',data.billid);
    formData.append('params[0][page]',0);
    formData.append('params[0][offset]',100);
    formData.append('params[0][startdate]',data.startdate);
    formData.append('params[0][queryType]',data.queryType);
    formData.append('params[0][wbs]',data.wbs);
    
    
    setTimeout(()=>{
        xhr.send(formData);
    },100);
    
    xhr.onload=(event)=>{
        onLoadFunction(xhr);
    }

    xhr.onloadstart=(event)=>{
        showLoader(event);
    }

    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        hideLoader(event);
    }
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
    const showLoader=(event)=>{
        const loader=document.querySelector('.loader');
        const table=document.querySelector('.generaltable');
        loader.classList.remove('hide');
        loader.classList.add('show');
        table.classList.add('hide');
      
      }
      
      const hideLoader=(event)=>{
        const loader=document.querySelector('.loader');
        const table=document.querySelector('.generaltable');
        loader.classList.remove('show');
        loader.classList.add('hide');
        table.classList.remove('hide');
      }

}

const onLoadFunction=(myXhr)=>{
    const loader=document.querySelector('.loader');
    loader.classList.add('.hide');
    loader.classList.remove('.show');

    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        createExcelFromJSON(res,'courseReport');
        
    }
}

const onProgressFunction=(event) =>{
    console.log(`Uploaded ${event.loaded} of ${event.total}`);
    const loader=document.querySelector('.loader');
    loader.classList.remove('.hide');
    loader.classList.add('.show');
}

const createExcelFromJSON=(res,op)=>{
    
    let listado=[];
    if (res[0].assessment_list.length>0){
        listado=res[0].assessment_list.map(elem=>{
            const sdate=new Date(elem.startdate*1000);
            elem.startdate=sdate;
            const edate=new Date(elem.enddate*1000);
            elem.enddate=edate;
            elem.assessment=elem.assessment*1;
            elem.assessment=parseFloat(elem.assessment);
            elem.assessment= Math.round(elem.assessment*100)/100;
            elem.attendance=elem.attendance*1;
            elem.attendance=parseFloat(elem.attendance);
            elem.attendance= Math.round(elem.attendance*100)/100;
            delete elem.customerid;
            delete elem.groupid;

            return Object.values(elem);
        });
        if (res[0].ifobserver){
            listado=res[0].assessment_list.map(elem=>{
                delete elem.customercode;
                return Object.values(elem);
            });
        }
        let titles=Object.keys(res[0].assessment_list[0]);
        listado.unshift(titles);
    }
    
    const wb=XLSX.utils.book_new();
    const dr=new Date();
    const dateFile=dr.getDate();
    const month=dr.getMonth()+1
    const year=dr.getFullYear();
    const min=dr.getMinutes();
    const hour=dr.getHours();
    
    wb.Props={
        Title: "Course assessment report",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year,month,dateFile)
    };
    wb.SheetNames.push("courseAssessment");
    
    const ws=XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets["courseAssessment"]=ws;
    const wbout=XLSX.write(wb,{bookType:'xlsx',type:'binary'});
    const nameFile='assessmentReport'+dateFile+'.'+month+'.'+year+'.'+hour+'.'+min+'.xlsx';
    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}),nameFile)
    
}

const s2ab=(s) => {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}