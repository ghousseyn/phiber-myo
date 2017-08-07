<?php
namespace myo\filaments;

class io
{
  public function getInput($hide = false){

    if($hide){
      if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        system('stty -echo');
        $psw = fgets(STDIN);
        system('stty echo');
      } else{
        $psw = `input.exe`;
      }

      return rtrim($psw, PHP_EOL);

    }else{
      $handle = fopen ("php://stdin","r");
      $line = fgets($handle);
      if(strlen(trim($line)) >0){
        return trim($line);
      }else{
        return chr(24);
      }
    }

  }
  public function chkFor($protocol)
  {

    $syswr = stream_get_wrappers();

    $syswr[] = extension_loaded  ('openssl')?'ssl':null;

    return in_array($protocol,$syswr);
  }
  public function archiveOpen($archive)
  {
    $zip = new \ZipArchive;
    $res = $zip->open($archive, \ZipArchive::CREATE);

    if ( $res === TRUE) {
      //CODE GOES HERE
      return $zip;

    } else {
      switch($res){
        case \ZipArchive::ER_EXISTS:
          $ErrMsg = "File already exists.";
          break;

        case \ZipArchive::ER_INCONS:
          $ErrMsg = "Zip archive inconsistent.";
          break;

        case \ZipArchive::ER_MEMORY:
          $ErrMsg = "Malloc failure.";
          break;

        case \ZipArchive::ER_NOENT:
          $ErrMsg = "No such file.";
          break;

        case \ZipArchive::ER_NOZIP:
          $ErrMsg = "Not a zip archive.";
          break;

        case \ZipArchive::ER_OPEN:
          $ErrMsg = "Can't open file.";
          break;

        case \ZipArchive::ER_READ:
          $ErrMsg = "Read error.";
          break;

        case \ZipArchive::ER_SEEK:
          $ErrMsg = "Seek error.";
          break;

        default:
          $ErrMsg = "Unknow error";
          break;


      }
      die( 'ZipArchive Error: ' . $ErrMsg);
    }
  }
  public function arguments ( $args )
  {
    array_shift( $args );
    $endofoptions = false;

    $ret = array
    (
        'commands' => array(),
        'options' => array(),
        'flags'    => array(),
        'arguments' => array(),
    );

    while ( $arg = array_shift($args) )
    {

      // if we have reached end of options,
      //we cast all remaining argvs as arguments
      if ($endofoptions)
      {
        $ret['arguments'][] = $arg;
        continue;
      }

      // Is it a command? (prefixed with --)
      if ( substr( $arg, 0, 2 ) === '--' )
      {

        // is it the end of options flag?
        if (!isset ($arg[3]))
        {
          $endofoptions = true;; // end of options;
          continue;
        }

        $value = "";
        $com   = substr( $arg, 2 );

        // is it the syntax '--option=argument'?
        if (strpos($com,'='))
          list($com,$value) = explode("=",$com,2);

        // is the option not followed by another option but by arguments
        elseif (isset($args[0]) && strpos($args[0],'-') !== 0)
        {
          while (isset($args[0]) && strpos($args[0],'-') !== 0)
            $value .= array_shift($args).' ';
          $value = rtrim($value,' ');
        }

        $ret['options'][$com] = !empty($value) ? $value : true;
        continue;

      }

      // Is it a flag or a serial of flags? (prefixed with -)
      if ( substr( $arg, 0, 1 ) === '-' )
      {
        for ($i = 1; isset($arg[$i]) ; $i++)
          $ret['flags'][] = $arg[$i];
        continue;
      }

      // finally, it is not option, nor flag, nor argument
      $ret['commands'][] = $arg;
      continue;
    }

    if (!count($ret['options']) && !count($ret['flags']))
    {
      //$ret['arguments'] = array_merge($ret['commands'], $ret['arguments']);
    //  $ret['commands'] = array();
    }
    return $ret;
  }

}

?>