<?PHP

  include "APIPinger.php";

  $pinger = new APIPinger();

  $pinger->disallow_unknown_endpoints(); //if an endpoint is not enlisted, then it is unknown.
  $pinger->allow_unknown_endpoints(); //now all endpoints can be pinged, even if not enlisted. Enabled by default.

  //This enlisting does not have a function closure, so by default all data will be returned from the api response. 
  $pinger->enlist_endpoint(
    "https://ethgasstation.info/api/ethgasAPI.json"
  );

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

  //This will return the gender, name and email of the random user, as specified by the function closure.
  $pinger->ping("https://randomuser.me/api");

  //This will return the data from the API response without applying a function closure. 
  $pinger->ping("https://randomuser.me/api", $return_raw_data=true);

?>