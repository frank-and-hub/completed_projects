const mongoose = require('mongoose');

const bcrypt = require('bcrypt');
const { makeSlug } = require('../utils/helper');
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const userSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    name: {
        first_name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
        middle_name: { type: String, required: false, trim: true, set: (value) => value.toLowerCase() },
        last_name: { type: String, required: true, trim: true, set: (value) => value.toLowerCase() },
    },
    dial_code: { type: String, default: null, minlength: 2, maxlength: 3 },
    phone: { type: Number, default: null, minlength: 8, maxlength: 12, unique: true, },
    email: { type: String, required: true, unique: true, match: [emailRegex, 'Please provide a valid email address'] },
    password: { type: String, required: true },
    password_text: { type: String, required: true, minlength: 8, maxlength: 16, unique: true, },
    role: { type: mongoose.Schema.Types.ObjectId, ref: 'Role', required: false },
    image: { type: mongoose.Schema.Types.ObjectId, ref: 'File' },
    gender: { type: String, enum: ['male', 'female', 'other'], default: 'male' },
    address: { type: String, default: null },
    about: { type: String, default: null },
    city: { type: String, default: null, maxlength: 100 },
    state: { type: String, default: null, maxlength: 100 },
    zipcode: { type: Number, default: null, maxlength: 10 },
    country: { type: String, default: null, maxlength: 100 },
    token: [{ type: mongoose.Schema.Types.ObjectId, ref: 'Token', required: false }],
    terms: { type: Boolean, default: true },
    slug: { type: String, required: true, unique: true, index: true },
    status: { type: Boolean, default: true },
    timezone: { type: String, default: null },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

userSchema.pre('save', async function (next) {
    if (this.isModified('password') || this.isNew) {
        this.password = await bcrypt.hash(this.password, 10);
    }

    this.slug = makeSlug(`${this.name?.first_name}-${this.name?.middle_name??''}-${this.name?.last_name ?? ''}`);
    next()
});

userSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

userSchema.virtual('full_name').get(function () {
    return `${this.name?.first_name} ${this.name?.middle_name ? this.name?.middle_name + ' ' : ''}${this.name?.last_name}`;
});

// Static method to get user by ID and active status
userSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

userSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

userSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('User', userSchema);
