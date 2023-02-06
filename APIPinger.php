<?PHP

  include "./pinger_exceptions/UnknownEndpointException.php";
  include "./pinger_exceptions/InvalidStatusCodeException.php";

  class APIPinger {
    
    protected $endpoints_to_funcs = Array();

    public final function get_endpoints() {
      return array_keys($this->endpoints_to_funcs);
    }

    public final function enlist_endpoint(String $endpoint, callable $func=NULL) {
      try {
        if(!$func) {
          $this->endpoints_to_funcs[$endpoint] = function($data) {
            return $data;
          };
        } else {
          $this->endpoints_to_funcs[$endpoint] = $func;
        }
        return true;
      } catch(Exception $e) {
        return false;
      }
    }

    public final function unlist_endpoint(String $endpoint) {
      unset($this->endpoints_to_funcs[$endpoint]);
    }

    public final function ping($endpoint, $return_raw_data=false) {
      try {
        if(!isset($this->endpoints_to_funcs[$endpoint])) {
          throw new UnknownEndpoint("$endpoint is not enlisted. To overcome this problem, use enlist_endpoint()");
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
            throw new InvalidStatusCode("Status code $status_code encountered for endpoint $endpoint");
          }
        }
      } catch(Exception $e) {
        echo $e;
        return false;
      }
    }
  }


?>