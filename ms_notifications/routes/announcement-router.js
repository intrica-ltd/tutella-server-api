const express = require('express');
const router = express.Router();

const AnnouncementController = require('./../controllers/announcement-controller');

router.post('/sendAnnouncement', AnnouncementController.SendAnnouncement);

module.exports = router;