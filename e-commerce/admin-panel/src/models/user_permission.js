const mongoose = require('mongoose');

const userPermissionSchema = new mongoose.Schema({
    _id: mongoose.Schema.Types.ObjectId,
    user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
    menu: { type: mongoose.Schema.Types.ObjectId, ref: 'Menu', required: true },
    view: { type: Boolean, default: false, Comment: '1:yes,0:no' },
    add: { type: Boolean, default: false, Comment: '1:yes,0:no' },
    edit: { type: Boolean, default: false, Comment: '1:yes,0:no' },
    delete: { type: Boolean, default: false, Comment: '1:yes,0:no' },
    status: { type: Boolean, default: true },
    updated_by: { type: mongoose.Schema.Types.ObjectId, required: false, ref: 'User' },
    deleted_at: { type: Date, default: null }
}, {
    timestamps: true
});

userPermissionSchema.pre(/^find/, function (next) {
    this.where({ deleted_at: null });
    next();
});

userPermissionSchema.statics.findActiveById = function(id, status) {
  return this.findById(id).select('_id').where('status').equals(status);
};

userPermissionSchema.statics.findActiveBySlug = function(slug, status) {
  return this.findOne({ slug, status });
};

userPermissionSchema.index({ user: 1, menu: 1, deleted_at: 1 });

module.exports = mongoose.model('UserPermission', userPermissionSchema);
