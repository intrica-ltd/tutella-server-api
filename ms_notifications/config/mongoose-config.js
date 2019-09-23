const mongoose = require('mongoose');
const env = require('./env');
const logger = require('./logger');

const mongoLoggingPlugin = function (schema, options) {
  schema
    .pre('validate', function (next) {
      logger.info('validate');
      next();
    }).pre('findOneAndUpdate', function (next) {
      logger.info('findOneAndUpdate');
      next();
    }).pre('save', function (next) {
      logger.info('save');
      next();
    });
};

const initMongoDB = async () => {
  mongoose.set('autoIndex', false);
  mongoose.set('autoCreate', false);
  mongoose.set('useCreateIndex', true);
  mongoose.set('useNewUrlParser', true);

  mongoose.plugin(mongoLoggingPlugin);

  mongoose.connect(env.MONGO_URI);

  mongoose.connection.on('error', (err) => {
    logger.error(`MongoDB connection error. Please make sure MongoDB is running.`);
    process.exit();
  });
};

module.exports = initMongoDB;