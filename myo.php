<?php
namespace myo;
class myo extends loader
{
  private $log = array();

  public function __construct()
  {
    spl_autoload_register(array($this, 'autoload'));
  }
  public function start($arg)
  {
    if (PHP_SAPI !== 'cli'){
      return;
    }

    $io = new filaments\io;

    $this->args = $io->arguments($arg);

    $this->route();
  }
  protected function route()
  {
    $Ns = $this->args['commands'];
    if(count($Ns) == 0){
      $this->usage();
      return;
    }
    if(!in_array('i',$this->args['flags'])){
      $this->args['options'] = array_map('strtolower',$this->args['options']);
    }

    $path = 'cmd'.DIRECTORY_SEPARATOR.$Ns[0].DIRECTORY_SEPARATOR.$Ns[0].'.php';

    if(file_exists($path)){
      $cmd =  'myo\\cmd'.'\\'.$Ns[0].'\\'.$Ns[0];
      $cmdObj = new $cmd;
      if($cmdObj->requireConfig){
        if(array_key_exists('conf-path',$this->args['options'])){
          $confPath = $this->args['options']['conf-path'];
        }else{
          $confPath = $this->args['options']['conf-file'];
        }

        if(!file_exists($confPath)){
          print PHP_EOL.'Can\'t find config.php!'.PHP_EOL.'Please cd to your application\'s folder and try again'.PHP_EOL.'You can specify the path using the conf-path option:'.PHP_EOL.PHP_EOL.' myo <command> --conf-path /path/to/config.php'.PHP_EOL;
          return;
        }

        require_once $confPath;
        $cmdObj->appConfig = \Phiber\config::getInstance();

      }
      try{
        $return = $cmdObj->run($this->args);
        $this->log[][$Ns[0]] = $return;
      }catch (\Exception $e){
        print $e->getMessage();
      }

    }else{
      $this->usage();
      return;
    }
  }
  protected function checkConfig()
  {

  }

}


