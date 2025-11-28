module.exports = {
    apps: [
      {
        name: 'backend',
        script: 'npm run start:prod',
        cwd: './klub-business-back',
        instances: 1,
        autorestart: true,
        watch: false,
        env: {
          NODE_ENV: 'production',
          PORT: 5000
        }
      },
      {
        name: 'frontend',
        script: 'npm run start:prod',
        cwd: './klub-business-front',
        instances: 1,
        autorestart: true,
        watch: false,
        env: {
          NODE_ENV: 'production',
          PORT: 3000
        }
      }
    ]
  };
  