
+ function ($) {
    "use strict";
    const Base = $.oc.foundation.base,
        BaseProto = Base.prototype;

    const Calendar = function (element, options) {
        this.options = options;
        this.$el = $(element);
        this.calendarControl =  null;
        this.$loadContainer = this.$el.find('.loading-indicator-container:first');

        $.oc.foundation.controlUtils.markDisposable(element)
        Base.call(this)
        this.init()
    }

    Calendar.prototype = Object.create(BaseProto)
    Calendar.prototype.constructor = Calendar

    Calendar.DEFAULTS = {
        alias: null,
        displayModes: 'month',
        editable: false,
    }

    Calendar.prototype.init = function () {

        let self = this;
        $(document).ready(function () {
            self.initCalendarControl();
        });

        this.$el.on('dispose-control', this.proxy(this.dispose));
        $(document).on('ajaxComplete', this.proxy(this.onFilterUpdate));

    };

    Calendar.prototype.dispose = function () {

        $(document).off('ajaxComplete', this.proxy(this.onFilterUpdate));
        this.$el.off('dispose-control', this.proxy(this.dispose));
        this.$el.removeData('oc.calendar');

        this.$el = null
        this.options = null
        BaseProto.dispose.call(this)
    }
    // Deprecated
    Calendar.prototype.unbind = function () {
        this.dispose()
    }

    Calendar.prototype.initCalendarControl = function(){
        const $calendar = this.$el.find('.calendar-control');
        const self = this;
        const timezone = $('meta[name="backend-timezone"]').attr('content');
        this.calendarControl = new FullCalendar.Calendar($calendar[0], {
            header: {
                left: 'prev,next today',
                center: 'title',
                right: this.options.displayModes
            },
            timeZone: timezone,
            timeZoneImpl: 'moment-timezone',
            titleFormat: {
                month: 'short',
                year: 'numeric',
                day: 'numeric',
                weekday: 'long'
            },
            navLinks: true, // can click day/week names to navigate views

            weekNumbers: true,
            weekNumbersWithinDays: true,

            editable: this.options.editable,
            eventLimit: true, // allow "more" link when too many events

            firstDay: 0,
            eventClick: function(info){
                self.onEventClick(info);
            },
            events: function (fetchInfo, successCallback, failureCallback){
                self.onPrevNextButtonClick(fetchInfo, successCallback, failureCallback);
            }

        });
        this.calendarControl.render();
        // this.fetchEvents();
        this.calendarControl.on('dateClick', this.proxy(this.onDateClick));

    };

    Calendar.prototype.onPrevNextButtonClick = function (fetchInfo, successCallback, failureCallback){
        this.refreshEvents(fetchInfo.start.getTime() / 1000,
            fetchInfo.end.getTime() / 1000, fetchInfo.timeZone, successCallback, failureCallback);

    };

    Calendar.prototype.refreshEvents = function (startTime, endTime, timeZone, onSuccessCallback = function () { }, onErrorCallback = function () { }) {
        const self = this;
        this.$loadContainer.loadIndicator();
        const data = {
            startTime: startTime,
            endTime: endTime,
            timeZone: timeZone
        };

        $.request(this.makeEventHandler('onRefreshEvents'), {
            data: data,
            success: function (data, textStatus, jqXHR) {
                const events = data.events;
                self.$loadContainer.loadIndicator('hide');
                onSuccessCallback(events);
            },
            error: function (jqXHR, textStatus, error) {
                self.$loadContainer.loadIndicator('hide');
                this.error(jqXHR, textStatus, error);
                onErrorCallback();
            }
        });
    }

    Calendar.prototype.onEventClick = function(info){
        info.jsEvent.preventDefault();
        const url = info.event.url;
        if (url) {
            if (url.startsWith('http')){
                location.href = url;
            }else{
                eval(url);
            }
        }
    }

    Calendar.prototype.disposeCalendarControl = function () {
        if (this.calendarControl){
            this.calendarControl.off('dateClick', this.proxy(this.onDateClick));
            this.calendarControl.destroy();
            this.calendarControl = null;
        }
    }

    Calendar.prototype.onDateClick = function (ev) {
        // alert('AAA');
    }

    Calendar.prototype.addEvent = function (eventObj = null) {
        this.calendarControl.addEvent(eventObj);
    }
    Calendar.prototype.addEvents = function (eventList) {
        for(let event of eventList){
            this.addEvent(event);
        }
    }

    /**
     * Make Event Handler, same as PHP $this->getEventHandler('xxx')
     */
    Calendar.prototype.makeEventHandler = function (methodName) {
        return this.options.alias + "::" + methodName;
    }
    Calendar.prototype.clearEvents = function(){
        this.calendarControl.getEvents().forEach(event => {
            event.remove();
        });
    }

    Calendar.prototype.onFilterUpdate = function (event, xhr, settings){
        const data = xhr.responseJSON;
        if (data && data.hasOwnProperty('id')
                && data.hasOwnProperty('events')
                && data.hasOwnProperty('method')) {
            if (data.id === 'calendar' && data.method === 'onRefresh') {
                this.clearEvents();
                this.addEvents(data.events);
            }
        }
    }

    Calendar.prototype.fetchEvents = function (onSuccessCallback = function () {}, onErrorCallback = function () {}){
        const self = this;
        this.$loadContainer.loadIndicator();
        $.request(this.makeEventHandler('onFetchEvents'), {
            data: '',
            success: function (data, textStatus, jqXHR) {
                const events = data['events'];
                self.addEvents(events);
                self.$loadContainer.loadIndicator('hide');
                onSuccessCallback();
            },
            error: function (jqXHR, textStatus, error) {
                self.$loadContainer.loadIndicator('hide');
                this.error(jqXHR, textStatus, error);
                onErrorCallback();
            }
        });
    }


    // CALENDAR CONTROL PLUGIN DEFINITION
    // ============================

    const old = $.fn.calendarControl

    $.fn.calendarControl = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result
        this.each(function () {
            var $this = $(this)
            var data = $this.data('oc.calendar')
            var options = $.extend({}, Calendar.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.calendar', (data = new Calendar(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.calendarControl.Constructor = Calendar

    // CALENDAR CONTROL NO CONFLICT
    // =================

    $.fn.calendarControl.noConflict = function () {
        $.fn.calendarControl = old
        return this
    }

    // CALENDAR CONTROL DATA-API
    // ===============

    $(document).render(function () {
        $('[data-control="calendar"]').calendarControl()
    });

}(window.jQuery);
