<?php
/**
 *
 * A very light version of GithubClient
 * https://github.com/tan-tan-kanarek/github-php-client
 *
 */
namespace myo\filaments;

class github
{

  protected $url = 'https://api.github.com';
  protected $uploadUrl = 'https://uploads.github.com';

  protected $debug = false;
  protected $username = null;
  protected $password = null;
  protected $timeout = 240;
  protected $rateLimit = 0;
  protected $rateLimitRemaining = 0;

  protected $page = null;
  protected $pageSize = 100;

  protected $lastPage = null;
  protected $lastUrl = null;
  protected $lastMethod = null;
  protected $lastData = null;
  protected $lastReturnType = null;
  protected $lastReturnIsArray = null;
  protected $lastExpectedHttpCode = null;
  protected $pageData = array();

  public function setCredentials($username, $password)
  {
    $this->username = $username;
    $this->password = $password;
  }

  public function getCommits($owner, $repo, $sha = null, $path = null, $author = null, $since = null, $until = null)
  {
    $data = array();
    if(!is_null($sha))
      $data['sha'] = $sha;
    if(!is_null($path))
      $data['path'] = $path;
    if(!is_null($author))
      $data['author'] = $author;
    if(!is_null($since))
      $data['since'] = $since;
    if(!is_null($until))
      $data['until'] = $until;

    return $this->request("/repos/$owner/$repo/commits", 'GET', $data, 200, 'GitHubCommit', true);
  }

  /**
   * do a github request and return array
   *
   * @param $url string
   * @param $method string
   *          GET POST PUT DELETE etc...
   * @param $data array
   * @return array
   */
  protected function doRequest($url, $method, $data, $contentType = null, $filePath = null)
  {
    if($method == 'FILE')
      $url = $this->uploadUrl . $url;
    else
      $url = $this->url . $url;

    if($this->debug){
      echo "URL: $url\n";
      echo "Data: " . print_r($data, true) . "\n";
    }

    $c = curl_init();

    curl_setopt($c, CURLOPT_VERBOSE, $this->debug);

    if($this->username && $this->password){
      curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($c, CURLOPT_USERPWD, "$this->username:$this->password");
    }

    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, "tan-tan.github-api");
    curl_setopt($c, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($c, CURLOPT_HEADER, true);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

    if(is_array($data)){
      foreach($data as $key => $value){
        if(is_bool($value))
          $data[$key] = $value ? 'true' : 'false';
      }
    }

    switch($method){
      case 'FILE':
        curl_setopt($c, CURLOPT_HTTPHEADER, array("Content-type: $contentType"));
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, file_get_contents($filePath));

        if(count($data))
          $url .= '?' . http_build_query($data);
        break;

      case 'GET':
        curl_setopt($c, CURLOPT_HTTPGET, true);
        if(count($data))
          $url .= '?' . http_build_query($data);
        break;

      case 'POST':
        curl_setopt($c, CURLOPT_POST, true);
        if(count($data))
          curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        break;

      case 'PUT':
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($c, CURLOPT_PUT, true);

        $headers = array('X-HTTP-Method-Override: PUT', 'Content-type: application/x-www-form-urlencoded');

        if(count($data)){
          $content = json_encode($data, JSON_FORCE_OBJECT);

          $fileName = tempnam(sys_get_temp_dir(), 'gitPut');
          file_put_contents($fileName, $content);

          $f = fopen($fileName, 'rb');
          curl_setopt($c, CURLOPT_INFILE, $f);
          curl_setopt($c, CURLOPT_INFILESIZE, strlen($content));
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        break;

      case 'PATCH':
      case 'DELETE':
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);

        if($data){
          curl_setopt($c, CURLOPT_POST, true);
          curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        }
        break;
    }

    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($c);

    curl_close($c);

    if($this->debug)
      echo "Response:\n$response\n";

