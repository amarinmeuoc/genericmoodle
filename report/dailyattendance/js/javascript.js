const selCustomer=document.querySelector('#id_selcustomer');
selCustomer.addEventListener('change',(ev)=>{
    let xhr = new XMLHttpRequest();
    
    xhr.onload=reloadAttendanceForm;
    xhr.open('POST','.',true);
    const formData= new FormData();
    const selectedOption=ev.target.options[ev.target.selectedIndex].value;
    formData.append('selCustomer',selectedOption);

    //Envio de datos
    xhr.send(formData);

});

const reloadAttendanceForm=()=>{
    if (this.readyState=== 4 && this. status === 200){
        if (this.response){
            const selectedCustomer=JSON.parse(this.response);
            
            
        } 
    }
}