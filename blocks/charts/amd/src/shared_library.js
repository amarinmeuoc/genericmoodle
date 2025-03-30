define(['core/modal','core/templates','core_form/modalform','core/toast'],function(ModalFactory,Templates,ModalForm,addToast) {
    
    return {
        
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

    }
})