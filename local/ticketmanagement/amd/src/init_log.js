define([
    'core/notification', 
    'core/templates', 
    'local_ticketmanagement/funciones_comunes' // Ajusta la ruta según sea necesario
], function(Notification, Templates, funcionesComunes){
  const loadTemplate =() => {
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';

    funcionesComunes.areElementsLoaded('#id_project,#id_vessel,#id_userlist,#id_category, #id_subcategory,input[name="token"],#startdate,#enddate').then((elements) => {
      //Se obtienen los valores de los campos necesarios
      const token = document.querySelector('input[name="token"]').value;
      

      //Load select logistics
      

      obtenerGestorValue(token,url);     
      const bosearchdate=document.querySelector('#id_bosearchdate');
        const bosearchbyid=document.querySelector('#id_bosearchbyid');
      

      bosearchdate.addEventListener('click',()=>{
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const newStartdate=new Date(Date.parse(startdate.value));
        const newEnddate=new Date(Date.parse(enddate.value));
        const newStartdateUnix=funcionesComunes.truncateDateToDay(newStartdate);
        const newEnddateUnix=funcionesComunes.truncateDateToDay(newEnddate);
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const selstate=document.querySelector('#id_state').value;
        const gestorvalue=document.querySelector('#id_logistic').value;
        
        const obj={
          activePage:1,
          firstDayOfWeek:newStartdateUnix,
          lastDayOfWeek:newEnddateUnix,
          order:order,
          orderby:orderby,
          page:newPage.value,
          state:selstate,
          gestor:gestorvalue
        }
        window.console.log("busqueda pulsando boton: search by date");
        funcionesComunes.requestDataToServer(obj, token, url,'controller');
      })

      bosearchbyid.addEventListener('click',()=>{
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const ticketNumber=document.querySelector('#id_tesearch').value.trim();
        window.console.log("busqueda pulsando boton: search by ID");
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        
        requestDataToServerbyTicket(1,ticketNumber, order, orderby, newPage.value, token, url);
      });

      

    });
    }

    const selgestor = async (token, url) => {
      try {
        const response = await loadLosistic(token, url);
    
        const sel_logistic = document.querySelector('#id_logistic');
        sel_logistic.innerHTML = '';
    
        // Añadir la opción "All" como primera opción
        const allOption = document.createElement('option');
        allOption.value = '0'; // Puedes usar un valor especial, por ejemplo "all" o "0"
        allOption.textContent = 'All';
        sel_logistic.appendChild(allOption);
    
        // Iterar sobre el array y añadir cada opción
        response.forEach(user => {
          const option = document.createElement('option');
          option.value = user.id;  // Asigna el ID como valor de la opción
          option.textContent = user.name;  // Asigna el nombre como texto visible
          sel_logistic.appendChild(option);  // Añade la opción al select
        });
    
        return sel_logistic.value;
    
      } catch (error) {
        console.error(error);
        return undefined; // Si ocurre algún error, devolvemos undefined
      }
    };

    const obtenerGestorValue = async (token, url) => {
      try {
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const page= document.querySelector('input[name="page"]').value;
        
        const dates=funcionesComunes.getFirstAndLastDayOfCurrentWeek();
        const firstDayOfWeek=funcionesComunes.truncateDateToDay(dates.firstDayOfWeek);
        const lastDayOfWeek=funcionesComunes.truncateDateToDay(dates.lastDayOfWeek);
        
        const startdate= document.querySelector('#startdate');
        const enddate= document.querySelector('#enddate');

        startdate.value=dates.firstDayOfWeek.toISOString().slice(0, 10);
        enddate.value=dates.lastDayOfWeek.toISOString().slice(0, 10);
        const activePage=1;
        // Esperamos a que selgestor resuelva su valor
        const gestorvalue = await selgestor(token, url);
        //states in selectbox
        const selstate=document.querySelector('#id_state').value;
        
        const obj={
          activePage:activePage,
          firstDayOfWeek:firstDayOfWeek,
          lastDayOfWeek:lastDayOfWeek,
          order:order,
          orderby:orderby,
          page:page,
          state:selstate,
          gestor:gestorvalue
        }
        window.console.log("primera búsqueda");
        //Carga de los datos por defecto
        funcionesComunes.requestDataToServer(obj, token, url,'controller');  
        // Ahora puedes usar 'gestorvalue' en tu lógica
      } catch (error) {
        console.error('Error al obtener el valor de selgestor:', error);
      }
    };
    

    const loadLosistic = (token, url) => {
      return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
    
        // Se prepara el objeto a enviar
        const formData = new FormData();
        formData.append('wstoken', token);
        formData.append('wsfunction', 'local_ticketmanagement_get_logistic_users');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][dummy]', 'dummy');
    
        xhr.open('POST', url, true);
        xhr.send(formData);
    
        xhr.onload = () => {
          if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.response);
            resolve(response);  // Resolvemos la promesa con la respuesta
          } else {
            reject(new Error('Error en la petición'));
          }
        };
    
        xhr.onerror = () => {
          reject(new Error('Error en la conexión'));
        };
      });
    }

    

    const requestDataToServerbyTicket=(activePage,ticketId, order,orderby,page,token,url)=>{
    let xhr = new XMLHttpRequest();
      
      //Se prepara el objeto a enviar
      const formData= new FormData();
      formData.append('wstoken',token);
      formData.append('wsfunction', 'local_ticketmanagement_get_ticket_byId');
      formData.append('moodlewsrestformat', 'json');
      formData.append('params[0][order]',order);
      formData.append('params[0][orderby]',orderby);
      formData.append('params[0][page]',page);
      formData.append('params[0][ticketId]',ticketId);
      formData.append('params[0][activePage]',activePage);
      
      

      xhr.open('POST',url,true);

        setTimeout(()=>{
          xhr.send(formData);
      },100);

      xhr.onload = (ev)=> {
          reqHandlerGetTicketById(xhr);
      }

      xhr.onloadstart=(event)=>{
        funcionesComunes.showLoader(event);
      }

      xhr.onprogress = (event)=>{
          funcionesComunes.onProgressFunction(event);
      } 
      xhr.onloadend=(event)=>{
          funcionesComunes.hideLoader(event);
      }

      xhr.onerror = ()=> {
          funcionesComunes.rejectAnswer(xhr);
      }
    }

    


    const reqHandlerGetTicketById=(xhr)=>{
    if (xhr.readyState=== 4 && xhr. status === 200){
      if (xhr.response){
          const response=JSON.parse(xhr.response);
          loadTemplateSingleTicketfromResponse(response);
          window.console.log("response");
      }
    }
    }

    const loadTemplateSingleTicketfromResponse=(response)=>{
    //Render the choosen mustache template by Javascript
    Templates.renderForPromise('local_ticketmanagement/content_log-ajax',response)
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
          const page= document.querySelector('input[name="page"]');

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

          

          const newPage=document.querySelector('input[name="page"]');
          newPage.value=1;
          const ticketNumber=document.querySelector('#id_tesearch').value.trim();
        
        
        requestDataToServerbyTicket(1,ticketNumber, parseInt(order.value), orderby.value, newPage.value, token, url);
          
          
        });
      });

      const pages=document.querySelectorAll('.page-link');
      pages.forEach((page)=>{
        page.addEventListener('click',(ev)=>{
          ev.preventDefault();
          ev.stopPropagation();
          const token = document.querySelector('input[name="token"]');
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
          const activePage=1;
          const newPage=document.querySelector('input[name="page"]');
          newPage.value=1;
          const ticketNumber=document.querySelector('#id_tesearch').value.trim();
        
          window.console.log("puff");
        requestDataToServerbyTicket(1,ticketNumber, parseInt(order), orderby, newPage.value, token.value, url);
        });
      });

      const chk_communication=document.querySelectorAll("#tablebody input[type='checkbox']");

      chk_communication.forEach((chk)=>{
        chk.addEventListener('click',(e)=>{
          const ticket=e.target.dataset.ticketid;
          const value=(e.target.checked)?1:0;
          const token = document.querySelector('input[name="token"]').value;
          funcionesComunes.updateCommunication(ticket,value,token,url);
        })
      })

    })
    .catch((error)=>displayException(error));
    }

    

    return {
      loadTemplate:loadTemplate
    };
})



  