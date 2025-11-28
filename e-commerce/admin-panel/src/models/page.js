const mongoose = require('mongoose');

const pageSchema = new mongoose.Schema({
    _id: { type: mongoose.Schema.Types.ObjectId, auto: true },
    type: {
        type: String,
        enum: [
            'about_us',
            'return_policy',
            'terms_and_conditions',
        ]
    },
    description: { type: String, required: true, trim: true, minlength: 10, maxlength: 10000 },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    deleted_at: { type: Date, default: null } 
}, {
    timestamps: true
});

pageSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

pageSchema.index({ name: 1, deleted_at: 1 });

module.exports = mongoose.model('Page', pageSchema);