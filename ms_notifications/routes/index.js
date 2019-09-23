const surveysRouter = require('./surveys-router');
const billingRouter = require('./billing-router');
const notificationsRouter = require('./notifications-router');
const announcementRouter = require('./announcement-router');

module.exports = {
    initRoutes(api) {
        api.use('/api/surveys', surveysRouter);
        api.use('/api/billing', billingRouter);
        api.use('/api/notifications', notificationsRouter);
        api.use('/api/announcements', announcementRouter);
    }
}