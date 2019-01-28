<?php return [
    'plugin' => [
        'name'        => 'Calendar Widget',
        'description' => 'Provides the Calendar widget and Calendar controller behavior for OctoberCMS',
    ],
    'behaviors' => [
        'calendar' => [
            'title' => 'Calendar',
            'behavior_not_ready' => 'Calendar behavior has not been initialized, check that you have called makeCalendar() in your controller.',
        ],
    ],
];
