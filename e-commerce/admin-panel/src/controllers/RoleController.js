const mongoose = require('mongoose');

const Role = require('../models/role');
const User = require('../models/user');
const Permission = require('../models/permission');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'roles/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { status, search } = req.query;

        if (status) filter.status = status;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Role.countDocuments({
            ...filter
        });

        const query = Role.find(filter)
            .select('_id name permissions status updated_by slug')
            .populate({
                path: 'permissions',
                select: '_id name menu',
                populate: {
                    path: 'menu',
                    select: '_id name'
                }
            })
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const roles = await query;

        if (roles.length === 0) return res.status(200).json({ message: `No roles found`, data: [] });

        const rolePromises = roles.map(async (role) => {
            return {
                'id': role._id,
                'name': role?.name,
                'permissions': role.permissions.map((per) => ({
                    'id': per._id,
                    'name': per.name,
                    'menu': per.menu.name
                })),
                'users_count': await User.countDocuments({ role: role?._id }),
                'status': role?.status,
                'slug' : role?.slug
            }
        });
        const roleResponses = await Promise.all(rolePromises);
        res.status(200).json({
            message: `Roles list retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: roleResponses
            }, title: 'Role'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create role form`,
            body: {
                'name': 'String',
                'permissions': 'Array of SchemaId',
            },
            title: 'Add role'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, permissions } = req.body;
    try {
        const existsRole = await Role.findOne({ name: name, status: status_active });
        if (existsRole) return res.status(200).json({ message: 'Role already exists' });

        const foundPermissions = await Permission.find({ _id: { $in: permissions }, status: status_active }).select('_id name');
        if (foundPermissions.length !== permissions.length) return res.status(404).json({ message: 'One or more active permissions not found' });

        const role = new Role({
            _id: new mongoose.Types.ObjectId(),
            name,
            permissions: foundPermissions.map(p => p._id)
        });

        const newData = await role.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'permissions': foundPermissions.map(perm => ({
                'id': perm._id,
                'name': perm.name,
                'menu': perm.menu
            }))
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const roleData = await this.findData(id, res);
        const result = {
            'id': roleData._id,
            'name': roleData?.name,
            'permissions': await helper.filterData(roleData?.permissions),
            'status': roleData?.status,
            'updated_by': roleData?.updated_by
        }
        res.status(200).json({ message: `Role data found`, data: result, title: `View ${roleData?.name} role detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const roleData = await this.findData(id, res);
        const result = {
            'id': roleData._id,
            'name': roleData?.name,
            'permissions': await helper.filterData(roleData.permissions),
            'status': roleData?.status,
            'updated_by': roleData?.updated_by
        }
        res.status(200).json({ message: `Role data found`, data: result, title: `Edit ${roleData?.name} role detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const role = await this.findData(id, res);
        if (!role) return res.status(404).json({ message: `Role not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (role may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const permissionsId = updateOps['permissions'];

        const permissions = await Permission.find({ _id: { $in: permissionsId } }).select('_id name');
        if (permissionsId && permissions?.length !== permissionsId?.length) return res.status(404).json({ message: 'One or more permissions not found' });

        const result = await Role.updateOne({ _id: role._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedRole = await this.findData(id, res);
            const result = {
                'id': updatedRole._id,
                'name': updatedRole?.name,
                'permissions': updatedRole.permissions.map(per => ({
                    'id': per._id,
                    'name': per.name,
                    'menu': per.menu.name
                }))
            }
            return res.status(200).json({ message: `Role details updated successfully`, data: result });
        }
        res.status(404).json({ message: `Role not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getRole = await Role.findById(id).select('_id').where('status').equals(!status_active);
        if (!getRole) return res.status(404).json({ message: 'Role not found' });

        // const roleData = await Role.deleteOne({ _id: id });
        // if (roleData.deletedCount === 1) {

        const roleData = await Role.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (roleData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'permissions': 'array of SchemaID'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Role not found` });
    } catch (err) { next(err) }
}

exports.getGuestRole = async (string) => {
    try {
        const role = await Role.findOne({ name: string }).select('_id');
        if (!role) {
            const newData = new Role({
                _id: new mongoose.Types.ObjectId(),
                name: string,
                permissions: []
            });
            const savedRole = await newData.save();
            return savedRole._id;
        }
        return role._id;
    } catch (err) {
        throw err;
    }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Role.findById(identifier): Role.findOne({ slug: identifier });
    
    const roleData = await query
        .select('_id name permissions updated_by status slug')
        .populate({
            path: 'permissions',
            select: '_id name menu',
            populate: {
                path: 'menu',
                select: '_id name'
            }
        })
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!roleData) return res.status(404).json({ message: `Role not found` });
    return roleData;
}