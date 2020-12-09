<?php

class Problem {

  public $source_file;
	public $text;
	public $random;
	public $solutions;

	public $params;
	public $answers;

	public $count_of_params;
	public $count_of_answers;
	
	public function __construct($filename){
    $this->source_file = $filename;
		$file = file_get_contents($filename);
		$input = explode("\n\n", $file);	
		$this->text = $input[0];
    $input[1] = preg_replace('/#([A-Za-z0-9_]*)/', '$this->params["$1"]', $input[1]); 
		$this->random = explode("\n", $input[1]);	
	  $this->count_of_params = sizeof($this->random);
		for($k=2; $k<sizeof($input); $k++){
      $input[$k] = preg_replace('/#([A-Za-z0-9_]*)/', '$this->params["$1"]', $input[$k]); 
      $input[$k] = preg_replace("/\((.*?)\)\^\((.*?)\)/", "pow($1,$2)", $input[$k]); 
      $input[$k] = preg_replace("/SIN\((.*?)\)/", "sin(($1)*M_PI/180)", $input[$k]); 
      $input[$k] = preg_replace("/COS\((.*?)\)/", "cos(($1)*M_PI/180)", $input[$k]); 
      $this->solutions[$k-2] = $input[$k];
		}
		$this->count_of_answers = $k-2;
	}

  public function gift($title) {
    $output  = "::$title::[html]\n";
    $output .= $this->text;
    $output .= "\n{\n=".$this->answers[1]."\n";
    for($i=2;$i<=$this->count_of_answers;$i++)
      $output .= "~" . $this->answers[$i] . "\n";
    $output .= "}\n";
    $output = preg_replace("/\$\((.*?)\)\$/", "/( $1 /)", $output); 
    $output = preg_replace("/=/", "\=", $output); 
    $output = preg_replace("/{/", "\{", $output); 
    $output = preg_replace("/}/", "\}", $output); 
    //$output = preg_replace("/^\/", "", $output); 
    return $output;
  }

	private function solve() {
		for($k=0; $k<$this->count_of_answers; $k++) 
			$this->answers[$k] = eval("return ".$this->solutions[$k]." ;");
	}

	public function evalParams() {
		for($k=0; $k<$this->count_of_params; $k++) {
      try{

          eval($this->random[$k].";");

      }catch(ParseError $p){
        
        echo "ERROR IN FILE: $this->source_file\n";
      }
    }
	}

	public function randomProblem() {
		$this->evalParams();
		$this->solve();
	}

	public function getText() {
		$result = $this->text;
		foreach ($this->params as $key => $value)
     if(!is_array($value))
      $result = preg_replace("/#$key/", $value, $result); 
		return $result;	
	}

	public function getProblem() {
    $result = $this->getText();
		$result .= "\\newline\nОтвет: ";
		for($i=0; $i<$this->count_of_answers; $i++) {
			if ($i!=0)
				$result .= ", ";
			$result .= $this->answers[$i];
		}
		return $result . "\n";	
	}

  public function GetParams() {
    $output = "";
    foreach ($this->params as $key => $value)
     if(!is_array($value))
      $output .= "$key $value\n";
    return $output;
  }
}
?>
