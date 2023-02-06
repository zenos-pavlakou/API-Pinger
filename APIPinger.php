<?PHP

  include "./pinger_exceptions/UnknownEndpointException.php";
  include "./pinger_exceptions/InvalidStatusCodeException.php";

  class APIPinger {
    
    private $endpoints_to_funcs;
    private $allow_unknown_endpoints;

    public function __construct() {
      $this->allow_unknown_endpoints = true;
      $this->endpoints_to_funcs = Array();
    }

    public function allow_unknown_endpoints() {
      $this->allow_unknown_endpoints = true;
    }

    public function disallow_unknown_endpoints() {
      $this->allow_unknown_endpoints = false;
    }

    public final function get_endpoints() {
      return array_keys($this->endpoints_to_funcs);
    }

    public final function enlist_endpoint(String $endpoint, callable $func=NULL) {
      if(!$func) {
        $this->endpoints_to_funcs[$endpoint] = function($data) {
          return $data;
        };
      } else {
        $this->endpoints_to_funcs[$endpoint] = $func;
      }
      return true;
    }

    public final function unlist_endpoint(String $endpoint) {
      unset($this->endpoints_to_funcs[$endpoint]);
    }

    public final function ping($endpoint, $return_raw_data=false) {
      try {
        if(!isset($this->endpoints_to_funcs[$endpoint])) {
          if(!$this->allow_unknown_endpoints) {
            
            throw new UnknownEndpointException("$endpoint is not enlisted. To overcome this problem, use enlist_endpoint()");
          } else {
            
            $this->enlist_endpoint($endpoint);
            return $this->ping($endpoint);
          }
          
        } else {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $endpoint);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $data = json_decode(curl_exec($ch), true);
          $status_code = curl_getinfo($ch)["http_code"];
          curl_close($ch);
          if($status_code == 200) {
            if($data) {
              if($return_raw_data) {
                return $data;
              }
              return $this->endpoints_to_funcs[$endpoint]($data);
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


?>