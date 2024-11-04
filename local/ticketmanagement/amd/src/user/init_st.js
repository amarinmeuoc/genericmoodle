import {exception as displayException} from 'core/notification'; 
import Templates from 'core/templates';

export const loadTemplate =() => {
      //definicion de url
  const url=M.cfg.wwwroot+'/webservice/rest/server.php';

  areElementsLoaded('input[name="user"],#id_category, #id_subcategory,input[name="token"],#startdate,#enddate').then((elements) => {
    //Se obtienen los valores de los campos necesarios
    const token = document.querySelector('input[name="token"]').value;
    const orderby = document.querySelector('input[name="orderby"]').value;
    const order = document.querySelector('input[name="order"]').value;
    const page= document.querySelector('input[name="page"]').value;
    const userid=document.querySelector('input[name="user"]').value;
    const activePage=1;
    

    //Carga de los datos por defecto
    requestDataToServer(activePage, userid, order, orderby, page, token, url);  

  });
}


const requestDataToServer=(activePage,userid, order,orderby,page,token,url)=>{
  let xhr = new XMLHttpRequest();
    
    //Se prepara el objeto a enviar
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction', 'local_ticketmanagement_get_tickets_byUserId');
    formData.append('moodlewsrestformat', 'json');
    formData.append('params[0][order]',order);
    formData.append('params[0][orderby]',orderby);
    formData.append('params[0][page]',page);
    formData.append('params[0][userid]',userid);
    formData.append('params[0][activePage]',activePage);
    
    
    

    xhr.open('POST',url,true);
    xhr.send(formData);

    xhr.onload = (ev)=> {
        reqHandlerGetTickets(xhr);
    }

    xhr.onerror = ()=> {
        rejectAnswer(xhr);
    }
}

const reqHandlerGetTickets=(xhr)=>{
  if (xhr.readyState=== 4 && xhr. status === 200){
    if (xhr.response){
      const response=JSON.parse(xhr.response);
      window.console.log(response);
        loadTemplatefromResponse(response);
    }
 }
}




const loadTemplatefromResponse=(response)=>{
  //Render the choosen mustache template by Javascript
  Templates.renderForPromise('local_ticketmanagement/content_user-ajax',response)
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

        const activePage=document.querySelector('li.page-item.active>a').textContent.trim();

        const userid=document.querySelector('input[name="user"]').value;
        
        requestDataToServer(activePage,userid, parseInt(order.value),orderby.value,page.value,token,url);
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
        const activePage=parseInt(page.value);
        const userid=document.querySelector('input[name="user"]').value;

        requestDataToServer(activePage,userid,parseInt(order), orderby, parseInt(page.value), token.value, url);
      });
    });
  })
  .catch((error)=>displayException(error));
}

// Función para verificar que todos los elementos seleccionados estén cargados
const areElementsLoaded = (selector) => {
    return new Promise((resolve) => {
        const checkElements = () => {
            const elements = document.querySelectorAll(selector);
            if (elements.length > 0 && Array.from(elements).every(elem => elem !== null)) {
                resolve(elements);
            } else {
                requestAnimationFrame(checkElements);
            }
        };
        checkElements();
    });
  };