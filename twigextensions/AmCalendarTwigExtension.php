<?php
namespace Craft;

class AmCalendarTwigExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'Events for date';
    }

    public function getFilters()
    {
        return array(
            'eventsForDate' => new \Twig_Filter_Method($this, 'eventsForDate')
        );
    }

    /**
     * Get events from the array.
     *
     * @param array $events
     * @param int   $year
     * @param int   $month
     * @param int   $day
     *
     * @return bool|array
     */
    public function eventsForDate($events, $year, $month, $day)
    {
        if (isset($events[$year][$month][$day])) {
            return $events[$year][$month][$day];
        }
        else {
            return false;
        }
    }
}