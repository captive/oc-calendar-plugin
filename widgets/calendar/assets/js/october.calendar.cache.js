/*jshint esversion: 6 */
const daysOfMonth = 42; // 6 weeks per month
const secondsOfDay = 86400;

class CalendarCache {
    /**
     *
     * @param int firstDay the first day of week, 0 = sunday ...
     * @param int capcity the default month data stored
     */
    constructor(firstDay = 0, capcity = 12) {
        this.cache = [];
        this.lfuCache = [];
        this.lastCacheEvents = null;
        this.lastMonthReqeustData = null;
        this.length = 0;
        this.firstDay = firstDay;
        this.capcity = capcity;
        this._hideIndicatorCallback = null;
        this._showIndicatorCallback = null;
    }

    set hideIndicatorCallback(value) {
        this._hideIndicatorCallback = value;
    }
    get hideIndicatorCallback() {
        return this._hideIndicatorCallback;
    }

    set showIndicatorCallback(value) {
        this._showIndicatorCallback = value;
    }
    get showIndicatorCallback() {
        return this._showIndicatorCallback;
    }

    isEmpty() {
        return this.length === 0;
    }

    count() {
        return this.length;
    }

    clearCache() {
        this.length = 0;
        this.lfuCache = [];
        this.cache = [];
    }

    incrLFUCount(key) {
        let value = this.lfuCache[key];
        this.lfuCache[key] = (value === undefined) ? 1 : ++value;
    }

    removeOldCache() {
        if (this.count() < this.capcity) return;
        let minKey;
        let minValue = Number.MAX_SAFE_INTEGER;
        for (let key in this.lfuCache) {
            let element = this.lfuCache[key];
            if (minValue <= element) {
                minValue = element;
                minKey = key;
            }
        }
        delete this.lfuCache[minKey];
        delete this.cache[minKey];
        this.length--;
    }

    getCacheData(requestData) {
        // Maybe the first month
        if (this.isEmpty()) {
            return null;
        }
        let startTime = requestData.startTime;
        let endTime = requestData.endTime;
        let results = null;

        const self = this;

        for (let key in this.cache) {
            let element = this.cache[key];
            const timeKeys = key.split('-');
            if (startTime >= parseInt(timeKeys[0]) && endTime <= parseInt(timeKeys[1])) {
                self.incrLFUCount(key);
                results = element;
                break;
            }
        }
        return results;
    }

    /**
     * Some weeks may be in two month, such as 2018-12-30 to 2019-01-05
     *
     *
     * @param Array requestData
     */
    getMonthRequestData(requestData) {
        const startDate = new Date(requestData.startTime * 1000);

        if (startDate.getDay() === this.firstDay && (requestData.endTime - requestData.startTime) === daysOfMonth * secondsOfDay) {
            this.lastMonthReqeustData = requestData;
            return requestData;
        }
        let firstDayOfMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        let daysDiff = firstDayOfMonth.getDay() - this.firstDay;
        let monthData;
        if (daysDiff !== 0) {
            // need to get the first day of week , eg: 2018-12-30 is the first day of jan, 2019
            if (daysDiff < 0) daysDiff = firstDayOfMonth.getDay() + this.firstDay;
            let firstDayOfMonthTime = firstDayOfMonth.getTime() / 1000 - secondsOfDay * daysDiff;
            monthData = {
                startTime: firstDayOfMonthTime,
                endTime: firstDayOfMonthTime + daysOfMonth * secondsOfDay,
                timeZone: requestData.timeZone,
            };
        } else {
            monthData = {
                startTime: requestData.startTime,
                endTime: requestData.startTime + daysOfMonth * secondsOfDay,
                timeZone: requestData.timeZone,
            };
        }
        this.lastMonthReqeustData = monthData;
        return monthData;

    }

    getMonthRequestDataString() {
        if (this.lastMonthReqeustData === null) return '';
        const requestString = 'calendar_start_time=' + this.lastMonthReqeustData.startTime +
            '&calendar_end_time=' + this.lastMonthReqeustData.endTime +
            '&calendar_time_zone=' + encodeURI(this.lastMonthReqeustData.timeZone);
        return requestString;
    }

    getLastMonthRequestData() {
        return this.lastMonthReqeustData;
    }

    saveCache(monthData, events) {
        const startTime = monthData.startTime;
        const endTime = monthData.endTime;
        const key = startTime + '-' + endTime;
        this.cache[key] = events;
        this.length++;
        this.incrLFUCount(key);
        this.removeOldCache();
    }

    showIndicator() {
        if (this.showIndicatorCallback) this.showIndicatorCallback();
    }

    hideIndicator() {
        if (this.hideIndicatorCallback) this.hideIndicatorCallback();
    }

    requestEvents(methodName, requestData, onSuccessCallback = () => {}, onErrorCallback = () => {}) {

        let events = this.getCacheData(requestData);
        if (events !== null) {
            if (events !== this.lastCacheEvents) {
                this.lastCacheEvents = events;
                this.lastMonthReqeustData = requestData;
                onSuccessCallback(events);
            }
            return;
        }

        this.showIndicator();

        const monthData = this.getMonthRequestData(requestData);
        // TODO month Data need to send to the server to refresh the session start_time and end_time
        const self = this;

        $.request(methodName, {
            data: monthData,
            success: function (data, textStatus, jqXHR) {
                const events = data.events;
                self.hideIndicator();
                // the events is whole month data
                self.saveCache(monthData, events);
                self.lastCacheEvents = events;
                onSuccessCallback(events);
            },
            error: function (jqXHR, textStatus, error) {
                self.hideIndicator();
                self.error(jqXHR, textStatus, error);
                onErrorCallback();
            }
        });
    }
    dispose() {
        this._hideIndicatorCallback = null;
        this._showIndicatorCallback = null;
        this.cache = [];
        this.lfuCache = [];
        this.lastCacheEvents = null;
    }
}
