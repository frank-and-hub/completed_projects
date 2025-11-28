const mongoose = require('mongoose');
const secrets = require('../config/secrets');

// Function to connect to MongoDB
const connectDB = async () => {
    try {
        // mongodb+srv://admin:vNE6FrO7FFBkCtPN@admin.cxxgw.mongodb.net/?retryWrites=true&w=majority&appName=admin
        const conn = await mongoose.connect(secrets.dbUrl, {
            serverSelectionTimeoutMS: 10000
        });
    } catch (err) {
        console.error(`Error: ${err.message}`);
        process.exit(1);
    }
}
module.exports = connectDB;