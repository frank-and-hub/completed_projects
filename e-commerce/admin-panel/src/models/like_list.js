const mongoose = require('mongoose');

const likeListSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: false },
    product: { type: mongoose.Schema.Types.ObjectId, ref: 'Product', required: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

likeListSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

likeListSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('LikeList', likeListSchema);