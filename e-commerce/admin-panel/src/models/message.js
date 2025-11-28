const mongoose = require('mongoose');

const messageSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    sender_id: { type: mongoose.Schema.Types.ObjectId, required: true, ref: 'User' },
    receiver_id: { type: mongoose.Schema.Types.ObjectId, required: true, ref: 'User' },
    message: { type: String, required: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

messageSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

messageSchema.index({ sender_id: 1, receiver_id: 1, deleted_at: 1 });

module.exports = mongoose.model('Message', messageSchema);
