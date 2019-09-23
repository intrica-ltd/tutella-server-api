const bodyParser = require('body-parser');
const helmet = require('helmet');
const env = require('./../config/env');
const request = require('request');

const applyApiMiddleware = (app) => {
    app.use(helmet());
    app.use(bodyParser.json());
    app.use(bodyParser.urlencoded({ extended: false }));

   /*  app.use(function (req, res, next) {
        res.header("Access-Control-Allow-Origin", "*");
        res.setHeader("Access-Control-Allow-Headers", "Authorization, Access-Control-Allow-Headers, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
        next();
    }); */

    // Oauth check requests from microservices
    app.use(function (req, res, next) {
        const headers = {
            'Authorization': req.headers.authorization
        };
        request.get(env.TUTELLA_AUTH_SERVICE_URL, { headers }, function (error, response, body) {
            if (error || response.statusCode !== 200) {
                return res.status(401).json({ error: 'Unauthorized' });
            }
            next();
        });
    });

    // Basic Route
    app.get('/', (req, res) => {
        res.json({
            name: env.basicRouteMessage
        });
    });
}

module.exports = applyApiMiddleware;


