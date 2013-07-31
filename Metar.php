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
  public $min_variation;
  public $max_variation;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  private function parse ($token) {
    $results = array();
    if (preg_match('!^/+!', $token)) {
      // slashed out
      $this->raw = $token;
    } else if (preg_match('/(VRB|\d\d\d)(\d\d)(?:G(\d\d))?(KT|MPS)(?: (\d{1,3})V(\d{1,3}))?$/', $token, $results)) {
      $this->raw = $token;
      $this->direction = $results[1];
      $this->speed = $results[2];
      $this->gust = @$results[3];
      $this->unit = @$results[4];
      $this->min_variation = @$results[5];
      $this->max_variation = @$results[6];
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
  public $directionality;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if (preg_match('!^(CAVOK|[MP]?(?:\d+ )?\d+(?:/\d+)?)(SM)?(NDV)?$!', $token, $results)) {
      $this->raw = $token;
      $this->visibility = $results[1];
      $this->unit = @$results[2];
      $this->directionality = @$results[3];
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
 * RVR from a METAR report.
 */
class MetarRVR extends \stdclass {
  public $raw;
  public $runway;
  public $assessment;
  public $rvr;
  public $unit;

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if (preg_match('!^(R.+)/([M|P])?(\d{4})(?:V(\d+)|[UDN])?(FT)?$!', $token, $results)) {
      $this->raw = $token;
      $this->runway = $results[1];
      $this->assessment = $results[2];
      $this->rvr = $results[3];
      $this->rvr_max = @$results[4];
      $this->unit = @$results[5];
    }
  }

  static function create ($token, $exception_on_error = false) {
    $rvr = new MetarRVR($token);
    if ($rvr->raw) {
      return $rvr;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized RVR", $token);
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
    } else if (preg_match('/^(NCD|NSC|FEW|SCT|BKN|OVC|VV)(\d+)?(ACC|TCU|CB)?$/', $token, $results)) {
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
 * Runway surface conditions from a European METAR report.
 *
 * See http://www.ivao.aero/training/tutorials/metar/metar.htm
 */
class MetarRunwayConditions extends \stdClass {

  public $raw;
  public $runway;
  public $deposits;
  public $extent;
  public $depth;
  public $friction;

  public static $DEPOSIT_TYPES = array(
    '0' => 'Clear and dry',
    '1' => 'Damp',
    '2' => 'Wet or water particles',
    '3' => 'Rime or frost covered',
    '4' => 'Dry snow',
    '5' => 'Wet snow',
    '6' => 'Slush',
    '7' => 'Ice',
    '8' => 'Compacted or rolled snow',
    '9' => 'Frozen ruts or ridges',
    '/' => 'Not reported (runway clearance in progress)',
  );

  public static $EXTENT_TYPES = array(
    '1' => '<10% contaminated (covered)',
    '2' => '11% to 25% contaminated (covered)',
    '5' => '26% to 50% contaminated (covered)',
    '9' => '51% to 100% contaminated (covered)',
    '/' => 'Not reported (runway clearance in progress)',
  );

  public static $DEPTH_TYPES = array(
    '00' => 'Less than 1mm',
    // Note: a value between 01-90 is actual depth in mm
    '92' => '10cm',
    '93' => '15cm',
    '94' => '20cm',
    '95' => '25cm',
    '96' => '30cm',
    '97' => '35cm',
    '98' => '40cm or more',
    '99' => 'Runway not operational due to snow, slush, ice, large drifts or runway clearance, depth not reported',
    '//' => 'Not operationally significant or measurable',
  );

  public static $FRICTION_TYPES = array(
    // Note: a lower value is the actual friction coefficient
    '91' => 'Braking action poor',
    '92' => 'Braking action medium to poor',
    '93' => 'Braking action medium',
    '94' => 'Braking action medium to good',
    '95' => 'Braking action good',
    '99' => 'Figures unreliable',
    '//' => 'Braking action not reported or runway not operational or airport closed',
  );

  function __construct ($token = null) {
    if ($token) {
      $this->parse($token);
    }
  }

  function parse ($token) {
    $results = array();
    if (preg_match('!^R(\d{2}[LRC]?)/([\d/])([\d/])([\d/]{2})([\d/]{2})$!', $token, $results)) {
      $this->raw = $token;
      $this->runway = $results[1];
      $this->deposits = $results[2];
      $this->extent = $results[3];
      $this->depth = $results[4];
      $this->friction = $results[5];
    }
  }

  static function create ($token, $exception_on_error = false) {
    $runwayConditions = new MetarRunwayConditions($token);
    if ($runwayConditions->raw) {
      return $runwayConditions;
    } else if ($exception_on_error) {
      throw new MetarParsingException("Unrecognized runway conditions", $token);
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
  public $auto = false;
  public $correction = false;
  public $wind;
  public $visibility;
  public $rvr = array();
  public $weather_types = array();
  public $cloud_layers = array();
  public $temperature;
  public $altimeter;
  public $nosig = false;
  public $runway_conditions = array();
  public $remarks;

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
      $this->auto = true;
    }

    if ($tokens[0] == 'COR') {
      array_shift($tokens);
      $this->correction = true;
    }

    while ($tokens) {

      $token = array_shift($tokens);

      // Read wind conditions
      if (!$this->wind) {
        if (preg_match('/^\d+V\d+$/', @$tokens[0])) {
          // special case of variable wind direction
          $token .= ' ' . array_shift($tokens);
        }
        $wind = MetarWind::create($token, false);
        if ($wind) {
          $this->wind = $wind;
          continue;
        }
      }

      // Read the visibility
      if (!$this->visibility) {
        if (preg_match('!^\d/\dSM$!', @$tokens[0])) {
          // special case of a number and fraction
          $token .= ' ' . array_shift($tokens);
        }
        $visibility = MetarVisibility::create($token, false);
        if ($visibility) {
          $this->visibility = $visibility;
          continue;
        }
      }
      
      // Read a runway visual range
      $rvr = MetarRVR::create($token);
      if ($rvr) {
        array_push($this->rvr, $rvr);
        continue;
      }

      // Read a weather types
      $weather_type = MetarWeatherType::create($token);
      if ($weather_type) {
        array_push($this->weather_types, $weather_type);
        continue;
      }

      // Read a cloud layer
      $cloud_layer = MetarCloudLayer::create($token);
      if ($cloud_layer) {
        array_push($this->cloud_layers, $cloud_layer);
        continue;
      }

      // Read the temperature
      if (!$this->temperature) {
        $temperature = MetarTemperature::create($token, false);
        if ($temperature) {
          $this->temperature = $temperature;
          continue;
        }
      }

      // Read the altimeter
      if (!$this->altimeter) {
        $altimeter = MetarAltimeter::create($token, false);
        if ($altimeter) {
          $this->altimeter = $altimeter;
          continue;
        }
      }

      // Check for no significant weather
      if ($token == 'NOSIG') {
        $this->nosig = true;
        continue;
      }

      // Check for European encoded runway conditions
      $runway_condition = MetarRunwayConditions::create($token, false);
      if ($runway_condition) {
        array_push($this->runway_conditions, $runway_condition);
        continue;
      }

      // Read remarks
      if ($token == 'RMK') {
        $this->remarks = implode(' ', $tokens);
        $tokens = array();
      }

    }

    foreach ($tokens as $token) {
      if ($token) {
        throw new MetarParsingException("Unprocessed tokens in \n$report\n", implode(' ', $tokens));
      }
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
