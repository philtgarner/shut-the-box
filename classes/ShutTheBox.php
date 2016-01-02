<?php

/*
 * The class that describes the Shut the Box game. The game can have any number of tiles but they must be in increments of one
 * Author: Philip Garner
 */

class ShutTheBox{
	public $min = 1;			//The smallest number on the board
	public $max = 9;			//The largest number on the boars
	private $children = null;	//An array of children games, these are populated when a turn is played on this game
	private $valid = true;		//Describes whether this game is valid. Do not access directly, use isValid() which checks the children too
	public $par;				//The parent of this game, all games have parents except the first one which represents the starting point
	public $turn = null;		//The turn taken to change the game from the state of its parent to the current state. All games have turns except the root.
	private $success = false;	//True if there is a path from this game to one where all tiles are knocked down
	private $map = array();		//The map representing the tiles in the game. This only shows changes made in this move. Check parents/children for other moves.
	
	
	/*
	 * Builds a new game. If a parent is supplied then minimums and maximums are inherited from it.
	 */
	public function __construct($par = null, $min = 1, $max = 9){
		$this->par = $par;
		if(is_null($par)){
			$this->min = $min;
			$this->max = $max;				
		}
		else{
			$this->par = $par;
			$this->max = $par->max;
			$this->min = $par->min;
		}

		//Populate the tiles
		for($i=$this->min; $i<=$this->max; $i++){
			$this->map[$i] = true;
		}
	}
	
	/*
	 * Get the numbers in this game as an array
	 */
	public function getNumbers(){
		$output = array();
		for($i=$this->min; $i<=$this->max; $i++){
			$output[] = $i;
		}
		return $output;
	}
	
	public function countValidChildren(){
		if(!is_null($this->children)){
			$output = 0;
			foreach($this->children as $c){
				$output += $c->countValidChildren();
			}
			return $output;
		}
		else if($this->valid){
			return 1;
		}
	}
	
	/*
	 * Checks to see if a given number is available to knock down. Also checks parents.
	 */
	public function isAvailable($check){
		if(array_key_exists($check, $this->map)){
			if($this->map[$check] == false){
				return false;
			}
			else if(is_null($this->par) == false){
				return $this->par->isAvailable($check);
			}
			else{
				return true;
			}
		}
		return false;
	}
	
	/*
	 * Drops a number. Will never return an error but will set the game to invalid if the move is impossible.
	 */
	public function dropNumber($drop){
		if($this->isAvailable($drop)){
			$this->map[$drop] = false;
		}
		else{
			$this->valid = false;
		}
		return $this->valid;
	}
	
	/*
	 * Checks to see if the game is complete. Checks this and parents to make sure all numbers are dropped.
	 */
	public function isComplete(){
		if(!$this->isValid()){
			return false;
		}
		
		$numbers = $this->getNumbers();
		foreach($numbers as $n){
			if($this->isAvailable($n)){
				return false;
			}
		}
		return true;
	}
	
	/*
	 * Returns true if game is valid, false otherwise. A game is considered valid if it or at least one child can make a move.
	 */
	public function isValid(){
		if(!is_null($this->children)){
			foreach($this->children as $c){
				if($c->isValid()){
					return true;
				}
			}
			return false;
		}
		else{
			return $this->valid;
		}
	}
	
	/*
	 * Takes a turn by finding all possible moves from a given dice roll and making one child for each move.
	 */
	public function takeTurn($roll){
		if($this->isValid() && !$this->isParentComplete()){
			//echo "Taking turn: $roll";
			if(is_null($this->children)){
				//If no children then make children and take the turns
				$this->children = array();
				//echo "Roll: $roll";
				$numbers = new Numbers($roll);
				$numbers->spawn();
				$permutations = $numbers->getChildren();
				
				//Loop through all the permutations and take the turns
				foreach($permutations as $p){
					$child = new ShutTheBox($this);
					$child->turn = $p;
					$toDrop = $p->values;
					foreach($toDrop as $d){
						$child->dropNumber($d);
					}
					
					$this->children[] = $child;
											
					if($child->isComplete()){
						$child->setSuccess();
					}
				}
			}
			else{
				//If has children then make them take the turn
				foreach($this->children as $c){
					$c->takeTurn($roll);
				}
			}
		}
		//If not valid then no point in taking the turn.
			
	}
	
	/*
	 * Takes a number of turns until the game is made invalid
	 */
	public function takeTurns($rolls){
		foreach($rolls as $roll){
			$this->takeTurn($roll);
			if(!$this->isValid()){
				break;
			}
		}
	}
	
	/*
	 * Checks if the parent (or parent of parent etc.) is complete.
	 */
	public function isParentComplete(){
		if(is_null($this->par)){
			return $this->getSuccess();
		}
		else{
			return $this->par->isParentComplete();
		}
	}
	
	/*
	 * Sets this game and its parent(s) to be a success
	 */
	public function setSuccess(){
		$this->success = true;
		if(!is_null($this->par)){
			$this->par->setSuccess();
		}
	}
	
	/*
	 * Finds if this game is part of a successful route. If this is part of a successful route it should have no children or have a child marked as successful.
	 */
	public function getSuccess(){
		return $this->success;
	}
	
	/*
	 * A simple means to display this game. Numbers are displayed along with [a] for available or [x] for dropped. e.g. 1 [a] 2 [a] 3 [x] ...
	 */
	public function display(){
		foreach($this->map as $k=>$v){
			echo " $k [";
			if($this->isAvailable($k)){
				echo 'a]';
			}
			else{
				echo 'x]';
			}
		}
		
		echo '<br>';
	}
	
	/*
	 * Displays this game and its successful children. Call this on the root node to show how to complete the game.
	 */
	public function displayComplete(){
		foreach($this->map as $k=>$v){
			echo " $k [";
			if($this->isAvailable($k)){
				echo 'a]';
			}
			else{
				echo 'x]';
			}
		}
		echo '<br>';
		if(!is_null($this->children)){
			foreach($this->children as $c){
				if($c->getSuccess()){
					$c->displayComplete();
				}
			}
		}
	}
	
	/*
	 * Gets the next successful game in the chain. Only call this on a successfully completed game, if not this will return null.
	 */
	public function next(){
		if(!is_null($this->children)){
			foreach($this->children as $c){
				if($c->getSuccess()){
					return $c;
				}
			}
		}
		return null;
	}
	
	
	
	
}
?>