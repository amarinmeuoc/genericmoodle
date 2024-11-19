

define(['core_form/modalform',
        'core/notification',
        'core/toast',
        'core/notification',
        'core/templates',
        'local_ticketmanagement/funciones_comunes'], 
        function(ModalForm, Notification, addToast, displayException, Templates, funcionesComunes){
              const url=M.cfg.wwwroot+'/webservice/rest/server.php';
              const token=document.querySelector('input[name="token"]').value;
              
              
              const init =() => {
                  const bonewticket=document.querySelector("#id_bocreate");
                  bonewticket.addEventListener('click',(e)=>{
                      e.preventDefault();
                      e.stopImmediatePropagation();
                      const project=document.querySelector('#id_project').value;
                      const vessel=document.querySelector('#id_vessel').value;
                      const trainee=document.querySelector('#id_userlist').value;
                      const category=(document.querySelector('#id_category').value)?document.querySelector('#id_category').value:0;
                      const subcategory=(document.querySelector('#id_subcategory').value)?document.querySelector('#id_subcategory').value:0;
                      
                      
                      const familyissue=document.querySelector('#id_familyissue').value;
                      const description=document.querySelector('textarea[name="description[text]"]').value;
                      const gestorid=document.querySelector('input[name="gestorid"]').value;
                      //const fileid=document.querySelector('#id_attachments').value;
                      let familiar=trainee;
                      if (familyissue==='yes'){
                        if (document.querySelector('#id_familiar').value!=='')
                          familiar=document.querySelector('#id_familiar').value;
                      }
                      const newTicket={
                          projectid:project,
                          vesselid:vessel,
                          traineeid:trainee,
                          categoryid:category,
                          subcategoryid:subcategory,
                          state:"Open",
                          priority:"Medium",
                          description:description,
                          gestorid:gestorid,
                          familiarid:familiar,
                
                      };
              
                      if (category!==0 && subcategory!==0)
                        createNewTicket(newTicket);
                      else{
                        Notification.addNotification({message:'Error: No categories have been defined yet. No ticket has been inserted',type:'error'});
                      }
              
                  })
              }
              
              const createNewTicket=(newTicket)=>{
                  let xhr = new XMLHttpRequest();
                  
                  //Se prepara el objeto a enviar
                  const formData= new FormData();
                  formData.append('wstoken',token);
                  formData.append('wsfunction', 'local_ticketmanagement_add_ticket');
                  formData.append('moodlewsrestformat', 'json');
                  formData.append('params[0][subcategoryid]',newTicket.subcategoryid);
                  formData.append('params[0][traineeid]',newTicket.traineeid);
                  formData.append('params[0][state]',newTicket.state);
                  formData.append('params[0][priority]',newTicket.priority);
                  formData.append('params[0][description]',newTicket.description);
                  formData.append('params[0][familiarid]',newTicket.familiarid);
                  formData.append('params[0][gestorid]',newTicket.gestorid);
              
                  xhr.open('POST',url,true);
                  xhr.send(formData);
              
                  xhr.onload = (ev)=> {
                      reqHandlerNewTicket(xhr);
                  }
              
                  xhr.onerror = ()=> {
                      rejectAnswer(xhr);
                  }
              }
              
              const reqHandlerNewTicket = (xhr) => {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.response) {
                        const response = JSON.parse(xhr.response);
              
                        if (response) {
                            window.console.log("alberto");
                            addToast.add('Ticket created successfully: ' + response.id);
              
                            // Actualiza el número de tickets y las páginas
                            let numRecords = parseInt(document.querySelector('#num_total_records').textContent.trim()) + 1;
                            let numPages = Math.ceil(numRecords / 25); // Cálculo de las páginas necesarias
                            let currentPage = numPages;
              
                            // Actualiza la lista de páginas en el paginador
                            const pageList = [];
                            let pagePrevious = Math.max(1, currentPage - 1);
                            let pageNext = currentPage + 1;
                            if (numPages===1){
                              pageNext=1;
                            }
                            
                            
                            for (let i = 1; i <= numPages; i++) {
                                pageList.push({
                                    page: i,
                                    active: i === currentPage
                                });
                            }
              
                            const formattedResponse = {
                                listadoTickets: [{
                                    ticketnumber: response.id,
                                    username: response.username,
                                    familyissue: response.familyissue,
                                    state: response.state,
                                    priority: response.priority,
                                    assigned: 'Waiting to be assigned.',
                                    date: response.dateticket,
                                    description: response.description,
                                    familyissue: response.familyissue,
                                    color:'yellow'
                                }],
                                num_records: numRecords,
                                num_total_records: numRecords,
                                num_pages: numPages,
                                pages: funcionesComunes.truncateArrayWithActiveMiddle(pageList,8),
                                previous: [{
                                    page: pagePrevious,
                                    url: '#'
                                }],
                                next: [{
                                    page: pageNext,
                                    url: '#'
                                }],
                                first: [{
                                    page: 1,
                                    url: '#'
                                }],
                                last: [{
                                    page: numPages,
                                    url: '#'
                                }],
                            };
              
                            addTickettoTemplate(formattedResponse);
                        } else {
                            addToast.add('Something went wrong. No ticket created');
                        }
                    }
                }
              };
              
              
              function addTickettoTemplate(response){
                  //Render the choosen mustache template by Javascript
                  Templates.renderForPromise('local_ticketmanagement/tr_log-ajax',response)
                  .then(({html,js})=>{
                    const content=document.querySelector('#tablebody');
                    const numRecords = document.querySelectorAll('.tickets').length;
                    
                    // Si el número total de registros supera los 25, limpiar la tabla para una nueva página
                    if (numRecords >= 25) {
                      content.innerHTML = '';  // Limpiar solo cuando se supere el límite de 25 registros
                    }
              
                    Templates.appendNodeContents(content,html,js);
                    const newTicket=document.querySelectorAll('.tickets')[document.querySelectorAll('.tickets').length-1];
                    newTicket.addEventListener('click',(e)=>{
                      
                        funcionesComunes.showTicketFormPopup(e,'controller');
                      
                    });
              
                    const logs=document.querySelectorAll('.logs')[document.querySelectorAll('.logs').length-1];
              
                  
                      logs.addEventListener('click',(e)=>{
                        const filaPadre=e.target.closest('tr');
                        
                          showTicketActions(e);
                        
                      })
                
              
                    const assignbtn=document.querySelectorAll('.assignbtn')[document.querySelectorAll('.assignbtn').length-1];
                    assignbtn.addEventListener('click',(e)=>{
                      const filaPadre=e.target.closest('tr');
                      if (!filaPadre.classList.contains('cerrado')){
                        
                        showAssigmentFormPopup(e);
                      } else {
                        e.preventDefault();
                        e.stopPropagation();
                      }
                      
                    })
              
              
                    //En caso de que se superen 25 tickets por página, se crea una nueva página y añade el registro ahí
              
              
                    Templates.renderForPromise('local_ticketmanagement/pagebar_log-ajax',response)
                    .then(({html,js})=>{
                      const content=document.querySelector('#pagination');
                      content.innerHTML='';
                      Templates.appendNodeContents(content,html,js);
              
              
                      const pages=document.querySelectorAll('.page-link');
                      pages.forEach((page)=>{
                        page.addEventListener('click',(ev)=>{
                          window.console.log('adleante');
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
                          const startdate= document.querySelector('#startdate');
                          const enddate= document.querySelector('#enddate');
                  
                          const startdateUnixFormat=funcionesComunes.truncateDateToDay(new Date(startdate.value));
                          const enddateUnixFormat=funcionesComunes.truncateDateToDay(new Date(enddate.value));
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
                          
                          funcionesComunes.requestDataToServer(obj, token, url,'controller');
                  
                          
                        });
                      });
                      
                    }).catch((error)=>displayException(error));
                  }).catch((error)=>displayException(error));
                }
                
              const showAssigmentFormPopup=(e)=>{
                e.stopPropagation();
                const fila=e.target.closest('tr');
                const ticket=fila.querySelector('.tickets');
                const ticketId=ticket.textContent;
                const modalForm=new ModalForm({
                    formClass: "\\local_ticketmanagement\\form\\AssignmentFormPopup",
                    args: {num_ticket: ticketId},
                    modalConfig: {title: `Ticket details: #${ticketId}`},
                    returnFocus:e.target
                });
              
                modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                    //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
                      
                    const ticket=e.detail.ticket;
                    const td=document.querySelector(`td a.assignbtn[data-ticketid="${ticket.id}"]`).parentElement;
                    const link=document.querySelector(`a.assignbtn[data-ticketid="${ticket.id}"]`)
                    const span = link.nextElementSibling; 
                    span.textContent=ticket.user;
                    const state=document.querySelector(`td a.assignbtn[data-ticketid="${ticket.id}"]`).parentElement.parentElement.children[4];
                    state.textContent=ticket.state;
                });
              
                modalForm.addEventListener(modalForm.events.LOADED, (e)=>{
                    //Changing the text of the dynamic button
                    //e.target.querySelector("button[data-action='save']").textContent="Send Email"
                    
                    }
                );
                
                modalForm.show();
              
              }
              
              const showTicketActions=(e)=>{
                e.stopPropagation();
                const ticketId=e.target.dataset.ticketid;
                const modalForm=new ModalForm({
                    formClass: "\\local_ticketmanagement\\form\\ActionsFormPopup",
                    args: {num_ticket: ticketId},
                    modalConfig: {title: `Ticket details: #${ticketId}`},
                    returnFocus:e.target
                });
              
                modalForm.show();
              
              
                modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, (e)=>{
                    //Se actualiza la pagina principal con los nuevos valores y se envia email de notificación
                });
              
                // Listen for the modal LOADED event
                modalForm.addEventListener(modalForm.events.LOADED, (e) => {
                    // Get the button after the modal is fully loaded
                    // Get the modal element after it is loaded
                    
                    funcionesComunes.areElementsLoaded('#boactionid').then((elements)=>{
                        const addActionBtn = (elements.length>0)?elements[0]:null;
                        
                        if (addActionBtn) {
                            // Add an event listener to the button
                            addActionBtn.addEventListener('click', function() {
                                console.log('Add Action button clicked!');
                            });
                        }
              
                    })
                });
              }
             
              return {
                init:init
              }
              
        });


