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
            'eventsForDate' => new \Twig_Filter_Method($this, 'eventsForDate'),
            'firstEvent' => new \Twig_Filter_Method($this, 'firstEvent')
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

    /**
     * Get the first event.
     *
     * @param array $events
     *
     * @return bool|array
     */
    public function firstEvent($events)
    {
        ksort($events);
        foreach ($events as $year => $months) {
            ksort($months);
            foreach ($months as $month => $days) {
                ksort($days);
                foreach ($days as $key => $dayEvents) {
                    return count($dayEvents) ? $dayEvents[0] : false;
                }
            }
        }
        return false;
    }
}