const mongoose = require('mongoose');

const verifyOtpSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    otp: { type: String, required: true, unique: true },
    generated_at: { type: Date, required: true },
    expired_at: { type: Date, required: true },
    verified_at: { type: Date, required: false, default: null },
    type: { type: String, enum: ['email', 'phone',] },
    verifyType: { type: String, default: 'general', enul: ['general', 'forget'] },
    status: { type: String, default: 'pending', enum: ['verified', 'pending', 'expired',] },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

verifyOtpSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

verifyOtpSchema.index({ user: 1, deleted_at: 1 });

module.exports = mongoose.model('VerifyOtp', verifyOtpSchema);
