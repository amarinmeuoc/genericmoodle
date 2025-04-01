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
            const graphicalpanel=document.querySelector('#graphicalpanel');
            const loader=graphicalpanel.querySelector('.loader');
            const table=graphicalpanel.querySelector('.graph');
            loader.classList.remove('hide');
            loader.classList.add('show');
            table.classList.add('hide');
            
             
          },
          
          hideLoader:function(event){
            const graphicalpanel=document.querySelector('#graphicalpanel');
            const loader=graphicalpanel.querySelector('.loader');
            const table=graphicalpanel.querySelector('.graph');
            loader.classList.remove('show');
            loader.classList.add('hide');
            table.classList.remove('hide');
            
          },
      
          onProgressFunction:function(event) {
            console.log(`Uploaded ${event.loaded} of ${event.total}`);
            const loader=document.querySelector('.loader');
            loader.classList.remove('.hide');
            loader.classList.add('.show');
        },

    }
})