    return $response;
  }

  protected function requestLast(array $data)
  {
    return $this->request($this->lastUrl, $this->lastMethod, $data, $this->lastExpectedHttpCode, $this->lastReturnType, $this->lastReturnIsArray);
  }

  public function request($url, $method, $data, $expectedHttpCode, $returnType, $isArray = false)
  {
    $this->lastUrl = $url;
    $this->lastMethod = $method;
    $this->lastData = $data;
    $this->lastExpectedHttpCode = $expectedHttpCode;
    $this->lastReturnIsArray = $isArray;
    $this->lastReturnType = $returnType;

    if(is_array($data) && ! is_null($this->page)){
      if(! is_numeric($this->page) || $this->page <= 0){
        $this->resetPage();
        throw new \Exception("Page must be positive value");
      }

      if(! is_numeric($this->pageSize) || $this->pageSize <= 0 || $this->pageSize > 100){
        $this->resetPage();
        throw new \Exception("Page size must be positive value, maximum value is 100");
      }

      $data['page'] = $this->page;
      $data['per_page'] = $this->pageSize;

      $this->resetPage();
    }

    $response = $this->doRequest($url, $method, $data);

    return $this->parseResponse($url, $response, $returnType, $expectedHttpCode, $isArray);
  }

  public function parseResponse($url, $response, $returnType, $expectedHttpCode, $isArray = false)
  {
    // parse response
    $header = false;
    $content = array();
    $status = 200;

    foreach(explode("\r\n", $response) as $line){
      if(strpos($line, 'HTTP/1.1') === 0){
        $lineParts = explode(' ', $line);
        $status = intval($lineParts[1]);
        $header = true;
      }else if($line == ''){
        $header = false;
      }else if($header){
        $line = explode(': ', $line);
        switch($line[0]){
          case 'Status':
            $status = intval(substr($line[1], 0, 3));
            break;

          case 'X-RateLimit-Limit':
            $this->rateLimit = intval($line[1]);
            break;

          case 'X-RateLimit-Remaining':
            $this->rateLimitRemaining = intval($line[1]);
            break;

          case 'Link':
            $matches = null;
            if(preg_match_all('/<https:\/\/api\.github\.com\/[^?]+\?([^>]+)>; rel="([^"]+)"/', $line[1], $matches)){
              foreach($matches[2] as $index => $page){
                $this->pageData[$page] = array();
                $requestParts = explode('&', $matches[1][$index]);
                foreach($requestParts as $requestPart){
                  list($field, $value) = explode('=', $requestPart, 2);
                  $this->pageData[$page][$field] = $value;
                }
              }
            }
            break;
        }
      }else{
        $content[] = $line;
      }
    }

    if($status !== $expectedHttpCode)
      throw new \Exception("Expected status [$expectedHttpCode], actual status [$status], URL [$url]");

    if($returnType == 'string')
      return implode("\n", $content);


      $response = json_decode(implode("\n", $content));

        return $response;

  }

  public function setDebug($debug)
  {
    $this->debug = $debug;
  }

  public function setTimeout($timeout)
  {
    $this->timeout = $timeout;
  }

  public function getRateLimit()
  {
    return $this->rateLimit;
  }

  public function getRateLimitRemaining()
  {
    return $this->rateLimitRemaining;
  }

  protected function resetPage()
  {
    $this->lastPage = $this->page;
    $this->page = null;
  }

  public function setPage($page = 1)
  {
    $this->page = $page;
  }

  public function setPageSize($pageSize)
  {
    $this->pageSize = $pageSize;
  }

  public function getLastPage()
  {
    if(!isset($this->pageData['last']))
      throw new \Exception("Last page not defined");

    if(isset($this->pageData['last']['page']))
      $this->page = $this->pageData['last']['page'];

    return $this->requestLast($this->pageData['last']);
  }

  public function getFirstPage()
  {
    if(isset($this->pageData['first']))
    {
      if(isset($this->pageData['first']['page']))
        $this->page = $this->pageData['first']['page'];

      return $this->requestLast($this->pageData['first']);
    }

    $this->page = 1;
    return $this->requestLast($this->lastData);
  }

  public function getNextPage()
  {
    if(isset($this->pageData['next']))
    {
      if(isset($this->pageData['next']['page']))
        $this->page = $this->pageData['next']['page'];

      return $this->requestLast($this->pageData['next']);
    }

    if(is_null($this->page))
      throw new \Exception("Page not defined");

    $this->page = $this->lastPage + 1;
    return $this->requestLast($this->lastData);
  }

  public function getPreviousPage()
  {
    if(isset($this->pageData['prev']))
    {
      if(isset($this->pageData['prev']['page']))
        $this->page = $this->pageData['prev']['page'];

      return $this->requestLast($this->pageData['prev']);
    }

    if(is_null($this->page))
      throw new \Exception("Page not defined");

    $this->page = $this->lastPage - 1;
    return $this->requestLast($this->lastData);
  }

}

?>