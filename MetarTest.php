<?php

require_once(__DIR__ . '/Metar.php');

class MetarTest extends PHPUnit_Framework_TestCase {


  /**
   * Test the CAVOK report (and also meters per second for wind speed
   */
  function testCAVOK () {
    $metar = new \metar_taf\Metar('ZMUB 021300Z 01002MPS CAVOK M31/M35 Q1036 NOSIG RMK QFE667.5 70');
    $template = array(
      'airport' => 'ZMUB',
      'time' => '021300Z',
      'auto' => false,
      'wind' => array(
        'direction' => 10,
        'speed' => 2,
        'unit' => 'MPS',
      ),
      'visibility' => array(
        'visibility' => 'CAVOK',
      ),
      'temperature' => array(
        'temperature' => -31,
        'dewpoint' => -35,
      ),
      'altimeter' => array(
        'altimeter' => '1036',
        'unit' => 'Q',
      ),
      'nosig' => true,
      'remarks' => 'QFE667.5 70',
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a typical UK METAR, with visibility in meters and altimeter in hectopascals
   */
  function testUK () {
    $metar = new \metar_taf\Metar('EGLL 021250Z 23009KT 9999 SCT023 BKN029 08/06 Q1024');
    $template = array(
      'airport' => 'EGLL',
      'time' => '021250Z',
      'auto' => false,
      'wind' => array(
        'direction' => 230,
        'speed' => 9,
        'unit' => 'KT',
      ),
      'visibility' => array(
        'visibility' => 9999,
      ),
      'cloud_layers' => array(
        array(
          'coverage' => 'SCT',
          'altitude' => 23,
        ),
        array(
          'coverage' => 'BKN',
          'altitude' => 29,
        ),
      ),
      'temperature' => array(
        'temperature' => 8,
        'dewpoint' => 6,
      ),
      'altimeter' => array(
        'altimeter' => '1024',
        'unit' => 'Q',
      ),
      'nosig' => false,
      'remarks' => null,
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a typical US METAR, with visibility in statute miles and altimeter in inches of mercury
   */
  function testUS () {
    $metar = new \metar_taf\Metar('KLAX 021253Z 10003KT 10SM CLR 07/M01 A3009 RMK AO2 SLP186 T00721011');
    $template = array(
      'airport' => 'KLAX',
      'time' => '021253Z',
      'auto' => false,
      'wind' => array(
        'direction' => 100,
        'speed' => 3,
        'unit' => 'KT',
      ),
      'cloud_layers' => array(
        array(
          'coverage' => 'CLR',
        ),
      ),
      'temperature' => array(
        'temperature' => 7,
        'dewpoint' => -1,
      ),
      'altimeter' => array(
        'altimeter' => '3009',
        'unit' => 'A',
      ),
      'nosig' => false,
      'remarks' => 'AO2 SLP186 T00721011',
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test "NSC" for no significant cloud
   */
  function testNSC () {
    $this->markTestIncomplete("Does not run through successfully");

    $metar = new \metar_taf\Metar('OAKB 021250Z 09006KT 5000 HZ FU NSC M01/M08 Q1020 NOSIG RMK WHT WHT');
    $template = array(
      'airport' => 'OAKB',
      'time' => '021250Z',
      'auto' => false,
      'wind' => array(
        'direction' => 90,
        'speed' => 6,
        'unit' => 'KT',
      ),
      'visibility' => array(
        'visibility' => 5000,
      ),
      'weather_types' => array(
        array(
          'obscuration' => 'HZ',
        ),
        array(
          'obscuration' => 'FU',
        ),
      ),
      'cloud_layers' => array(
        'coverage' => 'NSC',
      ),
      'temperature' => array(
        'temperature' => -1,
        'dewpoint' => -8,
      ),
      'altimeter' => array(
        'altimeter' => 1020,
        'unit' => 'Q',
      ),
      'nosig' => true,
      'remarks' => 'WHT WHT',
    );
    $this->compare_object($template, $metar);
  }

  function testNCDNDV () {
    $this->markTestIncomplete("Does not run through successfully");

    $metar = new \metar_taf\Metar('EKHN 021250Z AUTO 29021KT 9999NDV NCD 07/05 Q1017');
    $template = array(
      'airport' => 'EKHN',
      'time' => '021250Z',
      'auto' => true,
      'wind' => array(
        'direction' => 290,
        'speed' => 21,
        'unit' => 'KT',
      ),
      'visibility' => array(
        'visibility' => 9999,
        'directionality' => 'NDV',
      ),
      'cloud_layers' => array(
        'coverage' => 'NCD',
      ),
      'temperature' => array(
        'temperature' => 7,
        'dewpoint' => 5,
      ),
      'altimeter' => array(
        'altimeter' => 1017,
        'unit' => 'Q',
      ),
      'nosig' => true,
      'remarks' => null,
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a report with runway visual range.
   */
  function testRVR () {
    $metar = new \metar_taf\Metar('PAKU 021245Z 06016KT 3SM R06/P6000FT BR BKN017 M20/M22 A2955');
    $template = array(
      'rvr' => array(
        array(
          'runway' => 'R06',
          'assessment' => 'P',
          'rvr' => 6000,
          'unit' => 'FT',
        ),
      ),
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a report with RVR varying.
   */
  function testVaryingRVR () {
    $metar = new \metar_taf\Metar('KEUG 021248Z AUTO 23003KT 1/2SM R16R/1800V3000FT FZFG VV001 M05/M06 A3027 RMK AO2 PNO $');
    $template = array(
      'rvr' => array(
        array(
          'runway' => 'R16R',
          'rvr' => 1800,
          'rvr_max' => 3000,
          'unit' => 'FT',
        ),
      ),
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a METAR with 
   */
  function testRunwayConditions () {
    $metar = new \metar_taf\Metar('EYSA 021245Z 23009KT 9999 FEW012 SCT038 BKN048 03/01 Q1010 R14L/290161 R14R/290161');
    $template = array(
      'runway_conditions' => array(
        array(
          'raw' => 'R14L/290161',
          'runway' => '14L',
          'deposits' => '2',
          'extent' => '9',
          'depth' => '01',
          'friction' => '61',
        ),
        array(
          'raw' => 'R14R/290161',
          'runway' => '14R',
          'deposits' => '2',
          'extent' => '9',
          'depth' => '01',
          'friction' => '61',
        ),
      ),
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a METAR with visibility specified as a fraction
   */
  function testFraction () {
    $metar = new \metar_taf\Metar('CYVV 021245Z 33005KT 1 1/2SM -SN OVC018 RMK SN3SC5');
    $template = array(
      'visibility' => array(
        'visibility' => '1 1/2',
        'unit' => 'SM',
      ),
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test a METAR with variable wind direction.
   */
  function testWindVariation () {
    $metar = new \metar_taf\Metar('EEKA 021250Z 23007KT 170V280 9999 SCT021 02/01 Q1003');
    $template = array(
      'wind' => array(
        'direction' => 230,
        'speed' => 7,
        'unit' => 'KT',
        'min_variation' => 170,
        'max_variation' => 280,
      ),
    );
    $this->compare_object($template, $metar);
  }

  /**
   * Test against a worldwide METAR file.
   */
  function testMany () {
    $handle = fopen(__DIR__ . '/test-data.txt', 'r');
    $this->assertNotNull($handle);
    while (true) {
      $metar = trim(fgets($handle));
      if ($metar) {
        $m=new \metar_taf\Metar($metar);
        $this->assertInstanceOf('\metar_taf\Metar',$m);
      } else {
        break;
      }
    }
    fclose($handle);
  }

  /**
   * Compare an object to a template data structure.
   */
  private function compare_object ($template, stdClass $object) {
    foreach ($template as $key => $expected_value) {
      $actual_value = $object->$key;
      if (is_array($expected_value)) {
        if (is_array($actual_value)) {
          for ($i = 0; $i < count($expected_value); $i++) {
            $this->assertArrayHasKey($i,$expected_value,$key);
            $this->assertArrayHasKey($i,$actual_value,$key);
            $this->compare_object($expected_value[$i], $actual_value[$i]);
          }
        } else {
          $this->compare_object($expected_value, $actual_value);
        }
      } else {
        $this->assertEquals($expected_value, $actual_value);
      }
    }
  }

}
