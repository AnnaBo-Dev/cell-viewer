/** if site is start or reloading */
window.addEventListener('load', function() {
    const alternativeCells = document.querySelector('#alternative');
    
    // const tech = document.querySelector('#tech');
    // const series = document.querySelector('#series');

    var ajax = new XMLHttpRequest();


    if (alternativeCells !== null) {
        alternativeCells.addEventListener('click', function() {     
            /**
             * if click, then use series name to find tech and series and select this is clear form
             * ajax and php file for select from db needet
             */
            /** ajax get packing type elements
             * (layer, brutto, netto, packing_no, packing_weight)
             * @params typevalue, packing, technology, seriesValue
             */
            let alternativeSeries = this.value;
            var params = {
                alternativeSeries
            }
            ajax.onload = function() { 
                // parse from JSON
                var data = JSON.parse(this.response);
    
                // if SUCCESS
                if (this.status >= 200 && this.status < 400) {
    
                    if (data.result.series !== null) {
                        window.location.href
                        tech.selectedIndex = data.result.technology;
                        tech.value = data.result.technology;
                        tech.dispatchEvent(new CustomEvent('change'));
                        series.selectedIndex = data.result.series;
                        series.value = data.result.series;
                        series.dispatchEvent(new CustomEvent('change'));
                        type.selectedIndex = 0;
                        try {
                            type.showPicker();
                        } catch (error) {
                            alert("Please select an new type from the alternative series to continue!");
                        }
                    }
                } else {
                    console.log("Loading error:\n" + this.responseCode);
                }
            };    
            ajax.onerror = function() {
                console.log();
            };
            ajax.open(
                'GET', 
                './ajax/getAlternativeCells.php?' 
                    + 'alternativeSeries=' + params.alternativeSeries, 
                true
            );
            ajax.send();
        });
    }
    
});

function openPicker(inputDateElem) {
    var ev = document.createEvent('KeyboardEvent');
    ev.KeyboardEvent('keydown', true, true, document.defaultView, 'F4', 0);
    inputDateElem.dispatchEvent(ev);
}