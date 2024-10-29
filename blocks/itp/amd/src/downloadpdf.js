export const init = (PDF, user, courses) => {
    areElementsLoaded('#bocertificate').then((elements) => {
        const bocertificate = document.querySelector('#bocertificate');
        
        if (bocertificate!==null){
            bocertificate.addEventListener('click', () => {
                const visible = courses[0];
                const coursetitles = courses[1];
                const coursecodes = courses[2];
                const startdates = courses[3];
                const enddates = courses[4];
                const counts = courses[5];
                const att = courses[6];
                const ass = courses[7];
        
                // Grouping data by the 'codes' array
                const groupedData = groupbycodes(visible, coursecodes, coursetitles, startdates, enddates, counts, att, ass);
        
                // Calculate the required values for each group
                const result = getResult(groupedData).filter(obj=>obj.visible===1);
                
                
                createPDFAcroField(PDF,user,result);
            });
        }
    });
    
    
}

const groupbycodes = (visible, codes, coursetitles, startdates, enddates, counts, att, ass) => 
    codes.reduce((acc, code, index) => {
        // Inicializar el grupo si no existe
        acc[code] = acc[code] || {
            visible: 0,
            titles: [],
            startDates: [],
            endDates: [],
            counts: [],
            att: [],
            ass: []
        };

        // Asignar y agregar valores a los arrays de cada grupo
        acc[code].visible = visible[index];
        acc[code].titles.push(coursetitles[index]);
        acc[code].startDates.push(startdates[index]);
        acc[code].endDates.push(enddates[index]);
        acc[code].counts.push(counts[index]);

        // Agregar valores de att solo si son válidos
        if (att[index] !== null && att[index] !== undefined && att[index] !== "") {
            acc[code].att.push(att[index]);
        }

        // Agregar valores de ass solo si son válidos
        if (ass[index] !== null && ass[index] !== undefined && ass[index] !== "") {
            acc[code].ass.push(ass[index]);
        }

        return acc;
    }, {});

// Función para procesar los datos agrupados
const getResult = (groupedData) => Object.keys(groupedData).map((code) => {
    const group = groupedData[code];
    const minStartDate = Math.min(...group.startDates.map((date) => parseInt(date)));
    const maxEndDate = Math.max(...group.endDates.map((date) => parseInt(date)));
    const sumCount = group.counts.reduce((sum, count) => sum + parseInt(count), 0);

    // Calcular medias solo con valores válidos
    let averagePercentage1 = group.att.reduce((sum, percentage) => sum + parseFloat(percentage), 0) / group.att.length;
    averagePercentage1 = isNaN(averagePercentage1) ? "-" : averagePercentage1;

    let averagePercentage2 = group.ass.reduce((sum, percentage) => sum + parseFloat(percentage), 0) / group.ass.length;
    averagePercentage2 = isNaN(averagePercentage2) ? "-" : averagePercentage2;

    return {
        visible: parseInt(group.visible),
        code: code,
        titles: group.titles,
        minStartDate: minStartDate,
        maxEndDate: maxEndDate,
        sumCount: sumCount.toFixed(0),
        averagePercentage1: "-" === averagePercentage1 ? "-" : averagePercentage1.toFixed(2),
        averagePercentage2: "-" === averagePercentage2 ? "-" : averagePercentage2.toFixed(2)
    };
});



