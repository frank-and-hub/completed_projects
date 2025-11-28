const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const productSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    description: { type: String, required: true, trim: true },
    specification: { type: String, required: true, trim: true },
    code: { type: String, required: true, trim: true },
    price: { type: Number, required: true },
    quantity: { type: Number, required: true },
    image: { type: mongoose.Schema.Types.ObjectId, ref: 'File', required: true },
    discount: { type: mongoose.Schema.Types.ObjectId, ref: 'Discount', required: true },
    unit: { type: mongoose.Schema.Types.ObjectId, ref: 'Unit', required: true },
    warranty: { type: mongoose.Schema.Types.ObjectId, ref: 'Warranty', required: true },
    brand: { type: mongoose.Schema.Types.ObjectId, ref: 'Brand', required: true },
    tags: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Tag', required: true }],
    product_images: [{ type: mongoose.Schema.Types.ObjectId, ref: 'File', required: false }],
    categories: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Category', required: true }],
    colors: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Color', required: true }],
    manufactured_date: { type: Date, required: true },
    expiry_date: { type: Date, required: true },
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

productSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

productSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

productSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

productSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

productSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Product', productSchema);
