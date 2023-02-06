<?PHP

  require_once "./vendor/autoload.php";

  include "./pinger_exceptions/UnknownEndpointException.php";
  include "./pinger_exceptions/InvalidStatusCodeException.php";

  use GuzzleHttp\Client;

  class APIPinger {
    
    private $endpoints;
    private $allow_unknown_endpoints;

    public function __construct() {
      $this->allow_unknown_endpoints = true;
      $this->endpoints = Array();
    }

    public function allow_unknown_endpoints() {
      $this->allow_unknown_endpoints = true;
    }

    public function disallow_unknown_endpoints() {
      $this->allow_unknown_endpoints = false;
    }

    public final function get_endpoints() {
      return array_keys($this->endpoints);
    }

    public final function enlist_endpoint(String $endpoint, callable $func=NULL, Array $headers=[]) {
      if(!$func) {
        $this->endpoints[$endpoint]["func"] = function($data) {
          return $data;
        };
      } else {
        $this->endpoints[$endpoint]["func"] = $func;
      }
      $this->endpoints[$endpoint]["headers"] = $headers;
      return true;
    }

    public final function unlist_endpoint(String $endpoint) {
      unset($this->endpoints[$endpoint]);
    }

    public final function ping($endpoint, $return_raw_data=false) {
      try {
        if(!isset($this->endpoints[$endpoint])) {
          if(!$this->allow_unknown_endpoints) {
            
            throw new UnknownEndpointException("$endpoint is not enlisted. To overcome this problem, use enlist_endpoint()");
          } else {
            
            $this->enlist_endpoint($endpoint);
            return $this->ping($endpoint);
          }
          
        } else {
          
          $client = new Client(['base_uri' => $endpoint,'timeout'  => 2.0]);
          $response = $client->request('GET', '');
          $data = json_decode($response->getBody(), true);
          $status_code = $response->getStatusCode();
          
          if($status_code == 200) {
            if($data) {
              if($return_raw_data) {
                return $data;
              }
              return $this->endpoints[$endpoint]["func"]($data);
            }
          } else {
            throw new InvalidStatusCodeException("Status code $status_code encountered for endpoint $endpoint");
          }
        }
      } catch(Exception $e) {
        echo $e;
        return false;
      }
    }
  }


?>#