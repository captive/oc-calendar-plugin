<?php namespace Captive\Calendar\Behaviors;

use Str;
use Lang;
use Backend\Widgets\Filter as FilterWidget;
use Backend\Widgets\Toolbar as ToolbarWidget;
use Captive\Calendar\Widgets\Calendar as CalendarWidget;
use Backend\Classes\ControllerBehavior;
use ApplicationException;

class CalendarController extends ControllerBehavior
{
    /**
     * @var ToolbarWidget Reference to the toolbar widget instance
     */
    protected $toolbarWidget = null;

    /**
     * @var FilterWidget Reference to the filter widget instance
     */
    protected $filterWidget = null;

    /**
     * @var CalendarWidget
     */
    protected $calendarWidget = null;

    /**
     * @var Model The initialized model used by the behavior.
     */
    protected $model;

    /**
     * @var string The primary calendar alias to use, default 'calendar'
     */
    protected $primaryDefinition = 'calendar';

    /**
     * @var array Configuration values that must exist when applying the primary config file.
     * - modelClass: Class name for the model
     * - searchList: list field definitions for the search widget
     */
    protected $requiredConfig = ['modelClass', 'searchList'];

    /**
     * Behavior constructor
     * @param \Backend\Classes\Controller $controller
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        // Build the configuration
        $this->config = $this->makeConfig($controller->calendarConfig, $this->requiredConfig);
        $this->config->modelClass = Str::normalizeClassName($this->config->modelClass);
    }

    /**
     * Calendar Controller action.
     *
     * @return void
     */
    public function calendar()
    {
        $this->controller->pageTitle = $this->controller->pageTitle ? : Lang::get($this->getConfig(
            'title',
            'captive.calendar::lang.behaviors.calendar.title'
        ));
        $this->controller->bodyClass = 'slim-container';
        $this->makeCalendar();
    }

    /**
     * Creates the Calendar widget used by this behavior
     *
     * @return CalendarWidget
     */
    public function makeCalendar()
    {
        $model = $this->controller->calendarCreateModelObject();

        $config = $this->config;
        $config->model = $model;
        $config->alias = $this->primaryDefinition;

        // Initialize the Calendar widget
        $widget = $this->makeWidget(CalendarWidget::class, $config);
        $widget->model = $model;
        $widget->bindToController();
        $this->calendarWidget = $widget;

        // Initialize the Toolbar & Filter widgets
        $this->initToolbar($config, $widget);
        $this->initFilter($config, $widget);

        return $widget;
    }

    /**
     * Prepare the Toolbar widget if necessary
     *
     * @param object $config
     * @param CalendarWidget $widget
     * @return void
     */
    protected function initToolbar($config, $widget)
    {
        if (empty($config->toolbar)) {
            return;
        }

        // Prepare the config and intialize the Toolbar widget
        $toolbarConfig = $this->makeConfig($config->toolbar);
        $toolbarConfig->alias = $widget->alias . 'Toolbar';
        $toolbarWidget = $this->makeWidget(ToolbarWidget::class, $toolbarConfig);
        $toolbarWidget->bindToController();
        $toolbarWidget->cssClasses[] = 'list-header';

        /*
         * Link the Search widget to the Calendar widget
         */
        if ($searchWidget = $toolbarWidget->getSearchWidget()) {
            $searchWidget->bindEvent('search.submit', function () use ($widget, $searchWidget) {
                $widget->setSearchTerm($searchWidget->getActiveTerm());
                return $widget->onRefresh();
            });

            $widget->setSearchOptions([
                'mode' => $searchWidget->mode,
                'scope' => $searchWidget->scope,
            ]);

            // Find predefined search term
            $widget->setSearchTerm($searchWidget->getActiveTerm());
        }

        $this->toolbarWidget = $toolbarWidget;
    }

    /**
     * Prepare the Filter widget if necessary
     *
     * @param object $config
     * @param CalendarWidget $widget
     * @return void
     */
    protected function initFilter($config, $widget)
    {
        if (empty($config->filter)) {
            return;
        }

        $widget->cssClasses[] = 'list-flush';

        // Prepare the config and intialize the Toolbar widget
        $filterConfig = $this->makeConfig($config->filter);
        $filterConfig->alias = $widget->alias . 'Filter';
        $filterWidget = $this->makeWidget(FilterWidget::class, $filterConfig);
        $filterWidget->bindToController();

        /*
         * Filter the Calendar when the scopes are changed
         */
        $filterWidget->bindEvent('filter.update', function () use ($widget, $filterWidget) {
            return $widget->onFilter();
        });

        // Apply predefined filter values
        $widget->addFilter([$filterWidget, 'applyAllScopesToQuery']);
        $this->filterWidget = $filterWidget;
    }

    /**
     * Creates a new instance of a calendar model. This logic can be changed by overriding it in the controller.
     *
     * @return Model
     */
    public function calendarCreateModelObject()
    {
        $class = $this->config->modelClass;
        return new $class;
    }

    /**
     * Render the calendar widget
     */
    public function calendarRender($options = [])
    {
        if (empty($this->calendarWidget)) {
            throw new ApplicationException(Lang::get('captive.calendar::lang.behaviors.calendar.behavior_not_ready'));
        }

        if (!empty($options['readOnly']) || !empty($options['disabled'])){
            $this->calendarWidget->previewMode = true;
        }

        if (isset($options['preview'])) {
            $this->calendarWidget->previewMode = $options['preview'];
        }

        return $this->calendarMakePartial('container', [
            'toolbar'  => $this->toolbarWidget,
            'filter'   => $this->filterWidget,
            'calendar' => $this->calendarWidget,
        ]);
    }

    /**
     * Render the requested partial, providing opportunity for the controller to take over
     *
     * @param string $partial
     * @param array $params
     * @return string
     */
    public function calendarMakePartial($partial, $params = [])
    {
        $contents = $this->controller->makePartial('calendar_' . $partial, $params, false);
        if (!$contents) {
            $contents = $this->makePartial($partial, $params);
        }
        return $contents;
    }
}
