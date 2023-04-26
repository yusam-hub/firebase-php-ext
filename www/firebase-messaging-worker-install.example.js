if (firebase.messaging.isSupported())
{
    firebase_subscribe_console_log('Firebase messaging isSupported');

    firebase.initializeApp({
        apiKey: "",
        authDomain: "",
        projectId: "",
        storageBucket: "",
        messagingSenderId: "",
        appId: "",
        measurementId: ""
    });

    var messaging = firebase.messaging();

    navigator
        .serviceWorker
        .register("/firebase-messaging-worker.js")
        .then(function (registration) {

            firebase_subscribe_console_log('ServiceWorker is registered');

            messaging.useServiceWorker(registration);

        })
        .catch(function (error) {
            firebase_subscribe_console_log('ServiceWorker registration failed', error);
        });

    messaging
        .onTokenRefresh(function () {

            firebase_subscribe_console_log('Trying to refresh token');

            messaging
                .getToken()
                .then(function (refreshedToken) {

                    firebase_subscribe_console_log('Token is refreshed', refreshedToken);

                })
                .catch(function (err) {
                    firebase_subscribe_console_log('Refresh token fail', err);
                });
        });

    firebase.analytics().logEvent('notification_received');

} else {
    firebase_subscribe_console_log('Firebase messaging NOT isSupported');
}

function checkingCurrentTokenForSendToServer() {
    firebase_subscribe_console_log('Checking current token for send to server');

    messaging
        .getToken()
        .then(function (currentToken) {
            if (currentToken) {
                firebase_subscribe_console_log('Found current token');
                firebase_subscribe_console_log(currentToken);
            } else {
                firebase_subscribe_console_log('Current token not found');
            }
        })
        .catch(function (err) {
            firebase_subscribe_console_log('Checking current token fail', err);
        });
}

function requestPushPermission() {
    if (firebase.messaging.isSupported())
    {
        firebase_subscribe_console_log('Trying to request permission');

        messaging
            .requestPermission()
            .then(
                function () {
                    firebase_subscribe_console_log('Permission is granted');
                    checkingCurrentTokenForSendToServer();
                })
            .catch(
                function (err) {
                    firebase_subscribe_console_log('Unable to get permission', err);
                });
    }
}

function firebase_subscribe_console_log(...data)
{
    console.log(...data);
}