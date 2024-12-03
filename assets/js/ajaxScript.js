/** if site is start or reloading */
window.addEventListener('load', function() {

    this.document.addEventListener('keypress', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
        }
    });

    // formMethod = this.form.method; // post
    const ajaxRequest = new XMLHttpRequest();
    const usFormat = new Intl.NumberFormat('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2,});
    const euFormat = new Intl.NumberFormat('de-DE', {minimumFractionDigits: 2, maximumFractionDigits: 2,});

    // create const for all user input select fields
    const tech = document.querySelector('#tech');
    const series = document.querySelector('#series');
    const type = document.querySelector('#type');
    const quantity = document.querySelector('#quantity');
    const typevalue = document.querySelector('#battery').value;

    // create const for all async select fields
    let packingType = document.querySelector('#packing_type');
    let filledStatusType = document.querySelector('#filled_status');
    let calculationType = document.querySelector('#calculation_type');

    // reset all async select fileds
    resetAllAsyncSelectFields();
    // hide some content fields
    hideElement('#packing_dimensions', true);
    hideElement('#weight_p_fullyload_package', true);
    hideElement('#total_package_weight', true);
    //disable some select fields
    disableElement('#series', true);
    disableElement('#type', true);
    disableElement('#packing_type', true);
    if (document.querySelector('#pricing') !== null) {
        disableElement('#calculation_type', true);
    }
    
    // field varaibles for price calculation
    let grossPrice = document.querySelector('#gross_price');
    let pureMTZ = document.querySelector('#pure_mtz');
    // hide CAPACITY on first load 
    hideElementByTable('#nominal');
    hideElementByTable('#real');
    hideElementByTable('#real2');
    // hide MATNO SEPERATED on first load 
    hideElementByTable('#matno_seperated');
    hideElementByTable('#paletts_electrolyte');
    hideElementByTable('#total_weight_electrolyte');

    // hide MATNO on first load
    hideElementByTable('#matno');
    hideElement('#matno', true);
    
    // US / EU STANDARD change per button
    const aktivFormatType = document.querySelector('#aktiv_format_type');
    const usStandard = document.querySelector('#us_standard');
    const euStandard = document.querySelector('#eu_standard');
    disableElement('#us_standard', true);
    disableElement('#eu_standard', true);
    const INCHES = 0.0393701;    // in   -> mm
    const GALLON = 0.264172;     // gal  -> l
    const POUNDS = 2.20462;      // lbs  -> kg

    if (aktivFormatType.value === 'US') {
        // get all elements with an , and replace with .
        if (usStandard.classList.contains('aktive')) {
            return;
        }
        usStandard.classList.add('aktive');
        euStandard.classList.remove('aktive');
    }

    /**
     * ajax request for the version
     */
    var xhr = new XMLHttpRequest();
    const dbInerstVersion = document.querySelector('#db_insert_version');
    
    xhr.onload = function() {
        var data = JSON.parse(xhr.response);
        if (xhr.status >= 200 && xhr.status < 400) {
            if (data.result.version !== null) {
                dbInerstVersion.innerText = data.result.version;
            } else {
                dbInerstVersion.innerText = '7.0.0';
            }
        } else {
            console.log("Loading error:\n" + this.responseCode);
        }
    };
    xhr.onerror = function() {
        console.log();
    };
    xhr.open('GET', './ajax/getDBinsertVersion.php', true);
    xhr.send();


    /** TECHNOLOGY select option field by CANGE */
    tech.addEventListener('change', function() {
        // reset all select fields after TECHNOLOGY
        series.selectedIndex = null;
        type.selectedIndex = null;
        battery.value = null;
        resetAllAsyncSelectFields();

        // set headline of SERIES item
        const label = this.value;
        selectLabel(series, '#series', label);
    });

    /** TECHNOLOGY select option field by CLICK*/
    tech.addEventListener('click', function() {
        disableElement('#series', false);
    });

    /** SERIES select option field by CANGE */
    series.addEventListener('change', function() {
        // reset all select fields after SERIES
        type.selectedIndex = null;
        battery.value = null;
        resetAllAsyncSelectFields();

        // set headline of TYPE item
        const label = this.value;
        selectLabel(type, '#type', label);
    });

    /** SERIES select option field by CLICK*/
    series.addEventListener('click', function() {
        if (tech.value === 'Technology') {
            alert('Please select a Technology');
        }
        disableElement('#type', false);
    });

    /** TYPE select option field CANGE */
    type.addEventListener('change', function() {
        // reset all select fields after TYPE
        battery.value = this.value;
        resetAllAsyncSelectFields();
        enabledButton('#submit');
    });

    /** SERIES select option field by CLICK*/
    type.addEventListener('click', function() {
        if (series.value === 'Series') {
            alert('Please select a Series');
        }
    });

    /** TYPE select option field CANGE */
    quantity.addEventListener('change', function() {
        // reset all select fields after TYPE
        resetAllAsyncSelectFields();
        enabledButton('#submit');
    });

    /** BATTERY click is trigert by submit*/
    battery.addEventListener('click', function() {
        if (series.value === 'Series') {
            alert('Please select a Series');
        }

        const onCompletion = function () {
            if (tech.value === 'VRLA') {
                filledStatusType.value = 'filled and charged';
                filledStatusType.dispatchEvent(new Event('change'));
            }
        };

        // show MATNO element if the first data from db come back
        const matno = document.querySelector('#matno');
        const matnoTable = matno.closest('.outputtabel');
        matnoTable.style.display = '';
                
        if (aktivFormatType.value === 'US') {
            getCapacityByAjax('getCapacityUS.php', onCompletion);
        } else {
            getCapacityByAjax('getCapacity.php', onCompletion);
        }

        disableElement('#us_standard', false);
        disableElement('#eu_standard', false);

        enabledButton('#print');
    });

    let packingWeight = null;
    let packingCellPerBox = null;

    /** PACKING async select option field */
    packingType.addEventListener('change', function() {        
        // show all elements after PACKING TYPE
        hideElement('#packing_dimensions', false);
        hideElement('#weight_p_fullyload_package', false);
        hideElement('#total_package_weight', false);
        printAsyncSelectedValues('#packing_type_print', '#packing_type_print_value', packingType);

        /** ajax get packing type elements
         * (layer, brutto, netto, packing_no, packing_weight)
         * @params typevalue, packing, technology, seriesValue
         */
        const packing = this.value;
        const technology = tech.value;
        const seriesValue = series.value;
        var params = {
            typevalue,
            packing,
            technology,
            seriesValue
        }
        ajaxRequest.onload = function() { 
            // parse from JSON
            var data = JSON.parse(this.response);

            // if SUCCESS
            if (this.status >= 200 && this.status < 400) {
                
                // LAYER is not null default with 1
                let layer = document.querySelector('#layer');
                if (data.result.layer !== null) {
                    layer.innerText = data.result.layer;
                    layer = data.result.layer;
                } else {
                    layer.innerText = 1;
                    layer = 1;
                }
                
                // HEIGHT is not null default with 1
                let height = null;
                if (data.result.height !== null) {
                    height = data.result.height;
                } else {
                    height = 1;
                }

                // callculate NO OF PALLETS
                let noOfPalettes = document.querySelector('#paletts_count');
                let palletsCount = (quantity.value / data.result.cell_per_layer) / layer;
                noOfPalettes.innerText = Math.ceil(palletsCount);

                let bruttoHeight = document.querySelector('#brutto_height');
                let nettoHeight = document.querySelector('#netto_height');
                let packingName = document.querySelector('#packing_no');
                if (params.packing === 'Palett') {
                    // if PACKING TYP is PALETT calaculate the brutto and netto HEIGHT
                    let batteryHeight = (layer * parseFloat(height)) + 150; // fix save distance = 150
                    if (aktivFormatType.value === 'US') {
                        let usvalue = batteryHeight * INCHES;
                        bruttoHeight.innerText = usvalue.toFixed(0);
                        nettoHeight.innerText = usvalue.toFixed(0);
                    } else {
                        bruttoHeight.innerText = batteryHeight;
                        nettoHeight.innerText = batteryHeight;
                    }
                    // calculate the total PACKING WEIGHT 
                    calculatedPackingWeight(
                        filledStatusType.value, 
                        packingWeight = 22, // fix weight (Palett = 22kg)
                        packingCellPerBox = parseFloat(data.result.cell_per_layer),
                        quantity.value
                    );
                    packingName.innerText = '';
                } else {
                    // if PACKING TYP is NOT PALETT get the brutto and netto HEIGHT from DB
                    if (aktivFormatType.value === 'US') {
                        let bruttoUsvalue = data.result.brutto * INCHES;
                        let nettoUsvalue = data.result.netto * INCHES;
                        bruttoHeight.innerText = bruttoUsvalue.toFixed(0);
                        nettoHeight.innerText = nettoUsvalue.toFixed(0);
                    } else {
                        bruttoHeight.innerText = data.result.brutto;
                        nettoHeight.innerText = data.result.netto;
                    }
                    packingName.innerText = data.result.packing_no;
                    // calculate the total PACKING WEIGHT
                    calculatedPackingWeight(
                        filledStatusType.value, 
                        packingWeight = parseFloat(data.result.packing_weight), 
                        packingCellPerBox = parseFloat(data.result.cell_per_layer),
                        quantity.value
                    );
                }
            } else {
                console.log("Loading error:\n" + this.responseCode);
            }
        };    
        ajaxRequest.onerror = function() {
            console.log();
        };
        ajaxRequest.open(
            'GET', 
            './ajax/getPackingValues.php?' 
                + 'typevalue=' + params.typevalue 
                + '&packing=' + params.packing
                + '&technology=' + params.technology 
                + '&seriesValue=' + params.seriesValue, 
            true
        );
        ajaxRequest.send();
    });

    /** FILLED STATUS async select option field */
    filledStatusType.addEventListener('change', function() {
        if (this.querySelectorAll('option').length === 0) {
            return;
        }

        // reset all select fields after FILLED STATUS
        disableElement('#packing_type', false);
        if (document.querySelector('#calculation_type') !== null) {
            if (document.querySelector('#gross_price') === null) {
                disableElement('#calculation_type', true);
            } else {
                disableElement('#calculation_type', false);
            }
        }
        printAsyncSelectedValues('#filled_status_print', '#filled_status_print_value', filledStatusType);

        /** ajax get filled status elements
         * (gross_price, mtz, matno, matno_seperated)
         * @params typevalue, filledstatus
         */
        const filledstatus = this.value;
        var params = {
            typevalue,
            filledstatus,
        }
        ajaxRequest.onload = function() { 
            // parse from JSON
            var data = JSON.parse(this.response);
            
            // if SUCCESS
            if (this.status >= 200 && this.status < 400) {

                if (document.querySelector('#pricing') !== null) {
                    // GROSS PRICE if not null
                    if (data.result.price !== null) {
                        if (aktivFormatType.value === 'US') {
                            grossPrice.innerText = usFormat.format(data.result.price);
                        } else {
                            grossPrice.innerText = euFormat.format(data.result.price);
                        }
                    }

                    // MTZ if not null
                    if (data.result.mtz !== null) {
                        if (aktivFormatType.value === 'US') {
                            pureMTZ.innerText = usFormat.format(data.result.mtz);
                        } else {
                            pureMTZ.innerText = euFormat.format(data.result.mtz);
                        }
                    }

                    // price calculation
                    if (grossPrice !== null) {
                        disableElement('#calculation_type', false);
                        let pricePerCell = grossPrice !== null ? parseFloat(data.result.price) : null;
                        defaultCalculationPricing(grossPrice, pureMTZ, quantity.value, pricePerCell);
                    } 
                }

                // MATNO if not null
                hideElement('#matno', false);
                if (data.result.matno !== null) {
                    matno.innerText = data.result.matno;
                }

                // MATNO SEPERATED if not null AND only for unfilled
                const matnoSeparted = document.querySelector('#matno_seperated');
                const matnoSepartedTable = matnoSeparted.closest('.outputtabel');

                const noOfPalettsElectrolyte = document.querySelector('#paletts_electrolyte');
                const noOfPalettsElectrolyteTable = noOfPalettsElectrolyte.closest('.outputtabel');

                const totalWeightElectrolyte = document.querySelector('#total_weight_electrolyte');
                const totalWeightElectrolyteTable = totalWeightElectrolyte.closest('.outputtabel');

                
                let palettsElectrolyte = null;
                let totalElecWeight = null;
                if (data.result.seperated !== null) {
                    // if the filled status is unfilled, than the field seperates_matno is visible
                    if (params.filledstatus === 'unfilled') {
                        let countDrums30l = document.querySelector('#count_drums_30l').innerText;
                        let density = document.querySelector('#density').innerText;
                        matnoSeparted.innerText = data.result.seperated;
                        matnoSepartedTable.style.display = '';

                        palettsElectrolyte = Math.round(countDrums30l / 16) === 0 ? 1 : Math.round(countDrums30l / 16);
                        noOfPalettsElectrolyte.innerText = palettsElectrolyte;                        
                        noOfPalettsElectrolyteTable.style.display = '';

                        totalElecWeight = (countDrums30l * 30 * parseFloat(density.replace(',', '.')).toFixed(2)) + (palettsElectrolyte * 20);
                        if (aktivFormatType.value === 'US') {
                            let totalElecWeightUsvalue = totalElecWeight * POUNDS;
                            totalWeightElectrolyte.innerText = usFormat.format(totalElecWeightUsvalue);
                        } else {
                            totalWeightElectrolyte.innerText = euFormat.format(totalElecWeight);
                        }
                        totalWeightElectrolyteTable.style.display = '';

                        // if unfilled, callculate the packing weight with weightWoelectrolyte
                        calculatedPackingWeight(
                            filledStatusType.value, 
                            packingWeight,
                            packingCellPerBox,
                            quantity.value
                        );

                    } else {
                        matnoSepartedTable.style.display = 'none';
                        noOfPalettsElectrolyteTable.style.display = 'none';
                        totalWeightElectrolyteTable.style.display = 'none';

                        // if NOT unfilled, callculate the packing weight with total packing weight
                        calculatedPackingWeight(
                            filledStatusType.value, 
                            packingWeight,
                            packingCellPerBox,
                            quantity.value
                        );
                    }
                }
            } else {
                console.log("Loading error:\n" + this.responseCode);
            }
        };  
        ajaxRequest.onerror = function() {
            console.log();
        };
        ajaxRequest.open(
            'GET', 
            './ajax/getFilledStatusNumbers.php?' 
                + 'typevalue=' + params.typevalue 
                + '&filledstatus=' + params.filledstatus, 
            true
        );
        ajaxRequest.send();
    });

    // STANDARD or REVERSE DISCOUNT calculation input visible and calcultaion
    if (document.querySelector('#pricing') !== null) {
        calculationType.addEventListener('change', function () {
            printAsyncSelectedValues('#calculation_type_print', '#calculation_type_print_value', calculationType);
                        
            let discountType = this.value;
            // price callculation async input fields
            let standardDiscountInput = document.querySelector('#standard_discount_input');
            let reverseDiscountInput = document.querySelector('#reverse_discount_input');

            let price = parseFloat(document.querySelector('#gross_price').innerText.replace('.','').replace(',','.'));

            let pricePerCell = null;
            let discount = null;
            let discountOutput = null;
            let numberInput = null;
            
            // STANDARD
            if (discountType === 'Standard') {
                // hide reverse
                hideElement('#reverse_discount', true);
                reverseDiscountInput.value = null;
                
                hideElement('#standard_discount', false);
                /** STANDARD DISCOUNT async input field */
                standardDiscountInput.addEventListener('change', function() {
                    numberInput = standardDiscountInput.value;
                    if (numberInput > 100) {
                        numberInput = 100;
                    }
                    standardDiscountInput.value = parseFloat(numberInput).toFixed(1);

                    printDiscountValues('#standard_discount_input_print', '#standard_discount_input');

                    // input and output
                    discount = parseFloat(standardDiscountInput.value.replace(',', '.'));
                    discountOutput = document.querySelector('#standard_discount_price');
                    
                    if (price !== null) {
                        // calculation price per cell with STANDARD discount
                        pricePerCell = price - (price * discount / 100);
                        if (aktivFormatType.value === 'US') {
                            discountOutput.innerText = usFormat.format(pricePerCell);
                        } else {
                            discountOutput.innerText = euFormat.format(pricePerCell);
                        }
                    }
                    pricePerCell = discountOutput !== null ? pricePerCell : null;
                    defaultCalculationPricing(grossPrice, pureMTZ, quantity.value, pricePerCell);
                });
            }
            // REVERSE
            if (discountType === 'Reverse') {
                // hide standard
                hideElement('#standard_discount', true);
                standardDiscountInput.value = null;

                hideElement('#reverse_discount', false);
                /** REVERSE DISCOUNT async input field */
                reverseDiscountInput.addEventListener('change', function() {
                    numberInput = reverseDiscountInput.value;
                    reverseDiscountInput.value = parseFloat(numberInput).toFixed(2);
                    // input and output
                    discount = parseFloat(reverseDiscountInput.value.replace(',', '.'));
                    discountOutput = document.querySelector('#reverse_discount_price');  
                
                    if (price !== null) {
                        // calculation price per cell with REVERSE discount
                        pricePerCell = discount.toFixed(2);
                        let procent = ((price - discount) / price) * 100;
                        if (aktivFormatType.value === 'US') {
                            discountOutput.innerText = usFormat.format(procent);
                        } else {
                            discountOutput.innerText = euFormat.format(procent);
                        }
                    }
                    pricePerCell = discount !== null ? parseFloat(discount) : null;
                    defaultCalculationPricing(grossPrice, pureMTZ, quantity.value, pricePerCell);
                });
            }
        });
    }

    /** get CAPACITY labels and Values with AJAX
     * @param ajaxFileName 
     */
    function getCapacityByAjax(ajaxFileName, onCompletion) {
        onCompletion = typeof onCompletion === 'function' ? onCompletion : function () {};

        /** ajax get CAPACITY
         * (nominal, real, real2)
         * @params typevalue, technology, seriesValue
         */
        const technology = tech.value;
        const seriesValue = series.value;
        var params = {
            typevalue,
            technology,
            seriesValue
        }
        ajaxRequest.onload = function() { 
            // parse from JSON
            var data = JSON.parse(this.response);
            
            // if SUCCESS
            if (this.status >= 200 && this.status < 400) {

                // NOMINAL if not null
                const nominal = document.querySelector('#nominal');
                const nominalTable = nominal.closest('.outputtabel');
                const nominalLabel = document.querySelector('#nominal_label');
                if (data.result.nominal !== null) {
                    nominalTable.style.display = '';
                    nominalLabel.innerText = data.result.nominal_label;
                    nominal.innerText = data.result.nominal;
                } else {
                    nominalTable.style.display = 'none';
                }

                // REAL if not null
                const real = document.querySelector('#real');
                const realTable = real.closest('.outputtabel');

                const realLabel = document.querySelector('#real_label');
                if (data.result.real !== null) {
                    realTable.style.display = '';
                    realLabel.innerText = data.result.real_label;
                    real.innerText = data.result.real;
                } else {
                    realTable.style.display = 'none';
                }

                // REAL2 if not null
                const real2 = document.querySelector('#real2');
                const real2Table = real2.closest('.outputtabel');
                const real2Label = document.querySelector('#real2_label');
                if (data.result.real2 !== null) {
                    real2Table.style.display = '';
                    real2Label.innerText = data.result.real2_label;
                    real2.innerText = data.result.real2;
                }
                else {
                    real2Table.style.display = 'none';
                }

                onCompletion();
            } else {
                console.log("Loading error:\n" + this.responseCode);
            }
        };    
        ajaxRequest.onerror = function() {
            console.log();
        };
        ajaxRequest.open(
            'GET', 
            './ajax/' + ajaxFileName + '?' 
                + 'typevalue=' + params.typevalue
                + '&technology=' + params.technology
                + '&seriesValue=' + params.seriesValue, 
            true
        );
        ajaxRequest.send();
    }

    /** calculation of PACKING WEIGHT based on FILLED STATUS and PACKING TYPE
     * @param filledStatus 
     * @param box_weight 
     * @param cellPerLayer 
     * @param quantity 
     */
    function calculatedPackingWeight(
        filledStatus,
        box_weight,
        cellPerLayer, 
        quantity
    ) {
        // onCompletion = typeof onCompletion === 'function' ? onCompletion : function () {};
        // values with output fields
        let fullyLoadPackage = document.querySelector('#fullyload_package');
        let totalPackingWeight = document.querySelector('#total_weight_package');
        // values with curent field content
        let weight = document.querySelector('#total').innerText.replace(',', '.');
        let weightWoelectrolyte;
        if (document.querySelector('#without_electrolyte') !== null) {
            weightWoelectrolyte = document.querySelector('#without_electrolyte').innerText.replace(',', '.');
        }
        let palettsCount = document.querySelector('#paletts_count').innerText;
        let layers = document.querySelector('#layer').innerText;

        let fullyLoad = null;
        let totalWeight = null;
        // if FILLED STATUS is unfilled
        if (filledStatus === 'unfilled') {
            // fullyLoad = weightWoelectrolyte * cellPerLayer + box_weight;
            fullyLoad = weightWoelectrolyte * (cellPerLayer * layers) + box_weight;
            totalWeight = (weightWoelectrolyte * quantity) + (box_weight * palettsCount);
            if (aktivFormatType.value === 'US') {
                fullyLoadPackage.innerText = usFormat.format(fullyLoad);
                totalPackingWeight.innerText = usFormat.format(totalWeight);
            } else {
                fullyLoadPackage.innerText = euFormat.format(fullyLoad);
                totalPackingWeight.innerText = euFormat.format(totalWeight);
            }
            // if FILLED STATUS is NOT unfilled
        } else {
            fullyLoad = weight * (cellPerLayer * layers) + box_weight;
            totalWeight = (weight * quantity) + (box_weight * palettsCount);
            if (aktivFormatType.value === 'US') {
                fullyLoadPackage.innerText = usFormat.format(fullyLoad);
                totalPackingWeight.innerText = usFormat.format(totalWeight);
            } else {
                fullyLoadPackage.innerText = euFormat.format(fullyLoad);
                totalPackingWeight.innerText = euFormat.format(totalWeight);
            }
        }

        // onCompletion();
    }

    /** calculation of PRICING with and without discount
     * @param grossPrice
     * @param pureMTZ
     * @param quantity
     * calculation differnet regarding the @param pricePerCell
     */
    function defaultCalculationPricing(
        grossPrice, 
        pureMTZ, 
        quantity, 
        pricePerCell,
    ) {     
        let price = null;
        if (pricePerCell.length > 6) {
            price = grossPrice !== null ? parseFloat(pricePerCell.replace('.','').replace(',','.')) : null;
        } else {
            price = grossPrice !== null ? pricePerCell : null;
        }

        let mtz = null;
        if (pricePerCell.length > 6) {
            mtz = pureMTZ !== null ? pureMTZ.innerText.replace('.','').replace(',', '.') : null;
        } else {
            mtz = pureMTZ !== null ? pureMTZ.innerText.replace(',', '.') : null;
        }

        let priceWoMtz = document.querySelector('#calculated_without_MTZ');
        let priceMtzTotal = document.querySelector('#calculated_MTZ');
        let priceTotal = document.querySelector('#total_price');
        
        // calculation price without MTZ
        let calculatedPriceWoMtz = null;
        if (price !== null) {
            calculatedPriceWoMtz = price * quantity;
            if (aktivFormatType.value === 'US') {
                priceWoMtz.innerText = usFormat.format(calculatedPriceWoMtz);
            } else {
                priceWoMtz.innerText = euFormat.format(calculatedPriceWoMtz);
                
            }
        }
        // calculation total MTZ 
        let calculatedPriceMtzTotal = null;
        if (mtz !== null) {
            calculatedPriceMtzTotal = mtz * quantity;
            if (aktivFormatType.value === 'US') {
                priceMtzTotal.innerText = usFormat.format(calculatedPriceMtzTotal);
            } else {
                priceMtzTotal.innerText = euFormat.format(calculatedPriceMtzTotal);
            }
        } else {
            calculatedPriceMtzTotal = 0;
        }
        // calculation total price (price per cell + MTZ)
        let calculatedTotalPrice = null;
        if (priceMtzTotal !== null) {
            calculatedTotalPrice = calculatedPriceWoMtz + calculatedPriceMtzTotal;
            if (aktivFormatType.value === 'US') {
                priceTotal.innerText = usFormat.format(calculatedTotalPrice);
            } else {
                priceTotal.innerText = euFormat.format(calculatedTotalPrice);
            }
        } else {
            if (aktivFormatType.value === 'US') {
                priceTotal.innerText = usFormat.format(calculatedPriceWoMtz);
            } else {
                priceTotal.innerText = euFormat.format(calculatedPriceWoMtz);
            }
        }
    }

    /** reset all ASYNC select fields */
    function resetAllAsyncSelectFields() {
        // async select fields
        resetPacking();
        resetFilledStatus();
        resetPricing();
    }

    /** reset all packing elements */
    function resetPacking() {
        packingType.selectedIndex = null;
    }

    /** reset all filled status elements */
    function resetFilledStatus() {
        filledStatusType.selectedIndex = null;
    }

    /** reset all pricing elements */
    function resetPricing() {
        if (document.querySelector('#pricing') !== null) {
            document.querySelector('#calculation_type').selectedIndex = null;
            hideElement('#standard_discount', true);
            hideElement('#reverse_discount', true);
        }
    }

    /** SUBMIT button enable */
    function enabledButton(button) {
        if (battery.value === '') {
            return;
        }
        document.querySelector(button).disabled = false;
    }

    /** trigger click on fileds by SUBMIT click  */
    if (typeof window.afterLoadingCompleted === 'function') {
        window.afterLoadingCompleted();
    }

    /** print the async selected values with correct css 
     * @params asyncFieldPrintID, asyncFieldPrintValueID, asyncSelectedField 
     */
    function printAsyncSelectedValues(asyncFieldPrintID, asyncFieldPrintValueID, asyncSelectedField){
        let asyncValuePrint = document.querySelector(asyncFieldPrintID);
        let asyncValuePrintValue  = document.querySelector(asyncFieldPrintValueID);
        if (asyncSelectedField.value !== '') {
            asyncValuePrint.classList.add('d-print-inline');
            asyncValuePrintValue.innerText = asyncSelectedField.value;
        } else {
            asyncValuePrint.classList.remove('d-print-inline');
        }
    }

    /** print the discount input with correct css 
     * @params discountPrintValueID, asyncDiscountInputID
     */
    function printDiscountValues(discountPrintValueID, asyncDiscountInputID){
        let discountInput = document.querySelector(asyncDiscountInputID);
        let discountPrintValue = document.querySelector(discountPrintValueID);
        if (discountInput.value !== '') {
            discountPrintValue.classList.add('d-print-inline');
            discountPrintValue.innerText = discountInput.value;
        } else {
            discountPrintValue.classList.remove('d-print-inline');
        }
    }
});

