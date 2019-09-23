const admin = require("firebase-admin");
const env = require('./../config/env');

var serviceAccount = require('./../config/keys/tutella-serviceAccountKey.json');

const initializePushNotifications = () => {
    admin.initializeApp({
        credential: admin.credential.cert(serviceAccount),
        databaseURL: env.GCLOUD_DB_URL
    });
}

const sendPushNotification = (message, callback, errorCallback) => {

    admin.messaging().send(message)
        .then((response) => {
            if (callback && typeof callback === 'function') {
                callback(response);
            }
        })
        .catch((error) => {
            if (errorCallback && typeof errorCallback === 'function') {
                errorCallback(error);
            }
        });
}

module.exports = {
    initializePushNotifications,
    sendPushNotification
}