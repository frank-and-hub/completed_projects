const mongoose = require('mongoose');

const aboutDetailSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, required: true, ref: 'User' },
    title: { type: String, required: true },
    bio: { type: String, default: null },
    experience: { type: String, default: null },
    resume: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'File' },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

aboutDetailSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

aboutDetailSchema.index({ user: 1, deleted_at: 1 });

module.exports = mongoose.model('AboutDetail', aboutDetailSchema);