define([
    'core/templates',
    'block_graphical_events/shared_library',
    'pdf'
], function(Templates,shared,pdf) {

    const url=M.cfg.wwwroot+'/webservice/rest/server.php';
    const token=document.querySelector('#graphical_event-token').value;

    const init = (userIdFromPHP) => {
        
        shared.areElementsLoaded('#layer').then((elements)=>{
            
            const itemPanel=document.querySelectorAll('#graphpanel li');
            
            itemPanel.forEach((item)=>{
                item.addEventListener('click',(e)=>{
                    e.preventDefault();
                    const linkItemPanel=document.querySelectorAll('#graphpanel li a');
                    linkItemPanel.forEach((itemlink)=>{
                        itemlink.classList.remove('active','active_tree_node');
                    })
                    e.target.classList.add('active','active_tree_node');
                    let userId=0;
                    
                    if (e.target.dataset.user==='yes')
                        userId = userIdFromPHP;
                    else
                        userId=0;
    
                    reloadGraph(url,token,userId);
                })
            });
    
            const bosubmit=elements[0].querySelector('#idboclick');
            bosubmit.addEventListener('click',()=>{
                const tabs=document.querySelector('#graphpanel');
                const selectedTab=tabs.querySelector('.nav-link.active');
                let userId=0;
                if (selectedTab.dataset.user==='yes')
                    userId = userIdFromPHP;
                else
                    userId=0;
                reloadGraph(url,token,userId);
            });
    
            const bopdf=elements[0].querySelector('#id_graphical_event_export_pdf');
            bopdf.addEventListener('click',(e)=>{
                createPDFAcroField(pdf);
            });
        });

        // Modified reloadGraph to be simpler
    const reloadGraph = (url, token, userId) => {
        const projectSelect = document.querySelector("#id_graph_project");
        const vesselSelect = document.querySelector("#id_graph_vessel");
        
        if (!projectSelect || !vesselSelect) return;

        const selectedCustomer = projectSelect.value;
        const selectedGroup = vesselSelect.value;
        
        // Store current values (they've already been validated)
        localStorage.setItem('customerkey', selectedCustomer);
        localStorage.setItem('groupkey', selectedGroup);
        
        // Rest of your AJAX code...
        let xhr = new XMLHttpRequest();
        const formData = new FormData();
        formData.append('wstoken', token);
        formData.append('wsfunction', 'block_graphical_events_load_data');
        formData.append('moodlewsrestformat', 'json');
        formData.append('params[0][projectid]', selectedCustomer);
        formData.append('params[0][groupid]', selectedGroup);
        formData.append('params[0][userid]', userId);
        
        xhr.open('POST', url, true);
        xhr.send(formData);

        xhr.onload = (ev) => {
            if (xhr.status === 200) {
                reqHandlerLoadGraphEvent(xhr);
            } else {
                rejectAnswer(xhr);
            }
        };

        xhr.onerror = () => {
            rejectAnswer(xhr);
        };
    };

        const reqHandlerLoadGraphEvent=(xhr)=>{
            const response=JSON.parse(xhr.responseText);
            reloadTemplate(response);
        }

        shared.areElementsLoaded('#id_graph_project, #id_graph_vessel').then((elements)=>{
            const selectProject = elements[0];
            const selectVessel = elements[1];
    
            // Set up change handlers first
            selectProject.addEventListener('change', function() {
                localStorage.setItem('customerkey', this.value);
                // Clear the vessel selection when project changes
                localStorage.removeItem('groupkey');
                loadVesselOptions(url, token, null, null).then(() => {
                    // After vessels load, restore any valid selection
                    restoreVesselSelection();
                });
            });
    
            selectVessel.addEventListener('change', function() {
                localStorage.setItem('groupkey', this.value);
            });
    
            // Function to restore vessel selection only if valid for current project
            const restoreVesselSelection = () => {
                const storedGroup = localStorage.getItem('groupkey');
                if (storedGroup) {
                    const vesselSelect = document.querySelector('#id_graph_vessel');
                    const validVessels = Array.from(vesselSelect.options).map(opt => opt.value);
                    if (validVessels.includes(storedGroup)) {
                        vesselSelect.value = storedGroup;
                    }
                }
            };
    
            // Load projects first
            const loadProjectOptionsPromise = new Promise((resolve, reject) => {
                loadProjectOptions(url, token, resolve, reject);
            });
    
            loadProjectOptionsPromise.then(() => {
                // Restore project selection if exists
                const storedProject = localStorage.getItem('customerkey');
                if (storedProject && Array.from(selectProject.options).some(opt => opt.value === storedProject)) {
                    selectProject.value = storedProject;
                }
    
                // Then load vessels for the selected project
                const loadGroups = new Promise((resolve, reject) => {
                    loadVesselOptions(url, token, resolve, reject);
                });
    
                loadGroups.then(() => {
                    // Restore vessel selection if valid for current project
                    restoreVesselSelection();
                    
                    // Finally load the graph with the restored selections
                    reloadGraph(url, token, 0);
                });
            });
        });
    }
    

    const loadVesselOptions=async (url,token,resolve,reject)=>{
        const projectid=document.querySelector('#id_graph_project').value;
        let xhr = new XMLHttpRequest();
        
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_graphical_events_load_vessels');
        formData.append('moodlewsrestformat', 'json');
        formData.append('projectid', projectid);
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerVesselLoadEvent(xhr);
                if (typeof resolve === "function") {
                    // Llamar a la función opcional si existe
                    resolve();
                } 
            } else {
                rejectAnswer(xhr);
                if (typeof reject === "function") {
                    // Llamar a la función opcional si existe
                    reject();
                } 
            }
        }

        xhr.onerror = ()=> {
            rejectAnswer(xhr);
            
        }
    }

    const reqHandlerVesselLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectVessel=document.querySelector('#id_graph_vessel');

        // Clear existing options
        selectVessel.innerHTML = '';

        if (response && response.length>0){
            // Add vessel options
            response.forEach(vessel => {
                const option = document.createElement('option');
                option.value = parseInt(vessel.id);
                option.textContent = vessel.name;
                selectVessel.appendChild(option);
            });
        } else {
            // Add a default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '0';
            defaultOption.textContent = 'No group registered';
            selectVessel.appendChild(defaultOption);
        }

        //If the project selectbox changes, the group also needs to be updated
        const updatedGroup=selectVessel.value;
        localStorage.setItem('groupkey',updatedGroup);

        
    }

    const loadProjectOptions=(url,token,resolve,reject)=>{
        let xhr = new XMLHttpRequest();
    
        //Se prepara el objeto a enviar
        const formData= new FormData();
        formData.append('wstoken',token);
        formData.append('wsfunction', 'block_graphical_events_load_projects');
        formData.append('moodlewsrestformat', 'json');
        
        xhr.open('POST',url,true);
        xhr.send(formData);

        xhr.onload = (ev)=> {
            if (xhr.status === 200) {
                reqHandlerCustomerLoadEvent(xhr);
                resolve();
            } else {
                rejectAnswer(xhr);
                reject('Error al cargar la información');
            }
        }

        xhr.onerror = ()=> {
            rejectAnswer(xhr);
            reject('Error de conexión');
        }
    }

    const reqHandlerCustomerLoadEvent=(xhr)=>{
        const response=JSON.parse(xhr.responseText);
        const selectProject=document.querySelector('#id_graph_project');
        
        // Clear existing options
        selectProject.innerHTML = '';

        if (response){
            // Add project options
            response.forEach(project => {
                const option = document.createElement('option');
                option.value = parseInt(project.id);
                option.textContent = project.shortname;
                selectProject.appendChild(option);
            });
        } else {
            // Add a default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'No project registered';
            selectProject.appendChild(defaultOption);
        }

        
    }

    const rejectAnswer=(xhr)=>{
        window.console.log('error de conexion');
    }

    const reloadTemplate = (data) => {
        // Añadir el dato al contexto del template
        const templateData = {
            jsondata: JSON.stringify(data), // Convertir el dato a JSON
        };
    
        // Renderizar el template Mustache
        Templates.renderForPromise('block_graphical_events/canvas', templateData)
            .then(({ html, js }) => {
                const content = document.querySelector('#graphicalpanel');
                content.innerHTML = '';
                Templates.appendNodeContents(content, html, js);
            })
            .catch((error) => {
                console.error('Error rendering template:', error);
            });
    };

    async function createPDFAcroField(PDF) {
        const pdfDoc = await PDF.PDFDocument.create();
        const font = await pdfDoc.embedFont(PDF.StandardFonts.Helvetica);
        const fontBold = await pdfDoc.embedFont(PDF.StandardFonts.HelveticaBold);
    
        // Get the chart canvas and convert it to an image
        const canvas = document.getElementById('myChart');
        const canvasImageData = await canvasToImage(canvas);
        const chartImage = await pdfDoc.embedPng(canvasImageData);
    
        // Load other images
        const logoNavantiaURL = M.cfg.wwwroot + '/blocks/graphical_events/pix/navantia-logo.png';
        const logoNavantiaImageBytes = await fetch(logoNavantiaURL).then(res => res.arrayBuffer());
        const logoNavantia = await pdfDoc.embedPng(logoNavantiaImageBytes);
    
        const fondoURL = M.cfg.wwwroot + '/blocks/graphical_events/pix/marco-horizontal-navantia.jpg';
        const fondoURLImageBytes = await fetch(fondoURL).then(res => res.arrayBuffer());
        const fondo = await pdfDoc.embedJpg(fondoURLImageBytes);
    
        let page = pdfDoc.addPage([877,620]);
        const { width, height } = page.getSize();
    
        // Create a string of text and measure its width and height in our custom font
        const text = 'Chart: Total Ticket States';
        const textSize = 24;
        const textWidth = font.widthOfTextAtSize(text, textSize);
        const textHeight = font.heightAtSize(textSize);
        const verticalGap = textHeight / 2;
        const comienzo = 150;
    
        // Draw background
        page.drawImage(fondo, {
            x: 0,
            y: 0,
            width: page.getWidth(),
            height: page.getHeight(),
        });
    
        // Draw logo
        page.drawImage(logoNavantia, {
            x: 45,
            y: page.getHeight() - 100,
            width: 110,
            height: 60,
        });
    
        // Draw title
        page.drawText(text, {
            x: width / 2 - textWidth / 2,
            y: height - textHeight - verticalGap - comienzo,
            size: textSize,
            font: font,
            color: PDF.rgb(0, 0.53, 0.71),
        });
    
        // Draw the chart in the center of the page
        const chartWidth = width * 0.6; // 80% of page width
        const chartHeight = chartWidth * (canvas.height / canvas.width); // Maintain aspect ratio
        
        page.drawImage(chartImage, {
            x: (width - chartWidth) / 2, // Center horizontally
            y: (height - comienzo - textHeight - verticalGap - chartHeight) / 2, // Center vertically (below title)
            width: chartWidth,
            height: chartHeight,
        });
    
        // Generate and download the PDF
        const pdfBase64 = await pdfDoc.saveAsBase64();
        const byteCharacters = atob(pdfBase64);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'application/pdf' });
        const blobUrl = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = 'documento.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    // Helper function to convert canvas to image data
    function canvasToImage(canvas) {
        return new Promise((resolve) => {
            canvas.toBlob((blob) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.readAsArrayBuffer(blob);
            }, 'image/png');
        });
    }

    
         
    return {
        init: init
    };
});
