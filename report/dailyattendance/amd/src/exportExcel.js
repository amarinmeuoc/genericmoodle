
export const init=(XLSX, filesaver,blobutil,token)=>{
    const boexport=document.querySelector('#idexport');
    boexport.addEventListener('click',(e)=>{
        const loader=document.querySelector('.loader');
        loader.classList.remove('hide');
        loader.classList.add('show');
        exportToExcel(e,XLSX,filesaver,blobutil,token);
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil,token)=>{
    const data={
        orderby:'DateAtt',
        customerid:document.querySelector('#customerid').value,
        groupid:document.querySelector('#selgroupid').value,
        billid:document.querySelector('#billid').value,
        attP:(document.querySelector('#chkPresent').checked)?true:false,
        attA:(document.querySelector('#chkAbsent').checked)?true:false,
        attL:(document.querySelector('#chkLate').checked)?true:false,
        attE:(document.querySelector('#chkExcused').checked)?true:false,
        startdate:document.querySelector('#startdate').value,
        enddate:document.querySelector('#enddate').value,
        token:token
    }
    prepareDataToSend(data);
}

const prepareDataToSend=(data)=>{
    const xhr=new XMLHttpRequest();
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    xhr.open('POST',url,true);
 
    const formData= new FormData();
    formData.append('wstoken',data.token);
    formData.append('wsfunction','report_dailyattendance_get_list_courses');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][customerid]',data.customerid);
    formData.append('params[0][order]','ASC');
    formData.append('params[0][orderby]',data.orderby);
    formData.append('params[0][groupid]',data.groupid);
    formData.append('params[0][billid]',data.billid);
    formData.append('params[0][page]',0);
    formData.append('params[0][offset]',100);
    formData.append('params[0][startdate]',Math.floor(new Date(data.startdate).getTime() / 1000));
    formData.append('params[0][enddate]',Math.floor(new Date(data.enddate).getTime() / 1000));
    formData.append('params[0][attendanceStatus][statusPresent]',data.attP);
    formData.append('params[0][attendanceStatus][statusAbsent]',data.attA);
    formData.append('params[0][attendanceStatus][statusLate]',data.attL);
    formData.append('params[0][attendanceStatus][statusExcused]',data.attE);

    xhr.send(formData);
    xhr.onload=(event)=>{
        onLoadFunction(xhr);
    }

    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 

    xhr.onloadend=(event)=>{
        const loader=document.querySelector('.loader');
        loader.classList.remove('show');
        loader.classList.add('hide');
    };
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };

}

const onLoadFunction=(myXhr)=>{
    const loader=document.querySelector('.loader');
    loader.classList.add('.hide');
    loader.classList.remove('.show');

    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        createExcelFromJSON(res,'dailyattendance');
        
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
    if (res[0].attendance_list.length>0){
        listado=res[0].attendance_list.map(elem=>{
            const date=new Date(elem.dateatt*1000);
            const year=date.getFullYear();
            const month=date.getMonth();
            const day=date.getDate();
            elem.dateatt=new Date(year,month,day);
            elem.dateatt.setDate(elem.dateatt.getDate()+1);
            return Object.values(elem);
        });
        let titles=Object.keys(res[0].attendance_list[0]);
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
        Title: "Daily attendance report",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year,month,dateFile)
    };
    wb.SheetNames.push("daily attendance");
    
    const ws=XLSX.utils.aoa_to_sheet(listado);
    wb.Sheets["daily attendance"]=ws;
    const wbout=XLSX.write(wb,{bookType:'xlsx',type:'binary'});
    const nameFile='dailyReport_'+dateFile+'.'+month+'.'+year+'.'+hour+'.'+min+'.xlsx';
    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}),nameFile)
    
}

const s2ab=(s) => {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}