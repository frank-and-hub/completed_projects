const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const categorySchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
    description: { type: String, required: false, trim: true },
    icon: { type: String, required: true },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

categorySchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

categorySchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

categorySchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

categorySchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

categorySchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Category', categorySchema);