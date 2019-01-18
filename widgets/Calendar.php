<?php namespace Captive\CalendarWidget\Widgets;

use Db;
use Log;
use Html;
use Lang;
use Backend;
use DbDongle;
use Carbon\Carbon;
use October\Rain\Html\Helper as HtmlHelper;
use October\Rain\Router\Helper as RouterHelper;
use System\Helpers\DateTime as DateTimeHelper;
use System\Classes\PluginManager;
use Backend\Classes\ListColumn;
use Backend\Classes\WidgetBase;
use October\Rain\Database\Model;
use ApplicationException;

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

    public $recordTitle;

    public $recordStart;

    public $recordEnd;

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
            'recordTitle',
            'recordStart',
            'recordEnd',
            'availableDisplayModes'
        ]);

    }

    public function render($options = null)
    {
        // $this->prepareVars();
        $extraVars = [];
        return $this->makePartial(static::PARTIAL_FILE, $extraVars);
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

