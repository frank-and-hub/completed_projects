const mongoose = require('mongoose');

const tokenSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: false },
    token: { type: String, required: true },
    loginTime: { type: Date, default: Date.now, required: true },
    ip_address: { type: String, required: true, maxlength: 100 },
    status: { type: Boolean, default: true },
}, {
    timestamps: true
});

tokenSchema.index({ token: 1, user: 1 });

module.exports = mongoose.model('Token', tokenSchema);
