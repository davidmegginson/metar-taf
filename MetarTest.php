<?php

require_once(__DIR__ . '/Metar.php');

class MetarTest extends PHPUnit_Framework_TestCase {

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

  function testNSC () {
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
  }

  function xtestMany () {
    $handle = fopen(__DIR__ . '/test-data.txt', 'r');
    $this->assertNotNull($handle);
    while (true) {
      $metar = trim(fgets($handle));
      if ($metar) {
        new \metar_taf\Metar($metar);
      } else {
        break;
      }
    }
    fclose($handle);
  }

  private function compare_object ($template, stdClass $object) {
    foreach ($template as $key => $expected_value) {
      $actual_value = $object->$key;
      if (is_array($expected_value)) {
        if (is_array($actual_value)) {
          for ($i = 0; $i < count($expected_value); $i++) {
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
