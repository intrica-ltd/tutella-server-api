const pushNotificationsService = require('./../services/push-notifications-service');
const logger = require('./../config/logger');

const StartSurveyNotify = (request, response) => {

    let surveys = request.body.surveys;

    surveys.forEach(function (survey) {
        if (survey.firebase_token) {
            const count = survey.count ? survey.count : 0;
            let message = {
                notification: {
                    title: 'Tutella',
                    body: 'You have received a new survey. Click here to answer it!'
                },
                android: {
                    notification: {
                        "click_action": "FCM_PLUGIN_ACTIVITY"
                    }
                },
                apns: {
                    payload: {
                        aps: {
                            badge: count
                        }
                    }
                },
                data: {
                    type: 'survey',
                    survey_id: `${survey.id}`,
                    badge: `${count}`
                },
                token: survey.firebase_token
            };

            pushNotificationsService.sendPushNotification(message,
                (response) => {
                    logger.info(`Successfully sent message to: ${survey.user_id}. ${response}`);
                }, (error) => {
                    logger.error(`Error sending message to: ${survey.user_id}. ${error}`)
                });
        } else {
            logger.error(`Not sent message to:  ${survey.user_id}, with token: ${survey.firebase_token}`)
        }

    });

    response.status(200).json({
        status_code: 'success'
    });
};

module.exports = { StartSurveyNotify };