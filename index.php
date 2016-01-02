<?php
//Include the header
include 'header.php';
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

<script>
	$(document).ready(function(){
		var rolls = [];
		var current = 0;
		
		function play(){
			$.ajax({
				url: 'stb.php',
				type: 'POST',
				data: {
					'dice': rolls,
					'min': 1,
					'max': 9
				},
				success: function(result){
					$('#response').html(result);
				}
			});
		}
		
		$('#undo').prop('disabled', true);
		
		$('#undo').click(function(){
			if(rolls.length > 0){
				rolls.splice(-1,1)
			}
			if(rolls.length == 0){
				$('#undo').prop('disabled', true);
			}
			play();

		});
		
		$('.number').click(function(){
			rolls.push($(this).attr("value"));
			$('#undo').prop('disabled', false);
			play();		
		});
	});

</script>


<h1>Shut the Box</h1>
<hr>
<p class="centre">Click on the numbers as you roll the dice:</p>
<form method="post" action="stb.php">
	<div class="row">
		<button type="button" class="number" value="2">2</button>
		<button type="button" class="number" value="3">3</button>
		<button type="button" class="number" value="4">4</button>
	</div>
	<div class="row">
		<button type="button" class="number" value="5">5</button>
		<button type="button" class="number" value="6">6</button>
		<button type="button" class="number" value="7">7</button>
	</div>
	<div class="row">
		<button type="button" class="number" value="8">8</button>
		<button type="button" class="number" value="9">9</button>
		<button type="button" class="number" value="10">10</button>
	</div>
	<div class="row">
		<button type="button" class="number" value="11">11</button>
		<button type="button" class="number" value="12">12</button>
		<button type="button" id="undo">Undo</button>
	</div>
</form>
<hr>

<div id="response"></div>

<?php
//Include the footer
include 'footer.php';
?>