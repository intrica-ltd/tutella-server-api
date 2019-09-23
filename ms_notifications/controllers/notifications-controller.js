const notificationsService = require('./../services/notifications-service');

const getNotifications = async (req, res) => {
    // TODO VALIDATION
    const { school_id } = req.body;
    const dbNotifications = await notificationsService.getNotifications(school_id);
    const notifications = {
        read: [],
        unread: []
    };
    if (dbNotifications) {
        dbNotifications.notifications.forEach(notification => {
            if (notification.isRead) {
                notifications.read.push(notification);
            } else {
                notifications.unread.push(notification);
            }
        });
    }
    res.status(200).json(notifications);
}

const markNotificationsAsRead = async (req, res) => {
    const { school_id } = req.body;
    await notificationsService.markNotificationsAsRead(school_id);
    res.status(200).json({});
}

module.exports = { markNotificationsAsRead, getNotifications };