const mongoose = require('mongoose');

const aboutUsSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    info: { type: String, required: true, trim: true, minlength: 10, maxlength: 10000 },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

aboutUsSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

aboutUsSchema.index({ deleted_at: 1 });

module.exports = mongoose.model('AboutUs', aboutUsSchema);
