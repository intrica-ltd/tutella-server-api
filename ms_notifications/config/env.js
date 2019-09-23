"use restrict";

const getEnv = require('dotenv').config().parsed;

if (getEnv.error) {
    throw getEnv.error
}

const HTTP_PORT         = getEnv.PORT || '4000';
const basicRouteMessage = getEnv.BASIC_ROUTE_MSG || 'MS Notifications';
const URL               = getEnv.URL || 'http://localhost';
const MONGO_URI         = getEnv.MONGO_URI || 'mongodb://127.0.0.1/ms_notifications';
const GCLOUD_PROJECT     = getEnv.GCLOUD_PROJECT || 'tutella-3779a';
const GCLOUD_DB_URL       = getEnv.GCLOUD_DB_URL || 'https://tutella-3779a.firebaseio.com';

const TUTELLA_DOMAIN     = getEnv.TUTELLA_DOMAIN || 'http://tutella-ms-domain.local/api';
const TUTELLA_AUTH_SERVICE_URL = getEnv.TUTELLA_AUTH_SERVICE_URL || 'http://localhost';

module.exports = {
    HTTP_PORT,
    basicRouteMessage,
    URL,
    MONGO_URI,
    TUTELLA_DOMAIN,
    GCLOUD_DB_URL,
    GCLOUD_PROJECT,
    TUTELLA_AUTH_SERVICE_URL
};