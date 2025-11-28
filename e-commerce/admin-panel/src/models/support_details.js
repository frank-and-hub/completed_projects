const mongoose = require('mongoose');

const supportDetailSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    cell: { type: String, required: [true, 'Cell field is required'], trim: true, unique: true },
    email: { type: String, required: [true, 'Email field is required'], trim: true, lowercase: true, match: [/^\S+@\S+\.\S+$/, 'Please provide a valid email address'] },
    address: { type: String, default: null, trim: true },
    type: { type: String, enum: ['call', 'chat'], default: 'call' },
    hours_start: { type: String, default: null, trim: true },
    hours_end: { type: String, default: null, trim: true }, 
    week_start: { type: String, default: null, trim: true }, 
    week_end: { type: String, default: null, trim: true }, 
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

supportDetailSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

supportDetailSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

supportDetailSchema.index({ email: 1, deleted_at: 1 });

module.exports = mongoose.model('SupportDetail', supportDetailSchema);
