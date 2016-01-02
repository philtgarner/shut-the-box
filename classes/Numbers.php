<?php
/*
 * The class that describes the a number thrown by a die/some dice. This class is used to find all the possible tiles to drop in response to a dice roll.
 *
 * SAMPLE:
 *     -- 7 + 1 -- 5 + 2 + 1
 * 8-- -- 6 + 2
 *     -- 5 + 3
 *
 * Overview of algorithm:
 * The dice roll is the first possible move.
 * The dice roll is split into pairs by subtracting 1, 2, ... until the halfway point is met (numbers can only appear in the solution once)
 * The largest numbers in these pairs are then split into their possible pairs to make triplets that add up to the initial dice roll. The minimum allowed number in these pairs is one more than the second largest number in the previous set. E.g. 7 + 1 can have the 7 divided into any pair that has a minimum value of one more than 1, therefore 5 + 2 + 1 is allowed but 6 + 1 + 1 is not, this is also the reason 6 + 2 cannot be translated into a triplet.
 * This step is repeated until no more pairs can be generated.
 *
 * Author: Philip Garner
 */
class Numbers{
	
	public $values = array();		//The numbers to split into further possibilities. The first time this is run this value will be the dice roll.
	public $children = array();		//Any children this node has
	
	/*
	 * Constructs a number calculator for a number or a set of numbers.
	 */
	public function __construct($values){
		if(is_array($values)){
			$this->values = $values;
		}
		else{
			$this->values = array();
			$this->values[] = $values;
		}
	}
	
	/*
	 * Gets the dice roll by adding up all the values in this set.
	 */
	public function getDiceRoll(){
		$output = 0;
		foreach($this->values as $v){
			$output += $v;
		}
		return $output;
	}
	
	/*
	* Gets the maximum number in this set
	*/
	public function getMax(){
		rsort($this->values);
		return $this->values[0];
	}
	
	/*
	 * Gets the second largest value in this set (if there is one). Zero is returned if no second largest number is avaialble.
	 */
	public function getSecondMax(){
		if(sizeof($this->values) >= 2){
			rsort($this->values);
			return $this->values[1];
		}
		return 0;
	}
	
	/*
	 * Gets the children of this object, the root (dice roll) has all permutations as its children
	 */
	public function getChildren(){
		$output = array();
		$output[] = $this;
		foreach($this->children as $c){
			$output = array_merge($output, $c->getChildren());
		}
		return $output;
	}
	
	/*
	 * Finds all the possible ways in which this number(s) could be represented.
	 */
	public function spawn(){
		$number = $this->getMax();			//If only one number this will return the only number
		$start = $this->getSecondMax();		//If only one number this will return zero
		$half = 0;							//One less than half (rounded up). This represents the highest second number in a pair and avoids the doubles (e.g. 3 + 3).
		
		if($number % 2 == 0){
			$half = ($number-2)/2;
		}
		else{
			$half = ($number-1)/2;
		}
		
		//Find permutations and make them the children of this node.
		for($i = $start+1; $i<=$half; $i++){
			$newNumbers = array();
			for($j = 1; $j<sizeof($this->values); $j++){
				$newNumbers[] = $this->values[$j];
			}
			$newNumbers[] = $number - $i;
			$newNumbers[] = $i;
			$this->children[] = new Numbers($newNumbers);
		}
		
		//Make spawns of the children to find all possible matches.
		foreach($this->children as $c){
			$c->spawn();
		}
		
	}

}

?>