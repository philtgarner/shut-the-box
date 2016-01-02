<?php

//Automatically load the necessary classes from the classes directory
function __autoload($class_name) {
    include 'classes/'.$class_name . '.php';
}


//If valid minimum and maximums have been given then use them, if not just use the default 1-9 
if(isset($_POST['min']) && isset($_POST['max']) && $_POST['min'] < $_POST['max']){
	$stb = new ShutTheBox(null,$_POST['min'],$_POST['max']);
}
else{
	$stb = new ShutTheBox();
}

//Get the turns, iterate through them and make the moves
if(isset($_POST['dice']) && is_array($_POST['dice'])){
	$turns = $_POST['dice'];

	//print_r($turns);

	foreach($turns as $t){
		$stb->takeTurn($t);
	}
}





//If the root is marked as successful then a root to success has been found
if($stb->getSuccess()){
	echo '<h2>Complete</h2>';
	echo '<p>Here are the moves you could have made:</p><hr>';
	$next = $stb->next();
	$min = $stb->min;
	$max = $stb->max;
	
	//Display the beginning state:
	echo '<div class="tiles">';

	for($i=$min; $i<=$max; $i++){
		echo '<div class="tile available"><p>';
		echo "$i</p></div>";
	}
	echo '</div>';

	while(!is_null($next)){
		$to_drop = $next->turn->values;
		$length = sizeof($to_drop);
		
		echo '<p class="centre">Rolled a ';
		echo $next->turn->getDiceRoll();
		echo ': drop the ';
		for($i=0; $i<$length; $i++){
			if($i > 0 && $i == $length-1)
				echo ' and ';
			else if($i > 0)
				echo ', ';
			echo $to_drop[$length-$i-1];
		}
		echo '</p>';
		
		echo '<div class="tiles">';
		for($i=$min; $i<=$max; $i++){
			if($next->isAvailable($i))
				echo '<div class="tile available"><p>';
			else
				echo '<div class="tile unavailable"><p>';
			echo "$i</p></div>";
		}
		echo '</div>';
		
		$next = $next->next();
	}
}
//If not successful then check if there are still any open paths available
else if($stb->isValid()){
	echo '<h2>Not finished yet</h2>';
	if(isset($turns)){
		echo '<p>Rolls: ';
		$count = 0;
		foreach($turns as $t){
			if($count != 0)
				echo ', ';
			echo $t;
			$count++;
		}
		echo '</p>';
		echo '<p>From these rolls there are ' . $stb->countValidChildren() . ' valid solutions but no way to shut the box...yet!';
	}
}

//If no open paths are available and the game isn't successful then we must have hit a dead end
else{
	echo '<h2>Failed</h2>';
	if(isset($turns)){
		echo '<p>Rolls: ';
		$count = 0;
		foreach($turns as $t){
			if($count != 0)
				echo ', ';
			echo $t;
			$count++;
		}
		echo '</p>';
	}
}


?>