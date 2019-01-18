<?php namespace Captive\CalendarWidget\Widgets;

use Db;
use Log;
use Html;
use Lang;
use Backend;
use DbDongle;
use Response;
use Carbon\Carbon;
use October\Rain\Html\Helper as HtmlHelper;
use October\Rain\Router\Helper as RouterHelper;
use System\Helpers\DateTime as DateTimeHelper;
use System\Classes\PluginManager;
use Backend\Classes\ListColumn;
use Backend\Classes\WidgetBase;
use October\Rain\Database\Model;
use ApplicationException;
use Captive\CalendarWidget\Classes\Event as EventData;

class Calendar extends WidgetBase
{
    const PARTIAL_FILE = 'calendar';
    /**
     * @var Model Form model object.
     */
    public $model;

    /**
     * @var string Link for each record row. Replace :id with the record id.
     */
    public $recordUrl;

    /**
     * @var string Click event for each record row. Replace :id with the record id.
     */
    public $recordOnClick;

    public $recordId;
    public $recordTitle;

    public $recordStart;

    public $recordEnd;

    private $displayModeDictionary = [
        'month'=> 'month',
        'week' => 'agendaWeek',
        'day'  => 'agendaDay',
        'list' => 'listMonth'
    ];

    public $availableDisplayModes = [];

    /**
     * @var string The context of this form, fields that do not belong
     * to this context will not be shown.
     */
    public $context;

    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'calendar';

    /**
     * @var string Active session key, used for editing forms and deferred bindings.
     */
    public $sessionKey;

    /**
     * @var bool Render this form with uneditable preview data.
     */
    public $previewMode = false;

    /**
     * @var \Backend\Classes\WidgetManager
     */
    protected $widgetManager;

    public $searchTerm;
    public $searchMode;
    public $searchScope;

    /**
     * @inheritDoc
     */
    public function init()
    {
        //model/xxx/calendar.yaml
        $this->fillFromConfig([
            'recordUrl',
            'recordOnClick',
            'recordId',
            'recordTitle',
            'recordStart',
            'recordEnd',
            'availableDisplayModes'
        ]);
        $calendarControlRight = [];
        foreach ($this->availableDisplayModes as $modeKey) {
            if(array_key_exists($modeKey, $this->displayModeDictionary)){
                $calendarControlRight[] = $this->displayModeDictionary[$modeKey];
            }
        }
        $this->availableDisplayModes = implode(",", $calendarControlRight);

    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        // $this->addCss('css/fullcalendar.css', '4.0.0-alpha.4');
        $this->addCss(['css/fullcalendar.css' ,'less/calendar.less'], 'core');
        $this->addJs('js/fullcalendar.js', '4.0.0-alpha.4');
        $this->addJs('js/october.calendar.js', 'core');
    }

    public function render($options = null)
    {
        // $this->prepareVars();
        $extraVars = [];
        return $this->makePartial(static::PARTIAL_FILE, $extraVars);
    }


    public function onFetchEvents()
    {
        $startTime = post('startTime');
        $endTime = post('endTime');

        $records = $this->config->modelClass::select($this->recordId, $this->recordTitle, $this->recordStart, $this->recordEnd)->get();
        $list = [];
        foreach ($records as $record) {
            $id = $record->{$this->recordId};
            $eventData = new EventData([
                'id' => $id,
                'url' => str_replace(':id', $id, $this->recordUrl),
                'title' => $record->{$this->recordTitle},
                'start' => $record->{$this->recordStart},
                'end' => $record->{$this->recordEnd}
            ]);
            $list[] = $eventData->toArray();
        }
        traceLog($list);

        return Response::json([
            'events' => $list
        ]);
    }

    // search

    /**
     * Applies a search term to the list results, searching will disable tree
     * view if a value is supplied.
     * @param string $term
     */
    public function setSearchTerm($term)
    {
        $this->searchTerm = $term;
    }

    /**
     * Applies a search options to the list search.
     * @param array $options
     */
    public function setSearchOptions($options = [])
    {
        extract(array_merge([
            'mode' => null,
            'scope' => null
        ], $options));

        $this->searchMode = $mode;
        $this->searchScope = $scope;
    }

}

