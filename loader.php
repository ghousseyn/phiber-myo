<?php
namespace myo;

abstract class loader
{
  const ASCILOGO = '
  _ __ ___  _   _  ___
 | \'_ ` _ \| | | |/ _ \
 | | | | | | |_| | (_) |
 |_| |_| |_|\__, |\___/
             __/ |
            |___/
';
  const USAGE = '                    Phiber\'s Command Line Tool ';
  const AUTHOR ='                  Author: Housseyn Guettaf <ghoucine@gmail.com>';
  const VERSION = 'v0.6.5';

  protected $root = __dir__;
  protected $requireConfig = false;
  protected $appConfig;
  protected $args = array(
                       'commands' => array(),
                       'options' => array(),
                       'flags'    => array(),
                       'arguments' => array(),
                  );
  protected $interactive = false;
  protected $helpFile = 'HELP';

  public function usage()
  {
    $usage = PHP_EOL.self::ASCILOGO.self::USAGE.self::VERSION.PHP_EOL.self::AUTHOR.PHP_EOL;
    $usage .= PHP_EOL.' Usage:'.PHP_EOL.PHP_EOL;
    $usage .= ' myo <comand> <flag> [[--option1 value][--option2 = value]...] -- arg1 arg2 ...'.PHP_EOL.PHP_EOL.PHP_EOL;
    $usage .= ' Flags:'.PHP_EOL.PHP_EOL;
    $usage .= " -i\tPreserve case otherwise files will always be created in lowercase".PHP_EOL.PHP_EOL;
    $usage .= " -g\tGenerate entity files when used with myo entity".PHP_EOL.PHP_EOL;
    $usage .= PHP_EOL.PHP_EOL.' Options:'.PHP_EOL.PHP_EOL;
    $path = __dir__.DIRECTORY_SEPARATOR.'cmd';
    $list = scandir($path);

    foreach($list as $ext){
      if($ext != '.' && $ext != '..' ){
        $extFQN = 'myo\\cmd\\'.$ext.'\\'.$ext;
        $usage .= ' '.$ext."\t\t".$extFQN::USAGE.PHP_EOL;
      }
    }
    print $usage;
  }
  public function autoload($className)
  {
    $className = str_replace('myo\\', '', $className);
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
      $namespace = substr($className, 0, $lastNsPos);
      $className = substr($className, $lastNsPos + 1);
      $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    require $fileName;
  }
}

?>
