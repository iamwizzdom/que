# NetBridge - NetBridge is used for making asynchronous network (ajax) calls on web applications.

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

- [Usage](#usage)
- [Attributes and Methods](#attributes)
- [Installation](#installation)

<h2 id="usage"> Usage </h2>

```javascript
let instance = NetBridge.getInstance();

instance.addToRequestQueue({
    url: "https://swapi.co/api/people",
    method: "post",
    data: {
        userID: 2
    },
    dataType: 'JSON',
    queue: function () {
        console.log('comment', 'I am a post request and am waiting');
    },
    beforeSend: function () {
        console.log('comment', 'I am a post request and I ran beforeSend');
    },
    success: (data, status, xhr) => {
        console.info('success:', {data: data, status: status, xhr: xhr});
    },
    error: (data, xhr, status, statusText) => {
        console.error('error:', {data: data, xhr: xhr, status: status, statusText: statusText});
    },
    complete: (data, xhr, status) => {
        console.debug('complete:', {data: data, xhr: xhr, status: status});
    }
});

instance.addToRequestQueue({
    url: "https://swapi.co/api/people/2",
    method: "get",
    queue: function () {
        console.log('comment', 'I am a get request and am waiting');
    },
    beforeSend: function () {
        console.log('comment', 'I am a get request and I ran beforeSend');
    },
    success: (data, status, xhr) => {
        console.info('success:', {data: data, status: status, xhr: xhr});
    },
    error: (data, xhr, status, statusText) => {
        console.error('error:', {data: data, xhr: xhr, status: status, statusText: statusText});
    },
    complete: (data, xhr, status) => {
        console.debug('complete:', {data: data, xhr: xhr, status: status});
    }
});
```

> `NetBridge.getInstance()` returns a singleton from which you can use the `addToRequestQueue(...)` method
to queue up requests that are to be dispatched asynchronously, making network calls easier on your browser.

<h2 id="attributes"> Attributes and Methods </h2>

> NetBridge accepts similar attributes as the regular JQuery `$.ajax` method.

- `id` - This is a unique identifier for every request. If not defined, NetBridge will assign it the available index in the queue.

- `url` - This defines the url to the endpoint being called.

- `method` - This defines the request method you wish to use.

- `data` - This defines the payload being sent to the server.

- `processData` - This defines a boolean which when `true` tells NetBridge to process data being sent to the server or otherwise.

- `timeout` - This defines a time after which the request should be aborted if not complete already. (Time must be passed in milliseconds as `int` data type).

- `cache` - This defines a boolean which when `true` tells NetBridge to cache the request or otherwise.

- `headers` - This defines an object of headers being sent to the server. 

- `responseHeaders` - This defines a function which will receive as array of headers sent from the server.

- `dataType` - This defines the data type expected from the server response.

- `xhr` - This defines a function using to receive an object of the current `XMLHttpRequest`.

- `contentType` - This defines the content type being sent to the server. (If not needed, set to false or do not define).

- `queue` - This defines a function to be ran when the request is not dispatched immediately but queued for later execution.

- `beforeSend` - This defines a function to be ran just before your request is sent to the server.

- `cancel` - This defines a function to be ran when the request is cancelled.

- `abort` - This defines a function to be ran when the request is aborted.

- `ontimeout` - This defines a functions to be ran when the request times out.

- `complete` - This defines a function to be ran when the request is completed, regardless of a failure or success. It accepts 3 params, the first param receives the server response, the second param receives an object of the current `XMLHttpRequest` while the third param receives the server response status.

- `success` - This defines a function to be ran when the request is successful. It accepts 3 params, the first param receives the server response, the second param receives the server response status, while the third receives an object of the current `XMLHttpRequest`.

- `error` - This defines a function to be ran if an error occurs. It accepts 4 params, the first param receives the server response, the second param receives an object of the current `XMLHttpRequest`, the third param receives the server response status, while the fourth param receives the request status text.

> NetBridge's `addToRequestQueue(...)` method mentioned above also returns an object which can later be used to setup event listeners such `done(...)`, `fail(...)` and `always(...)`

- `done(...)` - This method is similar to the `success` attribute mentioned above. It takes a function as an argument, in which is to receive the params as the `success` function.

- `fail(...)` - This method is similar to the `error` attribute mentioned above. It takes a function as an argument, in which is to receive the params as the `error` function.

- `always(...)` - This method is similar to the `complete` attribute mentioned above. It takes a function as an argument, in which is to receive the params as the `complete` function.

> The NetBridge instance also has 3 more methods, which are `getRequestQueue()`, `finally(...)` and `reDispatch(..., ...)`

- `getRequestQueue()` - This method returns a list of all queue requests.

- `finally(...)` -  This method is ran each time all the request in the request queue has been dispatched. The method takes a function as an argument, this function would receive a stack of all response data from all recently dispatched request.

- `reDispatch(..., ...)` - This method is used to re-dispatch a previously queued request using the request `id`. The method takes 2 params as an argument, the first param defines the `id` of the request to be re-dispatched, while the second optional param takes an object containing attribute to be overridden in the previous request.


<h3 id="installation">Installing NetBridge</h3>

NetBridge can be install via [npm](https://www.npmjs.com/)

```$xslt
npm install iamwizzdom/net-bridge
```

