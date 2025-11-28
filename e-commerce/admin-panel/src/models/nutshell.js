const mongoose = require('mongoose');

const nutshellSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    module_name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    url: { type: String, required: true },
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: false},
    status: { type: Boolean, default: true },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

nutshellSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

nutshellSchema.index({ user: 1, deleted_at: 1 });

module.exports = mongoose.model('Mutshell', nutshellSchema);
