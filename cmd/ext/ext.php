<?php
namespace myo\cmd\ext;

use myo\loader;

class ext extends loader
{
  public $requireConfig = false;
  const USAGE = "Creates myo extensions.

  \t\t Usage:

  \t\t myo ext <extension name>

  \t\t The new extension will be used as a command:

  \t\t myo <extension name>\n\n";

  public function run($args)
  {
    array_shift($args['commands']);
    if(isset($args['commands'][0]) && strpos($args['commands'][0], " ") === false){
      $ext = strtolower($args['commands'][0]);
      $dir = $this->root.DIRECTORY_SEPARATOR.'cmd'.DIRECTORY_SEPARATOR.$ext;
      $path = $dir.DIRECTORY_SEPARATOR.$ext.'.php';
      $help = $dir.DIRECTORY_SEPARATOR.$this->helpFile;

      if(is_dir($dir)){
        throw new \Exception("Extension $ext already exists!");
      }
      $template = $this->template();
      $code = str_replace('extname', $ext, $template);
      if(mkdir($dir,0755)){
        chmod($dir, 0755);
      }else{
        throw new \Exception('Could not create folder for extension '.$ext);
      }

      file_put_contents($path, $code);
      file_put_contents($help, 'No more information is available at this time.');

      print 'Extension '.$ext.' was created successfully.';
    }else{
      print 'Please specify the name of the extension.'.PHP_EOL.'Make sure there are no spaces in the extension name.';
    }
  }
  protected function template()
  {
    $temp  = <<<'EOT'
<?php
namespace myo\cmd\extname;
use myo\loader;

class extname extends loader
{
const USAGE = 'Usage description of your extension';
public $requireConfig = false; // Change this to true if you need config loaded
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
EOT;

    return $temp;
  }
}

?>