<?php

namespace Sfi\Site\FlowQueryOperations;

// BEWARE: horid legacy code in this file!!!

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;

function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
{
  /*
	$interval can be:
	yyyy - Number of full years
	q - Number of full quarters
	m - Number of full months
	y - Difference between day numbers
		(eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
	d - Number of full days
	w - Number of full weekdays
	ww - Number of full weeks
	h - Number of full hours
	n - Number of full minutes
	s - Number of full seconds (default)
	*/

  if (!$using_timestamps) {
    $datefrom = strtotime($datefrom, 0);
    $dateto = strtotime($dateto, 0);
  }
  $difference = $dateto - $datefrom; // Difference in seconds

  switch ($interval) {

    case 'yyyy': // Number of full years

      $years_difference = floor($difference / 31536000);
      if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom) + $years_difference) > $dateto) {
        $years_difference--;
      }
      if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto) - ($years_difference + 1)) > $datefrom) {
        $years_difference++;
      }
      $datediff = $years_difference;
      break;

    case "q": // Number of full quarters

      $quarters_difference = floor($difference / 8035200);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($quarters_difference * 3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $quarters_difference++;
      }
      $quarters_difference--;
      $datediff = $quarters_difference;
      break;

    case "m": // Number of full months

      $months_difference = floor($difference / 2678400);
      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }
      $months_difference--;
      $datediff = $months_difference;
      break;

    case 'y': // Difference between day numbers

      $datediff = date("z", $dateto) - date("z", $datefrom);
      break;

    case "d": // Number of full days

      $datediff = floor($difference / 86400);
      break;

    case "w": // Number of full weekdays

      $days_difference = floor($difference / 86400);
      $weeks_difference = floor($days_difference / 7); // Complete weeks
      $first_day = date("w", $datefrom);
      $days_remainder = floor($days_difference % 7);
      $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?
      if ($odd_days > 7) { // Sunday
        $days_remainder--;
      }
      if ($odd_days > 6) { // Saturday
        $days_remainder--;
      }
      $datediff = ($weeks_difference * 5) + $days_remainder;
      break;

    case "ww": // Number of full weeks

      $datediff = floor($difference / 604800);
      break;

    case "h": // Number of full hours

      $datediff = floor($difference / 3600);
      break;

    case "n": // Number of full minutes

      $datediff = floor($difference / 60);
      break;

    default: // Number of full seconds (default)

      $datediff = $difference;
      break;
  }

  return $datediff;
}

function easter($year)
{
  $a = $year % 19;
  $b = $year % 4;
  $c = $year % 7;
  $d = (19 * $a + 15) % 30;
  $e = (2 * $b + 4 * $c + 6 * $d + 6) % 7;

  if (($d + $e) > 10)
    $easter_o = ($d + $e - 9) . "-4-" . $year;
  else
    $easter_o = (22 + $d + $e) . "-3-" . $year;
  $easter_o_stamp = strtotime($easter_o);
  $easter_stamp = strtotime("+13 days", $easter_o_stamp);
  return $easter_stamp;
}


/**
 * Sort Nodes by their position in the node tree.
 *
 * Use it like this:
 *
 *    ${q(node).children().sortRecursive(['ASC'|'DESC'])}
 */
class FilterSermonsByDateOperation extends AbstractOperation
{
  /**
   * {@inheritdoc}
   */
  protected static $shortName = 'filtterSermonsByDate';

  /**
   * {@inheritdoc}
   */
  protected static $priority = 100;

  /**
   * {@inheritdoc}
   *
   * We can only handle CR Nodes.
   */
  public function canEvaluate($context)
  {
    return (isset($context[0]) && ($context[0] instanceof NodeInterface));
  }

  /**
   * {@inheritdoc}
   *
   * @return void
   */
  public function evaluate(FlowQuery $flowQuery, array $arguments)
  {
    $date = $arguments[0] ?? date('Ymd');
    $date = date('Ymd', strtotime("-13 days", strtotime($date)));
    $dateStampO = strtotime($date);
    $dateStamp = strtotime("+13 days", $dateStampO);
    $dayOfWeekNumber = date('N', $dateStamp) % 7;
    $year = date("Y", $dateStamp);

    $easterStamp = easter($year);
    if ($easterStamp > $dateStamp) {
      $year = $year - 1;
      $easterStamp = easter($year);
    }
    $nextEasterStamp = easter($year + 1);

    $week = datediff('ww', $easterStamp, $dateStamp, true) + 1;

    $mondayAfterProsv = $this->getDayAfter('19-01-' . ($year + 1), 1, 0, 1); //NB: Weird stuff, but if Prosv is on Monday, the reset should occur the same day

    $weekToEaster = datediff('ww', $nextEasterStamp, $dateStamp, true) + 1;

    if ($week > 40 || $dateStamp >= $mondayAfterProsv) {
      $week = 50 + $weekToEaster;
    }

    $dayweek = $week . ';' . $dayOfWeekNumber;

    $nodes = $flowQuery->getContext();


    $todayDateSlashy = date('d/m', $dateStampO);
    $filteredNodes = array_filter($nodes, function ($node) use ($dateStampO, $todayDateSlashy, $dayweek) {
      $magicDate = $node->getProperty('magicDate');
      if ($magicDate) {
        if (strstr($magicDate, ";")) {
          if ($dayweek == $magicDate) {
            return true;
          }
        } else {
          $magicDate = $this->getKey($magicDate, $dateStampO);
          if ($todayDateSlashy == $magicDate) {
            return true;
          }
        }
      }
      return false;
    });

    $flowQuery->setContext($filteredNodes);
  }


