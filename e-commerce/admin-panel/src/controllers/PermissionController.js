const mongoose = require('mongoose');

const Menu = require('../models/menu');
const Permission = require('../models/permission');

// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
const constName = 'permissions/';

exports.index = async (req, res, next) => {
    try {

        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        const { menu_id, status, search } = req.query;

        if (menu_id) filter.menu = menu_id;
        if (status) filter.status = status;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const skip = (page - 1) * limit;
        const totalCount = await Permission.countDocuments({
            ...filter
        });

        const query = Permission.find(filter)
            .select('_id name menu status updated_by slug')

            .populate('menu', '_id name')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');
        if (req?.query?.page != 0) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const permissions = await query;

        if (permissions.length === 0) return res.status(200).json({ message: `No permissions found`, data: [] });

        const permissionPromises = permissions.map(async (permission) => {
            const { _id, name, menu, status, updated_by, slug} = permission;
            return {
                'id': _id,
                'name': name,
                'menu': menu,
                'status': status,
                'slug': slug,
                'updated_by': updated_by
            }
        });
        const permissionResponses = await Promise.all(permissionPromises);
        res.status(200).json({
            message: `List retrieved successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: permissionResponses
            }, title: 'Permission'
        });
    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create permission form`,
            body: {
                'name': 'String',
                'menu': 'SchemaId'
            },
            title: 'Add permission'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    const { name, menu } = req.body;
    try {
        const findMenu = await Menu.findById(menu).select('_id name').where('status').equals(status_active);
        if (!findMenu) return res.status(404).json({ message: `Menu not found!`, });

        const existsPermission = await Permission.findOne({ name: name, menu: menu, status: status_active });
        if (existsPermission) return res.status(200).json({ message: 'Permission already exists' });

        const permission = new Permission({
            _id: new mongoose.Types.ObjectId(),
            name,
            menu: findMenu._id
        });
        const newData = await permission.save();
        const response = {
            'id': newData?._id,
            'name': newData?.name,
            'menu': findMenu?.name
        }
        res.status(201).json({ message: `Successfully created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const permissionData = await this.findData(id, res);
        const { _id, name, menu, updated_by, status } = permissionData;
        const result = {
            'id': _id,
            'name': name,
            'menu': menu,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Permission data found`, data: result, title: `View ${name} permission detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const permissionData = await this.findData(id, res);
        const { _id, name, menu, updated_by, status } = permissionData;
        const result = {
            'id': _id,
            'name': name,
            'menu': menu,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Permission data found`, data: result, title: `Edit ${name} permission detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const permission = await this.findData(id, res);
        if (!permission) return res.status(404).json({ message: `Permission not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (permission may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        const result = await Permission.updateOne({ _id: permission._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedPermission = await this.findData(id, res);
            const { _id, anme, menu } = updatedPermission;
            const PermissionData = {
                'id': _id,
                'name': name,
                'menu': menu
            }
            return res.status(200).json({ message: `Permission details updated successfully`, data: PermissionData });
        }
        res.status(404).json({ message: `Permission not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getPermission = await Permission.findById(id).select('_id').where('status').equals(!status_active);
        if (!getPermission) return res.status(404).json({ message: 'Permission not found' });

        // const permissionData = await Permission.deleteOne({ _id: id });
        // if (permissionData.deletedCount === 1) {

        const permissionData = await Permission.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (permissionData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'menu': 'Menu ID'
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Permission not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Permission.findById(identifier): Permission.findOne({ slug: identifier });
    
    const permissionData = await query
        .select('_id name menu updated_by status slug')
        .populate('menu', '_id name')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!permissionData) return res.status(404).json({ message: `Permission not found` });
    return permissionData;
}