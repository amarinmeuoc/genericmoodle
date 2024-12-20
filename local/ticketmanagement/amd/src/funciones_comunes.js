define(['core/modal','core/templates','core_form/modalform','core/toast'],function(ModalFactory,Templates,ModalForm,addToast) {
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
                            const selstate=document.querySelector('#id_state').value;
                            const gestorvalue=document.querySelector('#id_logistic').value;
                            const obj={
                                activePage:1,
                                firstDayOfWeek:startdateUnixFormat,
                                lastDayOfWeek:enddateUnixFormat,
                                order:parseInt(order.value),
                                orderby:orderby.value,
                                page:1,
                                state:selstate,
                                gestor:gestorvalue
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
                            const selstate=document.querySelector('#id_state').value;
                            const gestorvalue=document.querySelector('#id_logistic').value;
                            const obj={
                                activePage:activePage,
                                firstDayOfWeek:startdateUnixFormat,
                                lastDayOfWeek:enddateUnixFormat,
                                order:parseInt(order),
                                orderby:orderby,
                                page:parseInt(page.value),
                                state:selstate,
                                gestor:gestorvalue
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

                const chk_communication=document.querySelectorAll("#tablebody input[type='checkbox']");

                chk_communication.forEach((chk)=>{
                    chk.addEventListener('click',(e)=>{
                    const ticket=e.target.dataset.ticketid;
                    const value=(e.target.checked)?1:0;
                    const token = document.querySelector('input[name="token"]').value;
                    this.updateCommunication(ticket,value,token,url);
                    })
                })
            
            })
            .catch((error)=>displayException(error));
  
        },
        reqHandlerGetTickets: function(xhr){
            if (xhr.readyState=== 4 && xhr. status === 200){
              if (xhr.response){
                  const response=JSON.parse(xhr.response);
                  response.pages=this.truncateArrayWithActiveMiddle(response.pages,8);
                  this.loadTemplateFromResponse(response);
                  
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
                    formData.append('params[0][state]',obj.state);
                    formData.append('params[0][gestor]',obj.gestor);
                }
                
                xhr.open('POST',url,true);
                //xhr.send(formData);

                setTimeout(()=>{
                    xhr.send(formData);
                },100);
            
                xhr.onload = (ev)=> {
                    self.reqHandlerGetTickets(xhr);
                }

                xhr.onloadstart=(event)=>{
                    self.showLoader(event);
                  }
            
                xhr.onprogress = (event)=>{
                    self.onProgressFunction(event);
                } 
                xhr.onloadend=(event)=>{
                    self.hideLoader(event);
                }
            
                xhr.onerror = ()=> {
                    self.rejectAnswer(xhr);
                }
        },

        showLoader: function (event){
            const loader=document.querySelector('.loader');
            const table=document.querySelector('.generaltable');
            loader.classList.remove('hide');
            loader.classList.add('show');
            table.classList.add('hide');
            const bosearch=document.querySelector('#id_bosearchdate');
            const bosearchbyID=document.querySelector('#id_bosearchbyid');
            if (bosearch)
                bosearch.disabled=true;
            if (bosearchbyID)
                bosearchbyID.disabled=true;
            
          },
          
          hideLoader:function(event){
            const loader=document.querySelector('.loader');
            const table=document.querySelector('.generaltable');
            loader.classList.remove('show');
            loader.classList.add('hide');
            table.classList.remove('hide');
            const bosearch=document.querySelector('#id_bosearchdate');
            const bosearchbyID=document.querySelector('#id_bosearchbyid');
            if (bosearch)
                bosearch.disabled=false;
            if (bosearchbyID)
                bosearchbyID.disabled=false;
          },
      
          onProgressFunction:function(event) {
            console.log(`Uploaded ${event.loaded} of ${event.total}`);
            const loader=document.querySelector('.loader');
            loader.classList.remove('.hide');
            loader.classList.add('.show');
        },
        showTicketFormPopup: function (e,role){
            const self=this;
            e.stopPropagation();
            const ticketId=e.target.textContent.trim();
            const formpopup=(role==='controller')?"TicketFormPopup":"TicketFormPopupStudent";
            
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
                           
                            eventoCat=event.target.selectedOptions[0].text;
                            
                            // Lógica para actualizar las opciones del selector de subcategorías
                            self.updateSubcategory(selectedCategory, subcategorySelect,token);
                        });
    
                        subcategorySelect.addEventListener('change',(e)=>{
                            eventoSubCat=e.target.selectedOptions[0].text;
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
                    
                    const ticket=response.ticket;
                    this.updateTemplate(ticket);
                    }
                    
                }
            }
        },
        updateTemplate: function(ticket){
            const fila = document.querySelector(`#${ticket.ticketid}`);
            const state = fila.querySelector('td:nth-child(6)');
            const priority = fila.querySelector('td:nth-child(8)');
            state.textContent=ticket.state;
            priority.textContent=ticket.priority;
            
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
            formData.append('params[0][role]','controller');
        
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
        },

        updateCommunication: function(ticketid, value, token, url){
            const self=this;
            let xhr = new XMLHttpRequest();
            const service='local_ticketmanagement_update_ticket_communication';
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', service);
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][ticketid]',ticketid);
            formData.append('params[0][value]',value);
            
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                self.reqHandlerUpdateCommunication(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        },

        reqHandlerUpdateCommunication: function(xhr){
            if (xhr.readyState=== 4 && xhr. status === 200){
                if (xhr.response){
                    const response=JSON.parse(xhr.response);
                    
                    addToast.add(`Now user attached to the ticket: ${response.ticket.ticketid} is ${response.ticket.communication === true ? 'allowed' : 'not allowed'} to update messages in the ticket.`);

                    
                }
            }
        },

        reqHandlerLoadActions: function(xhr) {
            if (xhr.readyState === 4 && xhr.status === 200) {
                if (xhr.response) {
                    const response = JSON.parse(xhr.response);
                    if (!response.allowfeedback)
                        this.loadActionsTemplate(response.result);
                    else
                        this.showTicketActionsWithFeedback(response.result)
        
                    
                }
            }
        },
        loadActionsTemplate: function (response){
            const modalContent = `
              <div class="modal-body">
                <p>This is the list of actions ordered by date</p>
            </div>
            <div class="table-responsive" style="max-height:300px">
                <table class="generaltable table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Task</th>
                            <th>Assigned to</th>
                        </tr>
                    </thead>
                    <tbody>
                        ` + 
                        response.map(action => {
                            if (action.user.match(/webservice/gi)){
                                action.user='Waiting for a controller'
                            }
                            return `
                                <tr>
                                    <td>${action.dateaction}</td>
                                    <td>${action.action}</td>
                                    <td>${action.user}</td>
                                </tr>
                            `;
                        }).join('') + // Unir todas las filas generadas
                        `</tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-action="confirm">Accept</button>
            </div>`;
        
            ModalFactory.create({
                title: 'Actions history',
                body: modalContent,
                size: 'modal-xl'
            }).then(modal => {
                
                // Manejar el clic en Aceptar
                modal.getRoot()[0].querySelector('[data-action="confirm"]').onclick = function() {
                    
                    modal.hide(); // Cierra el modal
                };
                modal.show(); // Muestra el modal
            });
        },
        
        
        showTicketActionsWithFeedback:function (arr){
            const ticketId=arr[0].ticketid;
            const modalForm=new ModalForm({
                formClass: "\\local_ticketmanagement\\form\\ActionsFormPopup",
                args: {num_ticket: ticketId,role:'student'},
                modalConfig: {title: `Ticket details: #${ticketId}`},
                returnFocus:e.target
            });
        
            modalForm.show();
        
            modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
                addToast.add(`Ticket: ${e.detail.hiddenticketid} has been updated. Your message has been recieved by the support team.`);
            });
        
            // Listen for the modal LOADED event
            modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                // Get the button after the modal is fully loaded
                // Get the modal element after it is loaded
                const formElement=e.target;
        
                this.areElementsLoaded('button[name="boExcel"],input[name="description"],input[name="state"]', formElement).then((elements) => {
                    const state=formElement.querySelector('input[name="state"]').value;
        
                    if (state==='Cancelled' || state==='Closed'){
                        const teDescription=formElement.querySelector('input[name="description"]');
                        teDescription.disabled=true;
                        const boSave=formElement.querySelector('button[data-action="save"]');
                        boSave.disabled=true;
                    }
                    
                    const boexport=formElement.querySelector('button[name="boExcel"]');
                    //boexport.remove();
                }).catch((error) => {
                    window.console.error('Error al cargar los elementos:', error);
                });
            });
        }
    }
})