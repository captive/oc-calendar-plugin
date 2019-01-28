<?php namespace Captive\Calendar;

use Backend;
use System\Classes\PluginBase;

/**
 * Calendar Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'captive.calendar::lang.plugin.name',
            'description' => 'captive.calendar::lang.plugin.description',
            'author'      => 'Captive Audience Inc.',
            'icon'        => 'icon-calendar'
        ];
    }
}
