const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const tagSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

tagSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

tagSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

tagSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

tagSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

tagSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Tag', tagSchema);