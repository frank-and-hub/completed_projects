const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const fileSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    path: { type: String, required: true },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

fileSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

fileSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

fileSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

fileSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

fileSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('File', fileSchema);