const mongoose = require('mongoose');

const termsAndConditionsSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    t_and_c: { type: String, required: true, trim: true, minlength: 10, maxlength: 10000 },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

termsAndConditionsSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

termsAndConditionsSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

termsAndConditionsSchema.index({ t_and_c: 1, deleted_at: 1 });

module.exports = mongoose.model('TermsAndConditions', termsAndConditionsSchema);
