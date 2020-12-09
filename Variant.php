<?php
$preamble_4_questions = <<<END
\documentclass[a5paper,12pt]{extreport}
\usepackage[utf8]{inputenc}
\usepackage[russian]{babel}

\usepackage{amsmath}
\\everymath{\\displaystyle}
\usepackage{tikz,pgfplots}

\usepackage[left=0cm,right=0.5cm,top=0.5cm,bottom=0.5cm]{geometry}
\usepackage{multicol}

\pagestyle{empty}
\\renewcommand{\labelenumii}{\asbuk{enumii})}
\\renewcommand{\\theenumii}{\asbuk{enumii}}
\\renewcommand{\\v}[1]{\overrightarrow{#1}}
\\begin{document}
END;
$preamble_4_answers = <<<END
\documentclass[a4paper]{extreport}
\usepackage[utf8]{inputenc}
\usepackage[russian]{babel}

\usepackage{amsmath,amssymb}
\usepackage{longtable}
\\everymath{\\displaystyle}

\usepackage[left=0cm,right=0.5cm,top=0.5cm,bottom=0.5cm,landscape]{geometry}
\usepackage{multicol}

\pagestyle{empty}
\\renewcommand{\labelenumii}{\asbuk{enumii})}
\\renewcommand{\\theenumii}{\asbuk{enumii}}
\\renewcommand{\\v}[1]{\overrightarrow{#1}}

END;

/**
 * Создает набор вопросов с ответами
 *
 * Данный класс является базовым для построения варианта:
 *  * включения задач
 *  * перемешивание задача
 *  * распечатка вариантов
 */
class Variant {

  private $variant;
  private $files;
	private $problems;
  private $shuffle = FALSE;
	private $header;
  private $theme;
  private $title;
  public $load_dir = ".";
  public $save_name_format = "%02d.tex";

/* Конструктор класса Variant 
 *
 * Создавать можно экземпляр класса указывая заголовок и тему
 *
 * @return resource
 **/
  public function __construct($title,$theme){
    $this->Title($title);
    $this->Theme($theme);
  }
  public function Title($title){
    $this->title = $title;
  }
  public function Theme($theme){
    $this->theme = $theme;
  }
  /* @return void */
  public function Shuffle(){
    $this->shuffle = TRUE;
  }

  // в следующих функциях нужно доопределить $load_dir
  public function Add(...$args){
    foreach ($args as $key => $value) 
      $args[$key] = "$this->load_dir/$value";
    $this->files[] = $args;
  }
  public function AddList(...$args){
    for($i=0;$i<sizeof($args);$i++)
      /* if(is_array($args[$i])) */
      /*   $this->files[] = $args[$i]; */
      /* else */
        $this->files[] = array( "0" => "$this->load_dir/$args[$i]" );
  }
  public function AddVar($dir = "." ){
    $dirs = scandir("$this->load_dir/$dir");
    unset($dirs[0], $dirs[1]);
    for($i=2;$i<=sizeof($dirs)+1;$i++)
        $this->files[] = array( "0" => "$this->load_dir/$dir/$dirs[$i]" );
  }
  public function Gen() {
    $this->problems = array();
    for ($i=0;$i<sizeof($this->files);$i++){
      $filename = $this->FileFromTree($this->files[$i]);
      $this->problems[] = new Problem($filename);
    }
    if($this->shuffle)
      shuffle($this->problems);
  }
  public function GenList($count) {
    $table = "";
    for($i=1;$i<=$count;$i++){
      $this->New($i);
      $filename = sprintf($this->save_name_format, $i);
      $this->Print($filename);
      $table .= $this->answers($i);
    }
    $this->printAnswers2($table);
  }

/* рекурсивная функция выбирающая случайным образом файл из переденного массива, осуществляя проход по дирректориям */
  public function FileFromTree($tree){
    // выбираем случайный элемент из переданного массива
    $i = array_rand($tree);
    $filename = $tree[$i]; 
    // проверяем является ли переданный файл дирректорией
    if(is_dir($filename)){
      // прибавляем относительный путь к каждому файлу директории 
      $new_tree = scandir($filename);
      foreach ($new_tree as $key => $value) {
        $str = str_split($value);
        if($str[0]=='.')
          unset($new_tree[$key]);
        else
          $new_tree[$key] = "$filename/$value"; 
      }
      // делаем рекурсивный вызов функции
      return $this->FileFromTree($new_tree);
    }
    else
      // возвращаем случайное имя файла
      return $filename;
  }
	public function randomProblem() {
		for($i=0;$i<sizeof($this->problems);$i++)
			$this->problems[$i]->randomProblem();
	}
	public function new_by_page($variant) {
		$this->header = "\\begin{center}\n$this->title:\\linebreak\n{\\bf <<$this->theme>>}.\n\n";
		$result = $this->header."{\it Вариант {$variant}.}\n\\end{center}\n\begin{enumerate}\n";
		for($i=0;$i<sizeof($this->problems);$i++){
			$result .= "\item\n".$this->problems[$i]->getText()."\n";
		}
		$result .= "\\end{enumerate}\n";
		$result .= "\begin{enumerate}\n";
		for($i=0;$i<sizeof($this->problems);$i++){
			$result .= "\\newpage\item\n".$this->problems[$i]->getText()."\n";
		}
		return $result . "\\end{enumerate}\n";
	}
	public function New($var_number) {
    $this->Gen();
    $this->randomProblem();
    $this->SaveVar($var_number);
		$this->header = "\\begin{center}\n$this->title:\\linebreak\n{\\bf <<$this->theme>>}.\n\n";
		$result = $this->header."{\it Вариант {$var_number}.}\n\\end{center}\n\begin{enumerate}\n";
		for($i=0;$i<sizeof($this->problems);$i++){
			$result .= "\item\n".$this->problems[$i]->getText()."\n";
		}
		$this->variant = $result . "\\end{enumerate}\n";
	}
	public function answers($variant) {
		$result = "$variant";
		for($i=0;$i<sizeof($this->problems);$i++)
			for($k=0;$k<$this->problems[$i]->count_of_answers;$k++)
					$result .= " & \$".$this->problems[$i]->answers[$k]."\$";
		return $result." \\\\\n";
	}
	public function answersDebug() {
		$result = "Ответы:\n";
		for($i=0;$i<sizeof($this->problems);$i++)
			for($k=1;$k<=$this->problems[$i]->count_of_answers;$k++)
				$result .= $this->problems[$i]->answers[$k]."\n";
		return $result;
	}
	public function Print($filename) {
		global $preamble_4_questions;
		$result = $preamble_4_questions;
		$result .= $this->variant;
		$result .= "\n\\end{document}";
		file_put_contents($filename, $result);
	}
  public function printAnswerHeader() {
    $list = ["a","б","в","г"];
		$title = "\\begin{document}\n\\begin{longtable}{|c|";
    $header = "Вариант";
    $kmax = 1;
    for($i=0;$i<sizeof($this->problems);$i++){
      for($j=0;$j<$this->problems[$i]->count_of_answers;$j++){
        $kmax++;
        if($this->problems[$i]->count_of_answers == 1)
          $header .= " & " . ($i+1);
        else
          $header .= " & " . ($i+1) . $list[$j];
      };
    }
    $header .= " \\\\\n \\hline\\endhead\n";
    for($k=0;$k<$kmax;$k++)
      $title .= "l|";
		$title .= "}\n\hline\n";
    return $title . $header; 
  }
	public function printAnswers2($text) {
    global $preamble_4_answers;
    $result = $preamble_4_answers;
    $result .= $this->printAnswerHeader();
		$result .= $text."\\hline\n\\end{longtable}\n\\end{document}";
		file_put_contents("tex/answers.tex",$result);
  }
  public function LoadDir($dir){
    $this->load_dir = $dir;
  }
  public function SaveNameFormat($pattern){
    $this->save_name_format = $pattern;
  }

  public function SaveVar($var_number) {
    $result = "";
    for($i=0;$i<sizeof($this->problems);$i++)
      $result .= $this->problems[$i]->GetParams() . "\n";
    $filename = date("Y-m-d_H:i:s_".$var_number);
    file_put_contents("var/$filename.txt", $result);
  }
}
?>
