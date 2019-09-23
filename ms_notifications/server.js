const express = require('express');
const http = require('http');
const env = require('./config/env');
const initMongoDB = require('./config/mongoose-config');
const applyApiMiddleware = require('./middleware/api-middleware');
const socketsService = require('./services/sockets-service');
const pushNotifications = require('./services/push-notifications-service');
const chalk = require('chalk');
const logger = require('./config/logger');

// Initialize the database
initMongoDB();

// Create the express server
const app = express();
app.set('port', env.HTTP_PORT);

// Apply middlewares
applyApiMiddleware(app);

// Configure the instance of the http server
const server = http.Server(app);

// Initialize the socket management
socketsService.initSockets(server);

// requre the routes here, so that the mongo Models dont instance before the global mongo plugins apply
const routes = require('./routes/index');

// Set up routes
routes.initRoutes(app);

server.listen(env.HTTP_PORT);

// Initialize the push notifications management
pushNotifications.initializePushNotifications();

server.on('listening', () => {
    logger.info(`${chalk.green('✓')} App is running at http://localhost:${app.get('port')} in ${app.get('env')} mode`);
});

server.on('error', (err) => {
    logger.error('Error in the server: ' + err.message);
    process.exit(err.statusCode);
});