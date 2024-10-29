export const sortTPTable=()=>{
    const linkDomStartDate=document.querySelector('#sd-link');
    const linkDomEndDate=document.querySelector('#ed-link');
    

    linkDomStartDate.addEventListener('click',x=>{
        x.preventDefault();
        orderbyField.setAttribute('value','startdate');
        if (document.readyState==='complete'){
            form.submit();
        }
    });
    linkDomEndDate.addEventListener('click',x=>{
        x.preventDefault();
        orderbyField.setAttribute('value','enddate');
        if (document.readyState==='complete'){
            form.submit();
        }
    });
   
}