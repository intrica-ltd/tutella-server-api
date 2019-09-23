const express = require('express');
const router = express.Router();

const NotificationsController = require('./../controllers/notifications-controller');

router.post('/', NotificationsController.getNotifications);
router.post('/markAsRead', NotificationsController.markNotificationsAsRead);

module.exports = router;