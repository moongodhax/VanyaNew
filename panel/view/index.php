<?php
require_once(__DIR__ . "/../php/mysqli.php");

$url_array = parse_url($_SERVER['REQUEST_URI']);
$path = $url_array["path"];
$hash = str_replace("/view/", "", $path);

$streamid = null;
$substreamid = null;
$substream_name = null;

$hash = mysqli_real_escape_string($mysqli, $hash);

$result = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM `substreams` WHERE `hash` = '$hash'");
$row = mysqli_fetch_assoc($result);

if ($row['cnt'] > 0) {
  $result = mysqli_query($mysqli, "SELECT * FROM `substreams` WHERE `hash` = '$hash'");
  $row = mysqli_fetch_assoc($result);
  $substreamid = $row["id"];
  $substream_name = $row["name"];
  $streamid = $row["streamid"];
} else {
  header("HTTP/1.0 404 Not Found");
  die();
}

if (isset($_POST["get_stats"])) {
  $date_start = $_POST['date_start'];
  $date_end = $_POST['date_end'];

  $out = [];
  
  $out["stream"] = $substream_name;

  $dates = "";
  $date = null;
  $start = 0;
  $end = 0;

  if ($date_start != "" && $date_end != "") {
    $date = DateTime::createFromFormat('Y-m-d', $date_start);
    $date->modify('today');
    $start = $date->getTimestamp();
  
    $date = DateTime::createFromFormat('Y-m-d', $date_end);
    $date->modify('tomorrow');
    $end = $date->getTimestamp();
    // echo $end;
    $dates = "AND `timestamp` >= $start AND `timestamp` < $end";
  }

  $q = "
  SELECT
  COUNT(CASE WHEN `a`.`type` = 'ok' THEN 1 END) as 'success',
  COUNT(CASE WHEN `a`.`type` = 'decline' OR `a`.`type` = 'banned' THEN 1 END) as 'decline'
  FROM
  (
    SELECT MIN(`id`), ANY_VALUE(`type`) as 'type' FROM `records` 
    WHERE `streamid` = '$streamid' AND `substreamid` = '$substreamid' AND (`type` = 'ok' OR `type` = 'decline' OR `type` = 'banned') $dates 
    GROUP BY `ip`
  ) a 
  ";
  $result = mysqli_query($mysqli, $q);
  $row = mysqli_fetch_assoc($result);
  $out["success"] = $row['success'];
  $out["decline"] = $row['decline'];
  
  // неделя
  $out["week"] = [];
  $days = [];

  if ($date_end != "") {
    $date = DateTime::createFromFormat('Y-m-d', $date_end);
  }
  else {
    $date = new DateTime();
  }

  $date->modify('today');

  $q = "SELECT\n";
  for ($i = 0; $i < 7; $i++) {
    $as = "count$i";
    $ts = $date->getTimestamp();
    $ts2 = $ts + 86400;

    $q .= "COUNT(CASE WHEN `timestamp` >= {$ts} AND `timestamp` < {$ts2} THEN 1 END) AS $as,\n";

    $days[] = $date->format("m/d/Y (l)");
    $date->sub(new DateInterval('P1D'));
  }

  $q = substr($q, 0, -2);
  $q .= "\nFROM\n";
  $q .= "
  (
    SELECT MIN(`id`), ANY_VALUE(`timestamp`) as 'timestamp'
    FROM `records` 
    WHERE `streamid` = '$streamid' AND `substreamid` = '$substreamid' AND `type` = 'ok'
    GROUP BY `ip`
  ) a
  ";

  $result = mysqli_query($mysqli, $q);
  $row = mysqli_fetch_assoc($result);

  for ($i = 0; $i < 7; $i++) {
    $out["week"][] = ["day" => $days[$i], "cnt" => $row["count$i"]];
  }

  $out["map"] = getMap($streamid, $substreamid, $start, $end);

  echo json_encode($out);
  die();
}

