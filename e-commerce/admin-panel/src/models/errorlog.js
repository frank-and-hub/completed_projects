const mongoose = require('mongoose');

const errorLogSchema = new mongoose.Schema({
    route: { type: String, required: true },
    errorType: { type: String },
    statusCode: { type: Number, required: true },
    IP: { type: String, required: true },
    errorMessage: { type: String, required: true },
    stackTrace: { type: String },
    status: { type: Boolean, default: false },
}, {
    timestamps: true
});

errorLogSchema.index({ errorType: 1, IP: 1 });

module.exports = mongoose.model('ErrorLog', errorLogSchema);
