let io;

const getSocketIoInstance = () => {
    return io;
}

const initSockets = (http) => {
    if (!!io) {
        return;
    }
    /*   app.use(function (req, res, next) {
          res.header("Access-Control-Allow-Origin", "*");
          res.setHeader("Access-Control-Allow-Headers", "Authorization, Access-Control-Allow-Headers, Origin, Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
          next();
      }); */

    io = require('socket.io')(http);

    io.on('connection', function (socket) {
        console.log('a user connected');

        socket.on('disconnect', function () {
            console.log('user disconnected');
        });
    });
}

const emitToNamespace = (namespace, data) => {
    const ioNamsepace = namespace || '/';
    console.log('emiting to', namespace, data);
    io.of(namespace).emit('notification', data);
}

module.exports = {
    initSockets,
    emitToNamespace
}