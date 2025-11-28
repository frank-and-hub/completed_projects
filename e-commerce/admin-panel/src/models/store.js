const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const storeSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
    phone: { type: Number, required: true, minlength: 8, maxlength: 12 },
    email: { type: String, required: true, unique: true, match: [emailRegex, 'Please provide a valid email address'] },
    address: { type: Object, default: null },
    city: { type: String, default: null, maxlength: 100 },
    state: { type: String, default: null, maxlength: 100 },
    zipcode: { type: Number, default: null, maxlength: 10 },
    country: { type: String, default: null, maxlength: 100 },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    supplier: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

storeSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

storeSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

storeSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

storeSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

storeSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Store', storeSchema);