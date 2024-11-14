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
      
      const bosearchdate=document.querySelector('#id_bosearchdate');
      const bosearchbyid=document.querySelector('#id_bosearchbyid');

      bosearchdate.addEventListener('click',()=>{
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const newStartdate=new Date(Date.parse(startdate.value));
        const newEnddate=new Date(Date.parse(enddate.value));
        const newStartdateUnix=funcionesComunes.truncateDateToDay(newStartdate);
        const newEnddateUnix=funcionesComunes.truncateDateToDay(newEnddate);
        const obj={
          activePage:1,
          firstDayOfWeek:newStartdateUnix,
          lastDayOfWeek:newEnddateUnix,
          order:order,
          orderby:orderby,
          page:newPage.value,
        }
        window.console.log("primero");
        funcionesComunes.requestDataToServer(obj, token, url,'controller');
      })

      bosearchbyid.addEventListener('click',()=>{
        const newPage=document.querySelector('input[name="page"]');
        newPage.value=1;
        const ticketNumber=document.querySelector('#id_tesearch').value.trim();
        
        
        requestDataToServerbyTicket(1,ticketNumber, order, orderby, newPage.value, token, url);
      });

      const obj={
        activePage:activePage,
        firstDayOfWeek:firstDayOfWeek,
        lastDayOfWeek:lastDayOfWeek,
        order:order,
        orderby:orderby,
        page:page,
      }
      window.console.log("segundo");
      //Carga de los datos por defecto
      funcionesComunes.requestDataToServer(obj, token, url,'controller');  

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
      xhr.send(formData);

      xhr.onload = (ev)=> {
          reqHandlerGetTicketById(xhr);
      }

      xhr.onerror = ()=> {
          rejectAnswer(xhr);
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

      const fila=document.querySelector('tbody>tr');
      if (typeof response.listadoTickets[0]!=='undefined')
        if (response.listadoTickets[0].state==='Closed' || response.listadoTickets[0].state==='Cancelled')
          fila.classList.add('cerrado');
      
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

    })
    .catch((error)=>displayException(error));
    }

    

    return {
      loadTemplate:loadTemplate
    };
})



  