  protected function getKey($key, $dStamp)
  {
    $d_Y = date('Y', $dStamp); //YEAR, OC

    if ($key == '25/12+0' || $key == '25/12+6') {
      if (date('m', $dStamp) == '01') //if december
        $d_Y--;
    }
    if ($key == '06/01-6' || $key == '06/01-0') {
      if (date('m', $dStamp) == '12') //if december
        $d_Y++;
    }
    if (preg_match("/(\d\d\/\d\d)(.)?(\w)?#?(\d)?/u", $key, $out)) {
      $shDateO = str_replace("/", "-", $out['1']) . "-" . $d_Y; //date, OC, with slashes
      $sh_sign = $out['2'] ?? null; //operation sign
      $shDayn = $out['3'] ?? null; //day number,0 - sunday
      $shTimes = $out['4'] ?? null;
      $shDateStamp = strtotime('+13 days', strtotime($shDateO)); //timestamp NC
      $shDate = date('d-m-Y', $shDateStamp); //NC date
      switch ($sh_sign) {
        case '+':
          $res_key = date('d/m', strtotime('-13 days', $this->getDayAfter($shDate, $shDayn, $shTimes))); //OC, key
          break;
        case '-':
          $res_key = date('d/m', strtotime('-13 days', $this->getDayBefore($shDate, $shDayn, $shTimes))); //OC, key
          break;
        case '~':
          $res_key = date('d/m', strtotime('-13 days', $this->getDayNearest($shDate, $shDayn))); //OC, key
          break;
        case '':
          $res_key = date('d/m', strtotime($shDateO)); //OC, key
          break;
      }
      return $res_key;
    }
  }

  protected function getDayAfter($date, $dayNumber = 1, $shTimes = 0, $noJumpIfSameDay = 0)
  {
    $day_stamp = strtotime($date);
    $currentDayNumber = date('N', $day_stamp);
    if ($currentDayNumber == 7)
      $currentDayNumber = 0;
    if ($currentDayNumber < $dayNumber) {
      $shiftToDay = $dayNumber - $currentDayNumber;
    } else if ($currentDayNumber == $dayNumber) {
      if ($noJumpIfSameDay == 0) {
        $shiftToDay = 7;
      }
    } else if ($currentDayNumber > $dayNumber) {
      $shiftToDay = 7 - $currentDayNumber + $dayNumber;
    }
    $day_after = strtotime('+' . $shiftToDay . ' day', $day_stamp);
    return $day_after;
  }

  protected function getDayBefore($date, $dayNumber = 1, $shTimes = 0)
  {
    $day_stamp = strtotime($date);
    $currentDayNumber = (int) date('N', $day_stamp);
    if ($currentDayNumber == 7) {
      $currentDayNumber = 0;
    }

    if ($dayNumber === 'w') {
      if ($currentDayNumber == 0) {
        $shiftToDay = 2;
      } else if ($currentDayNumber == 6) {
        $shiftToDay = 1;
      } else {
        $shiftToDay = 0;
      }
    } else {
      if ($currentDayNumber > $dayNumber) {
        $shiftToDay = $currentDayNumber - $dayNumber;
      } else if ($currentDayNumber == $dayNumber) {
        $shiftToDay = 7;
      } else if ($currentDayNumber < $dayNumber) {
        $shiftToDay = $currentDayNumber + 7 - $dayNumber;
      }
    }
    //additional shift of weeks
    $shiftToDay = $shiftToDay + $shTimes * 7;

    $day_after = strtotime('-' . $shiftToDay . ' day', $day_stamp);
    return $day_after;
  }

  protected function getDayNearest($date, $dayNumber = 1)
  {
    $day_stamp = strtotime($date);
    $dayBefore = $this->getDayBefore($date, $dayNumber);
    $dayAfter = $this->getDayAfter($date, $dayNumber);
    if (2 * $day_stamp > $dayBefore + $dayAfter) {
      return $dayAfter;
    } else if (2 * $day_stamp < $dayBefore + $dayAfter) {
      return $dayBefore;
    } else if (2 * $day_stamp == $dayBefore + $dayAfter) {
      return $day_stamp;
    }
  }
}
