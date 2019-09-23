const winston = require('winston');
const { combine, timestamp, printf } = winston.format;

const myFormat = printf(info => {
    return `${info.timestamp} [${info.level}]: ${info.message}`;
});

const logger = winston.createLogger({
    level: 'info',
    format: combine(
        timestamp(),
        myFormat
    ),
    transports: [
        new winston.transports.Console()
    ]
});

module.exports = logger;