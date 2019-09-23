const SchoolNotification = require('./../db/models/SchoolNotification');
const notificationsService = require('./notifications-service');

const enableAutomaticBilling = (schoolId, month, year) => {
    const notification = {
        isRead: false,
        message: `Billing for ${month}.${year}`,
        createdAt: new Date()
    };
    SchoolNotification.findOneAndUpdate(
        {
            schoolId
        }, {
            schoolId,
            $push: { notifications: { $each: [notification], $position: 0 } },
            hasUnread: true
        }, {
            upsert: true
        }, (err, existingUserNotification) => {
            if (err) { return err; }
            // notificationsService.
        }
    );
};

const invoiceCreated = (schoolId, month, year) => {
    const notification = {
        isRead: false,
        message: `There is a new Invoice for ${month}.${year}`,
        createdAt: new Date()
    };
    
    SchoolNotification.findOneAndUpdate(
        {
            schoolId
        }, {
            schoolId,
            $push: { notifications: { $each: [notification], $position: 0 } },
            hasUnread: true
        }, {
            upsert: true
        }, (err, existingNotification) => {
            if (err) { return err; };
            SchoolNotification
                .findOne({ schoolId })
                .limit(1)
                .select({ notifications: { $slice: 1 } })
                .exec((err, dbNotification) => {
                    const resultNotification = dbNotification.notifications[0];
                    notificationsService.sendNotification(`/schools/${schoolId}`, {
                         id: resultNotification._id,
                         createdAt: resultNotification.createdAt,
                         message: resultNotification.message,
                         type: 'billing',
                    });
                });
        }
    );
};

module.exports = {
    enableAutomaticBilling,
    invoiceCreated
};