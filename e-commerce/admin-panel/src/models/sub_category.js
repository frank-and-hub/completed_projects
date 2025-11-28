const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const subCategorySchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    category: { type: mongoose.Schema.Types.ObjectId, ref: 'Category', required: true },
    code: { type: String, required: true },
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

subCategorySchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

subCategorySchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

subCategorySchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

subCategorySchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

subCategorySchema.index({ name: 1, category: 1, deleted_at: 1 });

module.exports = mongoose.model('SubCategory', subCategorySchema);