const mongoose = require('mongoose');

const CartSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    products: [{
        product: { type: mongoose.Schema.Types.ObjectId, ref: 'Product', required: true },
        quantity: { type: Number, required: true, default: 1 }
    }],
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

CartSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

CartSchema.index({ user: 1, deleted_at: 1 });

module.exports = mongoose.model('Cart', CartSchema);