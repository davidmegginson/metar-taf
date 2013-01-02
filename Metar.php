<?php
////////////////////////////////////////////////////////////////////////
// Module for parsing and processing METAR data.
////////////////////////////////////////////////////////////////////////

namespace metar_taf;


/**
 * Exception thrown when a METAR can't be parsed.
 */
class MetarParsingException extends \Exception {

  public $error_message;
  public $token;

  function __construct($error_message, $token) {
    $this->error_message = $error_message;
    $this->token = $token;
    parent::__construct("$error_message: $token");
  }

}


/**
 * Wind information from a METAR report.
 */
class MetarWind extends \stdClass {

  public $raw;
  public $direction;
  public $speed;
  public $gust;
  public $unit;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  private function parse ($token) {
    $results = array();
    if (preg_match('/(VRB|\d\d\d)(\d\d)(?:G(\d\d))?(KT|MPS)$/', $token, $results)) {
      $this->raw = $token;
      $this->direction = $results[1];
      $this->speed = $results[2];
      $this->gust = $results[3];
      $this->unit = $results[4];
    }
  }

  static function create ($token, $exception_on_error = false) {
    $wind = new MetarWind($token);
    if ($wind->raw) {
      return $wind;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized wind information", $token);
    } else {
      return null;
    }
  }

}


/**
 * Surface visibility from a Metar report.
 */
class MetarVisibility extends \stdClass {
  public $raw;
  public $visibility;
  public $unit;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if (preg_match('!^(CAVOK|[MP]?\d+(?:/\d+)?)(SM)?$!', $token, $results)) {
      $this->raw = $token;
      $this->visibility = $results[1];
      $this->unit = $results[2];
    }
  }

  static function create ($token, $exception_on_error = false) {
    $visibility = new MetarVisibility($token);
    if ($visibility->raw) {
      return $visibility;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized visibility information", $token);
    } else {
      return null;
    }
  }

}

/**
 * Significant weather info from a METAR report.
 */
class MetarWeatherType extends \stdClass {
  public $raw;
  public $intensity_or_proximity;
  public $descriptor;
  public $precipitation;
  public $obscuration;
  public $other;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    // see http://www.nws.noaa.gov/oso/oso1/oso12/d31/appendix/appi.pdf
    $results = array();
    $regex = '/^' .
      '(-|\+|VC)?' .                       // intensity or proximity
      '(MI|BC|PR|DR|BL|SH|TS|FZ)?' .       // descriptor
      '(DZ|RA|SN|SG|IC|PE|GR|GS|UP)?' .    // precipitation
      '(BR|FG|FU|VA|DU|SA|HZ|PY)?' .       // obscuration
      '(PO|SQ|FC|SS|DS)?' .                // other
      '$/';
    if (preg_match($regex, $token, $results)) {
      $this->raw = $token;
      $this->intensity_or_proximity = @$results[1];
      $this->descriptor = @$results[2];
      $this->precipitation = @$results[3];
      $this->obscuration = @$results[4];
      $this->other = @$results[5];
    }
  }

  static function create ($token, $exception_on_error = false) {
    $weather_type = new MetarWeatherType($token);
    if ($weather_type->raw) {
      return $weather_type;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized weather type", $token);
    } else {
      return null;
    }
  }

}


/**
 * Cloud-layer information from a METAR report.
 *
 * This class describes a single cloud layer.
 */
class MetarCloudLayer extends \stdClass {
  public $raw;
  public $coverage;
  public $altitude;
  public $cloud_type;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if ($token == 'CLR') {
      $this->raw = $token;
      $this->coverage = 'CLR';
    } else if (preg_match('/^(FEW|SCT|BKN|OVC|VV)(\d+)?(ACC|TCU|CB)?$/', $token, $results)) {
      $this->raw = $token;
      $this->coverage = $results[1];
      $this->altitude = @$results[2];
      $this->cloud_type = @$results[3];
    }
  }

  static function create ($token, $exception_on_error = null) {
    $cloud_layer = new MetarCloudLayer($token);
    if ($cloud_layer->raw) {
      return $cloud_layer;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized cloud layer", $token);
    } else {
      return null;
    }
  }

}


