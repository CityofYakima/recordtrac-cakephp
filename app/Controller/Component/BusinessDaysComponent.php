<?php

App::uses('Component', 'Controller');
class BusinessDaysComponent extends Component {

  function add_business_days($days=0, $date="TODAY", $format="c") {
    // CREATE YOUR ARRAY OF HOLIDAYS
    $holidays = array();
    $november = strtotime(date('Y') . '-11-0');
    $january  = strtotime(date('Y') . '-01-0');
    $nextyear = mktime(0,0,0, 1, 1, date('Y') + 1);
    $holidays['Dr_M_L_King']  = date('r', strtotime('Third Monday', $january));
    $holidays['Independence'] = date('r', strtotime(date('Y') . '-07-04'));
    $holidays['Thanksgiving'] = date('r', strtotime('Fourth Thursday', $november));
    $holidays['Christmas']    = date('r', strtotime(date('Y') . '-12-25'));
    $holidays['NewYear']      = date('r', $nextyear);
    
    // ACTIVATE THIS TO SEE THE HOLIDAYS
    // print_r($holidays);
    
    // INTERPRET THE INPUTS INTO TIMESTAMPS
    $days = round($days);
    if ($days < 0) return FALSE;
    if (!$current   = strtotime($date)) return FALSE;
    if (!$timestamp = strtotime("$date $days DAYS")) return FALSE;
    
    // PAD THE FUTURE TO ALLOW ROOM FOR THE WEEKENDS
    $weeks     = $days * 2 + 2;
    $timestamp = strtotime("$date $weeks DAYS");
    
    // MAKE AN ARRAY OF FUTURE TIMESTAMPS AND RFC2822 DATES
    $arr = range($current, $timestamp, 86400);
    $arr = array_flip($arr);
    foreach ($arr as $timestamp_key => $nothing)
    {
      // ASSIGN RFC2822 DATE STRINGS TO EACH TIMESTAMP
      $arr[$timestamp_key] = date('r', $timestamp_key);
  
      // REMOVE THE DAY FROM THE ARRAY IF IT IS A HOLIDAY OR WEEKEND DAY
      if (in_array($arr[$timestamp_key], $holidays)) $arr[$timestamp_key] = 'S';
      if (substr($arr[$timestamp_key],0,1) == 'S') unset($arr[$timestamp_key]);
    }
    
    // RECAST THE ARRAY KEYS INTO OFFSETS FROM THE STARTING DATE
    $arr = array_values($arr);
    
    // RETURN THE FUTURE DATE ACCORDING TO THE REQUESTED FORMAT
    return date($format, strtotime($arr[$days]));
  }
}
//echo add_business_days($days=3, $date="TODAY", $format="c");