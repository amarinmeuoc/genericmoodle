define(['core/templates','core_form/modalform'],function(Templates,ModalForm) {
    let eventoCat="";
    let eventoSubCat="";
    let eventoFile="";
    let eventoPriority="";
    return {
        truncateArrayWithActiveMiddle: function(arr,maxLength){
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
        },
        areElementsLoaded: function (selector, parentElement = document) {
            return new Promise((resolve) => {
                const checkElements = () => {
                    const elements = parentElement.querySelectorAll(selector);
                    if (elements.length > 0 && Array.from(elements).every(elem => elem !== null)) {
                        resolve(elements);
                    } else {
                        requestAnimationFrame(checkElements);
                    }
                };
                checkElements();
            });
        },
        truncateDateToDay:function(date) {
            // Create a new Date object with the year, month, and date from the input date.
            const truncatedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        
            // Convert the truncated date to Unix time (milliseconds since the Unix epoch).
            const unixTime = truncatedDate.getTime() / 1000; // Divide by 1000 to get seconds
        
            return unixTime;
        },
        getFirstAndLastDayOfCurrentWeek:function(){
            const today = new Date();
            const dayOfWeek = today.getDay(); // 0 (Sunday) to 6 (Saturday)

            // Calculate the first day of the week (Monday)
            const firstDayOfWeek = new Date(today);
            firstDayOfWeek.setDate(today.getDate() - dayOfWeek );

            // Calculate the last day of the week (Sunday)
            const lastDayOfWeek = new Date(firstDayOfWeek);
            lastDayOfWeek.setDate(lastDayOfWeek.getDate() + 6);

            return { firstDayOfWeek, lastDayOfWeek };
        },
        loadTemplateFromResponse: function(response){
            const self=this; //Mantener el contexto
            const role = document.querySelector('input[name="role"]').value;
            const template=(role==='student')?'local_ticketmanagement/content_user-ajax':'local_ticketmanagement/content_log-ajax'
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
                
                        const activePage=document.querySelector('li.page-item.active>a').textContent.trim();
                
                        const startdate= document.querySelector('#startdate');
                        const enddate= document.querySelector('#enddate');
                
                        

                        if (role==='controller'){
                            const startdateUnixFormat=this.truncateDateToDay(new Date(startdate.value));
                            const enddateUnixFormat=this.truncateDateToDay(new Date(enddate.value));
                            const obj={
                                activePage:1,
                                firstDayOfWeek:startdateUnixFormat,
                                lastDayOfWeek:enddateUnixFormat,
                                order:parseInt(order.value),
                                orderby:orderby.value,
                                page:1,
                              }
                            self.requestDataToServer(obj, token.value, url,role);
                        } else if (role==='student'){
                            const userid=document.querySelector('input[name="user"]').value;
                            const obj={
                                activePage:1,
                                userid:userid,
                                order:order.value,
                                orderby:orderby.value,
                                page:1,
                              }
                            self.requestDataToServer(obj, token.value, url,role);
                        }
                        
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
                        const startdate= document.querySelector('#startdate');
                        const enddate= document.querySelector('#enddate');
                        //const role = document.querySelector('input[name="role"]').value;
                
                        
                        
                        if (role==='controller'){
                            const startdateUnixFormat=this.truncateDateToDay(new Date(startdate.value));
                            const enddateUnixFormat=this.truncateDateToDay(new Date(enddate.value));
                            const obj={
                                activePage:activePage,
                                firstDayOfWeek:startdateUnixFormat,
                                lastDayOfWeek:enddateUnixFormat,
                                order:parseInt(order),
                                orderby:orderby,
                                page:parseInt(page.value),
                              }
                              self.requestDataToServer(obj, token.value, url,role);
                        } else if (role==='student'){
                            const userid=document.querySelector('input[name="user"]').value;
                            const obj={
                                activePage:activePage,
                                userid:userid,
                                order:order,
                                orderby:orderby,
                                page:parseInt(page.value),
                              }
                              self.requestDataToServer(obj, token.value, url,role);
                        }
                        
                
                        
                    });
                });
            
            })
            .catch((error)=>displayException(error));
  
        },
        reqHandlerGetTickets: function(xhr){
            if (xhr.readyState=== 4 && xhr. status === 200){
              if (xhr.response){
                  const response=JSON.parse(xhr.response);
                  response.pages=this.truncateArrayWithActiveMiddle(response.pages,8);
                  this.loadTemplateFromResponse(response);
                  window.console.log(response);
              }
            }
        },
        requestDataToServer: function (obj,token,url,role){
                
                const self=this; // Usar self para conservar el contexto en xhr.onload
                let xhr = new XMLHttpRequest();

                const service= (role==='student')?'local_ticketmanagement_get_tickets_byUserId':'local_ticketmanagement_get_tickets';



                //Se prepara el objeto a enviar
                const formData= new FormData();
                formData.append('wstoken',token);
                formData.append('wsfunction', service);
                formData.append('moodlewsrestformat', 'json');
                if (role==='student'){
                    formData.append('params[0][order]',obj.order);
                    formData.append('params[0][orderby]',obj.orderby);
                    formData.append('params[0][page]',obj.page);
                    formData.append('params[0][userid]',obj.userid);
                    formData.append('params[0][activePage]',obj.activePage);
                }else if(role==='controller') {
                    formData.append('params[0][order]',obj.order);
                    formData.append('params[0][orderby]',obj.orderby);
                    formData.append('params[0][page]',obj.page);
                    formData.append('params[0][startdate]',obj.firstDayOfWeek);
                    formData.append('params[0][enddate]',obj.lastDayOfWeek);
                    formData.append('params[0][activePage]',obj.activePage);
                }
                
                
                xhr.open('POST',url,true);
                xhr.send(formData);
            
                xhr.onload = (ev)=> {
                    self.reqHandlerGetTickets(xhr);
                }
            
                xhr.onerror = ()=> {
                    rejectAnswer(xhr);
                }
        },
        showTicketFormPopup: function (e,role){
            const self=this;
            e.stopPropagation();
            const ticketId=e.target.textContent.trim();
            const formpopup=(role==='controller')?"TicketFormPopup":"TicketFormPopupStudent";
            window.console.log(role);
            const modalForm=new ModalForm({
                formClass: `\\local_ticketmanagement\\form\\${formpopup}`,
                args: {num_ticket: ticketId},
                modalConfig: {title: `Ticket details: #${ticketId}`},
                returnFocus:e.target
            });
    
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
                const formElement=e.target;
                const subcategoryValue = formElement.querySelector('select[name="subcategory"]')?.value;
                window.console.log(e.detail); 
                //Se captura el valor subcategory porque como campo de formulario aparece la categoria, pero no la subcategoria
                if (subcategoryValue) {
                    e.detail.subcategory = subcategoryValue;
                } 
    
                if (role==='controller'){
                    self.updateTicket(e.detail,token,url,role);
                }
                
            });
    
            modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                const self=this;
                // Obtener el formulario modal después de que se ha cargado
                const formElement = e.target;
            
                // Usa la función areElementsLoaded para esperar hasta que los selectores estén cargados en el DOM
                self.areElementsLoaded('select[name="category"], select[name="subcategory"]', formElement).then((elements) => {
                    
                    // Una vez que los selectores están disponibles, los seleccionamos
                    const categorySelect = formElement.querySelector('select[name="category"]');
                    const subcategorySelect = formElement.querySelector('select[name="subcategory"]');
                    const priority=formElement.querySelector('select[name="priority"]');
                    priority.addEventListener('change',(e)=>{
                        eventoPriority=e.target.value
                    })
            
                    // Asegúrate de que ambos selectores existen
                    if (categorySelect && subcategorySelect) {
                        // Añadir un listener para cuando cambie la categoría seleccionada
                        categorySelect.addEventListener('change', (event) => {
                            const selectedCategory = event.target.value;
                            window.console.log(`Categoría seleccionada: ${selectedCategory}`);
                            eventoCat=event.target.value;
                            // Lógica para actualizar las opciones del selector de subcategorías
                            self.updateSubcategory(selectedCategory, subcategorySelect,token);
                        });
    
                        subcategorySelect.addEventListener('change',(e)=>{
                            eventoSubCat=e.target.value;
                        })
                    } else {
                        window.console.error('Los selectores de categoría y subcategoría no están disponibles.');
                    }
                    
                    if (role==='controller'){
                        const closebox=formElement.querySelector("input[type='checkbox'][name='close']");
                        const cancelledbox=formElement.querySelector("input[type='checkbox'][name='cancelled']");
                        const bosave=formElement.querySelector(".btn-primary");
                        
                        if (closebox.checked){
                        bosave.disabled=true;
                        closebox.disabled=true;
                        }
        
        
                        if (cancelledbox.checked){
                        bosave.disabled=true;
                        cancelledbox.disabled=true;
                        }
                    }
                    
    
    
                    
                }).catch((error) => {
                    window.console.error('Error al cargar los elementos select:', error);
                });
            });
            modalForm.show();
        },
        updateTicket: function (obj,token,url,role) {
            const self=this;
            let xhr = new XMLHttpRequest();
            const service=(role==='controller')?'local_ticketmanagement_edit_ticket':'local_ticketmanagement_edit_ticket_byUser';
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', service);
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][ticketid]',obj.ticketid);
            formData.append('params[0][fileid]',obj.attachments);
            formData.append('params[0][cancelled]',obj.cancelled);
            formData.append('params[0][state]',obj.hiddenstate);
            formData.append('params[0][priority]',obj.priority);
            formData.append('params[0][closed]',obj.close);
            formData.append('params[0][category]',obj.subcategory);
            formData.append('params[0][eventoCat]',eventoCat);
            formData.append('params[0][eventoSubCat]',eventoSubCat);
            formData.append('params[0][eventoPriority]',eventoPriority);
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                self.reqHandlerUpdateTicket(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        
        },
        reqHandlerUpdateTicket: function(xhr){
            if (xhr.readyState=== 4 && xhr. status === 200){
                if (xhr.response){
                    const response=JSON.parse(xhr.response);
                    
                    if (response){
                    window.console.log(response.ticket);
                    const ticket=response.ticket;
                    this.updateTemplate(ticket);
                    }
                    
                }
            }
        },
        updateTemplate: function(ticket){
            const fila = document.querySelector(`#${ticket.ticketid}`);
            const state = fila.querySelector('td:nth-child(5)');
            const priority = fila.querySelector('td:nth-child(7)');
            state.textContent=ticket.state;
            priority.textContent=ticket.priority;
            window.console.log("edicion...");
            if (ticket.state==='Closed' || ticket.state==='Cancelled'){
                fila.classList.add("cerrado");
                const boAssigment=fila.querySelector('.assignbtn');
                boAssigment.addEventListener("click", (event) => {
                    event.stopPropagation(); // Detener la propagación del evento
                    event.preventDefault();  // Prevenir cualquier acción predeterminada
                });
                // Opcional: cambiar el cursor a "not-allowed" para indicar que no es clickeable
                boAssigment.style.cursor = "not-allowed";
            }
    
        },
        updateSubcategory: function(categoryid,subcategorySelect, token){
            const self=this;
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_load_subcategories');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][categoryid]',categoryid);
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                self.reqHandlerLoadSubcategories(xhr, subcategorySelect);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        },
        reqHandlerLoadSubcategories: function(xhr,subcategorySelect){
            if (xhr.readyState=== 4 && xhr. status === 200){
                if (xhr.response){
                    const response=JSON.parse(xhr.response);
                    const selsubcategory=subcategorySelect;
                    if (response){
                        selsubcategory.innerHTML='';
                        const optionsSubcategories = response;
                        
            
                        optionsHTML='';
                        optionsSubcategories.forEach(optionData=>{
                            optionsHTML += `<option value="${optionData.id}">${optionData.subcategory}</option>`;
                        })
                        selsubcategory.innerHTML = optionsHTML;
                    }
                    
                }
            }
        }
    }
})