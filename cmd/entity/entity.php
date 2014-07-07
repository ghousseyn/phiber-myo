<?php
namespace myo\cmd\entity;
use myo\loader;

class entity extends loader
{
const USAGE = "Creates an entity file. The entity represents a table name.

\t\t Usage:

\t\t myo entity <entityname>\n\n";
/**
* Execution point of your extension
*@throws Exception
*/
  public function run($args)
  {
    $this->args = $args;
    if(isset($this->args['commands'][1])){
      $this->entity($this->args['commands'][1]);
    }
  }
  public function entity($entity)
  {
    if(empty($entity)){
      return;
    }
    $temp = <<<'EOT'
<?php
namespace entity;
use Phiber;
class entityname extends Phiber\entity\entity
{

}

EOT;
    $path = $this->appConfig->application.DIRECTORY_SEPARATOR.'entity'.DIRECTORY_SEPARATOR.$entity.'.php';
    if(!stream_resolve_include_path($path)){
      $code = str_replace('entityname', $entity, $temp);
      file_put_contents($path, $code);
      print PHP_EOL.'Entity '.$entity.' created successfully';
    }else{
      print 'Entity '.$entity.' already exists!';
    }
    return $entity;
  }
}
?>