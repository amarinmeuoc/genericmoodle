define([
    'core/templates',
    'block_graphical_events/shared_library',
], function(Templates,shared) {

    const init = () => {
        
        shared.areElementsLoaded('#layer').then((elements)=>{
            console.log("TODO: START");
            const itemPanel=document.querySelectorAll('#graphpanel li');

            itemPanel.forEach((item)=>{
                item.addEventListener('click',(e)=>{
                    //Limpiamos todas las clases activas
                    const linkItemPanel=document.querySelectorAll('#graphpanel li a');
                    linkItemPanel.forEach((itemlink)=>{
                        itemlink.classList.remove('active','active_tree_node');
                    })
                    e.target.classList.add('active','active_tree_node');
                    const data={
                        'userId': M.cfg.userId,
                    }
                        
                    

                    //Se recarga la plantilla con los nuevos datos
                    reloadTemplate(data);
                })
            })

        });
    };

    const reloadTemplate=(data)=>{
        //Render the choosen mustache template by Javascript
        Templates.renderForPromise('block_graphical_events/canvas',data)
        .then(({html,js})=>{
          const content=document.querySelector('#graphicalpanel');
          content.innerHTML='';
          window.console.log(data);
          Templates.appendNodeContents(content,html,js);
        })
    };
      
          
          
         
    return {
        init: init
    };
});
