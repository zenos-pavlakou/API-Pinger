<?PHP

  include "APIPinger.php";

  $pinger = new APIPinger();

  $pinger->disallow_unknown_endpoints(); //if an endpoint is not enlisted, then it is unknown.
  $pinger->allow_unknown_endpoints(); //now all endpoints can be pinged, even if not enlisted. Enabled by default.

  //The function closure in the second argument is invoked with the data from the api response. 
  $pinger->enlist_endpoint(
    "https://randomuser.me/api",
    function($data) {
      $data = $data["results"][0];
      return [
        "gender" => $data['gender'],
        "name" => "{$data['name']['title']} {$data['name']['first']} {$data['name']['last']}",
        "email" => $data["email"]
      ];
    }
  );

  //This enlisting does not have a function closure, so by default all data will be returned from the api response. 
  $pinger->enlist_endpoint(
    "https://ethgasstation.info/api/ethgasAPI.json"
  );

  echo var_dump(
    $pinger->ping(
      "https://ethgasstation.info/api/ethgasAPI.json", 
      $return_raw_data=false
    )
  );

?>