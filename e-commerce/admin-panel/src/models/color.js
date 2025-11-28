const mongoose = require('mongoose');

const colorSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    hex_code: { type: String, required: true, trim: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

colorSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

colorSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Color', colorSchema);