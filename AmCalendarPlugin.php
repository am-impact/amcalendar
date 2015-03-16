<?php
/**
 * @package   Am Calendar
 * @author    Hubert Prein
 */
namespace Craft;

class AmCalendarPlugin extends BasePlugin
{
    public function getName()
    {
         return 'a&m calendar';
    }

    public function getVersion()
    {
        return '1.0.0';
    }

    public function getDeveloper()
    {
        return 'a&m impact';
    }

    public function getDeveloperUrl()
    {
        return 'http://www.am-impact.nl';
    }

    /**
     * Display command palette settings.
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('amCalendar/settings', array(
            'settings' => $this->getSettings(),
            'fields' => craft()->amCalendar->getFields()
        ));
    }

    public function addTwigExtension()
    {
        Craft::import('plugins.amcalendar.twigextensions.AmCalendarTwigExtension');
        return new AmCalendarTwigExtension();
    }

    /**
     * Plugin settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'section' => array(AttributeType::Mixed, 'default' => '*'),
            'multipleDates' => array(AttributeType::Bool, 'default' => false),
            'datesField' => array(AttributeType::String, 'default' => ''),
            'dateHandle' => array(AttributeType::String, 'default' => '')
        );
    }
}