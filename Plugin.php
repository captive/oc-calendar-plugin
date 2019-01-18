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
            'author'      => 'Captive',
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

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Captive\CalendarWidget\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'captive.calendarwidget.some_permission' => [
                'tab' => 'CalendarWidget',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'calendarwidget' => [
                'label'       => 'CalendarWidget',
                'url'         => Backend::url('captive/calendarwidget/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['captive.calendarwidget.*'],
                'order'       => 500,
            ],
        ];
    }
}
