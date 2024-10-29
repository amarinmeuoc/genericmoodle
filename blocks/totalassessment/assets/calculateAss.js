const areElementsAssLoaded = (selector) => {
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

document.addEventListener('itpDataUpdated', function() {
  let totalAss=0;
  document.querySelector('#containerass').innerHTML='';
  areElementsAssLoaded('.linkass').then((assessment_ass) => {
    
      // Convert NodeList to an array
      assessment_ass = Array.prototype.slice.call(assessment_ass);
      let total=0;
      let cont=0;
      //Avoiding duplicates
      let hash={};
      assessment_ass = assessment_ass.filter(o => hash[o.dataset.courseid] ? false : hash[o.dataset.courseid] = true);

      assessment_ass.forEach((link)=>{
          if (link.textContent!=='-'){
              total+=parseFloat(link.textContent);
              cont++;
          }
      })
      if (cont!==0)
          totalAss=total/cont;
      //Adding progress circular progress bar;
      let barass = new ProgressBar.Circle('#containerass', {
          color: '#aaa',
          // This has to be the same size as the maximum width to
          // prevent clipping
          strokeWidth: 4,
          trailWidth: 1,
          easing: 'easeInOut',
          duration: 1400,
          text: {
            autoStyleContainer: false
          },
          from: { color: '#a00', width: 1 },
          to: { color: '#00f', width: 4 },
          // Set default step function for all animate calls
          step: function(state, circle) {
            circle.path.setAttribute('stroke', state.color);
            circle.path.setAttribute('stroke-width', state.width);
        
            var value = circle.value() * 100;
            if (value === 0) {
              circle.setText('-');
            } else {
              circle.setText(value.toFixed(2));
            }
        
          }
        });
      
      barass.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
      barass.text.style.fontSize = '2rem';
      
      barass.animate(totalAss/100);  // Number from 0.0 to 1.0
  })

});


