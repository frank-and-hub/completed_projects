const mongoose = require('mongoose');

const orderSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    address: { type: Object, default: null },
    price: { type: Number, required: true },
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    cart: { type: mongoose.Schema.Types.ObjectId, ref: 'Cart', required: true },
    coupon: { type: mongoose.Schema.Types.ObjectId, ref: 'Coupon', required: false },
    time: { type: Date, required: true },
    status: {
        type: String, default: 'placed',
        enum: [
            'placed',         // order has been placed
            'pending',        // order is awaiting confirmation or processing
            'paymentFailed',  // Payment failed
            'readyToShip',    // Ready for shipping
            'shipped',        // order has been dispatched
            'dispatched',     // order has been sent out for delivery
            'outofDelivery',  // order is out for delivery
            'delivered',      // order has been delivered successfully
            'cancelled',      // order was cancelled by the user or system
            'returned',       // order has been returned by the customer
            'refunded',       // Payment was refunded
            'outOfStock'      // Product is out of stock and can't be fulfilled
        ]
    },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

orderSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

orderSchema.index({ cart: 1, user: 1, deleted_at: 1 });

module.exports = mongoose.model('Order', orderSchema);