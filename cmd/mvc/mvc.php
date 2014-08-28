<?php
namespace myo\cmd\mvc;
use myo\loader;

class mvc extends loader
{
  const USAGE = "Creates different parts of the MVC layout.

  \t\t mvc <flag> [option]

  \t\t Options:

  \t\t --module <module name>

  \t\t Creates a module and defaults to module 'default'


  \t\t --controller <controller name>

  \t\t Creates a controller and defaults to 'index'


  \t\t --model <model name>

  \t\t Creates a model


  \t\t --action <action name>

  \t\t Creates an action for a given controller

  ";

  public $module = 'default';
  public $controller = 'index';
  public $model,$action;
  public $requireConfig = true;
  public function run($args)
  {
    $this->args = $args;

    if(!$this->interactive){
      if(isset($this->args['options']['module']) ||
         isset($this->args['options']['controller']) ||
         isset($this->args['options']['model']) ||
         isset($this->args['options']['action'])){

        $this->module = isset($this->args['options']['module'])?$this->module($this->args['options']['module']):$this->module($this->module);
        $this->controller = isset($this->args['options']['controller'])?$this->controller($this->args['options']['controller']):$this->controller($this->controller);
        $this->model = isset($this->args['options']['model'])?$this->model($this->args['options']['model']):'';
        $this->action = isset($this->args['options']['action'])?$this->action($this->args['options']['action']):'';


      }

    }
  }
  public function module($module)
  {
    $path = $this->appConfig->application.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$module;
    $views = $path.DIRECTORY_SEPARATOR.'views';
    if(!is_dir($path)){
      mkdir($views, 0755,true);
      chmod($path, 0755);
      chmod($views, 0755);
      print PHP_EOL.'Module '.$module.' created successfully';
    }
    return $module;
  }
  public function controller($controller)
  {
    $temp = <<<'EOT'
<?php
class controllername extends Phiber\controller
{
  public function mainaction()
  {
    /* Your main action according to your config */
  }
}
EOT;

    $viewTemp = <<<'EOT'
<strong>This is the main view for controllername</strong>
EOT;

    $dir = $this->appConfig->application.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->module.DIRECTORY_SEPARATOR;
    $path = $dir.$controller.'.php';
    $viewPath = $dir.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$controller;
    if(!is_dir($viewPath)){
      mkdir($viewPath,0755);
      chmod($viewPath, 0755);
    }
    if(!stream_resolve_include_path($path)){
      $code = str_replace('controllername', $controller, $temp);
      $code = str_replace('mainaction', $this->appConfig->PHIBER_CONTROLLER_DEFAULT_METHOD, $code);

      $view = str_replace('controllername', $controller, $viewTemp);
      file_put_contents($path, $code);
      file_put_contents($viewPath.DIRECTORY_SEPARATOR.$this->appConfig->PHIBER_CONTROLLER_DEFAULT_METHOD.'.php', $view);
      print PHP_EOL.'Controller '.$controller.' created successfully with default action and view '.$this->appConfig->PHIBER_CONTROLLER_DEFAULT_METHOD;
    }

    return $controller;
  }

  public function model($model)
  {
    if(empty($model)){
      return;
    }
    $temp = <<<'EOT'
<?php
namespace model;

use Phiber\model;

class modelname extends model
{

}

EOT;
    $path = $this->appConfig->application.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR.$model.'.php';
    if(!stream_resolve_include_path($path)){
      $code = str_replace('modelname', $model, $temp);
      file_put_contents($path, $code);
      print PHP_EOL.'Model '.$model.' created successfully';
    }else{
      print 'Model '.$model.' already exists!';
    }
    return $model;
  }

  public function action($action)
  {
   $temp = <<<'EOT'
  public function actionname()
  {
   /* Action code here */
  }
}
EOT;
   $viewTemp = <<<'EOT'
<strong>This is the view for actionname</strong>
EOT;
    $viewPath = $this->appConfig->application.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->module.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$this->controller.DIRECTORY_SEPARATOR.$action.'.php';
    $path = $this->appConfig->application.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->module.DIRECTORY_SEPARATOR.$this->controller.'.php';
    if(!stream_resolve_include_path($path)){
      print 'Could not find the controller!'.PHP_EOL.'Creating a default '.$this->appConfig->PHIBER_CONTROLLER_DEFAULT.' controller...';
      $this->controller($this->appConfig->PHIBER_CONTROLLER_DEFAULT);
    }
    $code = file_get_contents($path);
    if(strpos($code, $action) !== false){
      print PHP_EOL.'Action '.$action.' already exists!';
      return $action;
    }
    $code = rtrim($code,'}');
    $code .= str_replace('actionname', $action, $temp);
   $viewTemp = str_replace('actionname',$action,$viewTemp);
    file_put_contents($path, $code);
    file_put_contents($viewPath, $viewTemp);
    print PHP_EOL.'Action '.$action.' created successfully';
    return $action;
  }

}

?>