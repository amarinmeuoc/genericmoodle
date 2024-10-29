
export const init=(XLSX, filesaver,blobutil)=>{
    const boexportAss=document.querySelector('#id_export_ass');
    const boexportAtt=document.querySelector('#id_export_att');
    const boexportTrainee=document.querySelector('#id_export_trainee');
    



    boexportAss.addEventListener('click',(e)=>{
        const customerid=document.querySelector('#selcustomer').value;
        exportToExcel(e,XLSX,filesaver,blobutil,'coursereport',customerid);
    });

    boexportAtt.addEventListener('click',(e)=>{
        const customerid=document.querySelector('#selcustomer').value;
        exportToExcel(e,XLSX,filesaver,blobutil,'dailyattendancereport',customerid);
    });

    boexportTrainee.addEventListener('click',(e)=>{
        const customerid=document.querySelector('#selcustomer').value;
        exportToExcel(e,XLSX,filesaver,blobutil,'traineereport',customerid);
    });
}

const exportToExcel=(e,XLSX,filesaver,blobutil,op,customerid)=>{
    const token=document.querySelector('input[name="token"]').value;
    
    prepareDataToSend(token,op,customerid);
}

const prepareDataToSend=(token,op,customerid)=>{
    const xhr=new XMLHttpRequest();
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    xhr.open('POST',url,true);
 
    const formData= new FormData();
    formData.append('wstoken',token);
    switch (op) {
        case 'coursereport':
            formData.append('wsfunction','report_coursereportadmin_get_total_assessment');
            break;
        case 'dailyattendancereport':
            formData.append('wsfunction','report_coursereportadmin_get_total_dailyattendance');
            break;
        case 'traineereport':
            formData.append('wsfunction','report_coursereportadmin_get_total_trainee_report');
            break;
        default:
            break;
    }
    
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][request]',op);
    formData.append('params[0][customerid]',customerid);
    
    const cargador=document.querySelector('.loader');
    cargador.classList.remove('hide');

    xhr.send(formData);
    xhr.onload=(event)=>{
        onLoadFunction(xhr,op);
    }

    xhr.onloadstart=(event)=>{
        
    }

    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };

    xhr.onloadend=(event)=>{
        const cargador=document.querySelector('.loader');
        cargador.classList.add('hide');
    }

}

const onLoadFunction=(myXhr,op)=>{
    const loader=document.querySelector('.loader');
    loader.classList.add('.hide');
    loader.classList.remove('.show');

    if (myXhr.readyState===4 && myXhr.status===200){
        const res=JSON.parse(myXhr.response);
        switch (op) {
            case 'coursereport':
                createExcelFromJSON(res,'courseReport');
                break;
            case 'dailyattendancereport':
                createExcelFromJSON(res,'dailyAttendanceReport');
                break;
            case 'traineereport':
                createExcelFromJSON(res,'traineeReport');
                break;
            default:
                break;
        }
        
        
    }
}

const onProgressFunction=(event) =>{
    console.log(`Uploaded ${event.loaded} of ${event.total}`);
    
}

const createExcelFromJSON=(res,op)=>{
    
    let listado=[];

    const wb=XLSX.utils.book_new();
    const dr=new Date();
    const dateFile=dr.getDate();
    const month=dr.getMonth()+1
    const year=dr.getFullYear();
    const min=dr.getMinutes();
    const hour=dr.getHours();
    
    wb.Props={
        Title: "Course report",
        Subject: "Training program report",
        Author: "Alberto Marín",
        CreateDate: new Date(year,month,dateFile)
    };

    if (op==='courseReport'){
        if (res[0].assessment_list.length>0){
            listado = res[0].assessment_list.map(elem => {
                // Convertir elem.startdate a objeto Date
                const sdate = new Date(elem.startdate * 1000);
                //sdate.setDate(sdate.getDate() + 1); // Sumar 1 día de forma segura
                elem.startdate = sdate; // Asignar la nueva fecha a elem.startdate
            
                // Convertir elem.enddate a objeto Date
                const edate = new Date(elem.enddate * 1000);
                elem.enddate = edate; // Asignar la fecha a elem.enddate
            
                // Procesar el valor de assessment si no es nulo
                if (elem.assessment !== null) {
                    elem.assessment = Math.round(parseFloat(elem.assessment) * 100) / 100;
                }
            
                // Procesar el valor de attendance
                elem.attendance = Math.round(parseFloat(elem.attendance) * 100) / 100;
            
                // Eliminar las propiedades innecesarias
                delete elem.customerid;
                delete elem.groupid;
            
                return Object.values(elem); // Devolver el objeto elem como array de valores
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
        wb.SheetNames.push("courseAssessment");
        const ws=XLSX.utils.aoa_to_sheet(listado);
        wb.Sheets["courseAssessment"]=ws;
    }

    if (op==='traineeReport'){
        if (res[0].assessment_list.length>0){
            const data=res[0].assessment_list;
            const groupedByMail=data.reduce((acc,curr)=>{
                const {customercode,groupname,billid,firstname,lastname,email,assessment,attendance}=curr;
                if (!acc[email]){
                    acc[email]={customercode,groupname,billid,firstname,lastname,email,countAss:0,countAtt:0, sumAssessment:0,sumAttendance:0};
                }
                if (assessment!==null) {
                    acc[email].countAss++;
                    acc[email].sumAssessment+=parseFloat(assessment)*1;
                    
                }
                acc[email].countAtt++;
                acc[email].sumAttendance+=parseFloat(attendance)*1;
                
                return acc;
            },{});
            const resultado=Object.entries(groupedByMail).map(([email,{customercode,groupname,billid,firstname,lastname,countAss,countAtt,sumAssessment,sumAttendance}])=>({
               
               customercode:customercode,
               groupname:groupname,
               billid:billid,
               firstname:firstname,
               lastname:lastname,
               email,
               attendance:sumAttendance/countAtt, 
               assessment:sumAssessment/countAss,
            }));
            
            listado=resultado.map(elem=>{
                
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
            
            //let titles=Object.keys(res[0].assessment_list[0]);
            listado.unshift(['customercode','group','billid','firstname','lastname','email','attendance','assessment']);
        }
        wb.SheetNames.push("traineeReport");
        const ws=XLSX.utils.aoa_to_sheet(listado);
        wb.Sheets["traineeReport"]=ws;
    }

    if (op==='dailyAttendanceReport'){
        if (res[0].attendance_list.length>0){
            listado=res[0].attendance_list.map(elem=>{
                const date=new Date(elem.dateatt*1000);
                elem.dateatt=date;
                return Object.values(elem);
            });
            let titles=Object.keys(res[0].attendance_list[0]);
            listado.unshift(titles);
        }
        wb.SheetNames.push("AttendanceReport");
        const ws=XLSX.utils.aoa_to_sheet(listado);
        wb.Sheets["AttendanceReport"]=ws;
    }
    
    
    
    
    
    
    const wbout=XLSX.write(wb,{bookType:'xlsx',type:'binary'});
    const nameFile=op+dateFile+'.'+month+'.'+year+'.'+hour+'.'+min+'.xlsx';
    saveAs(new Blob([s2ab(wbout)],{type:"application/octet-stream"}),nameFile)
    
}

const s2ab=(s) => {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}