<?php namespace Captive\CalendarWidget;

use Backend;
use System\Classes\PluginBase;

/**
 * CalendarWidget Plugin Information File
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
            'name'        => 'CalendarWidget',
            'description' => 'No description provided yet...',
            'author'      => 'Captive Audience Inc.',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

}
