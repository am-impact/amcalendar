<?php
namespace Craft;

class AmCalendarVariable
{
    /**
     * Get calendar grid.
     *
     * @param mixed $fromDate A timestamp or date. Default is current month (now).
     * @param mixed $toDate A timestamp or date. Default is last day of month (last day of).
     *
     * @return array
     */
    public function getGrid($fromDate = 'now', $toDate = 'last day of')
    {
        return craft()->amCalendar->getGrid($fromDate, $toDate);
    }

    /**
     * Get calendar events.
     *
     * @param mixed $fromDate A timestamp or date. Default is current month (now).
     * @param mixed $toDate A timestamp or date. Default is last day of month (last day of).
     *
     * @return array
     */
    public function getEvents($fromDate = 'now', $toDate = 'last day of')
    {
        return craft()->amCalendar->getEvents($fromDate, $toDate);
    }

    /**
     * Get all calendar events.
     *
     * This function will return the calendar grid as well, based on found events.
     *
     * @param mixed $fromDate
     *
     * @return array
     */
    public function getAllEvents($fromDate = 'now')
    {
        return craft()->amCalendar->getAllEvents($fromDate);
    }
}