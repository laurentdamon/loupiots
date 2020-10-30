function printDiv(id) {
  var divToPrint=document.getElementById(id);
  newWin= window.open("");
  newWin.document.write(divToPrint.outerHTML);
  newWin.print();
  newWin.close();
}

$(document).ready(function(){
	$('#forCheque').hide();
	$('#forVir').show("slow");
	$("#paymentType").change(function() {
		if (this.value == 'Cheque') {
			$('#forCheque').show("slow");
	        $('#forVir').hide();
	    } else if (this.value == 'Virement') {
	        $('#forVir').show("slow");
	        $('#forCheque').hide();
	    } else {
	    	$('#forCheque').hide();
	    	$('#forVir').hide();
	    }
	}); 
}); 

$(function() {
    //date picker
	$( "#start-date" ).datepicker({
        altField: "#end-date",
        changeMonth: true,
        dateFormat: "dd-mm-yy",
        minDate: 0,
        onClose: function( selectedDate ) {
            $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "#end-date" ).datepicker({
        changeMonth: true,
        numberOfMonths: 3,
        dateFormat: "dd-mm-yy",
        minDate: 0,
        onClose: function( selectedDate ) {
            $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });
    $( "#dateSel" ).datepicker({
        changeMonth: true,
        dateFormat: "dd-mm-yy",
	});
    
});



