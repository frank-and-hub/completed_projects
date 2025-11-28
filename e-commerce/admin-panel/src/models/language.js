const mongoose = require('mongoose');
const bcrypt = require('bcrypt');
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const { makeSlug } = require('../utils/helper');

const languageSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
    dial_code: { type: String, default: null, minlength: 2, maxlength: 10 },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});


languageSchema.pre('save', async function (next) {
    this.slug = makeSlug(this.name);
    next()
});

languageSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

languageSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Language', languageSchema);
