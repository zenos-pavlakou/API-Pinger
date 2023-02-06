<?PHP

  include "APIPinger.php";

  $pinger = new APIPinger();

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

  $pinger->enlist_endpoint(
    "https://ethgasstation.info/api/ethgasAPI.json",
    function($data) {
      return [
        "fastest" => $data["fastest"],
        "fast" => $data["fast"],
        "slow" => $data["safeLow"]
      ];
    }
  );

  echo var_dump(
    $pinger->ping(
      "https://ethgasstation.info/api/ethgasAPI.json", 
      $return_raw_data=false
    )
  );

?>