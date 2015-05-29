<?php
/**
 * Created by PhpStorm.
 * User: siemek
 * Date: 5/18/15
 * Time: 4:44 PM
 */

class Pos_Date extends DateTime
{
    protected $_year;
    protected $_month;
    protected $_day;

    public function __construct($timeZone = null)
    {
        if ($timeZone) {
            parent::__construct('now', $timeZone);
        } else {
            parent::__construct('now');
        }

        $this->_year = (int)$this->format('Y');
        $this->_month = (int)$this->format('n');
        $this->_day = (int)$this->format('j');
    }

    public function setDate($year, $month, $day)
    {
        if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
            throw new Exception('setDate() expects three numbers spearated by commas in the order: year, month, day');
        }
        if (!checkdate($month, $day, $year)) {
            throw new Exception('Non-existent date.');
        }
        parent::setDate($year, $month, $day);
        $this->_year = (int)$year;
        $this->_month = (int)$month;
        $this->_day = (int)$day;
    }

    public function setMDY($USDate)
    {
        $dateParts = preg_split('{[-/ :.]}', $USDate);
        if (!is_array($dateParts) || count($dateParts) != 3) {
            throw new Exception('setMDY() expect a date as "MM/DD/YYYY.');
        }
        $this->setDate($dateParts[2], $dateParts[0], $dateParts[1]);
    }

    public function setDMY($EuroDate)
    {
        $dateParts = preg_split('{[-/ :.]}', $EuroDate);
        if (!is_array($dateParts) || count($dateParts) != 3) {
            throw new Exception('setMDY() expect a date as "DD/MM/YYYY.');
        }
        $this->setDate($dateParts[2], $dateParts[1], $dateParts[0]);
    }

    public function setrFromMySQL($MySQL)
    {
        $dateParts = preg_split('{[-/ :.]}', $MySQL);
        if (!is_array($dateParts) || count($dateParts) != 3) {
            throw new Exception('setMDY() expect a date as "YYYY-MM-DD".');
        }
        $this->setDate($dateParts[0], $dateParts[1], $dateParts[2]);
    }

    public function getMDY($leadingZeros = false)
    {
        if ($leadingZeros) {
            return $this->format('m/d/Y');
        } else {
            return $this->format('n/j/Y');
        }
    }

    public function getDMY($leadingZeros = false)
    {
        if ($leadingZeros) {
            return $this->format('d/m/Y');
        } else {
            return $this->format('j/n/Y');
        }
    }

    public function getMySQLFormat()
    {
        return $this->format('Y-m-d');
    }

    public function addDays($numDays)
    {
        if (!is_numeric($numDays) || $numDays < 1) {
            throw new Exception('addDays() expects a positive integer.');
        }
        parent::modify('+', intval($numDays) . ' days.');
    }

    public function subDays($numDays)
    {
        if (!is_numeric($numDays)) {
            throw new Exception('subDays() expects an integer.');
        }
        parent::modify('+', intval($numDays) . ' days.');
    }

    public function addWeeks($numWeeks)
    {
        if (!is_numeric($numWeeks)) {
            throw new Exception('addWeeks() needs to be integer.');
        }
        parent::modify('+', intval($numWeeks) . ' weeks.');
    }

    public function subWeeks($numWeeks)
    {
        if (!is_numeric($numWeeks)) {
            throw new Exception('subWeeks() expects an integer.');
        }
        parent::modify('+', intval($numWeeks) . ' weeks.');
    }

    public function addMonths($numMonths)
    {
        if (!is_numeric($numMonths) || $numMonths < 1) {
            throw new Exception('addMonths() expect a positive integer.');
        }
        $numMonths = (int)$numMonths;
        $newValue = $this->_month + $numMonths;
        if ($newValue <= 12) {
            $this->_month = $newValue;
        } else {
            $notDecember = $newValue % 12;
            if ($notDecember) {
                $this->_year += floor($newValue / 12);
            } else {
                $this->_month = 12;
                $this->_year += ($newValue / 12) - 1;
            }
        }
        $this->checkLastDayOfMonth();
        parent::setDate($this->_year, $this->_month, $this->_day);
    }

    public function subMonths($numMonths)
    {
        if (!is_numeric($numMonths)) {
            throw new Exception('sumMonths() expect an integer.');
        }
        $numMonths = abs(intval($numMonths));
        $newValue = $this->_month - $numMonths;
        if ($newValue > 0) {
            $this->_month = $newValue;
        } else {
            $months = range(12, 1);
            $newValue = abs($newValue);
            $monthPosition = $newValue % 12;
            $this->_month = $months[$monthPosition];
            if ($monthPosition) {
                $this->_year -= ceil($newValue / 12);
            } else {
                $this->_year -= ceil($newValue / 12);
            }
            $this->checkLastDayOfMonth();
            parent::setDate($this->_year, $this->_month, $this->_day);
        }
    }

    final protected function checkLastDayOfMonth()
    {
        if (!checkdate($this->_month, $this->_day, $this->_year)) {
            $use30 = array(4, 6, 9, 11);
            if (in_array($this->_month, $use30)) {
                $this->_day = 30;
            } else {
                parent::setDate($this->_year, $this->_month, $this->_day);
            }
        }
    }

    public function isLeap()
    {
        if ($this->_year % 400 == 0 || ($this->_year % 4 == 0 && $this->_year % 100 != 0)) {
            return true;
        } else {
            return false;
        }
    }

    public function addYears($numYears)
    {
        if (!is_numeric($numYears) || $numYears < 1) {
            throw new Exception('addYears() expects an positive integer.');
        }
        $this->_year += $numYears;
        $this->checkLastDayOfMonth();
        parent::setDate($this->_year, $this->_month, $this->_day);
    }

    public function subYears($nunYears)
    {
        if (!is_numeric($numYears)) {
            throw new Exception('subYears expects an integer. ');
        }
        $this->_year -= abs(intval($numYears));
        $this->checkLastDayOfMonth();
        parent::setDate($this->_year, $this->_month, $this->_day);
    }
    static public function dateDiff(Pos_Date $startDate, Pos_Date $endDate)
    {
        $start = gmmktime(0, 0, 0, $startDate->_month,$startDate->_day, $startDate->_year);
        $end = gmmktime(0, 0, 0, $endDate->_month, $endDate->_day, $endDate->_year);
        return ($end - $start) / (60 * 60 * 24);
    }
}