const mongoose = require('mongoose');

const faqSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    question: { type: String, required: true, minlength: 1, maxlength: 1000 },
    answer: { type: String, required: false, minlength: 1 },
    status: { type: Boolean, default: true },
    created_by: { type: mongoose.Schema.Types.ObjectId, required: true, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

faqSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

faqSchema.index({ question: 1, deleted_at: 1 });

module.exports = mongoose.model('Faq', faqSchema);