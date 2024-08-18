export const init=(instanceData)=>{
    window.console.log('Datos de la instancia:', instanceData);
    isElementLoaded('button[name="config_boaddcustomer"]').then((selector)=>{
        const boaddnewproject = document.querySelector('button[name="config_boaddcustomer"]');
        if (boaddnewproject) {
            boaddnewproject.addEventListener('click', () => {
                window.console.log(`Hola mundo desde la instancia ${instanceData.instanceid}`);
            });
        } else {
            window.console.log('El botón no se encontró en el DOM.');
        }
    });
    const enlace=document.querySelector('a[data-block="block_itp_edit_form"');
    enlace.addEventListener('click',(e)=>{
        window.console.log("hola mundo");
    });
};

const isElementLoaded= async (elem)=>{
    const e=document.querySelector(elem);
    while (e===null){
        await new Promise(resolve=>requestAnimationFrame(resolve))
    }
    return document.querySelector(elem);
}