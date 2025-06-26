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
            
            const barchartpanel=document.querySelector('#barchartpanel');
            const loader=barchartpanel.querySelector('.loader');
            const graph=barchartpanel.querySelector('.graph');
            loader.classList.remove('hide');
            loader.classList.add('show');
            graph.classList.add('hide');
            
             
          },
          
          hideLoader:function(event){
            const barchartpanel=document.querySelector('#barchartpanel');
            const loader=barchartpanel.querySelector('.loader');
            const graph=barchartpanel.querySelector('.graph');
            loader.classList.remove('show');
            loader.classList.add('hide');
            graph.classList.remove('hide');
            
            
          },
      
         
      
          

    }
})