function getMap($streamid, $substreamid, $ts1, $ts2) {
  global $mysqli;

  $streamid = mysqli_real_escape_string($mysqli, $streamid);
  $substreamid = mysqli_real_escape_string($mysqli, $substreamid);

  $ts = "";
  if ($ts1 != "" && $ts2 != "") {
    $ts = "AND `timestamp` >= $ts1 AND `timestamp` < $ts2";
  }

  $countries = [
    "AF" => 0, "AL" => 0, "DZ" => 0, "AO" => 0, "AG" => 0, "AR" => 0, "AM" => 0, "AU" => 0, "AT" => 0,
    "AZ" => 0, "BS" => 0, "BH" => 0, "BD" => 0, "BB" => 0, "BY" => 0, "BE" => 0, "BZ" => 0, "BJ" => 0,
    "BT" => 0, "BO" => 0, "BA" => 0, "BW" => 0, "BR" => 0, "BN" => 0, "BG" => 0, "BF" => 0, "BI" => 0,
    "KH" => 0, "CM" => 0, "CA" => 0, "CV" => 0, "CF" => 0, "TD" => 0, "CL" => 0, "CN" => 0, "CO" => 0,
    "KM" => 0, "CD" => 0, "CG" => 0, "CR" => 0, "CI" => 0, "HR" => 0, "CY" => 0, "CZ" => 0, "DK" => 0,
    "DJ" => 0, "DM" => 0, "DO" => 0, "EC" => 0, "EG" => 0, "SV" => 0, "GQ" => 0, "ER" => 0, "EE" => 0,
    "ET" => 0, "FJ" => 0, "FI" => 0, "FR" => 0, "GA" => 0, "GM" => 0, "GE" => 0, "DE" => 0, "GH" => 0,
    "GR" => 0, "GD" => 0, "GT" => 0, "GN" => 0, "GW" => 0, "GY" => 0, "HT" => 0, "HN" => 0, "HK" => 0,
    "HU" => 0, "IS" => 0, "IN" => 0, "ID" => 0, "IR" => 0, "IQ" => 0, "IE" => 0, "IL" => 0, "IT" => 0,
    "JM" => 0, "JP" => 0, "JO" => 0, "KZ" => 0, "KE" => 0, "KI" => 0, "KR" => 0, "KW" => 0, "KG" => 0,
    "LA" => 0, "LV" => 0, "LB" => 0, "LS" => 0, "LR" => 0, "LY" => 0, "LT" => 0, "LU" => 0, "MK" => 0,
    "MG" => 0, "MW" => 0, "MY" => 0, "MV" => 0, "ML" => 0, "MT" => 0, "MR" => 0, "MU" => 0, "MX" => 0,
    "MD" => 0, "MN" => 0, "ME" => 0, "MA" => 0, "MZ" => 0, "MM" => 0, "NA" => 0, "NP" => 0, "NL" => 0,
    "NZ" => 0, "NI" => 0, "NE" => 0, "NG" => 0, "NO" => 0, "OM" => 0, "PK" => 0, "PA" => 0, "PG" => 0,
    "PY" => 0, "PE" => 0, "PH" => 0, "PL" => 0, "PT" => 0, "QA" => 0, "RO" => 0, "RU" => 0, "RW" => 0,
    "WS" => 0, "ST" => 0, "SA" => 0, "SN" => 0, "RS" => 0, "SC" => 0, "SL" => 0, "SG" => 0, "SK" => 0,
    "SI" => 0, "SB" => 0, "ZA" => 0, "ES" => 0, "LK" => 0, "KN" => 0, "LC" => 0, "VC" => 0, "SD" => 0,
    "SR" => 0, "SZ" => 0, "SE" => 0, "CH" => 0, "SY" => 0, "TW" => 0, "TJ" => 0, "TZ" => 0, "TH" => 0,
    "TL" => 0, "TG" => 0, "TO" => 0, "TT" => 0, "TN" => 0, "TR" => 0, "TM" => 0, "UG" => 0, "UA" => 0,
    "AE" => 0, "GB" => 0, "US" => 0, "UY" => 0, "UZ" => 0, "VU" => 0, "VE" => 0, "VN" => 0, "YE" => 0,
    "ZM" => 0, "ZW" => 0, "GL" => 0, "SS" => 0, "KP" => 0, "CU" => 0, "SO" => 0, "XS" => 0, "EH" => 0, 
    "TF" => 0, "NC" => 0, "FK" => 0, "PR" => 0, "XK" => 0, "XC" => 0, "PS" => 0, "UNDEFINED" => 0
  ];
  
  $unique = [];
  
  $result = mysqli_query($mysqli, "SELECT DISTINCT `country` FROM `records` WHERE `streamid` = '$streamid' AND `substreamid` = '$substreamid' $ts AND `type` = 'ok'");
  while($row = mysqli_fetch_assoc($result)) {
    $unique[] = $row['country'];
  }
  
  if (count($unique) > 0) {
    $q = "SELECT  \n";
    foreach($unique as $v) {
      $q .= "COUNT(CASE WHEN `country` = '$v' THEN 1 END) AS '$v',\n";
    }
    $q = substr($q, 0, -2);
    $q .= "\nFROM\n";
    $q .= "
    (
      SELECT MIN(`id`), ANY_VALUE(`country`) as 'country', ANY_VALUE(`streamid`) as 'streamid', ANY_VALUE(`substreamid`) as 'substreamid'
      FROM `records` 
      WHERE `streamid` = '$streamid' AND `substreamid` = '$substreamid' $ts AND `type` = 'ok'
      GROUP BY `ip`
    ) a
    ";
    
    $result = mysqli_query($mysqli, $q);
    $row = mysqli_fetch_assoc($result);
    
    foreach($unique as $v) {
      $countries[$v] = intval($row[$v]);
    }

    arsort($countries, SORT_NUMERIC);
  }
  return $countries;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="/assets/img/favicon.png">
  <title>
    Statistics
  </title>
  <!-- Fonts and icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- CSS Files -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  
  <link href="//cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css" rel="stylesheet" />

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
  <script src="//cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>

  <script src="https://cdn.jsdelivr.net/npm/jvectormap@2.0.4/jquery-jvectormap.min.js"></script>
  <script src="https://jvectormap.com/js/jquery-jvectormap-world-mill.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jvectormap@2.0.4/jquery-jvectormap.css">

  <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
</head>

<body class="g-sidenav-show bg-gray-200">
  <main class="container main-content vue-app">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <h3 class="font-weight-bolder mb-0">Statistics - {{ stream }}</h3>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <div class="col-12 col-sm-4">
          <div class="card">
            <div class="card-body">
              <h6>Date/Time Interval</h6>
              <div class="row mb-3">
                <div class="col-6">
                  <label for="start" class="form-label">Start</label> <br />
                  <input type="date" id="start" name="start" v-model="date_start">
                </div>
                <div class="col-6">
                  <label for="end" class="form-label">End</label> <br />
                  <input type="date" id="end" name="end" v-model="date_end">
                </div>
              </div>
              <h6>Statistics</h6>
              <div class="row">
                <div class="col-4 text-center">
                  <h6 class="text-dark font-weight-bold mb-0">Success</h6>
                  <p>{{ success }}</p>
                </div>
                <div class="col-4 text-center">
                  <h6 class="text-dark font-weight-bold mb-0">Decline</h6>
                  <p>{{ decline }}</p>
                </div>
              </div>
              <h6>Week</h6>
              <template v-if="week.length > 0">
                <div class="row" v-for="day in week">
                  <div class="col-8 text-secondary font-weight-bold text-xs mt-1">
                    {{ day["day"] }}:
                  </div>
                  <div class="col-4">
                    <b>{{ day["cnt"] }}</b>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-8">
          <div class="card">
            <div class="card-header pb-0 px-4">
              <h6>Map</h6>
            </div>
            <div class="card-body">
              <div id="map" style="height: 377px;"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row my-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 px-4">
              <h6>Installs</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="installs-table"></table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div class="preloader">
    <img src="./preloader.svg" />
  </div>

  <style>
    body {
      overflow: hidden;
    }

    .flag-wrapper {
      display: inline-block;
      width: 55px;
      text-align: center;
    }
    .flag {
      height: 20px;
    }
    table.dataTable {
      width: 100% !important;
    }

    .preloader {
      width: 100%;
      height: 100%;
      background: #fff;
      position: fixed;
      top: 0;
      left: 0;

      display: flex;
      justify-content: center;
      align-items: center;
    }

    .preloader img {
      height: 96px;
    }
  </style>

  <script>
    var app = new Vue({
      el: '.vue-app',
      data: {
        stream: "",
        date_start: "",
        date_end: "",
        hash: "",
        success: 0,
        decline: 0,
        week: [],
      },
      mounted: function () {
        this.hash = new URL(location.href).pathname.replace("/view/", "");
        let self = this;
        $("#installs-table").DataTable({
          serverSide: true,
          info: false,
          ajax: function (data, callback, settings) {
            data.date_start = self.date_start;
            data.date_end = self.date_end;
            $.post(`/view/datatables.php?hash=${self.hash}`, data)
            .done(function (data) {
              callback(JSON.parse(data));
            })
            .fail(function () {
              alert("Datatables Error");
            });
          },
          columns: [
            {
              title: "IP",
              data: "ip",
              render: function ( data, type, row, meta ) {
                return `<div class="flag-wrapper"><img src="/view/flags/${row.country.toLowerCase()}.svg" class="flag" title="${row.country}"></div> ${data}`;
              }
            },
            {
              title: "Country",
              data: "country",
              visible: false
            },
            {
              title: "Type",
              data: "type",
              render: function( data, type, row, meta ) {
                if (row.reason != "") return `${row.type} / ${row.reason}`;
                else return data;
              }
            },
            {
              data: "reason",
              visible: false,
            },
            {
              title: "Date",
              data: "timestamp",
              render: function ( data, type, row, meta ) {
                return self.formatDate(data);
              }
            },
          ],
          order: [[4, "desc"]],
        });

        this.reload();
      },
      methods: {
        reload: function() {
          let self = this;

          $("#map").html("");

          $.post(
            `/view/${this.hash}`,
            { 
              get_stats: true,
              date_start: this.date_start,
              date_end: this.date_end,
            }
          )
          .done(function(data) {
            let decoded = JSON.parse(data);
            self.stream = decoded.stream;
            self.success = decoded.success;
            self.decline = decoded.decline;
            self.week = decoded.week;
        
            $("#map").vectorMap({
              map: "world_mill", backgroundColor: "transparent",
              series: { regions: [ { values: decoded.map, scale: ["#a8a8a8", "#363636"], normalizeFunction: "polynomial", }, ], },
              onRegionTipShow: function (e, el, code) {
                let worldMap = $('#map').vectorMap('get', 'mapObject'); 
                el.html(el.html() + ": " + worldMap.series.regions[0].values[code]);
              },
              hoverOpacity: 0.7, hoverColor: false,
            });

            $("body").css("overflow", "auto");
            $(".preloader").hide();
          })
        },
        formatDate: function(ts) {
          let date = new Date(+ts * 1000);
          var hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
          var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();

          var day = date.getDate() < 10 ? "0" + date.getDate() : date.getDate();
          var month = date.getMonth() + 1 < 10 ? "0" + (+date.getMonth() + 1) : +date.getMonth() + 1;
          var year = date.getFullYear();

          return month + "/" + day + "/" + year + " " + hours + ":" + minutes;
        },
      },
      watch: {
        date_start: function (val) {
          $("#installs-table").DataTable().ajax.reload();
          this.reload();
        },
        date_end: function (val) {
          $("#installs-table").DataTable().ajax.reload();
          this.reload();
        },
      }
    });
  </script>
</body>

</html>