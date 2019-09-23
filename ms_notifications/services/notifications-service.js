const SchoolNotification = require('./../db/models/SchoolNotification');
const socketsService = require('./sockets-service');

const getNotifications = (schoolId) => {
    if (schoolId) {
        return SchoolNotification.findOne({ schoolId });
    } else {
        return SchoolNotification.find();
    }
}

const markNotificationsAsRead = (schoolId, notificationIds) => {
    return SchoolNotification.updateMany(
        {
            schoolId
        }, {
            hasUnread: false,
            $set: { "notifications.$[element].isRead": true }
        }, {
            arrayFilters: [{ "element.isRead": false }]
        }
    );
}

const sendNotification = (namespace, message) => {
    socketsService.emitToNamespace(namespace, message);
}

module.exports = {
    getNotifications,
    sendNotification,
    markNotificationsAsRead
};