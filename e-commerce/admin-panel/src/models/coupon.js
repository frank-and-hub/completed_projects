const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const couponSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    discount: { type: mongoose.Schema.Types.ObjectId, ref: 'Discount', required: true },
    code: { type: String, required: true },
    start_date: { type: Date, required: true },
    end_date: { type: Date, required: true },
    limit: { type: Number, required: true },
    once_per_customer: { type: Boolean, required: true, default: true },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

couponSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

couponSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

couponSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

couponSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};
couponSchema.index({ cart: 1, user: 1, deleted_at: 1 });

module.exports = mongoose.model('Coupon', couponSchema);