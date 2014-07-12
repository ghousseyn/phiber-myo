<?php
namespace myo\cmd\plugin;
use myo\loader;

class plugin extends loader
{
const USAGE = 'Usage description of your extension';
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