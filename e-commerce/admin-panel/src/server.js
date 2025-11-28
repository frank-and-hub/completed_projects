const http = require(`http`);
const app = require(`./core/app`);
const config = require(`../src/config/app.config`).server;
const server = http.createServer(app);

config
  ? server.listen(config.port, () => {
      console.log(``, new Date());
    })
  : console.log(`Database error`);