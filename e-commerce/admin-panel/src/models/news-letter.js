const mongoose = require('mongoose');

const NewsLetterSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    email: { type: String, default: null, unique: true },
    ip_address: { type: String, required: true, maxlength: 100 },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

NewsLetterSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

NewsLetterSchema.index({ email: 1, deleted_at: 1 });

module.exports = mongoose.model('NewsLetter', NewsLetterSchema);