async function createPDFAcroField(PDF, user, result){
    const pdfDoc = await PDF.PDFDocument.create();
    const font = await pdfDoc.embedFont(PDF.StandardFonts.Helvetica);
    const fontBold = await pdfDoc.embedFont(PDF.StandardFonts.HelveticaBold);

    //Colocamos las imagenes
    const logoNavantiaURL = M.cfg.wwwroot+'/blocks/itp/pix/navantia-logo.png';
    const logoNavantiaImageBytes = await fetch(logoNavantiaURL).then(res => res.arrayBuffer());
    const logoNavantia = await pdfDoc.embedPng(logoNavantiaImageBytes);

    const fondoURL = M.cfg.wwwroot+'/blocks/itp/pix/marco-vertical-navantia.jpg';
    const fondoURLImageBytes = await fetch(fondoURL).then(res => res.arrayBuffer());
    const fondo = await pdfDoc.embedJpg(fondoURLImageBytes);

    
    let page= pdfDoc.addPage();
    const { width, height } = page.getSize();

    // Create a string of text and measure its width and height in our custom font
    const text = 'TRAINEE REPORT';
    const textSize = 24;
    const textWidth = font.widthOfTextAtSize(text, textSize);
    const textHeight = font.heightAtSize(textSize);
    const verticalGap=textHeight/2;
    const comienzo=120;

    page.drawImage(fondo, {
        x: 0,
        y: 0,
        width:page.getWidth(),
        height:page.getHeight(),
        
      });

    page.drawImage(logoNavantia, {
    x: 45,
    y: page.getHeight() -100,
    width: 110, 
    height: 60,
    
    });

    // Draw the string of text on the page
    page.drawText(text, {
      x: width/2-textWidth/2,
      y: height-textHeight-verticalGap-comienzo,
      size: textSize,
      font: font,
      color: PDF.rgb(0, 0.53, 0.71),
    })

    const startdate=result.reduce((acc,currentValue)=>{
        return (acc<=currentValue.minStartDate)?acc:currentValue.minStartDate;
    },result[0].minStartDate);

    let startdateAux=new Date(startdate*1000);
    day=startdateAux.getDate();
    month=startdateAux.getMonth()+1;
    year=startdateAux.getFullYear();
    let formattedStartDate=`${day.toString().padStart(2, '0')}-${month.toString().padStart(2, '0')}-${year}`;

    const enddate=result.reduce((acc,currentValue)=>{
        return (acc>currentValue.maxEndDate)?acc:currentValue.maxEndDate;
    },result[0].maxEndDate)

    let enddateAux=new Date(enddate*1000);
    day=enddateAux.getDate();
    month=enddateAux.getMonth()+1;
    year=enddateAux.getFullYear();
    let formattedEndDate=`${day.toString().padStart(2, '0')}-${month.toString().padStart(2, '0')}-${year}`;
    
    
    const textBody=`Below is a detailed list of the grades obtained by the trainee: ${user.firstname}, ${user.lastname} with billid: ${user.billid}, in the training programme of Navantia which started in ${formattedStartDate} to ${formattedEndDate}.`
    const textBodySize=14;
    const textBodyWidth = font.widthOfTextAtSize(textBody, textBodySize);
    const textBodyHeight = font.heightAtSize(textBodySize);
    page.drawText(textBody, {
        x: 40,
        y: height-verticalGap-textHeight-verticalGap-textBodyHeight-verticalGap-verticalGap-comienzo,
        size: textBodySize,
        font: font,
        color: PDF.rgb(0, 0, 0),
        maxWidth: width-80, wordBreaks: [" "]
      })

    let lineHeightPos=height-175-comienzo;
    page.drawLine({
    start: { x: 40, y: lineHeightPos },
    end: { x: width-40, y: lineHeightPos },
    thickness: 1,
    color: PDF.rgb(0, 0.53, 0.71),
    opacity: 0.5,
    });
    const field1='Coursename';
    const textFieldSize=10;
    const textField1Width = font.widthOfTextAtSize(field1, textFieldSize);
    const textField1Height = font.heightAtSize(textFieldSize);
    const verticalFieldGap=textField1Height/2;
    page.drawText( field1, {
        x: 45,
        y: height - 180 + textField1Height-comienzo,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });
    const field2="Duration"
    const textField2Width = font.widthOfTextAtSize(field2, textFieldSize);
    page.drawText( field2, {
        x: 50+textField1Width+textField2Width+180,
        y: height - 180 + textField1Height-comienzo,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });

    const field3="Attendance"
    const textField3Width = font.widthOfTextAtSize(field3, textFieldSize);
    const field4="Assessment"
    const textField4Width = font.widthOfTextAtSize(field4, textFieldSize);
    page.drawText( field3, {
        x: width-55-textField3Width-textField4Width-30,
        y: height - 180 + textField1Height-comienzo,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });

    page.drawText( field4, {
        x: width-55-textField4Width,
        y: height - 180 + textField1Height-comienzo,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });
   
    

    result.map((course,index)=>{
        if (index==15 || index==30 || index==45 || index==60 || index==75){
            page=pdfDoc.addPage();

            page.drawImage(fondo, {
                x: 0,
                y: 0,
                width:page.getWidth(),
                height:page.getHeight(),
                
              });
        
            page.drawImage(logoNavantia, {
            x: 45,
            y: page.getHeight() -100,
            width: 110, 
            height: 60,
            
            });

            // Draw the string of text on the page
            page.drawText(text, {
                x: width/2-textWidth/2,
                y: height-textHeight-verticalGap-comienzo,
                size: textSize,
                font: font,
                color: PDF.rgb(0, 0.53, 0.71),
            })

            
            
            page.drawText(textBody, {
                x: 40,
                y: height-verticalGap-textHeight-verticalGap-textBodyHeight-verticalGap-verticalGap-comienzo,
                size: textBodySize,
                font: font,
                color: PDF.rgb(0, 0, 0),
                maxWidth: width-80, wordBreaks: [" "]
            })
            lineHeightPos=height-175-comienzo;
            page.drawLine({
                start: { x: 40, y: lineHeightPos },
                end: { x: width-40, y: lineHeightPos },
                thickness: 1,
                color: PDF.rgb(0, 0.53, 0.71),
                opacity: 0.5,
            });

            page.drawText( field1, {
                x: 45,
                y: height - 180 + textField1Height-comienzo,
                size: textFieldSize,
                font: fontBold,
                color: PDF.rgb(0, 0, 0)
            });

            page.drawText( field2, {
                x: 50+textField1Width+textField2Width+180,
                y: height - 180 + textField1Height-comienzo,
                size: textFieldSize,
                font: fontBold,
                color: PDF.rgb(0, 0, 0)
            });

            page.drawText( field3, {
                x: width-55-textField3Width-textField4Width-30,
                y: height - 180 + textField1Height-comienzo,
                size: textFieldSize,
                font: fontBold,
                color: PDF.rgb(0, 0, 0)
            });

            page.drawText( field4, {
                x: width-55-textField4Width,
                y: height - 180 + textField1Height-comienzo,
                size: textFieldSize,
                font: fontBold,
                color: PDF.rgb(0, 0, 0)
            });

        } //End if
        let textCourseTitleWith=font.widthOfTextAtSize(course.titles[0],textFieldSize);
        let textFieldHeight=font.heightAtSize(textFieldSize);
        
            lineHeightPos-=18;  
        
        const courseTitles=truncateString(course.titles[0],40);
        page.drawText( courseTitles, {
            x: 45,
            y: lineHeightPos+textFieldHeight/2,
            size: textFieldSize,
            font: font,
            color: PDF.rgb(0, 0, 0)
        });
        let textDurationWith=font.widthOfTextAtSize(course.sumCount,textFieldSize);
        page.drawText( course.sumCount, {
            x: 50+textField1Width+textField2Width+180+textField2Width/3,
            y: lineHeightPos+textFieldHeight/2,
            size: textFieldSize,
            font: font,
            color: PDF.rgb(0, 0, 0)
        });
        let attendance=(course.averagePercentage1==='-')?'-':(course.averagePercentage1*1).toFixed(2);
        
        page.drawText( attendance, {
            x: width-100-textField3Width-textField4Width+textField3Width/2,
            y: lineHeightPos+textFieldHeight/2,
            size: textFieldSize,
            font: font,
            color: PDF.rgb(0, 0, 0)
        });

        let assessment=(course.averagePercentage2==='-')?'-':(course.averagePercentage2*1).toFixed(2);
        
        page.drawText( assessment, {
            x: width-75-textField4Width+textField4Width/2,
            y: lineHeightPos+textFieldHeight/2,
            size: textFieldSize,
            font: font,
            color: PDF.rgb(0, 0, 0)
        });

        page.drawLine({
            start: { x: 40, y: lineHeightPos },
            end: { x: width-40, y: lineHeightPos },
            thickness: 1,
            color: PDF.rgb(0, 0.53, 0.71),
            opacity: 0.5,
        });

    })

    //This is the content of the last page:
    const listComputedAtt=result.filter(elem=>{
        return elem.averagePercentage1!=='-'
    })

    const listComputedAss=result.filter(elem=>{
        return elem.averagePercentage2!=='-'
    });

    let avgAtt=listComputedAtt.reduce((acumulador, elementoActual) => acumulador + Math.round(elementoActual.averagePercentage1*100)/100, 0)/listComputedAtt.length;
    avgAtt=avgAtt.toFixed(2);
    let avgAss=listComputedAss.reduce((acumulador, elementoActual) => acumulador + Math.round(elementoActual.averagePercentage2*100)/100, 0)/listComputedAss.length;
    avgAss=avgAss.toFixed(2);

    //Total score for the course
    page.drawText( 'Total course average: ', {
        x: width-350,
        y: lineHeightPos-24,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });

    
    page.drawText( avgAtt+' %', {
        x: width-100-textField3Width/2-textField4Width,
        y: lineHeightPos-24,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });
    
    
    page.drawText( ''+avgAss, {
        x: width-75-(textField4Width/2),
        y: lineHeightPos-24,
        size: textFieldSize,
        font: fontBold,
        color: PDF.rgb(0, 0, 0)
    });
    




    // Genera el PDF y obtén los datos en formato Base64
const pdfBase64 = await pdfDoc.saveAsBase64();

// Convierte el Base64 a un array de bytes
const byteCharacters = atob(pdfBase64);
const byteNumbers = new Array(byteCharacters.length);
for (let i = 0; i < byteCharacters.length; i++) {
    byteNumbers[i] = byteCharacters.charCodeAt(i);
}
const byteArray = new Uint8Array(byteNumbers);

// Crea un Blob a partir del array de bytes
const blob = new Blob([byteArray], { type: 'application/pdf' });

// Crea un objeto URL a partir del Blob
const blobUrl = URL.createObjectURL(blob);

// Crea un enlace temporal para descargar el archivo
const link = document.createElement('a');
link.href = blobUrl;
link.download = 'documento.pdf';

// Añade el enlace al documento y haz clic en él para forzar la descarga
document.body.appendChild(link);
link.click();

// Remueve el enlace del documento
document.body.removeChild(link);
};

function truncateString(str, maxLength) {
    if (str.length > maxLength) {
        return str.slice(0, maxLength - 3) + '...';
    }
    return str;
}

// Función para verificar que todos los elementos seleccionados estén cargados
const areElementsLoaded = (selector) => {
    return new Promise((resolve) => {
        const checkElements = () => {
            const elements = document.querySelectorAll(selector);
            if (elements.length > 0 && Array.from(elements).every(elem => elem !== null)) {
                resolve(elements);
            } else {
                requestAnimationFrame(checkElements);
            }
        };
        checkElements();
    });
};
