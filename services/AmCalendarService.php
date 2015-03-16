<?php
namespace Craft;

class AmCalendarService extends BaseApplicationComponent
{
    /**
     * Get available fields to indicate the multiple dates field.
     *
     * @return array
     */
    public function getFields()
    {
        $fields = array();
        $availableFields = craft()->fields->getAllFields();
        foreach ($availableFields as $availableField) {
            $fields[ $availableField->handle ] = $availableField->name;
        }
        return $fields;
    }

    /**
     * Get calendar grid.
     *
     * @param mixed $fromDate
     * @param mixed $toDate
     *
     * @return array
     */
    public function getGrid($fromDate = 'now', $toDate = 'last day of')
    {
        $months = array();

        $fromYear = date('Y', $this->_createTimestamp($fromDate));
        $toYear = date('Y', $this->_createTimestamp($toDate));
        $fromMonth = date('n', $this->_createTimestamp($fromDate));
        $toMonth = date('n', $this->_createTimestamp($toDate));

        for ($year = $fromYear; $year <= $toYear; $year++) {
            if ($year == $fromYear) {
                if ($toYear > $fromYear) {
                    for ($month = $fromMonth; $month <= 12; $month++) {
                        $this->_createMonthInformation($months, $year, $month);
                    }
                }
                else {
                    for ($month = $fromMonth; $month <= $toMonth; $month++) {
                        $this->_createMonthInformation($months, $year, $month);
                    }
                }
            }
            elseif ($year == $toYear) {
                for ($month = 1; $month <= $toMonth; $month++) {
                    $this->_createMonthInformation($months, $year, $month);
                }
            }
            else {
                for ($month = 1; $month <= 12; $month++) {
                    $this->_createMonthInformation($months, $year, $month);
                }
            }
        }
        return $months;
    }

    /**
     * Get calendar events.
     *
     * @param mixed $fromDate
     * @param mixed $toDate   [Optional] Set to false to get all events using the fromDate only.
     *
     * @return array
     */
    public function getEvents($fromDate = 'now', $toDate = 'last day of')
    {
        // Get plugin settings
        $plugin = craft()->plugins->getPlugin('amcalendar');
        $settings = $plugin->getSettings();

        // Set variables
        $events = array();
        $fromTimestamp = $this->_createTimestamp($fromDate);
        if ($toDate !== false) {
            $endTimestamp = $this->_createTimestamp($toDate);
        }
        $findMultipleDates = $settings->multipleDates && $settings->datesField != '' && $settings->dateHandle != '';

        // Get entries after current timestamp
        $criteria = craft()->elements->getCriteria(ElementType::Entry);
        if (! $findMultipleDates) {
            // We use postDate instead of a field for the dates
            $criteria->after = $fromTimestamp;
            $criteria->before = $endTimestamp;
        }
        if ($settings->section !== '*') {
            // Get from given section only!
            $criteria->sectionId = $settings->section;
        }
        $criteria->status = array('live', 'pending');
        $criteria->limit = null;
        $criteria->order = 'title asc';
        $entries = $criteria->find();

        // Add entries to proper year and month
        foreach ($entries as $entry) {
            // Use postDate or a field?
            if ($findMultipleDates) {
                if ($entry->{$settings->datesField} instanceof ElementCriteriaModel) {
                    $fieldData = $entry->{$settings->datesField}->find();

                    foreach ($fieldData as $availability) {
                        $timestamp = strtotime($availability->{$settings->dateHandle}->__toString());

                        if ($timestamp >= $fromTimestamp && ($toDate === false || ($toDate !== false && $timestamp <= $endTimestamp))) {
                            $events[ date('Y', $timestamp) ][ date('n', $timestamp) ][ date('j', $timestamp) ][] = $entry;
                        }
                    }
                }
            }
            else {
                // Because we used after and before, we don't have to validate the time anymore
                $timestamp = strtotime($entry->postDate->__toString());
                $events[ date('Y', $timestamp) ][ date('n', $timestamp) ][ date('n', $timestamp) ][] = $entry;
            }
        }
        return $events;
    }

    /**
     * Get all calendar events.
     *
     * @param mixed $fromDate
     *
     * @return array
     */
    public function getAllEvents($fromDate = 'now')
    {
        $events = $this->getEvents($fromDate, false);

        if (count($events)) {
            $fromYear = $this->_getYearOrMonth($events);
            $toYear = $this->_getYearOrMonth($events, true, false);
            $fromMonth = $this->_getYearOrMonth($events, false);
            $toMonth = $this->_getYearOrMonth($events, false, false);

            if (! $fromYear) {
                $fromYear = date('Y');
            }
            if (! $toYear) {
                $toYear = date('Y');
            }
            if (! $fromMonth) {
                $fromMonth = date('n');
            }
            if (! $toMonth) {
                $toMonth = date('n');
            }

            $grid = $this->getGrid(mktime(0, 0, 0, $fromMonth, 1, $fromYear), mktime(0, 0, 0, $toMonth, 1, $toYear));
        }
        else {
            $grid = array();
        }

        return array(
            'grid' => $grid,
            'events' => $events
        );
    }

    /**
     * Create a timestamp.
     *
     * @param mixed $date
     *
     * @return int
     */
    private function _createTimestamp($date)
    {
        if ($date === 'now') {
            return strtotime('today midnight');
        }
        elseif (is_numeric($date)) {
            return (int) $date;
        }
        else {
            return strtotime($date);
        }
    }

    /**
     * Create month information for given year and month.
     *
     * @param array &$months
     * @param int   $year
     * @param int   $month
     */
    private function _createMonthInformation(&$months, $year, $month)
    {
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        $dateTime = new DateTime('@'.$timestamp);
        $timestampInformation = getdate($timestamp);

        $months[$year][$month] = array(
            'timestamp' => $timestamp,
            'dateTime' => $dateTime,
            'firstDay' => $timestampInformation['wday'],
            'lastDay' => $dateTime->format('t')
        );
    }

    /**
     * Get a year or month from an events array.
     *
     * @param array $events
     * @param bool  $findYear
     * @param bool  $findFirst
     *
     * @return bool|int
     */
    private function _getYearOrMonth($events, $findYear = true, $findFirst = true)
    {
        if ($findFirst) {
            ksort($events);
        }
        else {
            krsort($events);
        }
        foreach ($events as $year => $months) {
            if ($findYear) {
                return $year;
            }
            else {
                if ($findFirst) {
                    ksort($months);
                }
                else {
                    krsort($months);
                }
                foreach ($months as $month => $days) {
                    return $month;
                }
            }
        }
        return false;
    }
}