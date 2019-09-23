const pushNotificationsService = require('./../services/push-notifications-service');
const logger = require('./../config/logger');

const SendAnnouncement = (request, response) => {

    let msg = request.body.msg;
    let users = request.body.users;
    
    if(users) {
        users.forEach((user) => {
            if(user.firebase_token) {
                let message = {
                    notification: {
                        title: 'Tutella',
                        body: 'You have a new message. Click here to see it!'
                    },
                    android: {
                        notification: {
                            "click_action": "FCM_PLUGIN_ACTIVITY"
                        }
                    },
                    apns: {
                        payload: {
                            aps: {
                                badge: user.count
                            }
                        }
                    },
                    data: {
                        type: 'announcement',
                        announcement: msg,
                        badge: `${user.count}`
                    },
                    token: user.firebase_token
                };

                pushNotificationsService.sendPushNotification(message,
                    (response) => {
                        logger.info(`Successfully sent announcement to: ${user.firebase_token}. ${response}`);
                    }, (error) => {
                        logger.error(`Error sending message to: ${user.firebase_token}. ${error}`)
                    });
            } else {
                logger.error(`Not sent message to user with token: ${user.firebase_token}`)
            }
        });
    }

    response.status(200).json({
        status_code: 'success'
    });
};

module.exports = { SendAnnouncement };