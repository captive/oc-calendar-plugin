// Sample for config_calendar.yaml -> recordOnClick
+ function ($) {
    "use strict";

    var EventController = function () {

        this.onEventClick = function (eventId) {
            alert('eventID  = '+ eventId);
        }

    }
    $.oc.eventController = new EventController;

}(window.jQuery);