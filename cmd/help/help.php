<?php
namespace myo\cmd\help;
use myo\loader;

class help extends loader
{
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
    print_r($this->args);
  }
}
?>