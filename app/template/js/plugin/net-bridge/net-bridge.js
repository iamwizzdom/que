/**
 * Name: NetBridge
 * Description: NetBridge is used for making asynchronous network (ajax) calls on web applications.
 * Author: Wisdom Emenike
 * License: MIT
 * Version: 1.8
 * GitHub: https://github.com/iamwizzdom/net-bridge
 */

/**
 *
 * @type {NetBridge}
 */
NetBridge = (function () {

    /**
     *
     * @constructor
     */
    let NetBridge = function () {

        let permitNetwork = true;

        let dispatchIndex = 0;

        /**
         * return boolean
         */
        const getPermitNetwork = () => permitNetwork;

        /**
         *
         * @param status
         */
        const setPermitNetwork = (status) => {
            if (isBoolean(status)) permitNetwork = status;
        };

        /**
         *
         * @return {number}
         */
        const getLastDispatchedIndex = () => dispatchIndex;

        /**
         *
         * @param index
         */
        const setNextDispatchIndex = (index) => {
            dispatchIndex = index;
        };

        /**
         *
         * @param variable
         * @return {boolean}
         */
        const isUndefined = (variable) => typeof variable === "undefined";

        /**
         *
         * @param variable
         * @return {boolean}
         */
        const isArray = (variable) => Array.isArray(variable);

        /**
         *
         * @param variable
         * @return {boolean}
         */
        const isObject = (variable) => variable !== null && typeof variable === "object";

        /**
         *
         * @param variable
         * @return {boolean}
         */
        const isFunction = (variable) => typeof variable === "function";

        /**
         *
         * @param variable
         */
        const isBoolean = (variable) => typeof variable === "boolean";

        /**
         *
         * @param variable
         * @return {boolean}
         */
        const isString = (variable) => typeof variable === "string";

        /**
         *
         * @param variable
         */
        const isNumeric = (variable) => isNaN(variable) === false;

        /**
         *
         * @param variable
         * @return {boolean}
         */
        const isEmpty = (variable) => {

            if (isArray(variable)) return variable.length < 1;

            if (isObject(variable)) return Object.keys(variable) < 1;

            let string = '';

            return (
                variable === undefined
                || variable === null
                || typeof variable === "undefined"
                || (string = variable.toString()) === ""
                || string.trim() === " "
            );
        };

        /**
         *
         * @param variable
         * @return {"undefined"|"object"|"boolean"|"number"|"string"|"function"|"symbol"|"bigint"}
         */
        const getType = (variable) => typeof variable;

        /**
         *
         * @param object
         * @return {string}
         */
        const serialize = (object) => {
            let list = [], x;
            for (x in object) {
                if (!isEmpty(x) && object.hasOwnProperty(x)) {
                    list[list.length] = encodeURIComponent(x) + "=" + encodeURIComponent(
                        !isEmpty(object[x]) ? (isArray(object[x]) || isObject(object[x]) ? JSON.stringify(object[x]) : object[x]) : ""
                    );
                }
            }
            return list.join('&');
        };

        /**
         *
         * @param request
         * @return {boolean|number}
         */
        const isInRequestQueue = (request) => {
            let requestQueue = this.getRequestQueue(),
                size = requestQueue.length;
            for (let x = 0; x < size; x++) {
                let queue = requestQueue[x], count = 0,
                    keys = Object.keys(queue).length;
                for (let n in queue) {
                    if (queue.hasOwnProperty(n) && request.hasOwnProperty(n)) {
                        if (isFunction(queue[n]) && isFunction(request[n])) count++;
                        else if (isObject(queue[n]) && isObject(request[n])) count++;
                        else if (isArray(queue[n]) && isArray(request[n])) count++;
                        else if (queue[n] === request[n]) count++;
                    }
                }
                if (count === keys) return x;
            }
            return false;
        };

        /**
         *
         * @param id
         * @return {boolean|number}
         */
        const isKeyInRequestQueue = (id) => {
            let requestQueue = this.getRequestQueue(),
                size = requestQueue.length;
            for (let x = 0; x < size; x++) {
                let queue = requestQueue[x];
                if (queue.id === id) return x;
            }
            return false;
        };

        /**
         *
         * @param url
         * @param params
         * @return {string}
         */
        const mergeUrlParams = (url, params) => {
            let urlParams = getSearchParameters(url);
            if (params && isObject(params)) urlParams = {...urlParams, ...params};
            return url.split('?')[0] + (urlParams ? "?" + serialize(urlParams) : '');
        };

        /**
         *
         * @param url
         * @return {{}}
         */
        const getSearchParameters = (url) => {
            let params = {};
            let parser = document.createElement('a');
            parser.href = url;
            let query = parser.search.substring(1);
            let queries = query.split('&');
            for (let i = 0; i < queries.length; i++) {
                let pair = queries[i].split('=');
                params[pair[0]] = decodeURIComponent(pair[1]);
            }
            return params;
        };

        /**
         *
         * @constructor
         */
        let Finalize = function() {

            /**
             *
             * @type {{always: null, fail: null, done: null}}
             */
            let callbacks = {done: null, fail: null, always: null};

            /**
             *
             * @param callback
             */
            this.done = (callback) => {
                callbacks.done = callback;
            };

            /**
             *
             * @param callback
             */
            this.fail = (callback) => {
                callbacks.fail = callback;
            };

            /**
             *
             * @param callback
             */
            this.always = (callback) => {
                callbacks.always = callback;
            };

            /**
             *
             * @return {{always: null, fail: null, done: null}}
             */
            this.getCallbacks = () => callbacks;
        };

        /**
         *
         * @type {{finally: null, queue: Array, responseStack: {}}}
         */
        let requestQueue = {queue: [], finally: null, responseStack: {}};

        /**
         *
         * @param queue
         * @return {number}
         */
        const push = (queue) => requestQueue.queue.push(queue);

        /**
         *
         * @param index
         * @return {*[]}
         */
        const pop = (index) => requestQueue.queue.splice(index, 1);

        /**
         *
         * @param key
         * @param response
         */
        const pushToResponseStack = (key, response) => {
            if (isUndefined(requestQueue.responseStack[key])) {
                requestQueue.responseStack[key] = response;
            } else if (isArray(requestQueue.responseStack[key])) {
                requestQueue.responseStack[key].push(response);
            } else {
                let resp = requestQueue.responseStack[key];
                requestQueue.responseStack[key] = [];
                requestQueue.responseStack[key].push(resp);
                requestQueue.responseStack[key].push(response);
            }
        };

        /**
         *
         * @return {Array}
         */
        this.getRequestQueue = () => requestQueue.queue;

        /**
         *
         * @param request
         * @return {Finalize}
         */
        const pushToQueue = (request) => {

            let size = this.getRequestQueue().length, network = getPermitNetwork();

            let index;

            if (!isUndefined(request.id) && (index = isKeyInRequestQueue(request.id)) !== false) {

                pop(index);
                dispatchIndex--;

            } else if ((index = isInRequestQueue(request)) !== false) {

                pop(index);
                dispatchIndex--;
            }

            if (!network && isFunction(request.queue)) request.queue();


            if (isUndefined(request.finalize))
                request.finalize = new Finalize();

            request.id = (!isUndefined(request.id) ? request.id : size);

            push(request);

            if (network) dispatcher();

            return request.finalize;
        };

        /**
         *
         * @param request
         * @return {Finalize}
         */
        this.addToRequestQueue = (request) => {

            if (!isObject(request)) throw "NetBridge's 'addToRequestQueue' method expects an object from its parameter, but got " + getType(request);
            if (isUndefined(request.url)) throw "NetBridge's 'addToRequestQueue' method expects a 'url' attribute from the passed object";
            if (!isString(request.url)) throw "NetBridge's 'addToRequestQueue' method expects the 'url' attribute to be a string, but got " + getType(request.url);
            if (isUndefined(request.method)) throw "NetBridge's 'addToRequestQueue' method expects a 'method' attribute from the passed object";
            if (!isString(request.method)) throw "NetBridge's 'addToRequestQueue' method expects the 'method' attribute to be a string, but got " + getType(request.method);

            return pushToQueue(request);
        };

        /**
         *
         * @param request
         */
        const dispatcher = (request = null) => {

            let queue = this.getRequestQueue(),

                timer = null,

                dispatch = (request) => {

                    if (!getPermitNetwork()) {
                        if (timer !== null) clearTimeout(timer);
                        timer = setTimeout(() => {
                            dispatch(request);
                        }, 500);
                        return;
                    }

                    setPermitNetwork(false);

                    let lastIndex = getLastDispatchedIndex();
                    if (queue.length > lastIndex) setNextDispatchIndex((lastIndex + 1));

                    let xhr = new XMLHttpRequest();

                    if (isFunction(request['beforeSend'])) {
                        xhr.onloadstart = () => {
                            request['beforeSend'](xhr);
                        };
                    }

                    xhr.onreadystatechange = function () {

                        let state = false, status = false;

                        if (this.readyState === 0) {
                            console.error("NetBridge error: request not initialized (URL:: " + request.url + ")");
                            if (isFunction(request.error)) request.error((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), xhr, this.status, this.statusText);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: null,
                                message: 'not initialized',
                                xhr: xhr,
                                status: null,
                                statusText: null
                            });
                        }

                        if (isFunction(request['responseHeaders']) &&
                            this.readyState === this.HEADERS_RECEIVED) {

                            let headers = xhr.getAllResponseHeaders();

                            let headerArray = headers.trim().split(/[\r\n]+/);

                            let headerMap = {};
                            headerArray.forEach(function (line) {
                                let parts = line.split(': ');
                                let header = parts.shift();
                                headerMap[header] = parts.join(': ');
                            });

                            request['responseHeaders'](headerMap);
                        }

                        if (this.readyState === 4) state = true;

                        if (state === true && this.status !== 200) {

                            console.error("NetBridge error: " + this.statusText + " - " + this.status + " (URL:: " + request.url + ")");
                            if (isFunction(request.error)) request.error((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), xhr, this.status, this.statusText);
                            let fail = request.finalize.getCallbacks().fail;
                            if (isFunction(fail)) fail((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), xhr, this.status, this.statusText);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: (!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)),
                                message: 'failed',
                                xhr: xhr,
                                status: this.status,
                                statusText: this.statusText
                            });

                        } else status = true;

                        if (state === true && status === true) {

                            if (isFunction(request.success)) request.success((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), this.status, xhr);
                            let done = request.finalize.getCallbacks().done;
                            if (isFunction(done)) done((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), this.status, xhr);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: (!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)),
                                message: 'successful',
                                xhr: xhr,
                                status: this.status,
                                statusText: this.statusText
                            });
                        }

                        if (state === true || this.readyState === 0) {

                            if (isFunction(request.complete)) request.complete((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), xhr, this.status);
                            let always = request.finalize.getCallbacks().always;
                            if (isFunction(always)) always((!isEmpty(this.responseType) && this.responseType !== 'text' ? this.response : (this.responseXML ? this.responseXML : this.responseText)), xhr, this.status);

                            if (queue.length === getLastDispatchedIndex() && isFunction(requestQueue.finally)) {
                                requestQueue.finally(requestQueue.responseStack);
                                requestQueue.responseStack = {};
                            }
                        }

                    };

                    xhr.onloadend = function () {
                        setPermitNetwork(true);
                        if (isBoolean(request['persist']) && request['persist'] === true) push(request);
                        let timer = setTimeout(() => {
                            let index = getLastDispatchedIndex();
                            if (queue.length > index) {
                                dispatch(queue[getLastDispatchedIndex()]);
                            }
                            clearTimeout(timer);
                        }, 1000);
                    };

                    if (isNumeric(request.timeout)) xhr.timeout = parseInt(request.timeout);

                    if (isFunction(request.ontimeout)) {

                        xhr.ontimeout = function () {

                            request.ontimeout(...arguments);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: null,
                                message: 'timed out',
                                xhr: xhr,
                                status: null,
                                statusText: null
                            });

                            if (isFunction(request.complete)) request.complete(null, xhr, null);
                            let always = request.finalize.getCallbacks().always;
                            if (isFunction(always)) always(null, xhr, null);

                            if (queue.length === getLastDispatchedIndex() && isFunction(requestQueue.finally)) {
                                requestQueue.finally(requestQueue.responseStack);
                                requestQueue.responseStack = {};
                            }

                        };
                    }

                    if (isFunction(request.error)) {

                        xhr.onerror = function () {

                            request.error(...arguments);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: null,
                                message: 'error occurred',
                                xhr: xhr,
                                status: null,
                                statusText: null
                            });

                            if (isFunction(request.complete)) request.complete(null, xhr, null);
                            let always = request.finalize.getCallbacks().always;
                            if (isFunction(always)) always(null, xhr, null);

                            if (queue.length === getLastDispatchedIndex() && isFunction(requestQueue.finally)) {
                                requestQueue.finally(requestQueue.responseStack);
                                requestQueue.responseStack = {};
                            }
                        };

                    }

                    if (isFunction(request.abort)) {

                        xhr.onabort = function () {

                            request.abort(...arguments);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: null,
                                message: 'aborted',
                                xhr: xhr,
                                status: null,
                                statusText: null
                            });

                            if (isFunction(request.complete)) request.complete(null, xhr, null);
                            let always = request.finalize.getCallbacks().always;
                            if (isFunction(always)) always(null, xhr, null);

                            if (queue.length === getLastDispatchedIndex() && isFunction(requestQueue.finally)) {
                                requestQueue.finally(requestQueue.responseStack);
                                requestQueue.responseStack = {};
                            }
                        };
                    }

                    if (isFunction(request.cancel)) {

                        xhr.oncancel = function () {

                            request.cancel(...arguments);

                            pushToResponseStack(request.id, {
                                url: request.url,
                                method: request.method,
                                response: null,
                                message: 'cancelled',
                                xhr: xhr,
                                status: null,
                                statusText: null
                            });

                            if (isFunction(request.complete)) request.complete(null, xhr, null);
                            let always = request.finalize.getCallbacks().always;
                            if (isFunction(always)) always(null, xhr, null);

                            if (queue.length === getLastDispatchedIndex() && isFunction(requestQueue.finally)) {
                                requestQueue.finally(requestQueue.responseStack);
                                requestQueue.responseStack = {};
                            }
                        };
                    }

                    xhr.msCaching = (isBoolean(request.cache) ? request.cache : false);

                    xhr.open(
                        request.method,
                        (request.method.toUpperCase() === 'GET' && !isUndefined(request.data)) ?
                            encodeURI(mergeUrlParams(request.url,  request.data)) : request.url,
                        (isBoolean(request.async) ? request.async : true),
                        (isString(request['username']) ? request['username'] : ""),
                        (isString(request.password) ? request.password : ""),
                    );

                    if (isFunction(request.xhr)) request.xhr(xhr);

                    if (isBoolean(request.contentType) && request.contentType === false) {
                        xhr.withCredentials = true;
                    } else {
                        xhr.setRequestHeader("Content-Type", isString(request.contentType) ?
                            request.contentType : "application/x-www-form-urlencoded; charset=UTF-8");
                    }

                    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

                    let headers, data;

                    if (isFunction(request.headers)) headers = request.headers();
                    else headers = request.headers;

                    if (isObject(headers)) {
                        for (let x in headers) {
                            if (headers.hasOwnProperty(x))
                                xhr.setRequestHeader(x, headers[x]);
                        }
                    }

                    if (isFunction(request.data)) data = request.data();
                    else data = request.data;

                    if (isString(request.dataType)) xhr.responseType = request.dataType.toLowerCase();

                    xhr.send((isBoolean(request.processData) &&
                    request.processData === false ? data : serialize(data)));

                };

            if (isObject(request)) {
                dispatch(request);
                return;
            }

            let lastIndex = getLastDispatchedIndex();
            if (queue.length > lastIndex) dispatch(queue[lastIndex]);
        };

        /**
         *
         * @param id
         * @param override
         * @return {null|Finalize}
         */
        this.reDispatch = (id, override) => {

            let index, request;

            if ((index = isKeyInRequestQueue(id)) !== false) {

                request = requestQueue.queue[index];

                if (override && isObject(override)) {
                    for (let key in override) {
                        if (key === 'id' || key === 'finalize') continue;
                        if (!override.hasOwnProperty(key)) continue;
                        request[key] = override[key];
                    }
                }

                return pushToQueue(request);
            }

            throw `NetBridge error: Queue ID '${id}' was not found in request queue`;

        };

        this.finally = (callback) => {
            requestQueue.finally = callback;
        };
    };

    /**
     *
     * @type {NetBridge}
     */
    let mInstance = null;

    /**
     *
     * @return {NetBridge}
     */
    NetBridge.getInstance = () => (mInstance instanceof NetBridge ? mInstance : (mInstance = new NetBridge()));

    return NetBridge;

}());
