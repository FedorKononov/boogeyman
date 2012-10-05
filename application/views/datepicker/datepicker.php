<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>

<style type="text/css">
	
	@import "//ajax.googleapis.com/ajax/libs/jqueryui/1.8.21/themes/pepper-grinder/jquery-ui.css";

	.ui-widget-content .ui-state-highlight a,.ui-widget-header .ui-state-highlight a { 
		color: green;
	}
</style>

<script type="text/javascript">
	var dates = new Array();

	function addDate(date) {if ($.inArray(date, dates) < 0) dates.push(date);}
	function removeDate(index) {dates.splice(index, 1);}

	function addOrRemoveDate(date)
	{
	  var index = $.inArray(date, dates);
	  if (index >= 0)
	    removeDate(index);
	  else 
	    addDate(date);
	}

	function padNumber(number)
	{
		var ret = new String(number);

		if (ret.length == 1)
			ret = "0" + ret;
		
		return ret;
	}

	$(function() {
		$('.input_date').each(function(i, date){
			dates.push($(date).val());
		});

		$("#datepicker").datepicker({	

			numberOfMonths: 3,
			autoSize: true,

			onSelect: function(dateText, inst) { 
				addOrRemoveDate(dateText);
				$('.input_date').remove();

				for (var i = 0; i < dates.length; i++)
					$('#datepicker').after($('<input type="hidden" name="dates[]" class="input_date">').val(dates[i]))
			},

			beforeShowDay: function (date){

				var year = date.getFullYear();

				var month = padNumber(date.getMonth() + 1);
				var day = padNumber(date.getDate());

				// This depends on the datepicker's date format
				var dateString = month + "/" + day + "/" + year;

				var gotDate = $.inArray(dateString, dates);

				if (gotDate >= 0)
					return [true, "ui-state-highlight"]; 

				return [true, ""];
			}


		});
	});

</script>
