const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const roleSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, unique: true, set: (value) => value.toLowerCase() },
    permissions: [{ type: mongoose.Schema.Types.ObjectId, required: false, ref: 'Permission' }],
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

roleSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

roleSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

roleSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

roleSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

roleSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Role', roleSchema);
