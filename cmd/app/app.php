<?php
namespace myo\cmd\app;
use myo\myo;

use myo\loader;

class app extends loader
{
  public $requireConfig = false;

  protected $io,$path,$file,$name;

const USAGE = "Creates a new Phiber application

\t\t Usage:

\t\t myo app <appname>

\t\t Options:

\t\t --app-path \t\t specify the application path


";
/**
* Execution point of your extension
*@throws Exception
*/
  public function run($args)
  {
    $this->args = $args;
    if(!isset($this->args['commands'][1])){
      print PHP_EOL.self::USAGE;
      return;
    }
    $this->name = trim($this->args['commands'][1]);

    $path = '.'.DIRECTORY_SEPARATOR;
    $this->path = realpath($path).DIRECTORY_SEPARATOR;
    if(is_dir($this->path.$this->name)){
       print 'Directory already exists!'.PHP_EOL;
       return;
    }
    $this->io = new \myo\filaments\io;
    $this->file = $this->root.DIRECTORY_SEPARATOR.'filaments'.DIRECTORY_SEPARATOR.'repo'.DIRECTORY_SEPARATOR.'master.zip';

    if(isset($this->args['options']['app-path'])){
      $this->path = rtrim($this->args['options']['app-path'],'/\\').DIRECTORY_SEPARATOR;
    }

    $zip = $this->io->archiveOpen($this->file);

    if(stream_resolve_include_path($this->file) && !in_array('c',$this->args['flags'])){

      print PHP_EOL.'The current version is: '.$zip->getArchiveComment().PHP_EOL;
      print PHP_EOL.'Do you want to check the HEAD revision on Github [no]?(yes/no)';

      $answer = $this->io->getInput();

      if($answer == 'yes'){
        $this->checkGithub($zip);
      }else{
        $this->deploy($zip);
      }

    }else{
      print PHP_EOL.'No archive found!'.PHP_EOL.PHP_EOL.'Downloading one from Github (Ctrl+c to abort)'.PHP_EOL;
      $this->checkGithub($zip);
    }

  }

  protected function checkGithub($zip)
  {
    print PHP_EOL.'Your github username:';
    $username = $this->io->getInput();
    print PHP_EOL.'Password:';
    $password = $this->io->getInput(true);

    $client = new \myo\filaments\github();
    $client->setCredentials($username, $password);
    try {
      $client->setpage();
      $client->setPageSize(1);
      $commits = $client->getCommits('ghousseyn', 'phiber-sample-app');
      if($commits[0]->sha != $zip->getArchiveComment()){
        print PHP_EOL.PHP_EOL.'Fetching revision: '.$commits[0]->sha.PHP_EOL;
        file_put_contents($this->file, fopen('https://github.com/ghousseyn/phiber-sample-app/archive/master.zip', 'r'));
        print PHP_EOL.'Done!'.PHP_EOL;
        $this->deploy($this->io->archiveOpen($this->file));
      }else{
        print PHP_EOL.PHP_EOL.'You seem to have the latest version!'.PHP_EOL;
        $this->deploy($zip);
      }
    }catch(\Exception $e){
      print $e->getMessage();
    }
  }
  protected function deploy($zip)
  {
    $path = $this->path.$this->name;
    print PHP_EOL.'Deploying '.$zip->getArchiveComment().' ...'.PHP_EOL;
    print PHP_EOL.'To: '.$path.PHP_EOL;


      print PHP_EOL.'Unpacking ... '.PHP_EOL;

      $zip->extractTo(str_replace("\\","/",$this->path));

      $zip->close();

      rename($this->path.'phiber-sample-app-master', $path);

      if(!$this->configure()){

        print PHP_EOL.'Done! '.PHP_EOL;

        print PHP_EOL.'Further steps: '.PHP_EOL;

        $msg = PHP_EOL.'Edit the config.php file in the "application" directory.'.PHP_EOL;

      }else{
        $msg = PHP_EOL.'Check the config.php file in the "application" directory for other settings.'.PHP_EOL;
      }

      print PHP_EOL.'Run "composer install" now to install dependencies.'.PHP_EOL;

      print $msg;
  }
  protected function configure()
  {
    print PHP_EOL.'Would you like to configure '.$this->name.' [yes]? (yes/no)'.PHP_EOL;
    $answer = $this->io->getInput();
    if($answer == 'yes' || $answer == chr(24)){

      $path = $this->path.$this->name.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR;
      $confFile = $path.'config.php';

      $config = file_get_contents($confFile);
      print PHP_EOL.'Host [mysql]: ';
      $host = $this->io->getInput();
      print PHP_EOL.'DB Name [mysql]: ';
      $db = $this->io->getInput();

      $dsn = 'mysql:host='.$host.';dbname='.$db;

      print PHP_EOL.'DB User [mysql]: ';
      $user = $this->io->getInput();

      print PHP_EOL.'DB Passwrod [mysql]: ';
      $password = $this->io->getInput(true);

      $config = str_replace('<db-dsn>', $dsn, $config);
      $config = str_replace('<db-user>', $user, $config);
      $config = str_replace('<db-password>', $password, $config);
      $config = str_replace('<application>', rtrim($path,'/\\'), $config);
      $config = str_replace('<library>', $this->path.$this->name.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'phiber'.DIRECTORY_SEPARATOR.'phiber'.DIRECTORY_SEPARATOR.'library', $config);

      if(isset($this->args['options']['phiber-ver'])){
        $version = $this->args['options']['phiber-ver'];
        $json = $this->path.$this->name.DIRECTORY_SEPARATOR.'composer.json';
        $composerJson = file_get_contents($json);
        $composerArr = json_decode($composerJson);
        $composerArr['require']['phiber/phiber'] = $version;
        $composerJson = json_encode($composerArr);
        file_put_contents($json, $composerJson);
      }
      file_put_contents($confFile, $config);
      print PHP_EOL;
      return true;
    }else{
      return false;
    }
  }
}
?>