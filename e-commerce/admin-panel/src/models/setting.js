const mongoose = require('mongoose');

const settingSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    color: { type: String, required: false },
    background: { type: String, required: false },
    theme: { type: String, enum: ['dark', 'light'], default: 'light' },
    filter: { type: String, enum: ['sepia', 'contrast', 'saturate', 'invert', 'grayscale', 'opacity', 'hue-rotate', 'blur', 'drop-shadow', 'brightness', 'none'], default: 'none' },
    isSidebarToggled: { type: Boolean, default: false },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

settingSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

settingSchema.index({ deleted_at: 1 });

module.exports = mongoose.model('Setting', settingSchema);