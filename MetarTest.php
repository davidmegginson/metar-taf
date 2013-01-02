<?php

require_once(__DIR__ . '/Metar.php');

class MetarTest extends PHPUnit_Framework_TestCase {

  function testUS () {
    $metar = new \metar_taf\Metar('KLAX 021253Z 10003KT 10SM CLR 07/M01 A3009 RMK AO2 SLP186 T00721011');
    $template = array(
      'airport' => 'KLAX',
      'time' => '021253Z',
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
      'remarks' => 'AO2 SLP186 T00721011',
    );
    $this->compare_object($template, $metar);
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
