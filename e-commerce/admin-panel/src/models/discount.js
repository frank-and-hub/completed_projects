const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const discountSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    percentage: { type: Number, required: true, trim: true },
    description: { type: String, required: true, trim: true },
    user: { type: mongoose.Schema.Types.ObjectId, required: true, ref: 'User' },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

discountSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

discountSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

discountSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

discountSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

discountSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Discount', discountSchema);
