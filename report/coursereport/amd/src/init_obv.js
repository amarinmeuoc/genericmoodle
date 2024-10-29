import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';


export const loadTemplate = () => {

  //definicion de url
  const url=M.cfg.wwwroot+'/webservice/rest/server.php';

  areElementsLoaded('#id_selCustomer,#id_grouptrainee,#id_list_trainees,#id_list_courses, input').then((elements) => {
    //Se obtienen los valores de los campos necesarios
    const token = document.querySelector('input[name="token"]');
    const orderby = document.querySelector('input[name="orderby"]');
    const order = document.querySelector('input[name="order"]');
    const page= document.querySelector('input[name="page"]');

    const bosubmit=document.querySelector('#id_bosubmit');

    //Carga de los datos por defecto
    requestDataToServer(order, orderby, page, token, url);  
    
    bosubmit.addEventListener('click',()=>{
      requestDataToServer(order, orderby, page, token, url);
    })

  });

    
}

const requestDataToServer= ( or, orby, page, token, url)=>{

    //Obtención de los elementos necesarios: trainees y cursos
    const list_trainees= document.querySelector('#id_list_trainees');
    const list_courses= document.querySelector('#id_list_courses');
  
    //Obtención del cliente seleccionado
    const customerid = document.querySelector('input[name="selCustomer"]').value;

    //Obtención del grupo seleccionado
    const groupSel = document.querySelector('#id_grouptrainee').value;
  
    //Obtención de los valores de los campos necesarios
    const billid = list_trainees.value;
    const wbs = list_courses.value;
    
    
    //Fechas seleccionadas
    const startdate_day = document.querySelector('#id_startdate_day');
    const startdate_month = document.querySelector('#id_startdate_month');
    const startdate_year = document.querySelector('#id_startdate_year');
    const startdate = Math.floor(new Date(startdate_year.value + '.' + startdate_month.value + '.' + startdate_day.value).getTime() / 1000);
        
    //Tipo de consulta
    const queryType = document.querySelector('input[type="radio"][name="status"]:checked');

    //orden y orderby
    const order = or.value;
    const orderby = orby.value;
        
        
        const xhr = new XMLHttpRequest();
        
        
        xhr.open('POST', url, true);
    
        const formData = new FormData();
        formData.append('wstoken', token.value);
        formData.append('wsfunction', 'report_coursereport_get_assessment');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][customerid]', customerid);
        formData.append('params[0][groupid]', groupSel);
        formData.append('params[0][billid]', billid);
        formData.append('params[0][wbs]', wbs);
        formData.append('params[0][startdate]', startdate);
        formData.append('params[0][offset]', 50);
        formData.append('params[0][order]', order);
        formData.append('params[0][orderby]', orderby);
        formData.append('params[0][queryType]', queryType.value);
        formData.append('params[0][page]', page.value);

        xhr.send(formData); 
        
        xhr.onload = (event) => {
          if (xhr.status === 200) {
            onLoadFunction(xhr);
          } 
        };

        xhr.onerror = () => {
            reject("Solicitud fallida");  // Rechazando la promesa en caso de error
        };

         // Configurar los otros eventos del XHR
        xhr.onloadstart = (event) => {
            showLoader(event);
        };

        xhr.onprogress = (event) => {
            onProgressFunction(event);
        };

        xhr.onloadend = (event) => {
            hideLoader(event);
        };
    

  };

const onProgressFunction= (event)=>{
  if (event.lengthComputable) {
    window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
  } else {
    window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
  }
}

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

const onLoadFunction=(myXhr)=>{
    if (myXhr.readyState=== 4 && myXhr. status === 200){
        const res=JSON.parse(myXhr.response);
        res[0].assessment_list=res[0].assessment_list.map(obj=>{
          if (obj.assessment===null){
            obj.assessment='';
          } else {
            obj.assessment=obj.assessment*1;
            obj.assessment= obj.assessment.toFixed(2);
          }
          
          obj.attendance=obj.attendance*1;
          obj.attendance= obj.attendance.toFixed(2);
          return obj;
        });
        
        res[0].pages=truncateArrayWithActiveMiddle(res[0].pages,8);
        showTemplateAssessment(res[0]);
        
    }
}

function showTemplateAssessment(response){
    //Render the choosen mustache template by Javascript
    Templates.renderForPromise('report_coursereport/content_obv-ajax',response)
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
          const token = document.querySelector('input[name="token"]');
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
          
          requestDataToServer(order, orderby, page, token, url);
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
          const orderby = document.querySelector('input[name="orderby"]');
          const order = document.querySelector('input[name="order"]');

          requestDataToServer(order, orderby, page, token, url);
        });
      });

    })
    .catch((error)=>displayException(error));
  }

  function truncateArrayWithActiveMiddle(arr, maxLength) {
    const activeIndex = arr.indexOf(arr.find(item => item.active)); // Combine find and indexOf
  
    // Handle cases where there's no active element or less than maxLength elements
    if (activeIndex === -1 || arr.length <= maxLength) {
      return arr;
    }
  
    // Similar logic to calculate before and after lengths
    const halfLength = Math.floor(maxLength / 2);
    const beforeLength = Math.min(halfLength, activeIndex);
    const afterLength = Math.min(halfLength, arr.length - activeIndex - 1);
  
    // Use a loop to iterate and build the truncated array
    const truncatedArray = [];
    for (let i = activeIndex - beforeLength; i <= activeIndex + afterLength; i++) {
      if (i >= 0 && i < arr.length) { // Ensure we stay within array bounds
        truncatedArray.push(arr[i]);
      }
    }
  
    return truncatedArray;
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
