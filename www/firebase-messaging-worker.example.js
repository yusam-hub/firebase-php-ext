importScripts('https://www.gstatic.com/firebasejs/7.0.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.0.0/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "",
    authDomain: "",
    projectId: "",
    storageBucket: "",
    messagingSenderId: "",
    appId: "",
    measurementId: ""
});

self.addEventListener('push', function(event) {

    const data = event.data.json();

    console.log("Worker push", data);

    const options = {
        body: data.data.body,
        icon: data.data.icon ? data.data.icon : '',
        image: data.data.image ? data.data.image : '',
        data: {
            url: data.data.click_action
        }
    };

    if (data.data.actions) {
        //console.log(data.data.actions);
        let actions = data.data.actions;

        if (typeof data.data.actions === "string") {
            try {
                actions = JSON.parse(data.data.actions);
                //console.log(actions);
                options.actions = actions;
            } catch (e) {
                console.error("Unable to parse json actions: ", data.data.actions);
            }
        } else {
            options.actions = actions;
        }
    }

    console.log("Worker showNotification", data.data.title, options);

    event.waitUntil(
        Promise.all([
            self.registration.showNotification(data.data.title, options)
        ])
    );
});

self.addEventListener('notificationclick', function(event)
{
    console.log("Worker notificationclick", event.notification);

    const target = event.notification.data.url || '/';

    event.notification.close();

    event.waitUntil(
        clients
            .matchAll(
                {
                    type: 'window',
                    includeUncontrolled: true
                })
            .then(function(clientList)
            {
                for (let i = 0; i < clientList.length; i++) {
                    let client = clientList[i];
                    if (client.url === target && 'focus' in client) {
                        return client.focus();
                    }
                }
                return clients.openWindow(target);
            })
    );
});