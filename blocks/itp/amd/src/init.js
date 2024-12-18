import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
const url=M.cfg.wwwroot+'/webservice/rest/server.php';

export const loadITP = () => {
    //Aseguramos que el token esté cargado
    areElementsLoaded('#filteritpform input, #filteritpform select').then((elements) => {
        //Se obtienen los valores de los campos necesarios
        const token = document.querySelector('input[name="token"]').value;
        const email = document.querySelector('input[name="email"]').value;
        const compacted = document.querySelector('#id_compacted').value;
        const orderby = document.querySelector('input[name="orderby"]');
        const order = document.querySelector('input[name="order"]');

        // Continuar con la carga de datos
        loadITPDataFromServer(token, email, orderby.value, order.value, compacted);

        //Definir el evento del selector de compactación
        document.querySelector('#id_compacted').addEventListener('change', (ev) => {
            //Al cambiar el valor del selector, se vuelve a cargar la información
            //y orderby se mantiene a startdate
            orderby.value = 'startdate';
            order.value=1;
            loadITPDataFromServer(token, email, orderby.value, order.value, ev.target.value);
        });
    });
}

const loadITPDataFromServer = (token, email, orderby, order, compacted) => {
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_itp_load_itp');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][email]',email);
        formData.append('params[0][orderby]',orderby);
        formData.append('params[0][order]',order);
        formData.append('params[0][compacted]',compacted);
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerLoadITP(xhr);
                resolve(); // Resuelve la promesa cuando la solicitud ha terminado correctamente
            } else {
                rejectAnswer(xhr);
                reject('Error al cargar la información'); // Rechaza la promesa en caso de error
            }
        }

        xhr.onerror = ()=> {
            rejectAnswer(xhr);
            reject('Error de red al cargar la información'); // Rechaza la promesa en caso de error de red
        }
    });
    
}

const reqHandlerLoadITP = (xhr) => {
    if(xhr.status == 200){
        let response = JSON.parse(xhr.responseText);
        
        if(response.errorcode){
            displayException(response.message);
        }else{
            Templates.render('block_itp/itp', response).then((html,js) => {
                const content=document.querySelector('.itp');
                content.innerHTML="";
                Templates.appendNodeContents('.itp',html,js);

                const token = document.querySelector('input[name="token"]').value;
                const email = document.querySelector('input[name="email"]').value;
                const compacted = document.querySelector('#id_compacted').value;
                const orderby = document.querySelector('input[name="orderby"]');
                const order = document.querySelector('input[name="order"]');
                
                //Definir el evento para el cambio de orden justo después de renderizar la tabla
                document.querySelectorAll('thead a').forEach((element) => {
                    element.addEventListener('click', (ev) => {
                        ev.preventDefault();
                        if (ev.target.dataset.activo==='activo'){
                            if (order.value === '1'){
                                order.value = '0';
                            }else{
                                order.value = '1';
                            }
                        } else {
                            orderby.value = ev.target.dataset.orderby;
                            order.value = '1';
                        }
                        loadITPDataFromServer(token, email, orderby.value, order.value, compacted);
                    });
                });

                //Definir el evento para mostrar el assessment
                const linkass=document.querySelectorAll('.linkass');

                linkass.forEach((link)=>{
                    link.addEventListener('click',showAssessmentList);
                });


                //Definir el evento para mostrar el attendance
                const linkatt=document.querySelectorAll('.linkatt');

                linkatt.forEach((link)=>{
                    link.addEventListener('click',showAttendanceList);
                });

                // Emitir evento personalizado después de actualizar los datos
                const event = new CustomEvent('itpDataUpdated');
                document.dispatchEvent(event);
                
            });
        }
    }else{
        displayException('Error al cargar la información');
    }
}

