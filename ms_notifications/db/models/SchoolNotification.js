const mongoose = require('mongoose');
const notificationSchema = require('./Notification');

const schoolNotificationSchema = new mongoose.Schema(
    {
        schoolId: { type: Number, unique: true, index: true },
        notifications: [notificationSchema],
        hasUnread: { type: Boolean, default: false }
    },
    {
        collection: 'schoolNotifications',
        timestamps: true
    });

const SchoolNotification = mongoose.model('SchoolNotification', schoolNotificationSchema);

module.exports = SchoolNotification;