
define(['core/notification','core/templates','local_ticketmanagement/funciones_comunes'],function(displayException,Templates,funcionesComunes){
  const loadTemplate =() => {
    //definicion de url
    const url=M.cfg.wwwroot+'/webservice/rest/server.php';

    funcionesComunes.areElementsLoaded('input[name="user"],#id_category, #id_subcategory,input[name="token"],#startdate,#enddate').then((elements) => {
        //Se obtienen los valores de los campos necesarios
        const token = document.querySelector('input[name="token"]').value;
        const orderby = document.querySelector('input[name="orderby"]').value;
        const order = document.querySelector('input[name="order"]').value;
        const page= document.querySelector('input[name="page"]').value;
        const userid=document.querySelector('input[name="user"]').value;
        const activePage=1;

        const obj={
          activePage:activePage,
          userid:userid,
          order:order,
          orderby:orderby,
          page:page,
        }
        

        //Carga de los datos por defecto
        funcionesComunes.requestDataToServer(obj, token, url,'student');  

      });
    }

    return {
      loadTemplate:loadTemplate
    }

});
