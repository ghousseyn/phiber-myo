<?php
namespace myo\cmd\version;
use myo\loader;
use myo\filaments\colors;
class version extends loader
{
const USAGE = 'Usage description of your extension';
/**
* Execution point of your extension
*@throws Exception
*/
  public function run($args)
  {
    $this->args = $args;
    print colors::colorString(parent::ASCILOGO,array_rand(colors::$foreground_colors));
    print parent::USAGE;
    print colors::colorString(parent::VERSION,'green').PHP_EOL.PHP_EOL;
    //print parent::AUTHOR.PHP_EOL;
  }
}
?>
