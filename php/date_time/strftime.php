<?php

namespace Date_Time;

if (!function_exists('strftimeA')) {
  /**
   * strftimeA simple replacement for strftime
   *
   * @param string $pattern
   * @param int $timestamp
   * @param string $timezone
   * @return string|false
   */
  function strftimeA($pattern, $timestamp = null, $timezone = "Europe/Berlin")
  {
    $formatter = new \IntlDateFormatter(null, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG, $timezone);
    $date_time = new \DateTime(timezone: new \DateTimeZone($timezone));
    try {
      if ($timestamp !== null && (is_long($timestamp) || is_int($timezone))) {
        $date_time->setTimestamp($timestamp);
      }

      $map_pattern = [
        '%A' => 'eeee',       // day of week
        '%W' => 'w',          // week of year
        '%Y' => 'y',          // year
        '%d' => 'dd',         // day in month
        '%m' => 'MM',         // month in year
        '%H' => 'HH',         // hour in day
        '%M' => 'mm',         // minute in hour
        '%S' => 'ss',         // second in minute
        '%F' => 'y-MM-dd',    // Same as "%Y-%m-%d" Example: 2009-02-05 für den 5. Februar 2009
        '%T' => 'HH:mm:ss',   // Same as "%H:%M:%S" Example: 21:34:17 für 09:34:17 PM
        '%s' => (string) $formatter->parse($formatter->format($date_time))
      ];

      //  $formatter->setPattern($map_pattern[$pattern]);
      $formatter->setPattern(str_replace(array_keys($map_pattern), array_values($map_pattern), $pattern));

      return $formatter->format($date_time);
    } finally {
      unset($formatter, $date_time);
    }
  }
}
