const mongoose = require('mongoose');

const returnPolicySchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    info: { type: String, required: true, trim: true, minlength: 10, maxlength: 10000 },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

returnPolicySchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

returnPolicySchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('RetunPolicy', returnPolicySchema);