/**
 * Temperature and dewpoint from a METAR report.
 */
class MetarTemperature extends \stdClass {
  public $raw;
  public $temperature;
  public $dewpoint;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if (preg_match('!^(M)?(\d+)/(M)?(\d+)$!', $token, $results)) {
      $this->raw = $token;
      $this->temperature = $results[2] * ($results[1] == 'M' ? -1 : 1);
      $this->dewpoint = $results[4] * ($results[3] == 'M' ? -1 : 1);
    }
  }

  static function create ($token, $exception_on_error = false) {
    $temperature = new MetarTemperature($token);
    if ($temperature->raw) {
      return $temperature;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized temperature information", $token);
    } else {
      return null;
    }
  }

}


/**
 * Altimeter setting from a METAR report.
 */
class MetarAltimeter extends \stdClass {

  public $raw;
  public $altimeter;
  public $unit;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if (preg_match('/^([QA])(\d{4})$/', $token, $results)) {
      $this->raw = $token;
      $this->altimeter = $results[2];
      $this->unit = $results[1];
    }
  }

  static function create ($token, $exception_on_error = false) {
    $altimeter = new MetarAltimeter($token);
    if ($altimeter->raw) {
      return $altimeter;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized altimeter setting", $token);
    } else {
      return null;
    }
  }

}


/** 
 * A full METAR report.
 */
class Metar extends \stdClass {

  public $raw;
  public $airport;
  public $time;
  public $auto_observation;
  public $wind;
  public $visibility;
  public $weather_types = array();
  public $cloud_layers = array();
  public $temperature;

  function __construct ($report = null) {
    if ($report) {
      $this->parse($report);
    }
  }

  function parse ($report) {

    $this->raw = $report;
    $tokens = preg_split('/\s/', $report);

    list($this->airport, $tokens) = $this->get_token($tokens, '/^[A-Z][A-Z0-9]{2,3}$/');

    list($this->time, $tokens) = $this->get_token($tokens, '/^[0-3]\d[0-9]\d[0-5]\dZ$/');

    if ($tokens[0] == 'AUTO') {
      array_shift($tokens);
      $this->auto_observation = true;
    }

    $token = array_shift($tokens);
    $this->wind = MetarWind::create($token, true);

    $token = array_shift($tokens);
    $this->visibility = MetarVisibility::create($token, true);

    while ($tokens) {
      $token = array_shift($tokens);
      $weather_type = MetarWeatherType::create($token);
      if ($weather_type) {
        array_push($this->weather_types, $weather_type);
      } else {
        array_unshift($tokens, $token);
        break;
      }
    }

    while ($tokens) {
      $token = array_shift($tokens);
      $cloud_layer = MetarCloudLayer::create($token);
      if ($cloud_layer) {
        array_push($this->cloud_layers, $cloud_layer);
      } else {
        array_unshift($tokens, $token);
        break;
      }
    }

    $token = array_shift($tokens);
    $this->temperature = MetarTemperature::create($token, true);

    $token = array_shift($tokens);
    $this->altimeter = MetarAltimeter::create($token, true);

    list($rmk, $tokens) = $this->get_token($tokens, '/^RMK$/', false);
    if ($rmk) {
      $this->remarks = implode(' ', $tokens);
      $tokens = array();
    }
  }

  private static function get_token ($tokens, $pattern, $required = true) {
    $token = array_shift($tokens);
    $orig_token = $token;
    if ($pattern && !preg_match($pattern, $token)) {
      array_unshift($tokens, $token);
      $token = null;
    }
    if ($required && !$token) {
      throw new MetarParsingException("Expected token matching $pattern", $orig_token);
    }
    return array($token, $tokens);
  }

}

// end
