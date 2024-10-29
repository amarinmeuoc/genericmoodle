import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
const url=M.cfg.wwwroot+'/webservice/rest/server.php';

export const loadITP = () => {
    //Aseguramos que el token esté cargado
    areElementsLoaded('#filteritpform input, #filteritpform select, #id_list_trainees, #id_tegroup').then((elements) => {
        //Se obtienen los valores de los campos necesarios
        const token = document.querySelector('input[name="token"]').value;
        const compacted = document.querySelector('#id_compacted').value;
        const orderby = document.querySelector('input[name="orderby"]');
        const order = document.querySelector('input[name="order"]');
        const list_trainees= document.querySelector('#id_list_trainees');
        const email = document.querySelector('input[name="email"]');
        const group = document.querySelector('#id_tegroup');
        if (list_trainees.selectedIndex!==-1){
            email.value=list_trainees.options[list_trainees.selectedIndex].value;
        } else {
            email.value='';
        }

        // Continuar con la carga de datos
        loadITPDataFromServer(token, email.value, orderby.value, order.value, compacted);

        //Definir el evento del selector de compactación
        document.querySelector('#id_compacted').addEventListener('change', (ev) => {
            //Al cambiar el valor del selector, se vuelve a cargar la información
            //y orderby se mantiene a startdate
            orderby.value = 'startdate';
            order.value=1;
            if (email.value)
                loadITPDataFromServer(token, email.value, orderby.value, order.value, ev.target.value);
            
        });

        //Definir el comportamiento del botón buscar
        document.querySelector('#id_boenviar').addEventListener('click', (ev) => {
            //Al hacer clic en el botón buscar, se vuelve a cargar la información
            //y orderby se mantiene a startdate
            orderby.value = 'startdate';
            order.value=1;
            if (list_trainees.selectedIndex!==-1){
                email.value=list_trainees.options[list_trainees.selectedIndex].value;
            } else {
                email.value='';
            }
            const compactedUpdated = document.querySelector('#id_compacted').value;
            if (email.value)
                loadITPDataFromServer(token, email.value, orderby.value, order.value, compactedUpdated);
        });

        //Defino el comportamiento del selector de grupo cuando cambie
        group.addEventListener('change',(ev)=>{
            const role='student';
            const customer=document.querySelector('input[name="customer"]').value;
            const groupname = ev.target.options[ev.target.selectedIndex].text;
            actualizarDesplegable(role,customer,groupname,url,token);
        });

        document.querySelector("#id_selCustomer").addEventListener('change',(ev)=>{
            
            //Obtiene el valor del campo customer
            const customer=ev.target.value;
            
            actualizarDesplegableGrupos(customer,url,token);
        });

    });
}

const actualizarDesplegableGrupos=(customer,url,token)=>{
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_itp_load_groups');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][customerid]',customer);
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerCustomerChangeEvent(xhr);
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

const reqHandlerCustomerChangeEvent=(xhr)=>{
    if(xhr.status == 200){
        let response = JSON.parse(xhr.responseText);
        /*
        Retorna la lista de usuarios asociados al grupo.
        Ejemplo de retorno:
        response=[
            {id: 1, name: 'C1'},
            {id: 2, name: 'C2'}
        ]
        */
        const selGroup=document.querySelector('#id_tegroup');
        selGroup.innerHTML = '';
        //Actualiza selGroup con los valores devueltos por response
        response.forEach((group) => {
            const option = document.createElement('option');
            option.value = group.id;
            option.textContent = group.name;
            selGroup.appendChild(option);
        });
        const role='student';
        const customer=document.querySelector('input[name="customer"]');
        //Actualizamos el campo oculto con el valor del texto del desplegable id_selCustomer seleccionado
        customer.value=document.querySelector('#id_selCustomer').options[document.querySelector('#id_selCustomer').selectedIndex].text;
        const groupname=selGroup.options[selGroup.selectedIndex].text;
        const token = document.querySelector('input[name="token"]').value;
        actualizarDesplegable(role,customer.value,groupname,url,token)

    }
}

const actualizarDesplegable=(role,customer,group,url,token)=>{
    return new Promise((resolve, reject) => {
        let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_itp_get_list_trainees');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][groupname]',group);
        formData.append('params[0][customername]',customer);
        formData.append('params[0][role]',role);
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerGroupChangeEvent(xhr);
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

const reqHandlerGroupChangeEvent=(xhr)=>{
    if(xhr.status == 200){
        let response = JSON.parse(xhr.responseText);
        /*
        Retorna la lista de usuarios asociados al grupo.
        Ejemplo de retorno:
        response=[
            {id: 480, username: 'g2c001@altra.com', firstname: 'student11', lastname: 'altra G2', email: 'g2c001@altra.com', customer: 'ALTR', billid: 'EN-111', role_name: 'student',groupname: 'G2'},
            {id: 481, username: 'g2c002@altra.com', firstname: 'student12', lastname: 'altra G2', email: 'g2c002@altra.com', customer: 'ALTR', billid: 'EN-111', role_name: 'student',groupname: 'G2'}
        ]
        */
        const selTrainees=document.querySelector('#id_list_trainees');
        selTrainees.innerHTML = '';

        //Actualiza selTrainees con los valores devueltos por response
        response.forEach((user) => {
            const option = document.createElement('option');
            option.value = user.email;
            option.textContent = `${user.groupname}_${user.billid} ${user.firstname}, ${user.lastname}`;
            selTrainees.appendChild(option);
        });

        let activeSpan = document.querySelector('#fitem_id_list_trainees>div>div>span[role="option"]');
        let selectedTrainee=response[0];
        if (selectedTrainee!==undefined){
            if (activeSpan!==null){
                activeSpan.setAttribute('data-value',(response.length===0)?'':response[0].email);
                const span=document.createElement('span');
                span.setAttribute('aria-hidden',true);
                span.textContent="× "
                activeSpan.innerHTML="";
                activeSpan.appendChild(span);
                activeSpan.innerHTML+= selectedTrainee.groupname+"_"+selectedTrainee.billid+" "+selectedTrainee.firstname+", "+selectedTrainee.lastname;
            } 
        } else {
            if (activeSpan!==null){
                activeSpan.setAttribute('data-value',(response.length===0)?'':response[0].email);
                const span=document.createElement('span');
                span.setAttribute('aria-hidden',true);
                span.textContent="× "
                activeSpan.innerHTML="";
                activeSpan.appendChild(span);
                activeSpan.innerHTML+= "This is an empty group.";
            } 
        }
    }
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

const reload=(elem)=>{
    const container = elem;
    if (container!==null){
        container.setAttribute('data-value',(list_trainees.length===0)?'':response[0].email);
        const span=document.createElement('span');
        span.setAttribute('aria-hidden',true);
        span.textContent="× "
        container.innerHTML="";
        container.appendChild(span);
        container.innerHTML+= selectedTrainee.groupname+"_"+selectedTrainee.billid+" "+selectedTrainee.firstname+", "+selectedTrainee.lastname;
    } 
    const content = container.innerHTML;
    container.innerHTML= content; 
    
   //this line is to watch the result in console , you can remove it later	
    console.log("Refreshed"); 

}