const rejectAnswer = (xhr) => {
    displayException('Error al cargar la información');
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


const showAssessmentList = (ev) => {
    ev.preventDefault();
    const courseid=ev.target.dataset.courseid;
    const token = document.querySelector('input[name="token"]').value;
    const email = document.querySelector('input[name="email"]').value;
    const xhr = new XMLHttpRequest();
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','block_itp_get_assessment_details');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][courseid]',courseid);
    formData.append('params[0][email]',email);
    xhr.open('POST',url,true);
    
    xhr.send(formData);
    xhr.onload = (event) =>{
        onLoadAssessmentFunction(xhr);
        };
    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
    
}

const showAttendanceList = (ev) => {
    ev.preventDefault();
    const courseid=ev.target.dataset.courseid;
    const token = document.querySelector('input[name="token"]').value;
    const email = document.querySelector('input[name="email"]').value;
    const startdate=ev.target.dataset.startdate;
    const enddate=ev.target.dataset.enddate;
    const xhr = new XMLHttpRequest();
    const formData= new FormData();
    formData.append('wstoken',token);
    formData.append('wsfunction','block_itp_get_daily_attendance');
    formData.append('moodlewsrestformat','json');
    formData.append('params[0][courseid]',courseid);
    formData.append('params[0][email]',email);
    formData.append('params[0][startdate]',startdate);
    formData.append('params[0][enddate]',enddate);
    xhr.open('POST',url,true);
    
    xhr.send(formData);
    xhr.onload = (event) =>{
        onLoadAttendanceFunction(xhr);
        };
    xhr.onprogress = (event)=>{
        onProgressFunction(event);
    } 
    xhr.onerror = function() {
        window.console.log("Solicitud fallida");
    };
    
}

const onLoadAttendanceFunction = (xhr) => {
    if(xhr.status == 200 || xhr.readyState == 4){
        let response = JSON.parse(xhr.responseText);
       
        if(response.errorcode){
            displayException(response.message);
        }else{
            
            Templates.render('block_itp/attendance', response).then((html) => {
                document.querySelector('.itp').innerHTML = html;

                //Definir el evento del botón de retroceso
                let boback=document.querySelector('.back');
                boback.addEventListener('click',(e)=>{
                    e.preventDefault();
                    //Se obtienen los valores de los campos necesarios
                    const token = document.querySelector('input[name="token"]').value;
                    const email = document.querySelector('input[name="email"]').value;
                    const compacted = document.querySelector('#id_compacted').value;
                    const orderby = document.querySelector('input[name="orderby"]').value;
                    const order = document.querySelector('input[name="order"]').value;

                    // Continuar con la carga de datos
                    loadITPDataFromServer(token, email, orderby, order, compacted);
                });
            });

            
        }
    }else{
        displayException('Error al cargar la información');
    }
}

const onLoadAssessmentFunction = (xhr) => {
    if(xhr.status == 200 || xhr.readyState == 4){
        let response = JSON.parse(xhr.responseText);
       
        if(response.errorcode){
            displayException(response.message);
        }else{
            
            Templates.render('block_itp/assessment', response).then((html) => {
                document.querySelector('.itp').innerHTML = html;

                //Definir el evento del botón de retroceso
                let boback=document.querySelector('.back');
                boback.addEventListener('click',(e)=>{
                    e.preventDefault();
                    //Se obtienen los valores de los campos necesarios
                    const token = document.querySelector('input[name="token"]').value;
                    const email = document.querySelector('input[name="email"]').value;
                    const compacted = document.querySelector('#id_compacted').value;
                    const orderby = document.querySelector('input[name="orderby"]').value;
                    const order = document.querySelector('input[name="order"]').value;

                    // Continuar con la carga de datos
                    loadITPDataFromServer(token, email, orderby, order, compacted);
                });
            });

            
        }
    }else{
        displayException('Error al cargar la información');
    }
}

const onProgressFunction= (event)=>{
    if (event.lengthComputable) {
      window.console.log(`Recibidos ${event.loaded} de ${event.total} bytes`);
    } else {
      window.console.log(`Recibidos ${event.loaded} bytes`); // sin Content-Length
    }
}