const mongoose = require('mongoose');
const { makeSlug } = require('../utils/helper');

const countrySchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    name: { type: String, required: true },
    dialCode: { type: String, required: true },
    region: { type: String, required: true },
    subregion: { type: String, required: false },
    population: { type: Number, required: true },
    cities: [{ type: String, required: true }],
    states: [{ type: String, required: true }],
    currencies: [{ type: String, required: true }],
    currencySymbols: [{ type: String, required: true }],
    flags: {
        png: { type: String, required: true },
        svg: { type: String, required: true },
    },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

countrySchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

countrySchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

countrySchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

countrySchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

countrySchema.index({ _id: 1, name: 1, dialCode: 1, deleted_at: 1 });

module.exports = mongoose.model('Country', countrySchema);