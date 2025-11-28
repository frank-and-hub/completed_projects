const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const warrantySchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
    description: { type: String, required: false, trim: true },
    duration: { type: Number, required: true },
    period: {
        type: String,
        default: 'month',
        enum: ['day', 'week', 'month', 'year',],
        required: true
    },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

warrantySchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

warrantySchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

warrantySchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

warrantySchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

warrantySchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Warranty', warrantySchema);