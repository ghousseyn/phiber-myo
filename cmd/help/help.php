<?php
namespace myo\cmd\help;
use myo\loader;

class help extends loader
{

  public $requireConfig = false;
const USAGE = "Provides more information about commands.

\t\t Usage:

\t\t myo help <command>\n\n";


/**
* Execution point of your extension
*@throws Exception
*/
  public function run($args)
  {
    $this->args = $args;
    $command = $this->args['commands'][1];
    $path = $this->root.DIRECTORY_SEPARATOR.'cmd'.DIRECTORY_SEPARATOR.$command;
    if(!is_dir($path)){
      print $command.' doesn\'t appear to be a valid command!';
      return;
    }
    $file = $path.DIRECTORY_SEPARATOR.$this->helpFile;
    if(stream_resolve_include_path($file)){
       print file_get_contents($file);
    }
  }
}
?>