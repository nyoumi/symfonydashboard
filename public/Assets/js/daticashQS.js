	 /*
	*************************************************************
	** Get the dir path to the currently executing script file **
	*************************************************************
	 */
	window.getRunningScript = () => {
		return () => {      
			return new Error().stack.match(/([^ \n])*([a-z]*:\/\/\/?)*?[a-z0-9\/\\]*\.js/ig)[0]
		}
	}
	console.log('%c Currently running script:', 'color: blue', getRunningScript()())

	var _thisLocationFile = getRunningScript()();
	//alert("PATH SCRIPT FILE IS: " + _thisLocationFile)
	/***********************************************************/

	var classesLocation ="js/" //get value
	var classesLocation = _thisLocationFile.replace("js/daticashQS.js", classesLocation); //get value
	//alert("CLASSES LOCATION: " + classesLocation)

	var jsLocation ="js/" //get value
	var jsLocation = _thisLocationFile.replace("js/daticashQS.js", jsLocation); //get value
	//alert("JS LOCATION: " + jsLocation)	
	
	var countriesDialCodeCurrencies = jsLocation +'components/data/countriesDialCodeCurrencies.json'; //get value
	//alert("UTILS LOCATION: " + countriesDialCodeCurrencies)	
	
   
$(document).ready(function() {
	let sender_country_code = "1";
	let recipient_country_code = "237";
    let default_country_code = "cm";
	
    $('#country_img').html("<img src='https://www.countryflags.io/"+default_country_code+"/flat/32.png'>")
    $('#country_name').html('Cameroon')
    $('#currency_received').val('XAF')
	
		$('.iti__country').click(function(e) {
			var $this = $(this);
			let _currency_received_txt = false;  
			//$('#recipient_phone').trigger("change");
			//$('#sender_phone').trigger("change");
			/*
			var _parent = $.each($this.parents(), function(index, value){alert("ok")
								let $parentCourant = value;
								if($parentCourant.hasClass("form-group-sender_phone")){
									_must_change_country = false;
									//return false;
								}
							});
				*/
					default_country_code = $($this).attr("data-country-code");
					var pid = $($this).attr("id")
/*
			sender_country_code = $this.find(".iti__dial-code").text();
			sender_country_code = sender_country_code.substring(1, sender_country_code.length);
					
			recipient_country_code = $this.find(".iti__dial-code").text();
			recipient_country_code = recipient_country_code.substring(1, recipient_country_code.length);
*/		  
		  
		  country_code = $this.find(".iti__dial-code").text();
		  country_code = country_code.substring(1, country_code.length);
		  

        	
	/*									
	    var country_code = $this.parents('.iti__flag-container').find('#countryCode').val()
		alert("country_code = "+country_code )
	*/	
        if($this.parents('.form-group-sender_phone').length){
                $("#sender_country_code").val(country_code);
        }
        
        if($this.parents('.form-group-recipient_phone').length){
                $("#recipient_country_code").val(country_code);
        }  
/*                
        alert("sender_country_code = "+$("#sender_country_code").val())	
        alert("recipient_country_code = "+$("#recipient_country_code").val())
*/
								
			if(_currency_received_txt = !$this.parents('.form-group-sender_phone').length){
				$('#country_img').html("<img src='https://www.countryflags.io/"+default_country_code+"/flat/32.png'>");		
				$('#country_name').html($("#"+pid).children(".iti__country-name").text());
			}
			
			//alert(sender_country_code)
			//alert(recipient_country_code)
			//alert(_currency_received_txt)

			$.get(countriesDialCodeCurrencies, function(data) { //ou utiliser l'api suivant "https://restcountries.eu/rest/v2/callingcode/"+default_country_code
				$.each(data, function(key, value) {
				  if(value.Iso2.toLowerCase() == default_country_code){
					  //alert(value.Iso2.toLowerCase())
					if(_currency_received_txt === true){
						$('#currency_received_txt').html('('+ value.Currency +')')
						$('#currency_received').val(value.Currency)
					}else{
						$('#currency_sent_txt').html('('+ value.Currency +')')
						$('#currency_sent').val(value.Currency)
					}
				  }
				})
			}, 'json')
	
		});
		
/* 		$('.sendBtn').on('click', function(e) {
 		  let total_pay = $("#total_pay").text();
		  let fees = $("#fees").text();

		  
		  $("#recipient_country_code").val(recipient_country_code);
		  $("#sender_country_code").val(sender_country_code);
		  
		/* 		  
			alert($("#sender_country_code").val());
			alert($("#recipient_country_code").val());
	
			alert("#sender_phone");
			alert("#recipient_phone");
			alert("#currency_sent");
			alert("#currency_received");
			alert("#amount_sent");
			alert("#amount_received");
			alert("#total_pay");
			alert("#fees");
			alert("#mode");
			alert("#reason");
			alert("#account_ref");
			alert("#sender_country_code").val();
			alert("#recipient_country_code"); 
		*/

			/*$(".sendBtn").text("Processing...").prop("disabled", true);			
			$('.form').submit();
			e.preventDefault();
						
		}); */

})
