const mongoose = require('mongoose');

const notificationSchema = new mongoose.Schema({
    isRead: { type: Boolean, default: false },
    message: { type: String, required: true },
    createdAt: { type: Date }
});

module.exports = notificationSchema;