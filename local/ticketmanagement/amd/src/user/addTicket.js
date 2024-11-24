define(['core/modal',
  'core/notification',
  'core/toast',
  'core/notification',
  'core/templates',
  'local_ticketmanagement/funciones_comunes'], 
  function(ModalFactory, Notification, addToast, displayException, Templates, funcionesComunes){
        const url=M.cfg.wwwroot+'/webservice/rest/server.php';
        const token=document.querySelector('input[name="token"]').value;
        const init =() => {
            const bonewticket=document.querySelector("#id_bocreate");

            if (bonewticket){
                bonewticket.addEventListener('click',(e)=>{
                  e.preventDefault();
                  e.stopImmediatePropagation();
                  
                  const trainee=document.querySelector('input[type="hidden"][name="user"]').value;
                  const category=(document.querySelector('#id_category').value)?document.querySelector('#id_category').value:0;
                  const subcategory=(document.querySelector('#id_subcategory').value)?document.querySelector('#id_subcategory').value:0;
                  
                  const gestorid=document.querySelector('input[name="gestorid"]').value;
                  const familyissue=document.querySelector('#id_familyissue').value;
                  const description=document.querySelector('textarea[name="description[text]"]').value;
                  
                  let familiar=trainee;
                  if (familyissue==='yes'){
                    if (document.querySelector('#id_familiar').value!=='')
                      familiar=document.querySelector('#id_familiar').value;
                  }
                  const newTicket={
                      
                      traineeid:trainee,
                      categoryid:category,
                      subcategoryid:subcategory,
                      state:"Open",
                      priority:"Medium",
                      description:description,
                      gestorid:gestorid,
                      familiarid:familiar
                  };

                  if (category!==0 && subcategory!==0)
                    createNewTicket(newTicket);
                  else{
                    Notification.addNotification({message:'Error: No categories have been defined yet. No ticket has been inserted',type:'error'});
                  }
              })
            }
            
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
            formData.append('params[0][gestorid]',newTicket.gestorid);
            formData.append('params[0][familiarid]',newTicket.familiarid);

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
                      window.console.log(response);
                      addToast.add('Ticket created successfully: ' + response.id);

                      // Actualiza el número de tickets y las páginas
                      let numRecords = parseInt(document.querySelector('#num_records').textContent.trim()) + 1;
                      let numTotalRecords = parseInt(document.querySelector('#num_total_records').textContent.trim()) + 1;
                      let numPages = Math.ceil(numTotalRecords / 25); // Cálculo de las páginas necesarias
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
                              familyissue: response.familyissue
                          }],
                          num_records: numRecords,
                          num_total_records: numTotalRecords,
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
            Templates.renderForPromise('local_ticketmanagement/tr_st-ajax',response)
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
                
                  funcionesComunes.showTicketFormPopup(e,'student');
                
              });

              const logs=document.querySelectorAll('.logs')[document.querySelectorAll('.logs').length-1];

            
                logs.addEventListener('click',(e)=>{
                  const filaPadre=e.target.closest('tr');
                  
                    showTicketActions(e);
                  
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
                    window.console.log('adleante andalucia');
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
                    
            
                    const userid=document.querySelector('input[name="user"]').value;

                    const obj={
                      activePage:activePage,
                      userid:userid,
                      order:parseInt(order),
                      orderby:orderby,
                      page:parseInt(page.value),
                    }
                    
                    funcionesComunes.requestDataToServer(obj, token, url,'student');
            
                    
                  });
                });
                
              }).catch((error)=>displayException(error));
            }).catch((error)=>displayException(error));
          }

          
          const showTicketActions=(e)=>{
            e.stopPropagation();
            const ticketId=e.target.dataset.ticketid;
            let xhr = new XMLHttpRequest();
            
            //Se prepara el objeto a enviar
            const formData= new FormData();
            formData.append('wstoken',token);
            formData.append('wsfunction', 'local_ticketmanagement_load_actions');
            formData.append('moodlewsrestformat', 'json');
            formData.append('params[0][ticketid]',ticketId);
            
        
            xhr.open('POST',url,true);
            xhr.send(formData);
        
            xhr.onload = (ev)=> {
                
                funcionesComunes.reqHandlerLoadActions(xhr);
            }
        
            xhr.onerror = ()=> {
                rejectAnswer(xhr);
            }
        }
        
             

          return {
            init:init
          }

  });