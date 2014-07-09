<?php
namespace myo\cmd\entity;
use myo\loader;

class entity extends loader
{
const USAGE = "Creates an entity file or generate entities from db with -g

\t\t Usage:

\t\t myo entity <entity name>

\t\t Creates an empty entity file


\t\t myo entity -g \t\t

\t\t Generates entity files from the database

\t\t with no options it will use your config to access the db and

\t\t put the files into the entity folder

\t\t options:

\t\t --db-dsn \t\t The dsn of your db
\t\t --db-host \t\t Database host
\t\t --db-name \t\t Database name, overides --db-dsn
\t\t --db-user \t\t Database username
\t\t --db-pass \t\t Database password
\t\t --entity-path \t\t The folder to put generated files in


";
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
    if(in_array('g',$this->args['flags'])){
      $this->generateEntities();
    }
  }
  public function generateEntities()
  {
    $path = $this->appConfig->application.DIRECTORY_SEPARATOR.'entity';

    if(isset($this->args['options']['db-name'])){
      $host  = isset($this->args['options']['db-host'])?$this->args['options']['db-host']:'localhost';
      $dsn = 'mysql:host='.$host.';dbname='.$this->args['options']['db-name'];
    }else{
      $dsn = isset($this->args['options']['db-dsn'])?$this->args['options']['db-dsn']:$this->appConfig->PHIBER_DB_DSN;
    }

    $user = isset($this->args['options']['db-user'])?$this->args['options']['db-user']:$this->appConfig->PHIBER_DB_USER;
    $pass = isset($this->args['options']['db-pass'])?$this->args['options']['db-pass']:$this->appConfig->PHIBER_DB_PASS;
    if(isset($this->args['options']['entity-path'])){
      $path = $this->args['options']['entity-path'];
      if(!is_dir($path)){
        print PHP_EOL.'Path '.$path.' dosn\'t exixst, do you wan to create it[no]?[yes/no]'.PHP_EOL;
        $io = new \myo\filaments\io;
        $answer = $io->getInput();
        if($answer == 'yes'){
          if(!mkdir($path,0755,true)){
            print 'Could not create directory '.$path.PHP_EOL;
            print 'Aborting!';
          }
        }else{
          return;
        }
      }
    }

    require $this->appConfig->library.DIRECTORY_SEPARATOR.'oosql'.DIRECTORY_SEPARATOR.'oogen.php';
    $gen = new \Phiber\oosql\oogen($dsn, $user, $pass);
    $gen->path = $path;
    $gen->generate();
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