/** set headline for next select option field  */
function selectLabel(selectField, fieldId, label) {
    const expectedGroup = document.querySelector(fieldId + ' optgroup[label="' + label + '"]');
    for (const group of selectField.querySelectorAll('optgroup')) {
        group.style.display = 'none';
    }
    if (expectedGroup !== null) {
        expectedGroup.style.display = '';
    }
}

function standardChange(fieldUnitId, unitAcronym) {
    const units = document.querySelectorAll(fieldUnitId);
    for (let unit of units) {
        if (unit !== null) {
            unit.innerText = unitAcronym;
        }
    }
}

/** hide or unhide elements form side */
function hideElement(elementField, bool) {
    if (bool === true) {
        document.querySelector(elementField).style.display = 'none';
    }
    if (bool === false) {
        document.querySelector(elementField).style.display = '';
    }
}

/** hide or unhide elements form side */
function hideElementByTable(fieldId) {
    const field = document.querySelector(fieldId);
    const fieldTable = field.closest('.outputtabel');
    if (field.innerText === ''
        || field.innerText === null
    ) {
        fieldTable.style.display = 'none';
    }
}

/** disable and aktivate elements form side */
function disableElement(elementField, bool) {
    if (bool === true) {
        document.querySelector(elementField).disabled = true;
    }
    if (bool === false) {
        document.querySelector(elementField).disabled = false;
    }
}

/** restart Form without content */
function clearForm() {
    window.location.href = window.location.href;
}

/** use select battery type value for submit */ 
function selectValue() {
    var selectOption = document.getElementById('type');
    var i = selectOption.selectedIndex;
    document.getElementById('battery').value = selectOption.options[i].text;
}
