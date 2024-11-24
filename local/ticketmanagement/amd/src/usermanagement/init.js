define([
    'core/notification', 
    'core/templates', 
    'local_ticketmanagement/funciones_comunes' // Ajusta la ruta según sea necesario
], function(Notification, Templates, funcionesComunes){
  const loadTemplate =() => {
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';

    funcionesComunes.areElementsLoaded('#id_project,#id_vessel,input[name="token"]').then((elements) => {
      //Se obtienen los valores de los campos necesarios
      const token = document.querySelector('input[name="token"]').value;
      const boshow=document.querySelector('#id_boshow');
      

      boshow.addEventListener('click',()=>{
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const groupid = document.querySelector('#id_vessel').value;
        const customerid= document.querySelector('#id_project').value;
                
        const obj={
          activePage:1,
          order:order,
          orderby:orderby,
          page:newPage.value,
          groupid:groupid,
          customerid:customerid
        }
        
        requestDataToServer(obj, token, url);
      });

      const bosearch=document.querySelector('#id_bosearchdate');
      bosearch.addEventListener('click',()=>{
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const groupid = document.querySelector('#id_vessel').value;
        const customerid= document.querySelector('#id_project').value;
        const billid=document.querySelector('#tebillid').value;
        const nombre=document.querySelector('#tenombre').value;
        const apellidos=document.querySelector('#teapellidos').value;
                
        const obj={
          activePage:1,
          order:order,
          orderby:orderby,
          page:newPage.value,
          groupid:groupid,
          customerid:customerid,
          billid:billid,
          nombre:nombre,
          apellidos:apellidos
        }
        
        requestDataToServer(obj, token, url);
      });

      
    });
  }

  const requestDataToServer=(obj,token,url)=>{
    
    let xhr = new XMLHttpRequest();

    const service= 'local_ticketmanagement_get_list_users';

    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', service);
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][customerid]',obj.customerid);
    formData.append('params[0][groupid]',obj.groupid);
    formData.append('params[0][order]',obj.order);
    formData.append('params[0][orderby]',obj.orderby);
    formData.append('params[0][page]',obj.page);
    formData.append('params[0][activePage]',obj.activePage);
    if ('billid' in obj){
      formData.append('params[0][billid]',obj.billid);
    }
    if ('nombre' in obj){
      formData.append('params[0][firstname]',obj.nombre);
    }
    if ('apellidos' in obj){
      formData.append('params[0][lastname]',obj.apellidos);
    }
    
    
    xhr.open('POST',url,true);
    //xhr.send(formData);

    setTimeout(()=>{
        xhr.send(formData);
    },100);

    xhr.onload = (ev)=> {
        reqHandlerGetUsers(xhr);
    }

    xhr.onloadstart=(event)=>{
        //self.showLoader(event);
        }

    xhr.onprogress = (event)=>{
        //self.onProgressFunction(event);
    } 
    xhr.onloadend=(event)=>{
        //self.hideLoader(event);
    }

    xhr.onerror = ()=> {
        //self.rejectAnswer(xhr);
    }
}

const reqHandlerGetUsers=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
        const response=JSON.parse(xhr.response);
        window.console.log(response);
        loadTemplateFromResponse(response);
    }
  }
}

const loadTemplateFromResponse=(response)=>{
  
            const template='local_ticketmanagement/users/table-users-ajax'
            //Render the choosen mustache template by Javascript
            Templates.renderForPromise(template,response)
            .then(({html,js})=>{
                const content=document.querySelector('#content');
                content.innerHTML='';
                Templates.appendNodeContents(content,html,js);
                
                //Ahora que se ha cargado la plantilla, se puede añadir el evento a los enlaces de ordenación
                const orderlinks=document.querySelectorAll('.orderby');
                orderlinks.forEach((link)=>{
                    link.addEventListener('click',(ev)=>{
                        ev.preventDefault();
                
                        //Se obtienen los valores de los campos necesarios
                        const token = document.querySelector('input[name="token"]').value;
                
                        //Obtenemos el ordenamiento y el campo de orden actuales
                        const orderby = document.querySelector('input[name="orderby"]');
                        const order = document.querySelector('input[name="order"]');
                        
                        //Si el campo de ordenamiento es el mismo que el actual, se cambia el orden
                        if (ev.target.dataset.activo==='activo'){
                            if (order.value==='1'){
                                ev.target.dataset.order=0;
                                order.value=0;
                            } else {
                                ev.target.dataset.order=1;
                                order.value=1;
                            }
                        } else {
                            orderlinks.forEach((link)=>{
                                link.dataset.activo='';
                            });
                            ev.target.dataset.activo='activo';
                            orderby.value=ev.target.dataset.orderby;
                            ev.target.dataset.order=1;
                            order.value=1;
                        }
                
                        const groupid = document.querySelector('#id_vessel').value;
                        const customerid= document.querySelector('#id_project').value;
                                
                        const obj={
                          activePage:1,
                          order:order.value,
                          orderby:orderby.value,
                          page:1,
                          groupid:groupid,
                          customerid:customerid
                        }

                        const billid=document.querySelector('#tebillid').value;
                        const nombre=document.querySelector('#tenombre').value;
                        const apellidos=document.querySelector('#teapellidos').value;

                        if (billid!=='' || nombre!=='' || apellidos !==''){
                          obj.billid=billid;
                          obj.nombre=nombre;
                          obj.apellidos=apellidos;
                        }

                        
                        requestDataToServer(obj, token, url);
                
                      
                    });
                });
            
                const pages=document.querySelectorAll('.page-link');
                pages.forEach((page)=>{
                    page.addEventListener('click',(ev)=>{
                        ev.preventDefault();
                        ev.stopPropagation();
                        const token = document.querySelector('input[name="token"]').value;
                        const page= document.querySelector('input[name="page"]');
                
                        //Se obtiene el elemento padre del elemento clicado
                        const padre=ev.currentTarget.parentElement;
                        
                        if (padre.dataset.control==='first' || padre.dataset.control==='last' || padre.dataset.control==='previous' || padre.dataset.control==='next'){
                        //Si es la primera página se coge el valor de arial-label
                        page.value=ev.currentTarget.getAttribute('aria-label');
                        } else {
                        page.value=ev.currentTarget.textContent.trim();
                        }
                        
                        //Obtenemos el ordenamiento y el campo de orden actuales
                        const orderby = document.querySelector('input[name="orderby"]').value;
                        const order = document.querySelector('input[name="order"]').value;
                        const activePage=parseInt(page.value);
                        
                        const groupid = document.querySelector('#id_vessel').value;
                        const customerid= document.querySelector('#id_project').value;
                                
                        const obj={
                          activePage:activePage,
                          order:order,
                          orderby:orderby,
                          page:page.value,
                          groupid:groupid,
                          customerid:customerid
                        }

                        const billid=document.querySelector('#tebillid').value;
                        const nombre=document.querySelector('#tenombre').value;
                        const apellidos=document.querySelector('#teapellidos').value;

                        if (billid!=='' || nombre!=='' || apellidos !==''){
                          obj.billid=billid;
                          obj.nombre=nombre;
                          obj.apellidos=apellidos;
                        }
                        
                        requestDataToServer(obj, token, url);
                       
                        
                
                        
                    });
                });

                
            
            })
            .catch((error)=>displayException(error));
}

  return {
    loadTemplate:loadTemplate
  };
});

